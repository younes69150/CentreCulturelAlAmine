<?php
declare(strict_types=1);

// Mesure d'audience sans cookie : un simple compteur de pages vues par jour.
// Aucune donnée personnelle (ni IP, ni identifiant) n'est enregistrée.

const STATS_MONTHS = 13;

function is_bot(): bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse/i', $ua);
}

function stats_path(string $path): string
{
    $path = preg_replace('#/\d+(?=/|$)#', '/{id}', $path) ?? $path;
    return substr($path, 0, 100);
}

function track_view(string $path): void
{
    if (is_bot()) return;
    try {
        db_run(
            'INSERT INTO page_views (day, path, views) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE views = views + 1',
            [date('Y-m-d'), $path]
        );
        if (random_int(1, 200) === 1) {
            db_run('DELETE FROM page_views WHERE day < ?', [date('Y-m-d', strtotime('-' . STATS_MONTHS . ' months'))]);
        }
    } catch (PDOException) {
        // La mesure d'audience ne doit jamais casser une page.
    }
}

function track_page_view(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;
    $code = http_response_code();
    if ($code === 404) {
        track_view('(page introuvable)');
        return;
    }
    if ($code !== 200) return;
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    track_view(stats_path($path === '/' ? '/' : rtrim($path, '/')));
}
