<?php

declare(strict_types=1);

namespace Drupal\domain_investors_theme;

use Drupal\commerce_product\Entity\ProductInterface;

/**
 * The templates and colour schemes, and which ones a page uses.
 *
 * In a class rather than in domain_investors_theme.theme: the settings form
 * runs under the admin theme, where domain_investors_theme.theme is not loaded,
 * and the installer's Appearance step asks the same question.
 */
final class Appearance {

  // The view that lists the domains, on the front page and at /domains.
  public const VIEW = 'portfolio_domains';

  // The domain search at /search (recipes/portfolio_search).
  public const SEARCH_VIEW = 'portfolio_domain_search';

  // The templates, as the design system names them, with what each is for.
  public const TEMPLATES = [
    'minimal' => ['Minimal', 'One column, a price list of names. For up to about 50 names.'],
    'standard' => ['Standard', 'Search, filters and cards. For most portfolios, 20 to 2,000 names.'],
    'showcase' => ['Showcase', 'A few strong names, each with its story and a large type setting.'],
    'catalog' => ['Catalog', 'A sortable table. For large portfolios, 500 to 10,000 names.'],
  ];

  // The colour schemes, keyed as tokens.css names them; custom is the
  // owner's own, set under Appearance (Palette).
  public const SCHEMES = [
    'light' => 'Verdigris',
    'dark' => 'Verdigris Night',
    'brass' => 'Brass',
    'brass-night' => 'Brass Night',
    'mono' => 'Mono',
    'cobalt' => 'Cobalt',
    'custom' => 'Custom',
  ];

  /**
   * The site's template, chosen under Appearance.
   */
  public static function siteTemplate(): string {
    $template = (string) theme_get_setting('template', 'domain_investors_theme');
    return isset(self::TEMPLATES[$template]) ? $template : 'standard';
  }

  /**
   * The site's colour scheme.
   */
  public static function siteScheme(): string {
    $scheme = (string) theme_get_setting('scheme', 'domain_investors_theme');
    return isset(self::SCHEMES[$scheme]) ? $scheme : 'light';
  }

  /**
   * The custom scheme's colours, as saved: empty ones are worked out.
   *
   * @return array<string, string>
   *   Colours keyed by token.
   */
  public static function customColours(): array {
    $colours = theme_get_setting('custom', 'domain_investors_theme');
    return is_array($colours) ? array_filter($colours, 'is_string') : [];
  }

  /**
   * The template a domain's page uses: its own, else the site's.
   */
  public static function template(ProductInterface $product): string {
    $own = $product->hasField('page_template') ? (string) $product->get('page_template')->value : '';
    return isset(self::TEMPLATES[$own]) ? $own : self::siteTemplate();
  }

  /**
   * The colour scheme a domain's page uses: its own, else the site's.
   */
  public static function scheme(ProductInterface $product): string {
    $own = $product->hasField('page_scheme') ? (string) $product->get('page_scheme')->value : '';
    return isset(self::SCHEMES[$own]) ? $own : self::siteScheme();
  }

  /**
   * Template options for a form.
   *
   * @param bool $describe
   *   Whether to add what each template is for.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup|string>
   *   Labels keyed by template.
   */
  public static function templateOptions(bool $describe = FALSE): array {
    $options = [];
    foreach (self::TEMPLATES as $key => [$label, $description]) {
      // phpcs:disable Drupal.Semantics.FunctionT.NotLiteralString
      $options[$key] = $describe
        ? t('<strong>@label</strong>: @description', ['@label' => t($label), '@description' => t($description)])
        : t($label);
      // phpcs:enable
    }
    return $options;
  }

  /**
   * Colour scheme options for a form.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   *   Labels keyed by scheme.
   */
  public static function schemeOptions(): array {
    // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
    return array_map(static fn(string $label) => t($label), self::SCHEMES);
  }

}
