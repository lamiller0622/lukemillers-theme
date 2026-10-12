<?php

/**
 * Mixes: custom post type, ACF local JSON, folder-based file picker,
 * Audacity label parsing and ffmpeg chapter embedding (local or via GitHub Actions).
 *
 * Data model (one Mix post per mix):
 *   title    → post title
 *   slug     → post slug (used as ?mix=slug on the Mixes page)
 *   album    → post excerpt
 *   artwork  → featured image
 *   artist   → always MIX_ARTIST
 *   file     → ACF "mix_file" (a file name inside wp-content/mixes/)
 *   chapters → ACF "mix_labels" (pasted Audacity label export)
 *   kind     → derived from the file extension (video vs audio)
 *
 * Workflow:
 *   1. Upload the audio/video file to wp-content/mixes/ (FTP).
 *   2. Mixes → Add New: title, pick the file, paste the labels, publish.
 *   3. Chapters are embedded into the file by ffmpeg on the server if present,
 *      otherwise by the "Embed chapters" GitHub Actions workflow if configured,
 *      otherwise the edit screen offers the ffmetadata file + command to run locally.
 */

namespace App;

/* -------------------------------------------------------------------------
 | Config
 | ---------------------------------------------------------------------- */

const MIX_POST_TYPE = 'mix';
const MIX_ARTIST = 'Luke Miller';
const MIX_EXTENSIONS = ['m4a', 'mp4', 'm4v', 'mov', 'mp3', 'aac', 'wav', 'ogg', 'webm'];
const MIX_VIDEO_EXTENSIONS = ['mp4', 'm4v', 'mov', 'webm'];
const MIX_MP4_FAMILY = ['m4a', 'mp4', 'm4v', 'mov'];

/**
 * Read a setting from a wp-config constant or the Acorn .env file.
 */
function mix_setting(string $name): string
{
    if (defined($name)) {
        return (string) constant($name);
    }

    return function_exists('env') ? (string) (env($name) ?? '') : (string) getenv($name);
}

function mix_artist(): string
{
    return (string) apply_filters('lm/mix_artist', MIX_ARTIST);
}

/**
 * Absolute path to the folder that holds all mix files.
 * Override with define('MIXES_DIR', ...) in wp-config.php or the lm/mixes_dir filter.
 */
function mixes_dir(): string
{
    $dir = defined('MIXES_DIR') ? MIXES_DIR : WP_CONTENT_DIR . '/mixes';

    return untrailingslashit(apply_filters('lm/mixes_dir', $dir));
}

/**
 * Public URL of the mixes folder.
 */
function mixes_url(): string
{
    $url = defined('MIXES_URL') ? MIXES_URL : content_url('mixes');

    return untrailingslashit(apply_filters('lm/mixes_url', $url));
}

/**
 * Sanitize a file name coming from post meta / request so it can never escape the folder.
 */
function mix_sanitize_filename(?string $file): string
{
    return sanitize_file_name(basename((string) $file));
}

function mix_file_path(string $file): string
{
    return mixes_dir() . '/' . mix_sanitize_filename($file);
}

function mix_file_url(string $file): string
{
    return mixes_url() . '/' . rawurlencode(mix_sanitize_filename($file));
}

/**
 * 'video' or 'audio', from the file extension.
 */
function mix_kind_for_file(string $file): string
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    return in_array($ext, MIX_VIDEO_EXTENSIONS, true) ? 'video' : 'audio';
}

/**
 * List media files in the mixes folder (natural sort).
 */
function mixes_files(): array
{
    $dir = mixes_dir();
    if (! is_dir($dir)) {
        return [];
    }

    $files = [];
    foreach (scandir($dir) ?: [] as $f) {
        if ($f[0] === '.') {
            continue;
        }
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, MIX_EXTENSIONS, true) && is_file("$dir/$f")) {
            $files[] = $f;
        }
    }
    natcasesort($files);

    return array_values($files);
}

/* -------------------------------------------------------------------------
 | Custom post type
 | ---------------------------------------------------------------------- */

add_action('init', function () {
    register_post_type(MIX_POST_TYPE, [
        'labels' => [
            'name' => 'Mixes',
            'singular_name' => 'Mix',
            'add_new' => 'Add Mix',
            'add_new_item' => 'Add New Mix',
            'edit_item' => 'Edit Mix',
            'new_item' => 'New Mix',
            'view_item' => 'View Mix',
            'search_items' => 'Search Mixes',
            'not_found' => 'No mixes found',
            'not_found_in_trash' => 'No mixes in trash',
            'all_items' => 'All Mixes',
            'menu_name' => 'Mixes',
            'featured_image' => 'Artwork',
            'set_featured_image' => 'Set artwork',
            'remove_featured_image' => 'Remove artwork',
            'use_featured_image' => 'Use as artwork',
        ],
        'description' => 'DJ mixes played on the Mixes / Video Mixes pages.',
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => false, // classic edit screen so ACF + meta boxes render normally
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'rewrite' => false,
        'menu_icon' => 'dashicons-album',
        'menu_position' => 21,
        'hierarchical' => false,
        'supports' => ['title', 'excerpt', 'thumbnail', 'page-attributes'],
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ]);

    // Make sure the mixes folder exists locally; on hosts where this fails
    // the Tools page explains how to create it over FTP.
    if (is_admin() && ! is_dir(mixes_dir())) {
        @wp_mkdir_p(mixes_dir());
    }
});

/* -------------------------------------------------------------------------
 | ACF: local JSON + dynamic file select
 | ---------------------------------------------------------------------- */

add_filter('acf/settings/save_json', fn () => get_stylesheet_directory() . '/acf-json');

add_filter('acf/settings/load_json', function ($paths) {
    $paths = is_array($paths) ? $paths : [];
    $paths[] = get_stylesheet_directory() . '/acf-json';

    return array_values(array_unique($paths));
});

