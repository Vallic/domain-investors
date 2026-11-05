<?php

declare(strict_types=1);

namespace Drupal\portfolio;

/**
 * The custom colour scheme: the owner's three colours, and the rest from them.
 *
 * The design system's schemes are eleven colours each. A custom scheme asks
 * for three (the background, the text and the accent) and works out the
 * others the way the presets are built: raised and sunken surfaces, lines and
 * muted text mixed between the background and the text, the accent's
 * stronger and softer shades, and white or near-black text on the accent,
 * whichever reads better. Any of them can be set by hand instead.
 */
final class Palette {

  // The colours a custom scheme sets, in the order the settings form lists
  // them; the first three are asked for, the rest are worked out.
  public const TOKENS = [
    'surface' => 'Background',
    'ink' => 'Text',
    'accent' => 'Accent',
    'surface-raised' => 'Raised background: cards, the buy box, fields',
    'surface-sunken' => 'Sunken background: bands, table headings',
    'line' => 'Lines',
    'line-strong' => 'Strong lines: field borders, leader dots',
    'ink-muted' => 'Muted text',
    'accent-strong' => 'Accent on hover',
    'accent-soft' => 'Soft accent: badges, selected chips',
    'on-accent' => 'Text on the accent: buttons',
  ];

  // Notices, errors and shadows on a dark background, as the night presets
  // have them; on a light one the defaults in tokens.css apply.
  protected const DARK = [
    'notice' => '#f2b84f',
    'notice-soft' => '#3a290a',
    'danger' => '#ff8b7c',
    'danger-soft' => '#3f1611',
    'shadow-pop' => '0 12px 32px rgba(0, 0, 0, 0.55), 0 1px 3px rgba(0, 0, 0, 0.40)',
  ];

  // The Verdigris preset's three, for a custom scheme not set up yet.
  public const DEFAULTS = ['surface' => '#f4f5f1', 'ink' => '#151a19', 'accent' => '#0b6656'];

  /**
   * Works out a whole scheme from the settings.
   *
   * @param array<string, string> $colours
   *   Colours keyed by token, as saved; empty ones are worked out.
   *
   * @return array<string, string>
   *   Every token in TOKENS, as #rrggbb.
   */
  public static function resolve(array $colours): array {
    $pick = fn(string $token): string => self::isHex($colours[$token] ?? '') ? strtolower($colours[$token]) : '';
    $surface = $pick('surface') ?: self::DEFAULTS['surface'];
    $ink = $pick('ink') ?: self::DEFAULTS['ink'];
    $accent = $pick('accent') ?: self::DEFAULTS['accent'];
    $dark = self::isDark($surface);

    $derived = [
      'surface' => $surface,
      'ink' => $ink,
      'accent' => $accent,
      'surface-raised' => $dark ? self::mix($surface, $ink, 0.04) : self::mix($surface, '#ffffff', 0.8),
      'surface-sunken' => $dark ? self::mix($surface, '#000000', 0.3) : self::mix($surface, $ink, 0.05),
      'line' => self::mix($surface, $ink, $dark ? 0.14 : 0.12),
      'line-strong' => self::mix($surface, $ink, $dark ? 0.42 : 0.5),
      'ink-muted' => self::mix($ink, $surface, $dark ? 0.3 : 0.28),
      'accent-strong' => $dark ? self::mix($accent, '#ffffff', 0.25) : self::mix($accent, '#000000', 0.2),
      'accent-soft' => self::mix($surface, $accent, $dark ? 0.2 : 0.15),
      'on-accent' => self::contrast('#ffffff', $accent) >= self::contrast('#111111', $accent) ? '#ffffff' : '#111111',
    ];
    foreach (array_keys($derived) as $token) {
      if ($own = $pick($token)) {
        $derived[$token] = $own;
      }
    }
    return $derived;
  }

  /**
   * The CSS that defines the custom scheme.
   *
   * @param array<string, string> $colours
   *   Colours keyed by token, as saved.
   */
  public static function css(array $colours): string {
    $palette = self::resolve($colours);
    $rules = [];
    foreach ($palette as $token => $value) {
      $rules[] = "--$token: $value;";
    }
    if (self::isDark($palette['surface'])) {
      foreach (self::DARK as $token => $value) {
        $rules[] = "--$token: $value;";
      }
    }
    // :root as well, to outrank tokens.css's ":root" defaults, which load
    // after this; [data-theme] alone, for a domain's custom scheme inside a
    // page in another.
    return ':root[data-theme="custom"], [data-theme="custom"] { ' . implode(' ', $rules) . ' --focus: var(--accent); --link: var(--accent); }';
  }

  /**
   * The pairs that must read well, and what each must reach.
   *
   * WCAG AA: 4.5 for text, which the accent is too, as links.
   *
   * @return array<int, array{0: string, 1: string, 2: float, 3: string}>
   *   Foreground token, background token, the ratio needed, and what the pair
   *   is, for the error.
   */
  public static function pairs(): array {
    return [
      ['ink', 'surface', 4.5, 'Text on the background'],
      ['ink-muted', 'surface', 4.5, 'Muted text on the background'],
      ['ink', 'surface-raised', 4.5, 'Text on cards'],
      ['accent', 'surface', 4.5, 'Links on the background'],
      ['on-accent', 'accent', 4.5, 'Button text on the accent'],
    ];
  }

  /**
   * Checks a scheme's contrast.
   *
   * @param array<string, string> $palette
   *   A resolved scheme.
   *
   * @return array<int, array{label: string, ratio: float, needed: float, tokens: string[]}>
   *   The pairs that fall short.
   */
  public static function problems(array $palette): array {
    $problems = [];
    foreach (self::pairs() as [$fore, $back, $needed, $label]) {
      $ratio = self::contrast($palette[$fore], $palette[$back]);
      if ($ratio < $needed) {
        $problems[] = ['label' => $label, 'ratio' => round($ratio, 2), 'needed' => $needed, 'tokens' => [$fore, $back]];
      }
    }
    return $problems;
  }

  /**
   * Whether a value is a colour as #rrggbb.
   */
  public static function isHex(string $value): bool {
    return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $value);
  }

  /**
   * Whether a background is dark, so the scheme is a night scheme.
   */
  public static function isDark(string $hex): bool {
    return self::luminance($hex) < 0.2;
  }

  /**
   * The WCAG contrast ratio of two colours.
   */
  public static function contrast(string $a, string $b): float {
    [$light, $dark] = [max(self::luminance($a), self::luminance($b)), min(self::luminance($a), self::luminance($b))];
    return ($light + 0.05) / ($dark + 0.05);
  }

  /**
   * Mixes two colours: $amount of $b into $a.
   */
  public static function mix(string $a, string $b, float $amount): string {
    $x = self::rgb($a);
    $y = self::rgb($b);
    $out = '#';
    foreach ([0, 1, 2] as $i) {
      $out .= sprintf('%02x', (int) round($x[$i] + ($y[$i] - $x[$i]) * $amount));
    }
    return $out;
  }

  /**
   * The relative luminance of a colour, as WCAG defines it.
   */
  protected static function luminance(string $hex): float {
    $channel = static function (int $value): float {
      $c = $value / 255;
      return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    };
    [$r, $g, $b] = self::rgb($hex);
    return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
  }

  /**
   * A colour's red, green and blue.
   *
   * @return int[]
   *   Three values, 0 to 255.
   */
  protected static function rgb(string $hex): array {
    return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
  }

}
