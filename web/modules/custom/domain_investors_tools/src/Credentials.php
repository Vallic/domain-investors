<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;

/**
 * The credentials a site needs, and the .env file that can hold them.
 *
 * Every credential is an environment variable. Where the site runs, they are
 * set as variables (on Vallic Cloud, in the console); anywhere else they can
 * go into the .env file beside web/, which git ignores and
 * settings.portfolio.php loads. The installer writes that file with a sample
 * value for each credential the chosen gateways, registrars and marketplaces
 * need, so there is one place to replace them.
 *
 * A sample contains "replace-me". Gateways run with samples, so the checkout
 * can be tried (a payment fails); registrars and marketplaces stay disabled
 * until theirs are real, as their syncs would call the API with them.
 */
final class Credentials {

  // What every sample contains; settings.portfolio.php checks for it too.
  public const SAMPLE = 'replace-me';

  // Gateway ID => the variables it cannot run without. settings.portfolio.php
  // lists every variable a gateway reads, the optional ones too.
  public const GATEWAY_REQUIRED = [
    'stripe' => ['STRIPE_PUBLISHABLE_KEY', 'STRIPE_SECRET_KEY'],
    'paypal' => ['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET'],
    'wise' => ['WISE_TAG', 'WISE_PUBLIC_KEY'],
    'escrow_pay' => ['ESCROW_API_KEY', 'ESCROW_EMAIL'],
    'escrow_offer' => ['ESCROW_API_KEY', 'ESCROW_EMAIL'],
  ];

  // Samples shaped like the real value, where its shape is checked.
  protected const SAMPLES = [
    'STRIPE_PUBLISHABLE_KEY' => 'pk_test_replace-me',
    'STRIPE_SECRET_KEY' => 'sk_test_replace-me',
    'ESCROW_EMAIL' => 'replace-me@example.com',
  ];