/**
 * Populate the "File" select with whatever lives in the mixes folder.
 */
add_filter('acf/load_field/name=mix_file', function ($field) {
    // Don't bake choices into the field group when editing it in the ACF UI.
    if (function_exists('acf_is_screen') && acf_is_screen('acf-field-group')) {
        return $field;
    }

    $choices = [];
    foreach (mixes_files() as $f) {
        $choices[$f] = $f;
    }

    // Keep a saved value selectable even if the file has gone missing.
    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : (int) ($GLOBALS['post']->ID ?? 0);
    if ($post_id) {
        $current = (string) get_post_meta($post_id, 'mix_file', true);
        if ($current !== '' && ! isset($choices[$current])) {
            $choices[$current] = $current . ' (missing from folder)';
        }
    }

    $field['choices'] = $choices;
    $field['instructions'] = sprintf(
        'Files found in <code>%s</code>. Upload new ones there via FTP, then reload this page. Video files (mp4/m4v/mov/webm) show on the Video Mixes page, everything else on the Mixes page.',
        esc_html(str_replace(ABSPATH, '/', mixes_dir()))
    );

    return $field;
});

/* -------------------------------------------------------------------------
 | Label parsing
 | ---------------------------------------------------------------------- */

/**
 * Parse an Audacity label export into [[start, end, title], ...] sorted by start.
 * Accepts tab or whitespace separated "start end title" lines; a missing end
 * is filled in from the next chapter's start.
 */
function mix_parse_labels(?string $raw): array
{
    $rows = [];
    foreach (preg_split("/\r\n|\r|\n/", (string) $raw) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = preg_split("/\t+/", $line);
        if (count($parts) < 2) {
            $parts = preg_split("/\s+/", $line, 3);
        }
        if (! is_numeric($parts[0] ?? null)) {
            continue;
        }
        $start = (float) $parts[0];
        $end = isset($parts[1]) && is_numeric($parts[1]) ? (float) $parts[1] : null;
        $title = trim((string) ($parts[2] ?? (is_numeric($parts[1] ?? null) ? '' : ($parts[1] ?? ''))));
        $rows[] = [round($start, 3), $end === null ? null : round($end, 3), $title];
    }

    usort($rows, fn ($a, $b) => $a[0] <=> $b[0]);

    foreach ($rows as $i => &$row) {
        if ($row[2] === '') {
            $row[2] = 'Chapter ' . ($i + 1);
        }
        $next = $rows[$i + 1][0] ?? null;
        if ($row[1] === null || $row[1] <= $row[0]) {
            $row[1] = $next !== null ? $next : $row[0] + 1;
        }
    }
    unset($row);

    return $rows;
}

/**
 * Chapters in the shape the front-end player expects: [[start, title], ...].
 */
function mix_player_chapters(?string $raw): array
{
    return array_map(fn ($r) => [$r[0], $r[2]], mix_parse_labels($raw));
}

/* -------------------------------------------------------------------------
 | Data for templates
 | ---------------------------------------------------------------------- */

/**
 * Everything the templates and ffmpeg need for one mix post.
 */
function mix_data(\WP_Post|int $post): array
{
    $post = get_post($post);
    $file = mix_sanitize_filename((string) get_post_meta($post->ID, 'mix_file', true));

    return [
        'slug' => $post->post_name,
        'title' => get_the_title($post),
        'artist' => mix_artist(),
        'album' => trim((string) $post->post_excerpt),
        'file' => $file,
        'kind' => $file !== '' ? mix_kind_for_file($file) : 'audio',
        'url' => $file !== '' ? mix_file_url($file) : '',
        'artwork' => get_the_post_thumbnail_url($post, 'full') ?: '',
        'chapters' => mix_player_chapters((string) get_post_meta($post->ID, 'mix_labels', true)),
    ];
}

/**
 * All published mixes of a kind ('audio' | 'video'), ordered by menu order then date.
 * Falls back to the legacy ACF repeater on the current page when no mix posts exist yet.
 */
function get_mixes(string $kind = 'audio'): array
{
    $url_key = $kind === 'video' ? 'videoUrl' : 'audioUrl';

    $posts = get_posts([
        'post_type' => MIX_POST_TYPE,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
    ]);

    if ($posts) {
        $out = [];
        foreach ($posts as $p) {
            $m = mix_data($p);
            if ($m['kind'] !== $kind || $m['file'] === '') {
                continue;
            }
            $m[$url_key] = $m['url'];
            unset($m['url'], $m['kind']);
            $out[] = $m;
        }

        return $out;
    }

    // Legacy: repeater on the page itself.
    if (! function_exists('get_field')) {
        return [];
    }

    return array_values(array_map(fn ($m) => [
        'slug' => $m['slug'] ?? '',
        'title' => $m['title'] ?? '',
        'artist' => $m['artist'] ?? mix_artist(),
        'album' => $m['album'] ?? '',
        'file' => basename((string) ($m['audio_url'] ?? '')),
        $url_key => $m['audio_url'] ?? '',
        'artwork' => $m['artwork'] ?? '',
        'chapters' => function_exists('parse_audacity_labels')
            ? parse_audacity_labels((string) ($m['labels_raw'] ?? ''))
            : mix_player_chapters($m['labels_raw'] ?? ''),
    ], get_field('mixes') ?: []));
}

/* -------------------------------------------------------------------------
 | ffmpeg (local)
 | ---------------------------------------------------------------------- */

function mix_exec_available(): bool
{
    if (! function_exists('exec')) {
        return false;
    }
    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

    return ! in_array('exec', $disabled, true);
}

/**
 * Locate the ffmpeg binary. Override with define('MIXES_FFMPEG', '/path/to/ffmpeg').
 */
