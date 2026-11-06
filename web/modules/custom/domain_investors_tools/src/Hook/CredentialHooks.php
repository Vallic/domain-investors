<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\domain_investors_tools\Credentials;

/**
 * Reports credentials that still hold the installer's samples.
 */
final class CredentialHooks {

  use StringTranslationTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_runtime_requirements().
   *
   * @phpstan-return array<string, array<string, mixed>>
   */
  #[Hook('runtime_requirements')]
  public function runtimeRequirements(): array {
    $samples = [];
    $gateways = $this->entityTypeManager->getStorage('commerce_payment_gateway')
      ->loadMultiple(array_keys(Settings::get('domain_investors_gateway_credentials', [])));
    foreach ($gateways as $id => $gateway) {
      foreach (Credentials::GATEWAY_REQUIRED[$id] ?? [] as $variable) {
        if (Credentials::isSample(getenv($variable))) {
          $samples[$variable] = $variable;
        }
      }
    }
    foreach ($this->entityTypeManager->getStorage('commerce_domains_provider')->loadMultiple() as $provider) {
      /** @var \Drupal\commerce_domains\Entity\ProviderInterface $provider */
      foreach ($provider->getPlugin()?->getCredentialSources() ?? [] as $source) {
        if (Credentials::isSample(getenv($source['variable']))) {
          $samples[$source['variable']] = $source['variable'];
        }
      }
    }
    if (!$samples) {
      return [];
    }
    return [
      'domain_investors_credentials' => [
        'title' => $this->t('Sample credentials'),
        'value' => implode(', ', $samples),
        'description' => $this->t('These hold the sample values the installer wrote: payments with them fail, and registrars and marketplaces with them should stay disabled. Replace them in @file, or set them where the site runs.', ['@file' => Credentials::path()]),
        'severity' => RequirementSeverity::Warning,
      ],
    ];
  }

}
