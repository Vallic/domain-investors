<?php

declare(strict_types=1);

namespace Drupal\domain_investors\Installer;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\domain_investors_tools\Credentials;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The installer's keys step: real credentials, or samples to replace later.
 *
 * Lists the credentials the chosen gateways, registrars and marketplaces read,
 * and writes them into the .env file beside web/ (git ignores it): the ones
 * filled in as they are, the others as samples. Nothing goes into
 * configuration. The file is where to replace the samples later; a variable
 * already set where the site runs is left out.
 *
 * Without the web installer, drush passes them as form values, such as
 * domain_investors_credentials.STRIPE_SECRET_KEY=sk_test_...
 */
final class CredentialsForm extends FormBase {

  // Credentials pasted as several lines.
  protected const MULTILINE = ['WISE_PUBLIC_KEY'];

  public function __construct(
    protected Credentials $credentials,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get(Credentials::class));
  }

  /**
   * The credentials the answers call for.
   *
   * @return array<string, array{label: string, variables: array<string, bool>}>
   *   As Credentials::forChoices() returns them.
   */
  public static function groups(): array {
    $answers = SetupForm::answers() + ['payments' => [], 'registrars' => [], 'marketplaces' => []];
    return \Drupal::service(Credentials::class)->forChoices(
      Finisher::gateways($answers['payments']),
      array_unique(array_merge($answers['registrars'], $answers['marketplaces'])),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'domain_investors_credentials';
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed> $form
   * @phpstan-param array<mixed>|null $install_state
   * @phpstan-return array<mixed>
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array &$install_state = NULL): array {
    SetupForm::loadMailerOverrides();
    $form['#title'] = $this->t('Keys for payments and connections');
    $path = Credentials::path();
    $writable = Credentials::writable();

    $form['intro'] = [
      '#markup' => '<p>' . ($writable
        ? $this->t('These go into <code>@file</code>, which git ignores, and never into the configuration. Fill in the ones you have. Any left empty gets a sample value, to replace in that file later: payment gateways run with samples, though no payment goes through, and registrars and marketplaces stay disabled until theirs are real.', ['@file' => $path])
        : $this->t('<code>@file</code> cannot be written here, so set these as environment variables where the site runs, such as in your hosting control panel. Payment gateways are enabled without them, and registrars and marketplaces stay disabled.', ['@file' => $path])) . '</p>',
    ];

    foreach (self::groups() as $id => $group) {
      $form[$id] = [
        '#type' => 'details',
        '#title' => $group['label'],
        '#open' => TRUE,
      ];
      foreach ($group['variables'] as $variable => $required) {
        $set = Credentials::isReal($variable) && !Credentials::fromFile($variable);
        if (!$writable || $set) {
          $form[$id][$variable] = [
            '#type' => 'item',
            '#title' => $variable,
            '#markup' => $set ? $this->t('Set where the site runs.') : ($required ? $this->t('Required.') : $this->t('Optional.')),
          ];
          continue;
        }
        $form[$id][$variable] = [
          '#type' => in_array($variable, self::MULTILINE, TRUE) ? 'textarea' : 'textfield',
          '#rows' => 4,
          '#title' => $variable,
          '#description' => $required ? $this->t('Empty: the sample %sample.', ['%sample' => Credentials::sample($variable)]) : $this->t('Optional.'),
          '#default_value' => '',
          '#maxlength' => 4096,
          '#attributes' => ['autocomplete' => 'off', 'spellcheck' => 'false'],
          '#parents' => [$variable],
        ];
      }
    }

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
    foreach (self::groups() as $group) {
      foreach (array_keys($group['variables']) as $variable) {
        if (Credentials::isSample((string) $form_state->getValue($variable, ''))) {
          $form_state->setErrorByName($variable, $this->t('Leave %variable empty for a sample, or fill in your own.', ['%variable' => $variable]));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed> $form
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (!Credentials::writable()) {
      return;
    }
    $groups = self::groups();
    $values = [];
    foreach ($groups as $group) {
      foreach (array_keys($group['variables']) as $variable) {
        $values[$variable] = trim((string) $form_state->getValue($variable, ''));
      }
    }
    try {
      Credentials::write($groups, $values);
    }
    catch (\RuntimeException $e) {
      $this->messenger()->addWarning($e->getMessage());
    }
  }

}
