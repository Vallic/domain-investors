<?php

// phpcs:ignoreFile

/**
 * @file
 * Payment gateway credentials and other services' settings, read from
 * environment variables.
 *
 * Included from settings.php. It holds variable names, never values, so it is
 * committed like the rest of the project. Set the variables in your hosting
 * control panel (the Vallic Cloud console: Variables, marked secret), in
 * .ddev/.env under DDEV, or in the environment PHP runs in anywhere else.
 *
 * A variable that is not set, or is empty, overrides nothing: the gateway
 * keeps what its form says, which for every credential is empty. Never type a
 * credential into a gateway's form, since configuration is exported and
 * committed.
 *
 * Domain providers (registrars and marketplaces) are not listed here: they
 * read their API credentials through keys (Key module, Environment provider),
 * which the installer and `drush domain-investors:provider` create. See docs/secrets.md.
 *
 * Do not load and save a payment gateway from code, such as a drush script:
 * an entity loaded with these overrides and saved again writes them into the
 * active configuration, and from there into the next export. The gateway
 * forms in the admin UI are safe; they edit the configuration without
 * overrides.
 */

use Drupal\Core\Installer\InstallerKernel;

// Gateway ID => [setting => environment variable]. The payment recipes create
// the gateways under these IDs.
$portfolio_gateways = [
  // recipes/portfolio_payments_stripe.
  'stripe' => [
    'publishable_key' => 'STRIPE_PUBLISHABLE_KEY',
    'secret_key' => 'STRIPE_SECRET_KEY',
    'webhook_signing_secret' => 'STRIPE_WEBHOOK_SIGNING_SECRET',
  ],
  // recipes/portfolio_payments_paypal.
  'paypal' => [
    'client_id' => 'PAYPAL_CLIENT_ID',
    'client_secret' => 'PAYPAL_CLIENT_SECRET',
    'webhook_id' => 'PAYPAL_WEBHOOK_ID',
  ],
  // recipes/portfolio_payments_wise. The public key is Wise's, for checking
  // its deposit webhooks; the tag is your business account's.
  'wise' => [
    'wise_tag' => 'WISE_TAG',
    'public_key' => 'WISE_PUBLIC_KEY',
  ],
  // recipes/portfolio_escrow: buy now through Escrow.com, and offers
  // negotiated there. One Escrow.com account for both.
  'escrow_pay' => [
    'api_key' => 'ESCROW_API_KEY',
    'email' => 'ESCROW_EMAIL',
  ],
  'escrow_offer' => [
    'api_key' => 'ESCROW_API_KEY',
    'email' => 'ESCROW_EMAIL',
  ],
];

// The gateway forms show these as coming from the environment rather than
// asking for them (domain_investors_tools), so saving a form never puts a
// credential into configuration.
$settings['domain_investors_gateway_credentials'] = $portfolio_gateways;

// The installer never sees credentials: nothing it does needs them, and
// nothing it saves can then carry them into configuration.
if (!InstallerKernel::installationAttempted()) {
  foreach ($portfolio_gateways as $portfolio_gateway => $portfolio_credentials) {
    foreach ($portfolio_credentials as $portfolio_setting => $portfolio_variable) {
      $portfolio_value = getenv($portfolio_variable);
      if (is_string($portfolio_value) && $portfolio_value !== '') {
        $config['commerce_payment.commerce_payment_gateway.' . $portfolio_gateway]['configuration'][$portfolio_setting] = $portfolio_value;
      }
    }
  }

  // recipes/portfolio_escrow: the secret in the webhook URL all Escrow.com
  // gateways share. Set even when the variable is not: Commerce Escrow would
  // otherwise generate one and keep it in exported configuration. While it is
  // empty, webhooks are refused.
  $config['commerce_escrow.settings']['webhook_secret'] = (string) getenv('ESCROW_WEBHOOK_SECRET');
}

// Whether payments are real: PAYMENT_MODE=live or PAYMENT_MODE=test. Unset,
// the gateways stay in the mode their configuration says (test, as the
// recipes create them), except on a Vallic Cloud production environment,
// which settings.vallic.php switches to live. The mode has to match the keys:
// live keys in test mode, or test keys in live mode, fail at checkout.
$portfolio_mode = getenv('PAYMENT_MODE');
if ($portfolio_mode === 'live' || $portfolio_mode === 'test') {
  foreach (array_keys($portfolio_gateways) as $portfolio_gateway) {
    $config['commerce_payment.commerce_payment_gateway.' . $portfolio_gateway]['configuration']['mode'] = $portfolio_mode;
  }
}

// Two-factor authentication keeps its secrets encrypted with
// TFA_ENCRYPTION_KEY (32 random bytes, base64). Without it, or with a key of
// another length, they could not be kept, and the roles that require it could
// not log in, so it stays off.
$portfolio_tfa = getenv('TFA_ENCRYPTION_KEY');
$portfolio_tfa = is_string($portfolio_tfa) ? base64_decode(trim($portfolio_tfa), TRUE) : FALSE;
if ($portfolio_tfa === FALSE || strlen($portfolio_tfa) !== 32) {
  $config['tfa.settings']['enabled'] = FALSE;
}
unset($portfolio_tfa);

// recipes/portfolio_search_meilisearch: where Meilisearch answers, and its
// key. Vallic Cloud sets MEILISEARCH_HOST (a host name, port 7700) and
// MEILI_MASTER_KEY for each environment; .ddev/config.yaml sets the same.
$portfolio_meili = getenv('MEILISEARCH_HOST');
if (is_string($portfolio_meili) && $portfolio_meili !== '') {
  $config['search_api.server.meilisearch']['backend_config']['meilisearch_host_address'] = str_contains($portfolio_meili, '://') ? $portfolio_meili : 'http://' . $portfolio_meili;
  $config['search_api.server.meilisearch']['backend_config']['meilisearch_host_port'] = getenv('MEILISEARCH_PORT') ?: '7700';
  $config['search_api.server.meilisearch']['backend_config']['meilisearch_master_key'] = (string) getenv('MEILI_MASTER_KEY');
}
unset($portfolio_meili);

// recipes/portfolio_google_tag: GOOGLE_TAG_ID, such as GTM-ABC1234 or
// G-ABC1234, or several separated by commas, turns the tag on. Unset, the
// tag stays off, so development copies send nothing to Analytics.
$portfolio_tag = getenv('GOOGLE_TAG_ID');
if (is_string($portfolio_tag) && trim($portfolio_tag) !== '' && !InstallerKernel::installationAttempted()) {
  $config['google_tag.container.portfolio']['tag_container_ids'] = array_values(array_filter(array_map('trim', explode(',', $portfolio_tag))));
  $config['google_tag.container.portfolio']['status'] = TRUE;
}
unset($portfolio_tag);

unset($portfolio_gateways, $portfolio_gateway, $portfolio_credentials, $portfolio_setting, $portfolio_variable, $portfolio_value, $portfolio_mode);
