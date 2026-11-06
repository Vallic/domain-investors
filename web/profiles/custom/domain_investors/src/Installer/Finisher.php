<?php

declare(strict_types=1);

namespace Drupal\domain_investors\Installer;

use Drupal\commerce_price\CurrencyImporterInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\domain_investors_tools\EscrowSetup;
use Drupal\domain_investors_tools\ProviderSetup;
use Drupal\domain_investors_theme\Appearance;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sets up what recipes cannot: the store, its currency, and the answers.
 *
 * Recipes hold configuration that is the same on every site; the store is
 * content, and the providers and gateways depend on the answers. Every change
 * here is one the admin pages can make later.
 *
 * Gateways are changed as plain configuration, never loaded and saved as
 * entities: settings.portfolio.php overrides their credentials, and an
 * entity saved with overrides would write them into the configuration.
 */
final class Finisher implements ContainerInjectionInterface {

  use StringTranslationTrait;

  // The default content the base recipe ships for the checkout terms.
  public const TERMS_PAGE_UUID = '5d0a5d3e-9f53-4b8e-a3f0-6e1c2b7d9a41';

  // Gateway ID => [setting => variable] for the credentials a gateway cannot
  // run without, as settings.portfolio.php reads them.
  public const GATEWAY_CREDENTIALS = [
    'stripe' => ['publishable_key' => 'STRIPE_PUBLISHABLE_KEY', 'secret_key' => 'STRIPE_SECRET_KEY'],
    'paypal' => ['client_id' => 'PAYPAL_CLIENT_ID', 'client_secret' => 'PAYPAL_CLIENT_SECRET'],
    'wise' => ['wise_tag' => 'WISE_TAG', 'public_key' => 'WISE_PUBLIC_KEY'],
    'escrow_pay' => ['api_key' => 'ESCROW_API_KEY', 'email' => 'ESCROW_EMAIL'],
    'escrow_offer' => ['api_key' => 'ESCROW_API_KEY', 'email' => 'ESCROW_EMAIL'],
  ];

  // The EU member states, for the store's VAT registration.
  public const EU_COUNTRIES = [
    'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
    'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
  ];

