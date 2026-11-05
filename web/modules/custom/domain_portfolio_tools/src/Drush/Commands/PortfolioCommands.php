<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\AppraisalInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\CsvExportInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\DnsRecordsInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\DomainManagementInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\ListingStatsInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\MarketplaceInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\PaymentLinksInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\PremiumListingsInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\RegistrarInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\RenewalPricesInterface;
use Drupal\commerce_domains\Plugin\CommerceDomains\Provider\SalesReportInterface;
use Drupal\commerce_domains\ProviderPluginManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain_portfolio_tools\DemoContent;
use Drupal\domain_portfolio_tools\EscrowSetup;
use Drupal\domain_portfolio_tools\ProviderSetup;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Drush commands for setting up a Domain Investors site.
 */
final class PortfolioCommands extends DrushCommands {

  use AutowireTrait;

  // Capability interfaces, by the name the listing gives them.
  protected const CAPABILITIES = [
    'registrar' => RegistrarInterface::class,
    'management' => DomainManagementInterface::class,
    'marketplace' => MarketplaceInterface::class,
    'csv' => CsvExportInterface::class,
    'appraisal' => AppraisalInterface::class,
    'stats' => ListingStatsInterface::class,
    'sales' => SalesReportInterface::class,
    'premium' => PremiumListingsInterface::class,
    'dns' => DnsRecordsInterface::class,
    'payment_links' => PaymentLinksInterface::class,
    'renewal_prices' => RenewalPricesInterface::class,
  ];

  public function __construct(
    protected ProviderSetup $providerSetup,
    protected DemoContent $demoContent,
    #[Autowire(service: 'plugin.manager.commerce_domains_provider')]
    protected ProviderPluginManager $providerPluginManager,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EscrowSetup $escrowSetup,
  ) {
    parent::__construct();
  }

  /**
   * Adds registrars and marketplaces, with keys that read their credentials.
   *
   * Each provider with an API gets a key (Key module, Environment provider)
   * for every credential it needs, so setting the environment variables is
   * all that connects it. A name no plugin has, such as Hover, is added as a
   * registrar without an API.
   */
  #[CLI\Command(name: 'domain-investors:provider', aliases: ['dip'])]
  #[CLI\Argument(name: 'providers', description: 'Provider plugin IDs, such as dynadot or afternic (see domain-investors:providers), or names of registrars without an API.')]
  #[CLI\Option(name: 'disabled', description: 'Add new providers disabled, to review a dry run before any sync.')]
  #[CLI\Usage(name: 'drush domain-investors:provider dynadot afternic sedo', description: 'Adds Dynadot, Afternic and Sedo, and lists the variables to set.')]
  #[CLI\Usage(name: 'drush dip Hover', description: 'Adds Hover as a registrar without an API.')]
  public function provider(array $providers, array $options = ['disabled' => FALSE]): void {
    foreach ($providers as $name) {
      if ($this->providerPluginManager->hasDefinition($name)) {
        $provider = $this->providerSetup->addPluginProvider($name, !$options['disabled']);
        $variables = array_column($this->providerSetup->variables($name), 'variable');
        $this->logger()->success(dt('@label is set up as provider "@id". Its API credentials come from: @variables.', [
          '@label' => $provider->label(),
          '@id' => $provider->id(),
          '@variables' => $variables ? implode(', ', $variables) : dt('none needed'),
        ]));
      }
      else {
        $provider = $this->providerSetup->addRegistrar($name);
        $this->logger()->success(dt('@label is set up as provider "@id", a registrar without an API.', [
          '@label' => $provider->label(),
          '@id' => $provider->id(),
        ]));
      }
    }
  }

  /**
   * Adds 200 domains to try the site with, or removes them again.
   *
   * They are real domains for sale by this project's makers, for a copy to try
   * things on: remove them before opening a site to buyers. They are never
   * listed on marketplaces, and domains already in the catalog stay as they
   * are.
   */
  #[CLI\Command(name: 'domain-investors:demo', aliases: ['didemo'])]
  #[CLI\Option(name: 'remove', description: 'Remove the domains added, except any that have an order.')]
  #[CLI\Usage(name: 'drush domain-investors:demo', description: 'Adds the domains.')]
  #[CLI\Usage(name: 'drush didemo --remove', description: 'Removes them again.')]
  public function demo(array $options = ['remove' => FALSE]): void {
    if ($options['remove']) {
      $result = $this->demoContent->remove();
      $this->logger()->success(dt('Removed @removed demo domains; @kept kept, as they have an order.', [
        '@removed' => $result['removed'],
        '@kept' => $result['kept'],
      ]));
      return;
    }
    if (!DemoContent::isAvailable()) {
      throw new \RuntimeException(dt('Demo domains are not for a production environment.'));
    }
    $added = 0;
    foreach ($this->demoContent->chunks() as $rows) {
      $added += $this->demoContent->import($rows);
    }
    $this->logger()->success(dt('Added @added demo domains. Remove them with drush domain-investors:demo --remove before you open the site.', ['@added' => $added]));
  }