function mix_ffmpeg_path(): ?string
{
    static $resolved = false;
    if ($resolved !== false) {
        return $resolved;
    }

    $candidates = [];
    if (defined('MIXES_FFMPEG')) {
        $candidates[] = MIXES_FFMPEG;
    }
    $candidates = array_merge($candidates, [
        '/usr/local/bin/ffmpeg',
        '/opt/homebrew/bin/ffmpeg',
        '/usr/bin/ffmpeg',
        '/opt/local/bin/ffmpeg',
    ]);

    foreach ($candidates as $c) {
        if ($c && is_file($c) && is_executable($c)) {
            return $resolved = $c;
        }
    }

    if (mix_exec_available()) {
        $found = trim((string) @exec('command -v ffmpeg 2>/dev/null'));
        if ($found !== '' && is_executable($found)) {
            return $resolved = $found;
        }
    }

    return $resolved = null;
}

function mix_can_embed(): bool
{
    return mix_exec_available() && mix_ffmpeg_path() !== null;
}

function mix_auto_embed_enabled(): bool
{
    return (bool) get_option('lm_mixes_auto_embed', 1);
}

function mix_ffmeta_escape(string $v): string
{
    return str_replace(["\\", '=', ';', '#', "\n"], ["\\\\", '\\=', '\\;', '\\#', "\\\n"], $v);
}

/**
 * Build an FFMETADATA1 document from parsed labels (+ global tags).
 */
function mix_ffmetadata(array $labels, array $tags = []): string
{
    $out = ";FFMETADATA1\n";
    foreach ($tags as $k => $v) {
        if ((string) $v !== '') {
            $out .= $k . '=' . mix_ffmeta_escape((string) $v) . "\n";
        }
    }
    foreach ($labels as [$start, $end, $title]) {
        $out .= "\n[CHAPTER]\nTIMEBASE=1/1000\n";
        $out .= 'START=' . (int) round($start * 1000) . "\n";
        $out .= 'END=' . (int) round($end * 1000) . "\n";
        $out .= 'title=' . mix_ffmeta_escape($title) . "\n";
    }

    return $out;
}

/**
 * Tags written to the file's global metadata.
 */
function mix_tags(int $post_id): array
{
    $m = mix_data($post_id);

    return ['title' => $m['title'], 'artist' => $m['artist'], 'album' => $m['album']];
}

/**
 * The ffmpeg command, shell-escaped. $ffmpeg may be a plain "ffmpeg" for display.
 */
function mix_ffmpeg_command(string $ffmpeg, string $in, string $meta, string $out, array $tags): string
{
    $ext = strtolower(pathinfo($in, PATHINFO_EXTENSION));
    $parts = [
        $ffmpeg === 'ffmpeg' ? 'ffmpeg' : escapeshellarg($ffmpeg),
        '-hide_banner -loglevel error -y',
        '-i ' . escapeshellarg($in),
        '-i ' . escapeshellarg($meta),
        '-map 0 -map_metadata 0 -map_chapters 1 -codec copy',
    ];
    foreach ($tags as $k => $v) {
        if ((string) $v !== '') {
            $parts[] = '-metadata ' . escapeshellarg("$k=$v");
        }
    }
    if (in_array($ext, MIX_MP4_FAMILY, true)) {
        $parts[] = '-movflags +faststart';
    }
    $parts[] = escapeshellarg($out);

    return implode(' ', $parts);
}

/**
 * Fingerprint of everything that influences the embedded result.
 */
function mix_embed_hash(int $post_id): string
{
    return md5(implode("\n", [
        (string) get_post_meta($post_id, 'mix_file', true),
        (string) get_post_meta($post_id, 'mix_labels', true),
        wp_json_encode(mix_tags($post_id)),
    ]));
}

function mix_embed_status(int $post_id): array
{
    $s = get_post_meta($post_id, '_mix_embed', true);

    return is_array($s) ? $s : [];
}

function mix_set_embed_status(int $post_id, ?bool $ok, string $message, string $hash = '', array $extra = []): void
{
    update_post_meta($post_id, '_mix_embed', $extra + [
        'ok' => $ok, // true = done, false = failed, null = pending (remote)
        'time' => time(),
        'message' => $message,
        'file' => (string) get_post_meta($post_id, 'mix_file', true),
        'hash' => $hash,
    ]);
}

/**
 * Embed the chapters (and title/artist/album tags) into the mix file in place.
 */
