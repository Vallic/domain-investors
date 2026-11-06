<?php

declare(strict_types=1);

namespace Drupal\domain_investors\Installer;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\CsvExportInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\MarketplaceInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\RegistrarInterface;
use Drupal\commerce_domains\ProviderPluginManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\domain_investors_tools\DemoContent;
use Drupal\domain_investors_theme\Appearance;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The installer's questions: store, currency, payments, connections and looks.
 *
 * Runs once recipes/portfolio_base is applied, so the registrars and
 * marketplaces it offers are the ones Commerce Domains has plugins for. The
 * answers decide which optional recipes the next step applies, and what
 * Finisher sets up after them.
 *
 * Without the web installer, drush passes the answers as form values:
 *
 * @code
 * drush site:install domain_investors \
 *   domain_investors_setup.currency=EUR \
 *   domain_investors_setup.payments=stripe,bank_transfer \
 *   domain_investors_setup.registrars=dynadot,namecheap \
 *   domain_investors_setup.marketplaces=afternic,sedo \
 *   domain_investors_setup.extras=search,vallic_cloud \
 *   domain_investors_setup.template=showcase \
 *   domain_investors_setup.scheme=brass
 * @endcode
 */
final class SetupForm extends FormBase {

  // Where the answers wait for the next install tasks.
  public const STATE_KEY = 'domain_investors.setup';

  // The recipe each payment choice applies.
  public const PAYMENT_RECIPES = [
    'stripe' => 'portfolio_payments_stripe',
    'paypal' => 'portfolio_payments_paypal',
    'bank_transfer' => 'portfolio_payments_bank_transfer',
    'wise' => 'portfolio_payments_wise',
    'escrow' => 'portfolio_escrow',
    'lease' => 'portfolio_lease',
  ];

  // The recipe each extra applies.
  public const EXTRA_RECIPES = [
    'search' => 'portfolio_search',
    'meilisearch' => 'portfolio_search_meilisearch',
    'vallic_cloud' => 'portfolio_vallic_cloud',
    'eu_invoicing' => 'portfolio_eu_invoicing',
    'google_tag' => 'portfolio_google_tag',
  ];

  // Symfony Mailer's override manager; see buildForm().
  protected const MAILER_OVERRIDES = 'Drupal\mailer_override\OverrideManagerInterface';

  // Currencies offered first; any other ISO 4217 code is accepted too.
  protected const COMMON_CURRENCIES = [
    'USD', 'EUR', 'GBP', 'AUD', 'CAD', 'CHF', 'JPY', 'SGD',
    'HKD', 'CNY', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'NZD',
  ];