  /**
   * Finishes Escrow.com on a site that applied recipes/portfolio_escrow later.
   *
   * The installer does this when Escrow.com is chosen there. Afterwards, run
   * it once after `drush recipe ../recipes/portfolio_escrow`: it keeps the
   * other payment gateways off orders of offers negotiated on Escrow.com (as
   * plain configuration, so no credential is saved with them), and fills in
   * Commerce Escrow's "In stock" field on the domains already in the
   * catalog, which Commerce Domains would otherwise count as off sale.
   */
  #[CLI\Command(name: 'domain-investors:escrow-setup', aliases: ['diescrow'])]
  #[CLI\Option(name: 'dry-run', description: 'Report what would change, without saving.')]
  #[CLI\Usage(name: 'drush domain-investors:escrow-setup', description: 'Run once, right after applying recipes/portfolio_escrow to an installed site.')]
  public function escrowSetup(array $options = ['dry-run' => FALSE]): void {
    $dry_run = (bool) $options['dry-run'];
    $gateways = $this->escrowSetup->excludeOffersFromOtherGateways($dry_run);
    $this->logger()->success($gateways
      ? dt('@verb off Escrow.com offers: @gateways.', [
        '@verb' => $dry_run ? dt('Would keep') : dt('Kept'),
        '@gateways' => implode(', ', $gateways),
      ])
      : dt('Every other gateway already stays off Escrow.com offers.'));
    [$in_stock, $out] = $this->escrowSetup->fillInStock($dry_run);
    $this->logger()->success(dt('@verb @in domains in stock and @out out of stock.', [
      '@verb' => $dry_run ? dt('Would mark') : dt('Marked'),
      '@in' => $in_stock,
      '@out' => $out,
    ]));
    $this->logger()->success(dt('@verb @count open orders to Validation.', [
      '@verb' => $dry_run ? dt('Would move') : dt('Moved'),
      '@count' => $this->escrowSetup->moveOpenOrders($dry_run),
    ]));
  }

  /**
   * Lists the registrars and marketplaces Commerce Domains can connect to.
   */
  #[CLI\Command(name: 'domain-investors:providers', aliases: ['dil'])]
  #[CLI\FieldLabels(labels: [
    'id' => 'ID',
    'label' => 'Name',
    'capabilities' => 'Capabilities',
    'variables' => 'Environment variables',
    'added' => 'Added',
  ])]
  #[CLI\DefaultTableFields(fields: ['id', 'label', 'capabilities', 'variables', 'added'])]
  #[CLI\Usage(name: 'drush domain-investors:providers', description: 'Shows each provider plugin, what it can do and the variables it reads.')]
  public function providers(array $options = ['format' => 'table']): RowsOfFields {
    $capabilities = [];
    foreach (self::CAPABILITIES as $name => $interface) {
      foreach ($this->providerPluginManager->getPluginIdsWith($interface) as $id) {
        $capabilities[$id][] = $name;
      }
    }
    $added = $this->entityTypeManager->getStorage('commerce_domains_provider')->loadMultiple();
    $rows = [];
    foreach ($this->providerPluginManager->getDefinitions() as $id => $definition) {
      $variables = [];
      foreach ($this->providerSetup->variables($id) as $variable) {
        $variables[] = $variable['variable'] . ($variable['required'] ? '' : ' (optional)');
      }
      $rows[$id] = [
        'id' => $id,
        'label' => (string) $definition['label'],
        'capabilities' => implode(', ', $capabilities[$id] ?? []),
        'variables' => implode(', ', $variables),
        'added' => isset($added[$id]) ? dt('yes') : '',
      ];
    }
    ksort($rows);
    return new RowsOfFields($rows);
  }

}