function mix_embed_chapters(int $post_id): array
{
    $file = mix_sanitize_filename((string) get_post_meta($post_id, 'mix_file', true));
    $labels = mix_parse_labels((string) get_post_meta($post_id, 'mix_labels', true));

    $fail = function (string $msg) use ($post_id) {
        mix_set_embed_status($post_id, false, $msg);

        return ['ok' => false, 'message' => $msg];
    };

    if ($file === '') {
        return $fail('No file selected.');
    }
    if (! $labels) {
        return $fail('No chapters could be parsed from the labels.');
    }
    if (! mix_exec_available()) {
        return $fail('PHP exec() is disabled on this server.');
    }
    $ffmpeg = mix_ffmpeg_path();
    if (! $ffmpeg) {
        return $fail('ffmpeg binary not found on this server.');
    }

    $in = mix_file_path($file);
    if (! is_file($in)) {
        return $fail("File not found: $in");
    }
    if (! is_writable($in) || ! is_writable(dirname($in))) {
        return $fail('The mixes folder or file is not writable by PHP.');
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $tmp_out = dirname($in) . '/.' . pathinfo($file, PATHINFO_FILENAME) . '.embedding.' . $ext;
    $meta = tempnam(get_temp_dir(), 'ffmeta-' . $post_id . '-');
    if (! $meta || file_put_contents($meta, mix_ffmetadata($labels, mix_tags($post_id))) === false) {
        return $fail('Could not write temporary metadata file.');
    }

    if (function_exists('set_time_limit')) {
        @set_time_limit(600);
    }

    $cmd = mix_ffmpeg_command($ffmpeg, $in, $meta, $tmp_out, mix_tags($post_id));
    $output = [];
    $code = 1;
    @exec($cmd . ' 2>&1', $output, $code);
    @unlink($meta);

    if ($code !== 0 || ! is_file($tmp_out) || filesize($tmp_out) < 1024) {
        @unlink($tmp_out);

        return $fail('ffmpeg failed (exit ' . $code . '): ' . trim(implode("\n", $output)));
    }

    if (! @rename($tmp_out, $in)) {
        @unlink($tmp_out);

        return $fail('ffmpeg succeeded but the original file could not be replaced.');
    }
    @chmod($in, 0644);

    $msg = sprintf('%d chapters embedded into %s.', count($labels), $file);
    mix_set_embed_status($post_id, true, $msg, mix_embed_hash($post_id), ['via' => 'server']);

    return ['ok' => true, 'message' => $msg];
}

/* -------------------------------------------------------------------------
 | ffmpeg (remote, via GitHub Actions)
 |
 | For hosts without ffmpeg. WordPress fires a repository_dispatch event; the
 | .github/workflows/embed-chapters.yml workflow downloads the file + ffmetadata
 | from the site, runs ffmpeg, uploads the result over SFTP, then calls back.
 |
 | Configure in wp-config.php (or .env):
 |   MIXES_GITHUB_REPO     e.g. "lamiller0622/lukemillers-theme"
 |   MIXES_GITHUB_TOKEN    PAT with permission to trigger workflows (Contents: write)
 |   MIXES_WEBHOOK_SECRET  random string, same value as the MIXES_WEBHOOK_SECRET repo secret
 | ---------------------------------------------------------------------- */

function mix_remote_configured(): bool
{
    return mix_setting('MIXES_GITHUB_REPO') !== ''
        && mix_setting('MIXES_GITHUB_TOKEN') !== ''
        && mix_setting('MIXES_WEBHOOK_SECRET') !== '';
}

function mix_remote_dispatch(int $post_id): array
{
    $file = mix_sanitize_filename((string) get_post_meta($post_id, 'mix_file', true));
    $labels = mix_parse_labels((string) get_post_meta($post_id, 'mix_labels', true));

    $fail = function (string $msg) use ($post_id) {
        mix_set_embed_status($post_id, false, $msg);

        return ['ok' => false, 'message' => $msg];
    };

    if (! mix_remote_configured()) {
        return $fail('GitHub Actions embedding is not configured (MIXES_GITHUB_REPO / MIXES_GITHUB_TOKEN / MIXES_WEBHOOK_SECRET).');
    }
    if ($file === '') {
        return $fail('No file selected.');
    }
    if (! $labels) {
        return $fail('No chapters could be parsed from the labels.');
    }

    $hash = mix_embed_hash($post_id);
    $response = wp_remote_post('https://api.github.com/repos/' . mix_setting('MIXES_GITHUB_REPO') . '/dispatches', [
        'timeout' => 15,
        'headers' => [
            'Accept' => 'application/vnd.github+json',
            'Authorization' => 'Bearer ' . mix_setting('MIXES_GITHUB_TOKEN'),
            'X-GitHub-Api-Version' => '2022-11-28',
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'event_type' => 'embed-chapters',
            'client_payload' => [
                'post_id' => $post_id,
                'file' => $file,
                'file_url' => mix_file_url($file),
                'ffmeta_url' => rest_url('luke/v1/mix/' . $post_id . '/ffmetadata'),
                'callback_url' => rest_url('luke/v1/mix/' . $post_id . '/embedded'),
                'hash' => $hash,
                'chapters' => count($labels),
            ],
        ]),
    ]);

    if (is_wp_error($response)) {
        return $fail('GitHub request failed: ' . $response->get_error_message());
    }
    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code !== 204) {
        return $fail('GitHub rejected the dispatch (HTTP ' . $code . '): ' . wp_strip_all_tags(wp_remote_retrieve_body($response)));
    }

    $msg = 'Queued on GitHub Actions; the file will be replaced in a few minutes.';
    mix_set_embed_status($post_id, null, $msg, $hash, ['via' => 'github']);

    return ['ok' => true, 'message' => $msg];
}

add_action('rest_api_init', function () {
    // Public: chapters are already public on the Mixes page.
    register_rest_route('luke/v1', '/mix/(?P<id>\d+)/ffmetadata', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (\WP_REST_Request $r) {
            $post_id = (int) $r['id'];
            if (get_post_type($post_id) !== MIX_POST_TYPE) {
                return new \WP_Error('not_found', 'No such mix', ['status' => 404]);
            }
            $labels = mix_parse_labels((string) get_post_meta($post_id, 'mix_labels', true));
            $body = mix_ffmetadata($labels, mix_tags($post_id));

            $response = new \WP_REST_Response($body);
            $response->header('Content-Type', 'text/plain; charset=utf-8');

            return $response;
        },
    ]);

    // Called by the workflow when it has finished (or failed).
    register_rest_route('luke/v1', '/mix/(?P<id>\d+)/embedded', [
        'methods' => 'POST',
        'permission_callback' => function (\WP_REST_Request $r) {
            $secret = mix_setting('MIXES_WEBHOOK_SECRET');

            return $secret !== '' && hash_equals($secret, (string) $r->get_header('x-mix-secret'));
        },
        'callback' => function (\WP_REST_Request $r) {
            $post_id = (int) $r['id'];
            if (get_post_type($post_id) !== MIX_POST_TYPE) {
                return new \WP_Error('not_found', 'No such mix', ['status' => 404]);
            }
            $ok = (bool) $r->get_param('ok');
            $message = sanitize_text_field((string) $r->get_param('message'));
            $hash = sanitize_text_field((string) $r->get_param('hash'));
            mix_set_embed_status($post_id, $ok, $message ?: ($ok ? 'Embedded via GitHub Actions.' : 'GitHub Actions run failed.'), $ok ? $hash : '', ['via' => 'github']);

            return ['ok' => true];
        },
    ]);
});

