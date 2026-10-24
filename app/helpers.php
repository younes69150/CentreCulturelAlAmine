<?php
declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Texte brut échappé, retours à la ligne conservés. */
function text(string $value): string
{
    return nl2br(e($value), false);
}

function url(string $path = '/'): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function input(string $key, int $max = 20000): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    $value = str_replace("\r\n", "\n", trim($value));
    return mb_substr($value, 0, $max);
}

function input_int(string $key): ?int
{
    $value = $_POST[$key] ?? $_GET[$key] ?? null;
    if (!is_string($value) || !ctype_digit($value)) {
        return null;
    }
    return (int) $value;
}

function query_int(string $key, int $default = 1): int
{
    $value = $_GET[$key] ?? '';
    return is_string($value) && ctype_digit($value) && (int) $value > 0 ? (int) $value : $default;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    $titles = [403 => 'Accès refusé', 404 => 'Page introuvable', 400 => 'Requête invalide', 429 => 'Trop de tentatives'];
    render('errors/error', [
        'title'   => $titles[$code] ?? 'Erreur',
        'code'    => $code,
        'message' => $message,
    ]);
    exit;
}

const MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

function format_date(?string $datetime, bool $withTime = false): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    $out = date('j', $ts) . ' ' . MOIS[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    return $withTime ? $out . ' à ' . date('H\hi', $ts) : $out;
}

/** Convertit une valeur de champ datetime-local en DATETIME SQL (ou null). */
function parse_datetime_local(string $value): ?string
{
    if ($value === '') {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
    return $dt ? $dt->format('Y-m-d H:i:00') : null;
}

function to_datetime_local(?string $datetime): string
{
    return $datetime ? date('Y-m-d\TH:i', strtotime($datetime)) : '';
}

function human_size(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' o';
    }
    if ($bytes < 1048576) {
        return number_format($bytes / 1024, 0, ',', ' ') . ' Ko';
    }
    return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
}

function excerpt(string $text, int $length = 220): string
{
    $text = preg_replace('/\s+/u', ' ', $text);
    return mb_strlen($text) > $length ? rtrim(mb_substr($text, 0, $length)) . '…' : $text;
}

function role_label(string $role): string
{
    return ['eleve' => 'Élève', 'prof' => 'Professeur', 'admin' => 'Administrateur'][$role] ?? $role;
}

function all_classes(): array
{
    return db_all('SELECT id, nom FROM classes ORDER BY nom');
}

/** Valide un id de classe venant d'un formulaire. null = tout le monde. */
function input_classe(string $key = 'classe_id'): array
{
    $raw = $_POST[$key] ?? '';
    if ($raw === '' || $raw === 'tous') {
        return [null, null];
    }
    $id = input_int($key);
    if ($id === null || !db_one('SELECT id FROM classes WHERE id = ?', [$id])) {
        return [null, 'Classe invalide.'];
    }
    return [$id, null];
}

function paginate(string $countSql, array $params, int $perPage): array
{
    $total = (int) db_value($countSql, $params);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(query_int('page'), $pages);
    return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $perPage, 'limit' => $perPage, 'total' => $total];
}
