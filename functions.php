<?php

use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        App\Providers\ThemeServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

if (!function_exists('parse_audacity_labels')) {
  function parse_audacity_labels(string $filepath): array {
    if (!file_exists($filepath)) return [];
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $chapters = [];
    foreach ($lines as $line) {
      $ln = trim($line);
      if ($ln === '') continue;
      // split on tabs first; if not present, split on whitespace (max 3 parts)
      $parts = preg_split("/\t+/", $ln);
      if (count($parts) < 2) $parts = preg_split("/\s+/", $ln, 3);
      if (!$parts || count($parts) < 1) continue;

      $start = floatval($parts[0] ?? 0);
      $title = trim($parts[2] ?? ($parts[1] ?? '')) ?: ('Chapter ' . (count($chapters) + 1));

      $chapters[] = [round($start, 3), $title];
    }
    usort($chapters, fn($a,$b) => $a[0] <=> $b[0]);
    return $chapters;
  }
}

function theme_asset_url($entry) {
  $manifest_path = get_stylesheet_directory() . '/manifest.json'; // or /public/manifest.json if you configured it there
  if (!file_exists($manifest_path)) return null;

  $manifest = json_decode(file_get_contents($manifest_path), true);
  if (!isset($manifest[$entry])) return null;

  // Vite manifest "file" is relative to outDir (public/)
  $path = $manifest[$entry]['file'] ?? '';
  if (!$path) return null;

  // Build absolute URL that matches vite.config.js base
  return get_stylesheet_directory_uri() . '/public/' . $path;
}

add_action('wp_enqueue_scripts', function () {
  // CSS (if emitted)
  if ($css = theme_asset_url('resources/styles/app.css')) {
    wp_enqueue_style('theme-app', $css, [], null);
  }

  // JS
  if ($js = theme_asset_url('resources/scripts/app.js')) {
    wp_enqueue_script('theme-app', $js, [], null, true);
  }
}, 20);