/**
 * Serve plain text for the ffmetadata route instead of a JSON-encoded string.
 */
add_filter('rest_pre_serve_request', function ($served, $result, $request) {
    if (str_starts_with($request->get_route(), '/luke/v1/mix/') && str_ends_with($request->get_route(), '/ffmetadata') && is_string($result->get_data())) {
        echo $result->get_data();

        return true;
    }

    return $served;
}, 10, 3);

/* -------------------------------------------------------------------------
 | Save hook: embed when something relevant changed
 | ---------------------------------------------------------------------- */

add_action('acf/save_post', function ($post_id) {
    if (get_post_type($post_id) !== MIX_POST_TYPE || wp_is_post_revision($post_id)) {
        return;
    }
    if (! mix_auto_embed_enabled()) {
        return;
    }
    if ((string) get_post_meta($post_id, 'mix_file', true) === '') {
        return;
    }
    $status = mix_embed_status($post_id);
    $hash = mix_embed_hash($post_id);
    if (($status['hash'] ?? '') === $hash && (! empty($status['ok']) || ($status['ok'] ?? false) === null)) {
        return; // already embedded (or queued) with identical data
    }

    if (mix_can_embed()) {
        mix_embed_chapters($post_id);
    } elseif (mix_remote_configured()) {
        mix_remote_dispatch($post_id);
    }
}, 20);

/* -------------------------------------------------------------------------
 | Admin: meta boxes on the edit screen
 | ---------------------------------------------------------------------- */

add_action('add_meta_boxes_' . MIX_POST_TYPE, function () {
    // Replace the generic excerpt box with a one-line "Album" field (still saved as the excerpt).
    remove_meta_box('postexcerpt', MIX_POST_TYPE, 'normal');
    add_meta_box('mix-album', 'Album', function (\WP_Post $post) {
        echo '<input type="text" name="excerpt" class="widefat" value="' . esc_attr($post->post_excerpt) . '" placeholder="e.g. Mixes 2025">';
        echo '<p class="description">Shown as the album in Media Session (lock screen) and written into the file. Artist is always ' . esc_html(mix_artist()) . '.</p>';
    }, MIX_POST_TYPE, 'normal', 'high');

    add_meta_box('mix-file-status', 'File & Chapters', __NAMESPACE__ . '\\mix_render_status_box', MIX_POST_TYPE, 'side', 'high');
});

function mix_render_status_box(\WP_Post $post): void
{
    $file = mix_sanitize_filename((string) get_post_meta($post->ID, 'mix_file', true));
    $labels = mix_parse_labels((string) get_post_meta($post->ID, 'mix_labels', true));
    $status = mix_embed_status($post->ID);
    $path = $file ? mix_file_path($file) : '';
    $exists = $path && is_file($path);
    $current_hash = mix_embed_hash($post->ID);
    $up_to_date = ! empty($status['ok']) && ($status['hash'] ?? '') === $current_hash;

    echo '<style>#mix-file-status p{margin:.4em 0}#mix-file-status code{font-size:11px;word-break:break-all}#mix-file-status .ok{color:#007017}#mix-file-status .bad{color:#b32d2e}#mix-file-status .wait{color:#996800}#mix-file-status textarea{width:100%;font:11px/1.4 monospace}</style>';

    echo '<p><strong>File</strong><br>';
    if (! $file) {
        echo '<span class="bad">None selected.</span>';
    } elseif (! $exists) {
        echo '<span class="bad">Missing:</span> <code>' . esc_html($file) . '</code>';
    } else {
        echo '<a href="' . esc_url(mix_file_url($file)) . '" target="_blank" rel="noopener">' . esc_html($file) . '</a> <span class="description">(' . esc_html(size_format(filesize($path))) . ', ' . esc_html(mix_kind_for_file($file)) . ')</span>';
    }
    echo '</p>';

    echo '<p><strong>Chapters parsed</strong><br>' . count($labels);
    if ($labels) {
        $last = end($labels);
        echo ' <span class="description">(' . esc_html($labels[0][2]) . ' … ' . esc_html($last[2]) . ')</span>';
    }
    echo '</p>';

    echo '<p><strong>Embedded in file</strong><br>';
    if ($status) {
        $cls = ($status['ok'] ?? null) === null ? 'wait' : (! empty($status['ok']) ? 'ok' : 'bad');
        echo '<span class="' . $cls . '">' . esc_html($status['message'] ?? '') . '</span><br>';
        echo '<span class="description">' . esc_html(wp_date('M j, Y g:i a', (int) ($status['time'] ?? 0))) . (! empty($status['via']) ? ' via ' . esc_html($status['via']) : '') . '</span>';
        if (! empty($status['ok']) && ! $up_to_date) {
            echo '<br><span class="bad">Title, album, labels or file changed since — re-embed.</span>';
        }
    } else {
        echo '<span class="description">Never.</span>';
    }
    echo '</p>';

    if ($post->post_status === 'auto-draft') {
        echo '<p class="description">Save the mix first to enable embedding.</p>';

        return;
    }

    $embed_url = wp_nonce_url(admin_url('admin-post.php?action=mix_embed&post=' . $post->ID), 'mix_embed_' . $post->ID);
    $remote_url = wp_nonce_url(admin_url('admin-post.php?action=mix_embed_remote&post=' . $post->ID), 'mix_embed_remote_' . $post->ID);
    $meta_url = wp_nonce_url(admin_url('admin-post.php?action=mix_ffmeta&post=' . $post->ID), 'mix_ffmeta_' . $post->ID);
    $auto = mix_auto_embed_enabled() ? ' Runs automatically on save.' : ' Auto-embed is off.';

    if (mix_can_embed()) {
        echo '<p><a class="button button-primary" href="' . esc_url($embed_url) . '">Embed chapters now</a></p>';
        echo '<p class="description">ffmpeg on this server: <code>' . esc_html(mix_ffmpeg_path()) . '</code>.' . $auto . '</p>';
    } elseif (mix_remote_configured()) {
        echo '<p><a class="button button-primary" href="' . esc_url($remote_url) . '">Embed via GitHub Actions</a></p>';
        echo '<p class="description">No ffmpeg on this server; the workflow downloads the file, embeds the chapters and uploads it back.' . $auto . '</p>';
    } else {
        echo '<p><span class="bad">ffmpeg is not available on this server</span> (' . (mix_exec_available() ? 'binary not found' : 'exec() disabled') . ') and GitHub Actions embedding is not configured. ';
        echo 'The web player still shows the chapters; only the downloadable file lacks them. To embed them locally:</p>';
        if ($file) {
            $cmd = mix_ffmpeg_command('ffmpeg', $file, 'ffmetadata.txt', 'out-' . $file, mix_tags($post->ID));
            echo '<textarea readonly rows="5" onclick="this.select()">' . esc_textarea($cmd . "\nmv " . escapeshellarg('out-' . $file) . ' ' . escapeshellarg($file)) . '</textarea>';
            echo '<p class="description">Then upload the result back to the mixes folder.</p>';
        }
    }
    echo '<p><a href="' . esc_url($meta_url) . '">Download ffmetadata.txt</a></p>';
}