  public function __construct(
    protected ProviderPluginManager $providerPluginManager,
    protected CountryManagerInterface $countryManager,
    protected StateInterface $state,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.commerce_domains_provider'),
      $container->get('country_manager'),
      $container->get('state'),
    );
  }

  /**
   * Returns the answers saved by the form, for the install tasks after it.
   *
   * @return array<string, mixed>
   *   The answers, or an empty array before the form was submitted.
   */
  public static function answers(): array {
    return \Drupal::state()->get(self::STATE_KEY, []);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'domain_investors_setup';
  }

  /**
   * Loads Mailer Override's definitions before its form alter does.
   *
   * It filters them by what is enabled only once they are cached, and during
   * installation they are not: loaded first by its form alter, unfiltered,
   * they make it warn about a missing "metadata_key" (symfony_mailer 2.0.2).
   * Loaded by an installer form first, they are filtered by the time it runs.
   */
  public static function loadMailerOverrides(): void {
    if (\Drupal::hasService(self::MAILER_OVERRIDES)) {
      \Drupal::service(self::MAILER_OVERRIDES)->getDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed> $form
   * @phpstan-param array<mixed>|null $install_state
   * @phpstan-return array<mixed>
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array &$install_state = NULL): array {
    self::loadMailerOverrides();

    // Drush passes lists as "a,b"; the checkboxes want arrays.
    if ($form_state->isProgrammed()) {
      $input = &$form_state->getUserInput();
      foreach (['payments', 'registrars', 'marketplaces', 'extras'] as $name) {
        if (isset($input[$name]) && is_string($input[$name])) {
          $list = array_values(array_filter(array_map('trim', explode(',', $input[$name]))));
          $input[$name] = $list ? array_combine($list, $list) : [];
        }
      }
    }

    // Without overrides: during installation the site name reads as the
    // distribution's (core's installer override), not the one just entered.
    $site = $this->configFactory()->getEditable('system.site');
    $form['#title'] = $this->t('Your domain store');

    $form['store'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Store'),
    ];
    $form['store']['store_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Store name'),
      '#description' => $this->t('Shown to buyers in checkout and in their emails.'),
      '#default_value' => $site->get('name'),
      '#required' => TRUE,
      '#parents' => ['store_name'],
    ];
    $form['store']['store_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Store email'),
      '#description' => $this->t('Order emails come from this address, and sales, offers and expiring domains are reported to it.'),
      '#default_value' => $site->get('mail'),
      '#required' => TRUE,
      '#parents' => ['store_email'],
    ];
    $country = (string) $this->config('system.date')->get('country.default');
    $form['store']['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#description' => $this->t('Where the store is, for its address and taxes.'),
      '#options' => $this->countryManager->getList(),
      '#default_value' => $country !== '' ? $country : 'US',
      '#required' => TRUE,
      '#parents' => ['country'],
    ];
    $form['store']['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency'),
      '#description' => $this->t('The currency your domains are priced in. More can be added later.'),
      '#options' => $this->currencyOptions(),
      '#default_value' => 'USD',
      '#required' => TRUE,
      '#parents' => ['currency'],
    ];

    $form['payments'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Payments'),
      '#description' => $this->t('Gateways start enabled and in test mode. The next step asks for their keys; any you leave out get samples, to replace later in .env: see docs/payments.md.'),
      '#options' => [
        'stripe' => $this->t('Cards, with Stripe'),
        'paypal' => $this->t('PayPal'),
        'bank_transfer' => $this->t('Bank transfer, which you mark paid when the money arrives'),
        'wise' => $this->t('Wise, paid into your Wise business account and recorded when Wise reports it'),
        'escrow' => $this->t('Escrow.com, for buy now and for offers negotiated there'),
        'lease' => $this->t('Lease to own: a down payment, then monthly payments, by card with Stripe or by bank transfer or Wise'),
      ],
      '#default_value' => ['stripe', 'bank_transfer'],
    ];

    $registrars = $this->pluginOptions([RegistrarInterface::class]);
    $form['registrars'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Where your domains are registered'),
      '#description' => $this->t('Each is added with keys that read its API credentials from environment variables, so registrations, expiry dates and handovers can follow the account. One with sample keys starts disabled.'),
      '#options' => $registrars,
      '#default_value' => [],
    ];
    $form['other_registrars'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other registrars'),
      '#description' => $this->t('Registrars without an API here, such as Hover, separated by commas. Domains can be recorded there; nothing is synced.'),
      '#default_value' => '',
      '#maxlength' => 512,
    ];
    $form['marketplaces'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Marketplaces you list on'),
      '#description' => $this->t('Syncs push your catalog to them once you have reviewed a dry run; marketplaces without an API take CSV files.'),
      '#options' => $this->pluginOptions([MarketplaceInterface::class, CsvExportInterface::class]),
      '#default_value' => [],
    ];
    $form['other_marketplaces'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other marketplaces'),
      '#description' => $this->t('Marketplaces you list on by hand, separated by commas. Listings and sales there are recorded; nothing is pushed.'),
      '#default_value' => '',
      '#maxlength' => 512,
    ];

    $form['extras'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Also set up'),
      '#options' => [
        'search' => $this->t('Domain search, with Search API'),
        'meilisearch' => $this->t('Domain search on Meilisearch rather than the database (DDEV and Vallic Cloud run it)'),
        'vallic_cloud' => $this->t('Vallic Cloud: Redis cache and Varnish and CDN purging'),
        'eu_invoicing' => $this->t('EU VAT and invoices'),
        'google_tag' => $this->t('Google Tag Manager or Analytics, with ecommerce events; on once GOOGLE_TAG_ID is set'),
        'demo' => $this->t('Demo content: 200 example domains to try the site with, which you remove before you open it'),
      ],
      // On Vallic Cloud the platform's variables are there; Meilisearch where
      // it runs beside the site, as on DDEV and Vallic Cloud.
      '#default_value' => array_merge(
        ['search'],
        getenv('VALLIC_ENVIRONMENT') !== FALSE ? ['vallic_cloud'] : [],
        getenv('MEILISEARCH_HOST') ? ['meilisearch'] : [],
      ),
    ];

    // Real domains of the project's makers: not for a site open to buyers.
    if (!DemoContent::isAvailable()) {
      unset($form['extras']['#options']['demo']);
    }

    // The theme's template and colour scheme, which the base recipe
    // installed. Both can be changed later under Appearance, and each domain
    // can choose its own.
    $form['appearance'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Appearance'),
    ];
    $form['appearance']['template'] = [
      '#type' => 'radios',
      '#title' => $this->t('Template'),
      '#options' => Appearance::templateOptions(TRUE),
      '#default_value' => 'standard',
      '#parents' => ['template'],
    ];
    $form['appearance']['scheme'] = [
      '#type' => 'radios',
      '#title' => $this->t('Colour scheme'),
      '#options' => Appearance::schemeOptions(),
      '#default_value' => 'light',
      '#parents' => ['scheme'],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save and continue'),
        '#button_type' => 'primary',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed> $form
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // A lease is paid by card with Stripe, or each month by bank transfer or
    // Wise: one of them has to be there.
    $payments = array_filter((array) $form_state->getValue('payments'));
    if (isset($payments['lease']) && !array_intersect_key($payments, array_flip(['stripe', 'bank_transfer', 'wise']))) {
      $form_state->setErrorByName('payments', $this->t('Lease to own is paid by card with Stripe, or by bank transfer or Wise: choose one of them too.'));
    }
    $currency = strtoupper(trim((string) $form_state->getValue('currency')));
    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
      $form_state->setErrorByName('currency', $this->t('Choose a three-letter currency code.'));
    }
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed> $form
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $checked = fn(string $name): array => array_values(array_filter((array) $form_state->getValue($name)));
    $payments = $checked('payments');
    $marketplaces = $checked('marketplaces');
    $extras = $checked('extras');
    // Search on Meilisearch is the domain search too.
    if (in_array('meilisearch', $extras, TRUE) && !in_array('search', $extras, TRUE)) {
      $extras[] = 'search';
    }

    // Search first: the Escrow.com recipe adds its "In stock" filter to the
    // search when it is there.
    $recipes = in_array('search', $extras, TRUE) ? [self::EXTRA_RECIPES['search']] : [];
    foreach (array_intersect_key(self::PAYMENT_RECIPES, array_flip($payments)) as $recipe) {
      $recipes[] = $recipe;
    }
    // Marketplaces listed on by hand need the marketplace module too.
    if ($marketplaces || trim((string) $form_state->getValue('other_marketplaces')) !== '') {
      $recipes[] = 'portfolio_marketplaces';
    }
    foreach (array_intersect_key(self::EXTRA_RECIPES, array_flip(array_diff($extras, ['search']))) as $recipe) {
      $recipes[] = $recipe;
    }

    $list = fn(string $name): array => array_values(array_filter(array_map('trim', explode(',', (string) $form_state->getValue($name)))));
    $this->state->set(self::STATE_KEY, [
      'store' => [
        'name' => trim((string) $form_state->getValue('store_name')),
        'email' => trim((string) $form_state->getValue('store_email')),
        'country' => (string) $form_state->getValue('country'),
        'currency' => strtoupper(trim((string) $form_state->getValue('currency'))),
      ],
      'payments' => $payments,
      'registrars' => $checked('registrars'),
      'other_registrars' => $list('other_registrars'),
      'other_marketplaces' => $list('other_marketplaces'),
      'marketplaces' => $marketplaces,
      'extras' => $extras,
      'recipes' => $recipes,
      'appearance' => [
        'template' => (string) $form_state->getValue('template'),
        'scheme' => (string) $form_state->getValue('scheme'),
      ],
    ]);
  }

  /**
   * Lists the provider plugins with any of the given capabilities.
   *
   * @param string[] $interfaces
   *   Capability interfaces.
   *
   * @return array<string, string>
   *   Plugin labels, keyed by plugin ID, in alphabetical order.
   */
  protected function pluginOptions(array $interfaces): array {
    $ids = [];
    foreach ($interfaces as $interface) {
      $ids = array_merge($ids, $this->providerPluginManager->getPluginIdsWith($interface));
    }
    $options = [];
    foreach (array_unique($ids) as $id) {
      $options[$id] = (string) $this->providerPluginManager->getDefinition($id)['label'];
    }
    natcasesort($options);
    return $options;
  }

  /**
   * Lists currencies, the common ones first.
   *
   * @return array<string, string|array<string, string>>
   *   Currency names keyed by code, in two groups.
   */
  protected function currencyOptions(): array {
    // Every ISO 4217 currency, not only those the site has imported yet.
    $all = (new CurrencyRepository())->getList();
    $common = [];
    foreach (self::COMMON_CURRENCIES as $code) {
      if (isset($all[$code])) {
        $common[$code] = $code . ' - ' . $all[$code];
      }
    }
    $others = [];
    foreach (array_diff_key($all, $common) as $code => $name) {
      $others[$code] = $code . ' - ' . $name;
    }
    return [
      (string) $this->t('Common') => $common,
      (string) $this->t('Other currencies') => $others,
    ];
  }

}