  // The gateways each payment answer adds.
  public const PAYMENT_GATEWAYS = [
    'stripe' => ['stripe'],
    'paypal' => ['paypal'],
    'bank_transfer' => ['bank_transfer'],
    'wise' => ['wise'],
    'escrow' => ['escrow_pay', 'escrow_offer'],
    // Leases are paid through the other gateways.
    'lease' => [],
  ];

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ConfigFactoryInterface $configFactory,
    protected CurrencyImporterInterface $currencyImporter,
    protected EntityRepositoryInterface $entityRepository,
    protected MessengerInterface $messenger,
    protected ProviderSetup $providerSetup,
    protected EscrowSetup $escrowSetup,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('commerce_price.currency_importer'),
      $container->get('entity.repository'),
      $container->get('messenger'),
      $container->get(ProviderSetup::class),
      $container->get(EscrowSetup::class),
    );
  }

  /**
   * Applies the answers of the setup form.
   *
   * @param array<string, mixed> $answers
   *   As SetupForm saved them; the defaults stand in for missing ones.
   */
  public function finish(array $answers): void {
    // Without overrides, as during installation the site name reads as the
    // distribution's.
    $site = $this->configFactory->getEditable('system.site');
    $store = ($answers['store'] ?? []) + [
      'name' => (string) $site->get('name'),
      'email' => (string) $site->get('mail'),
      'country' => (string) ($this->configFactory->get('system.date')->get('country.default') ?: 'US'),
      'currency' => 'USD',
    ];
    $answers += [
      'payments' => [],
      'registrars' => [],
      'other_registrars' => [],
      'marketplaces' => [],
      'other_marketplaces' => [],
      'extras' => [],
    ];

    $this->setUpStore($store);
    if (in_array('eu_invoicing', $answers['extras'], TRUE)) {
      $this->setUpTax($store['country']);
    }
    $this->setUpOwner();
    $this->setUpTerms();
    $this->setUpAppearance($answers['appearance'] ?? []);
    $this->setUpLeases($store['email']);
    $todo = $this->setUpGateways($answers['payments']);
    $todo = array_merge($todo, $this->setUpProviders($answers));
    if (in_array('demo', $answers['extras'], TRUE)) {
      $todo[] = $this->t('The demo domains are real domains for sale by others, to try the site with. Remove them before you open it: drush domain-investors:demo --remove.');
    }
    $todo[] = $this->t('Replace the example text on the About, Privacy policy and Terms and conditions pages, at Content, before you open the site.');
    // Two-factor authentication stays off without its key
    // (settings.portfolio.php).
    if (!$this->configFactory->get('tfa.settings')->get('enabled')) {
      $todo[] = $this->t('Set TFA_ENCRYPTION_KEY to 32 random bytes, base64 (openssl rand -base64 32), as a secret, to turn on two-factor authentication for the Owner and the Assistant.');
    }

    $this->messenger->addStatus($this->t('Your domain store is set up. The dashboard lists the next steps.'));
    foreach ($todo as $line) {
      $this->messenger->addWarning($line);
    }
  }

  /**
   * Sends lease notices to the store, when portfolio_lease was applied.
   *
   * Missed payments, defaults and leases paid off.
   */
  protected function setUpLeases(string $email): void {
    $settings = $this->configFactory->getEditable('commerce_domains_lease.settings');
    if (!$settings->isNew() && !$settings->get('notify_mail')) {
      $settings->set('notify_mail', $email)->save();
    }
  }

  /**
   * Saves the template and colour scheme the answers chose.
   *
   * @param array{template?: string, scheme?: string} $answers
   *   The appearance answers.
   */
  protected function setUpAppearance(array $answers): void {
    $settings = $this->configFactory->getEditable('domain_investors_theme.settings');
    if (isset(Appearance::TEMPLATES[$answers['template'] ?? ''])) {
      $settings->set('template', $answers['template']);
    }
    if (isset(Appearance::SCHEMES[$answers['scheme'] ?? ''])) {
      $settings->set('scheme', $answers['scheme']);
    }
    $settings->save();
  }

  /**
   * Adds the currency and the store, which is the default store.
   *
   * @param array{name: string, email: string, country: string, currency: string} $answers
   *   The store answers.
   */
  protected function setUpStore(array $answers): void {
    $currency_storage = $this->entityTypeManager->getStorage('commerce_currency');
    if (!$currency_storage->load($answers['currency'])) {
      $this->currencyImporter->import($answers['currency']);
    }

    $store_storage = $this->entityTypeManager->getStorage('commerce_store');
    if ($store_storage->loadDefault()) {
      return;
    }
    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    $store = $store_storage->create([
      'type' => 'online',
      'uid' => 1,
      'name' => $answers['name'],
      'mail' => $answers['email'],
      'default_currency' => $answers['currency'],
      'timezone' => (string) ($this->configFactory->get('system.date')->get('timezone.default') ?: 'UTC'),
      'address' => ['country_code' => $answers['country']],
      // Domains are bought from anywhere.
      'billing_countries' => [],
    ]);
    $store->setDefault(TRUE);
    $store->save();

    // Owner mail goes to the store's address too, until set otherwise.
    $this->configFactory->getEditable('commerce_domains_owner.settings')
      ->set('notify_mail', $answers['email'])
      ->save();
    $contact = $this->configFactory->getEditable('contact.form.feedback');
    if (!$contact->isNew()) {
      $contact->set('recipients', [$answers['email']])->save();
    }
  }

  /**
   * Registers the store for VAT in its own country, when that is in the EU.
   *
   * Commerce charges EU VAT only where the store is registered; registrations
   * in more countries, or for the One-Stop Shop, are added on the store.
   */
  protected function setUpTax(string $country): void {
    if (!in_array($country, self::EU_COUNTRIES, TRUE)) {
      $this->messenger->addWarning($this->t('The store is outside the EU: add its EU VAT registrations on the store, if it has any, before EU VAT applies.'));
      return;
    }
    /** @var \Drupal\commerce_store\Entity\StoreInterface|null $store */
    $store = $this->entityTypeManager->getStorage('commerce_store')->loadDefault();
    if ($store && $store->hasField('tax_registrations') && $store->get('tax_registrations')->isEmpty()) {
      $store->set('tax_registrations', [$country])->save();
    }
  }

  /**
   * Gives the administrator account the Owner role.
   */
  protected function setUpOwner(): void {
    /** @var \Drupal\user\UserInterface|null $account */
    $account = $this->entityTypeManager->getStorage('user')->load(1);
    if ($account && !$account->hasRole('owner')) {
      $account->addRole('owner');
      $account->save();
    }
  }

  /**
   * Links the checkout's "I agree with the terms" to the terms page.
   */
  protected function setUpTerms(): void {
    $page = $this->entityRepository->loadEntityByUuid('node', self::TERMS_PAGE_UUID);
    $flow = $this->configFactory->getEditable('commerce_checkout.commerce_checkout_flow.domain');
    if (!$page || $flow->isNew()) {
      return;
    }
    $flow->set('configuration.panes.agree_terms.nid', (string) $page->id())
      ->set('configuration.panes.agree_terms.step', 'review')
      ->save();
  }

  /**
   * Turns on the chosen gateways whose credentials are set.
   *
   * @param string[] $payments
   *   The payment answers.
   *
   * @return string[]
   *   What is left to do.
   */
  protected function setUpGateways(array $payments): array {
    $todo = [];
    $chosen = [];
    foreach ($payments as $payment) {
      $chosen = array_merge($chosen, self::PAYMENT_GATEWAYS[$payment] ?? []);
    }
    foreach ($chosen as $gateway_id) {
      $gateway = $this->configFactory->getEditable('commerce_payment.commerce_payment_gateway.' . $gateway_id);
      if ($gateway->isNew()) {
        continue;
      }
      $missing = array_filter(self::GATEWAY_CREDENTIALS[$gateway_id] ?? [], function (string $variable): bool {
        $value = getenv($variable);
        return !is_string($value) || $value === '';
      });
      if ($gateway_id === 'bank_transfer') {
        $todo[] = $this->t('Bank transfer: add your bank details to its payment instructions, then enable it, at Commerce > Configuration > Payment gateways.');
        continue;
      }
      if ($missing) {
        $todo[] = $this->t('@gateway stays disabled until @variables are set; then enable it at Commerce > Configuration > Payment gateways.', [
          '@gateway' => $gateway->get('label'),
          '@variables' => implode(', ', array_unique($missing)),
        ]);
        continue;
      }
      $gateway->set('status', TRUE)->save();
    }

    // Orders whose offer is negotiated on Escrow.com are paid there, and
    // only there: the other gateways stay off them.
    if (in_array('escrow', $payments, TRUE)) {
      $this->escrowSetup->excludeOffersFromOtherGateways();
    }
    return $todo;
  }

  /**
   * Adds the chosen registrars and marketplaces.
   *
   * @param array<string, mixed> $answers
   *   The answers.
   *
   * @return string[]
   *   What is left to do.
   */
  protected function setUpProviders(array $answers): array {
    $todo = [];
    $plugins = array_unique(array_merge($answers['registrars'], $answers['marketplaces']));
    $variables = [];
    foreach ($plugins as $plugin_id) {
      $this->providerSetup->addPluginProvider($plugin_id);
      foreach ($this->providerSetup->variables($plugin_id) as $variable) {
        $value = getenv($variable['variable']);
        if ($variable['required'] && (!is_string($value) || $value === '')) {
          $variables[] = $variable['variable'];
        }
      }
    }
    foreach ($answers['other_registrars'] as $name) {
      $this->providerSetup->addRegistrar($name);
    }
    foreach ($answers['other_marketplaces'] as $name) {
      $this->providerSetup->addMarketplace($name);
    }
    if ($variables) {
      $todo[] = $this->t('Set @variables for your registrars and marketplaces: each provider reads its API credentials from a key that reads one of these.', [
        '@variables' => implode(', ', array_unique($variables)),
      ]);
    }
    if ($answers['marketplaces']) {
      $todo[] = $this->t('Review a dry run before the first marketplace sync: drush commerce-domains:marketplace-sync PROVIDER --dry-run --details.');
    }
    return $todo;
  }

}