/* -------------------------------------------------------------------------
 | Admin: actions (admin-post.php)
 | ---------------------------------------------------------------------- */

function mix_redirect(string $url, string $msg, bool $ok = true): void
{
    wp_safe_redirect(add_query_arg(['mix_msg' => rawurlencode($msg), 'mix_ok' => $ok ? 1 : 0], $url));
    exit;
}

function mix_tools_url(): string
{
    return admin_url('edit.php?post_type=' . MIX_POST_TYPE . '&page=mix-tools');
}

function mix_guard_post_action(string $action): int
{
    $post_id = (int) ($_GET['post'] ?? 0);
    check_admin_referer($action . '_' . $post_id);
    if (! current_user_can('edit_post', $post_id) || get_post_type($post_id) !== MIX_POST_TYPE) {
        wp_die('Not allowed.');
    }

    return $post_id;
}

add_action('admin_post_mix_embed', function () {
    $post_id = mix_guard_post_action('mix_embed');
    $result = mix_embed_chapters($post_id);
    mix_redirect(get_edit_post_link($post_id, 'raw'), $result['message'], $result['ok']);
});

add_action('admin_post_mix_embed_remote', function () {
    $post_id = mix_guard_post_action('mix_embed_remote');
    $result = mix_remote_dispatch($post_id);
    mix_redirect(get_edit_post_link($post_id, 'raw'), $result['message'], $result['ok']);
});

add_action('admin_post_mix_ffmeta', function () {
    $post_id = mix_guard_post_action('mix_ffmeta');
    $labels = mix_parse_labels((string) get_post_meta($post_id, 'mix_labels', true));
    $name = pathinfo((string) get_post_meta($post_id, 'mix_file', true), PATHINFO_FILENAME) ?: 'mix';

    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($name . '-ffmetadata.txt') . '"');
    echo mix_ffmetadata($labels, mix_tags($post_id));
    exit;
});

add_action('admin_notices', function () {
    if (empty($_GET['mix_msg'])) {
        return;
    }
    printf(
        '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
        ! empty($_GET['mix_ok']) ? 'success' : 'error',
        esc_html(rawurldecode(wp_unslash($_GET['mix_msg'])))
    );
});

/* -------------------------------------------------------------------------
 | Admin: list table columns
 | ---------------------------------------------------------------------- */

add_filter('manage_' . MIX_POST_TYPE . '_posts_columns', function ($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
        $new[$k] = $v;
        if ($k === 'title') {
            $new['mix_kind'] = 'Kind';
            $new['mix_file'] = 'File';
            $new['mix_album'] = 'Album';
            $new['mix_chapters'] = 'Chapters';
            $new['mix_embed'] = 'Embedded';
            $new['mix_order'] = 'Order';
        }
    }
    unset($new['date']);

    return $new;
});

add_action('manage_' . MIX_POST_TYPE . '_posts_custom_column', function ($col, $post_id) {
    $file = mix_sanitize_filename((string) get_post_meta($post_id, 'mix_file', true));
    switch ($col) {
        case 'mix_kind':
            echo $file ? esc_html(ucfirst(mix_kind_for_file($file))) : '—';
            break;
        case 'mix_file':
            if (! $file) {
                echo '<span style="color:#b32d2e">—</span>';
            } elseif (! is_file(mix_file_path($file))) {
                echo '<code style="color:#b32d2e" title="Missing from folder">' . esc_html($file) . '</code>';
            } else {
                echo '<code>' . esc_html($file) . '</code>';
            }
            break;
        case 'mix_album':
            echo esc_html(get_post_field('post_excerpt', $post_id)) ?: '—';
            break;
        case 'mix_chapters':
            echo count(mix_parse_labels((string) get_post_meta($post_id, 'mix_labels', true)));
            break;
        case 'mix_embed':
            $s = mix_embed_status($post_id);
            if (! $s) {
                echo '—';
            } elseif (($s['ok'] ?? null) === null) {
                echo '<span style="color:#996800">Queued</span>';
            } elseif (! empty($s['ok'])) {
                $stale = ($s['hash'] ?? '') !== mix_embed_hash($post_id);
                echo $stale ? '<span style="color:#996800">Stale</span>' : '<span style="color:#007017">✓</span>';
            } else {
                echo '<span style="color:#b32d2e" title="' . esc_attr($s['message'] ?? '') . '">Failed</span>';
            }
            break;
        case 'mix_order':
            echo (int) get_post_field('menu_order', $post_id);
            break;
    }
}, 10, 2);

