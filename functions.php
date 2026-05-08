<?php

use Roots\Acorn\Application;

require_once get_template_directory() . '/app/api-chat.php';

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
  function parse_audacity_labels(string $input): array {
    if (file_exists($input)) {
      $lines = file($input, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    } else {
      $lines = array_filter(explode("\n", $input), fn($l) => trim($l) !== '');
    }

    $chapters = [];
    foreach ($lines as $line) {
      $ln = trim($line);
      if ($ln === '') continue;
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
  $manifest_path = get_stylesheet_directory() . '/manifest.json'; 
  if (!file_exists($manifest_path)) return null;

  $manifest = json_decode(file_get_contents($manifest_path), true);
  if (!isset($manifest[$entry])) return null;

  $path = $manifest[$entry]['file'] ?? '';
  if (!$path) return null;

  return get_stylesheet_directory_uri() . '/public/' . $path;
}

add_action('wp_enqueue_scripts', function () {

  if ($css = theme_asset_url('resources/styles/app.css')) {
    wp_enqueue_style('theme-app', $css, [], null);
  }


  if ($js = theme_asset_url('resources/scripts/app.js')) {
    wp_enqueue_script('theme-app', $js, [], null, true);
  }
}, 20);