  // The file's opening lines.
  protected const HEADER = <<<'TEXT'
    # Credentials for this copy of the site, as environment variables. Git
    # ignores this file: it never goes into the repository or the exported
    # configuration. settings.portfolio.php loads it on every request; a
    # variable set where the site runs wins over the same one here.
    #
    # Values containing "replace-me" are samples: replace each with your own.
    # Payment gateways run with samples, but no payment goes through.
    # Registrars and marketplaces stay disabled until their keys are real;
    # then enable them at Commerce > Configuration > Domains > Providers.
    TEXT;

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected ProviderSetup $providerSetup,
  ) {}

  /**
   * The .env file, beside the document root.
   */
  public static function path(): string {
    return dirname(DRUPAL_ROOT) . '/.env';
  }

  /**
   * Whether the .env file can be written, or created.
   */
  public static function writable(): bool {
    $path = self::path();
    return file_exists($path) ? is_writable($path) : is_writable(dirname($path));
  }

  /**
   * Whether a value is a sample.
   */
  public static function isSample(string|false $value): bool {
    return is_string($value) && str_contains(strtolower($value), self::SAMPLE);
  }

  /**
   * Whether a variable holds a real value: set, not empty, not a sample.
   */
  public static function isReal(string $variable): bool {
    $value = getenv($variable);
    return is_string($value) && $value !== '' && !self::isSample($value);
  }

  /**
   * Whether a variable comes from the .env file.
   */
  public static function fromFile(string $variable): bool {
    return in_array($variable, explode(',', (string) getenv('DOMAIN_INVESTORS_DOTENV')), TRUE);
  }

  /**
   * The sample value for a variable.
   */
  public static function sample(string $variable): string {
    return self::SAMPLES[$variable] ?? self::SAMPLE;
  }

  /**
   * The credentials for the chosen gateways and providers, grouped by them.
   *
   * @param string[] $gateways
   *   Payment gateway IDs.
   * @param string[] $plugins
   *   Provider plugin IDs.
   *
   * @return array<string, array{label: string, variables: array<string, bool>}>
   *   Keyed by gateway or plugin ID: its label, and its variables, each
   *   whether it is required. A variable is listed once, with the first.
   */
  public function forChoices(array $gateways, array $plugins): array {
    $groups = [];
    $seen = [];
    $all = Settings::get('domain_investors_gateway_credentials', []);
    foreach ($gateways as $gateway_id) {
      $config = $this->configFactory->get('commerce_payment.commerce_payment_gateway.' . $gateway_id);
      if ($config->isNew() || empty($all[$gateway_id])) {
        continue;
      }
      $variables = [];
      foreach ($all[$gateway_id] as $variable) {
        if (!isset($seen[$variable])) {
          $variables[$variable] = in_array($variable, self::GATEWAY_REQUIRED[$gateway_id] ?? [], TRUE);
          $seen[$variable] = TRUE;
        }
      }
      if ($variables) {
        $groups[$gateway_id] = ['label' => (string) $config->get('label'), 'variables' => $variables];
      }
    }
    foreach ($plugins as $plugin_id) {
      $variables = [];
      foreach ($this->providerSetup->variables($plugin_id) as $variable) {
        if (!isset($seen[$variable['variable']])) {
          $variables[$variable['variable']] = $variable['required'];
          $seen[$variable['variable']] = TRUE;
        }
      }
      if ($variables) {
        $groups[$plugin_id] = ['label' => $this->providerSetup->label($plugin_id), 'variables' => $variables];
      }
    }
    return $groups;
  }

  /**
   * Writes variables into the .env file, keeping what else it holds.
   *
   * A variable already in the file gets its new value; the others are added
   * under their group's name. Optional ones without a value are added as
   * comments, to show where they go.
   *
   * @param array<string, array{label: string, variables: array<string, bool>}> $groups
   *   As forChoices() returns them.
   * @param array<string, string> $values
   *   Values by variable; a missing or empty one gets its sample.
   *
   * @throws \RuntimeException
   *   When the file can't be written.
   */
  public static function write(array $groups, array $values): void {
    $path = self::path();
    $lines = file_exists($path) ? (file($path, FILE_IGNORE_NEW_LINES) ?: []) : explode("\n", self::HEADER);
    foreach ($groups as $group) {
      $block = [];
      foreach ($group['variables'] as $variable => $required) {
        $value = trim($values[$variable] ?? '');
        if ($value === '' && self::isReal($variable) && !self::fromFile($variable)) {
          // Set where the site runs: that wins anyway.
          continue;
        }
        $line = $value !== '' || $required
          ? $variable . '=' . self::quote($value !== '' ? $value : self::sample($variable))
          : '# ' . $variable . '=';
        $existing = preg_grep('/^\s*(#\s*)?(export\s+)?' . preg_quote($variable, '/') . '\s*=/', $lines);
        if ($existing) {
          $index = array_key_first($existing);
          // Never comment out, or overwrite with a sample, a value the
          // file already has.
          if ($value !== '' || str_starts_with(ltrim($lines[$index]), '#')) {
            $lines[$index] = $line;
          }
          continue;
        }
        $block[] = $line;
      }
      if ($block) {
        array_push($lines, '', '# ' . $group['label'], ...$block);
      }
    }
    $content = implode("\n", $lines) . "\n";
    if (@file_put_contents($path, $content) === FALSE) {
      throw new \RuntimeException(sprintf('%s could not be written.', $path));
    }
    @chmod($path, 0600);
    // For the rest of this request; settings.portfolio.php loads it on the
    // next ones.
    foreach ($values as $variable => $value) {
      if (trim($value) !== '') {
        putenv($variable . '=' . trim($value));
      }
    }
  }

  /**
   * Quotes a value for the .env file, where it needs it.
   */
  protected static function quote(string $value): string {
    if (preg_match('/^[A-Za-z0-9_.@:\/+=,-]*$/', $value)) {
      return $value;
    }
    return '"' . strtr($value, ['\\' => '\\\\', '"' => '\\"', "\r" => '', "\n" => '\\n']) . '"';
  }

}