add_action('pre_get_posts', function ($q) {
    if (is_admin() && $q->is_main_query() && $q->get('post_type') === MIX_POST_TYPE && ! $q->get('orderby')) {
        $q->set('orderby', ['menu_order' => 'ASC', 'date' => 'DESC']);
    }
});

/* -------------------------------------------------------------------------
 | Admin: Tools page (environment, settings, seeding, legacy import)
 | ---------------------------------------------------------------------- */

add_action('admin_init', function () {
    register_setting('lm_mixes', 'lm_mixes_auto_embed', [
        'type' => 'boolean',
        'sanitize_callback' => fn ($v) => $v ? 1 : 0,
        'default' => 1,
    ]);
});

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=' . MIX_POST_TYPE,
        'Mix Tools',
        'Tools',
        'manage_options',
        'mix-tools',
        __NAMESPACE__ . '\\mix_render_tools_page'
    );
});

/**
 * Map of file name → mix posts using it (any status).
 */
function mix_files_in_use(): array
{
    $in_use = [];
    foreach (get_posts(['post_type' => MIX_POST_TYPE, 'post_status' => 'any', 'numberposts' => -1]) as $p) {
        $f = (string) get_post_meta($p->ID, 'mix_file', true);
        if ($f !== '') {
            $in_use[$f][] = $p;
        }
    }

    return $in_use;
}

function mix_render_tools_page(): void
{
    $dir = mixes_dir();
    $files = mixes_files();
    $in_use = mix_files_in_use();
    $unused = array_values(array_filter($files, fn ($f) => empty($in_use[$f])));
    $legacy = mix_legacy_rows();
    $seed_url = wp_nonce_url(admin_url('admin-post.php?action=mix_seed_folder'), 'mix_seed_folder');
    $import_url = wp_nonce_url(admin_url('admin-post.php?action=mix_import_legacy'), 'mix_import_legacy');

    $yes = '<span style="color:#007017">Yes</span>';
    $no = '<span style="color:#b32d2e">No</span>';
    ?>
    <div class="wrap">
        <h1>Mix Tools</h1>

        <h2>Environment</h2>
        <table class="widefat striped" style="max-width:900px">
            <tbody>
            <tr><th style="width:240px">Mixes folder</th><td><code><?php echo esc_html($dir); ?></code><br><a href="<?php echo esc_url(mixes_url()); ?>/" target="_blank" rel="noopener"><?php echo esc_html(mixes_url()); ?>/</a></td></tr>
            <tr><th>Folder exists</th><td><?php echo is_dir($dir) ? $yes : $no . ' — create <code>' . esc_html(str_replace(ABSPATH, '', $dir)) . '</code> via FTP.'; ?></td></tr>
            <tr><th>Folder writable by PHP</th><td><?php echo is_dir($dir) && is_writable($dir) ? $yes : $no . ' (needed for server-side embedding)'; ?></td></tr>
            <tr><th>PHP exec() available</th><td><?php echo mix_exec_available() ? $yes : $no; ?></td></tr>
            <tr><th>ffmpeg binary</th><td><?php echo mix_ffmpeg_path() ? '<code>' . esc_html(mix_ffmpeg_path()) . '</code>' : $no . ' — define <code>MIXES_FFMPEG</code> in wp-config.php if it lives somewhere unusual.'; ?></td></tr>
            <tr><th>Server-side embedding</th><td><?php echo mix_can_embed() ? $yes : $no; ?></td></tr>
            <tr><th>GitHub Actions embedding</th><td><?php
                if (mix_remote_configured()) {
                    echo $yes . ' — repo <code>' . esc_html(mix_setting('MIXES_GITHUB_REPO')) . '</code>';
                } else {
                    $missing = array_filter(['MIXES_GITHUB_REPO', 'MIXES_GITHUB_TOKEN', 'MIXES_WEBHOOK_SECRET'], fn ($k) => mix_setting($k) === '');
                    echo $no . ' — set <code>' . esc_html(implode('</code>, <code>', $missing)) . '</code> in wp-config.php or .env (see <code>.github/workflows/embed-chapters.yml</code>).';
                }
            ?></td></tr>
            <tr><th>Which will be used on save</th><td><?php echo mix_can_embed() ? 'Server ffmpeg' : (mix_remote_configured() ? 'GitHub Actions' : 'Neither — manual ffmetadata download only'); ?></td></tr>
            <tr><th>PHP max_execution_time</th><td><?php echo esc_html(ini_get('max_execution_time')); ?>s</td></tr>
            </tbody>
        </table>

        <h2>Settings</h2>
        <form method="post" action="options.php" style="max-width:900px">
            <?php settings_fields('lm_mixes'); ?>
            <label><input type="checkbox" name="lm_mixes_auto_embed" value="1" <?php checked(mix_auto_embed_enabled()); ?>> Automatically embed chapters into the file whenever a mix is saved.</label>
            <?php submit_button('Save settings', 'secondary', 'submit', true); ?>
        </form>

        <h2>Files in folder (<?php echo count($files); ?>)</h2>
        <?php if (! $files): ?>
            <p>No media files found. Upload <code>.m4a</code> / <code>.mp4</code> files to the folder above.</p>
        <?php else: ?>
        <table class="widefat striped" style="max-width:900px">
            <thead><tr><th>File</th><th>Kind</th><th>Size</th><th>Modified</th><th>Used by</th></tr></thead>
            <tbody>
            <?php foreach ($files as $f): $p = "$dir/$f"; ?>
                <tr>
                    <td><code><?php echo esc_html($f); ?></code></td>
                    <td><?php echo esc_html(mix_kind_for_file($f)); ?></td>
                    <td><?php echo esc_html(size_format(filesize($p))); ?></td>
                    <td><?php echo esc_html(wp_date('M j, Y g:i a', filemtime($p))); ?></td>
                    <td><?php
                        if (empty($in_use[$f])) {
                            echo '<span class="description">— not used yet</span>';
                        } else {
                            echo implode(', ', array_map(fn ($mp) => '<a href="' . esc_url(get_edit_post_link($mp->ID)) . '">' . esc_html(get_the_title($mp)) . '</a>' . ($mp->post_status !== 'publish' ? ' <span class="description">(' . esc_html($mp->post_status) . ')</span>' : ''), $in_use[$f]));
                        }
                    ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <h2>Seed mixes from folder</h2>
        <?php if (! $unused): ?>
            <p class="description">Every file in the folder already has a mix post.</p>
        <?php else: ?>
            <p>Creates a <strong>draft</strong> Mix post for each of the <?php echo count($unused); ?> unused file(s) below, with the title and slug taken from the file name. Open each draft, paste the labels, set the album and artwork, then publish.</p>
            <ul style="margin-left:1.5em;list-style:disc">
                <?php foreach ($unused as $f): ?>
                    <li><code><?php echo esc_html($f); ?></code> → "<?php echo esc_html(mix_title_from_filename($f)); ?>" (<code><?php echo esc_html(sanitize_title(pathinfo($f, PATHINFO_FILENAME))); ?></code>)</li>
                <?php endforeach; ?>
            </ul>
            <p><a class="button button-primary" href="<?php echo esc_url($seed_url); ?>">Create draft mixes</a></p>
        <?php endif; ?>

        <h2>Import legacy mixes</h2>
        <?php if (! $legacy): ?>
            <p class="description">No mixes found in the old page repeaters.</p>
        <?php else: ?>
            <p>Found <?php echo count($legacy); ?> mix(es) in the old ACF repeater on the Mixes / Video Mixes pages. Importing creates a published Mix post for each one that doesn't already exist (matched by slug): title, slug, album → excerpt, artwork → featured image, labels, and the file name from the end of the old audio URL. Make sure that file is in the mixes folder.</p>
            <table class="widefat striped" style="max-width:900px">
                <thead><tr><th>Slug</th><th>Title</th><th>Album</th><th>File</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($legacy as $row): ?>
                    <tr>
                        <td><code><?php echo esc_html($row['slug']); ?></code></td>
                        <td><?php echo esc_html($row['title']); ?></td>
                        <td><?php echo esc_html($row['album']); ?></td>
                        <td><code><?php echo esc_html($row['file']); ?></code> <?php echo is_file(mix_file_path($row['file'])) ? '' : '<span style="color:#b32d2e">(not in folder)</span>'; ?></td>
                        <td><?php echo $row['exists'] ? 'Already imported' : 'Will import'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><a class="button" href="<?php echo esc_url($import_url); ?>">Import missing mixes</a></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * "25raw-meta.m4a" → "25raw Meta".
 */
function mix_title_from_filename(string $file): string
{
    $base = pathinfo($file, PATHINFO_FILENAME);
    $base = preg_replace('/[-_]+/', ' ', $base);

    return ucwords(trim((string) $base));
}

add_action('admin_post_mix_seed_folder', function () {
    check_admin_referer('mix_seed_folder');
    if (! current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }

    $in_use = mix_files_in_use();
    $created = 0;
    foreach (mixes_files() as $file) {
        if (! empty($in_use[$file])) {
            continue;
        }
        $post_id = wp_insert_post([
            'post_type' => MIX_POST_TYPE,
            'post_status' => 'draft',
            'post_title' => mix_title_from_filename($file),
            'post_name' => sanitize_title(pathinfo($file, PATHINFO_FILENAME)),
        ], true);
        if (is_wp_error($post_id)) {
            continue;
        }
        update_field('field_mix_file', $file, $post_id);
        $created++;
    }

    mix_redirect(mix_tools_url(), "Created $created draft mix(es).");
});

/**
 * Rows from the old page repeaters, with their import state.
 */
function mix_legacy_rows(): array
{
    if (! function_exists('get_field')) {
        return [];
    }

    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'numberposts' => -1,
        'meta_key' => '_wp_page_template',
        'meta_value' => ['mixes.blade.php', 'video-mixes.blade.php'],
        'meta_compare' => 'IN',
    ]);

    $rows = [];
    foreach ($pages as $page) {
        foreach ((array) (get_field('mixes', $page->ID, false) ?: []) as $m) {
            $slug = sanitize_title($m['field_mix_slug'] ?? $m['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $rows[] = [
                'page_id' => $page->ID,
                'slug' => $slug,
                'title' => (string) ($m['field_mix_title'] ?? $m['title'] ?? $slug),
                'album' => (string) ($m['field_mix_album'] ?? $m['album'] ?? ''),
                'artwork' => (int) ($m['field_mix_artwork'] ?? $m['artwork'] ?? 0),
                'file' => basename((string) ($m['field_mix_audio_url'] ?? $m['audio_url'] ?? '')),
                'labels' => (string) ($m['field_mix_labels_raw'] ?? $m['labels_raw'] ?? ''),
                'exists' => (bool) get_page_by_path($slug, OBJECT, MIX_POST_TYPE),
            ];
        }
    }

    return $rows;
}

add_action('admin_post_mix_import_legacy', function () {
    check_admin_referer('mix_import_legacy');
    if (! current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }

    $created = 0;
    $order = 0;
    foreach (mix_legacy_rows() as $row) {
        $order++;
        if ($row['exists']) {
            continue;
        }
        $post_id = wp_insert_post([
            'post_type' => MIX_POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $row['title'],
            'post_name' => $row['slug'],
            'post_excerpt' => $row['album'],
            'menu_order' => $order,
        ], true);
        if (is_wp_error($post_id)) {
            continue;
        }
        if ($row['artwork']) {
            set_post_thumbnail($post_id, $row['artwork']);
        }
        update_field('field_mix_file', $row['file'], $post_id);
        update_field('field_mix_labels', $row['labels'], $post_id);
        $created++;
    }

    mix_redirect(mix_tools_url(), "Imported $created mix(es).");
});
