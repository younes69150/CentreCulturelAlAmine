<?php
declare(strict_types=1);

const SESSION_IDLE_SECONDS = 7200;

function start_session(): void
{
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_IDLE_SECONDS);
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
    session_save_path(BASE_PATH . '/storage/sessions');
    session_name('ccalamine');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $last = $_SESSION['last_activity'] ?? null;
    if ($last !== null && time() - $last > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('info', 'Votre session a expiré. Merci de vous reconnecter.');
    }
    $_SESSION['last_activity'] = time();
}

function current_user(): ?array
{
    static $loaded = false;
    static $user = null;
    if (!$loaded) {
        $loaded = true;
        $id = $_SESSION['user_id'] ?? null;
        if ($id) {
            $user = db_one(
                'SELECT u.id, u.prenom, u.nom, u.email, u.role, u.classe_id, u.actif, c.nom AS classe_nom
                 FROM users u LEFT JOIN classes c ON c.id = u.classe_id WHERE u.id = ?',
                [$id]
            );
            if (!$user || !$user['actif']) {
                $user = null;
                unset($_SESSION['user_id']);
            }
        }
    }
    return $user;
}

function is_staff(?array $user): bool
{
    return $user !== null && in_array($user['role'], ['prof', 'admin'], true);
}

function is_admin(?array $user): bool
{
    return $user !== null && $user['role'] === 'admin';
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/';
        flash('info', 'Connectez-vous pour accéder à cette page.');
        redirect('/connexion');
    }
    return $user;
}

function require_staff(): array
{
    $user = require_login();
    if (!is_staff($user)) {
        abort(403);
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (!is_admin($user)) {
        abort(403);
    }
    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $_SESSION['show_annonces'] = true;
    db_run('UPDATE users SET last_login_at = ? WHERE id = ?', [now(), $user['id']]);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

function hash_password(string $password): string
{
    return password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
}

function password_error(string $password, string $confirmation): ?string
{
    if (mb_strlen($password) < 10) {
        return 'Le mot de passe doit contenir au moins 10 caractères.';
    }
    if (strlen($password) > 200) {
        return 'Le mot de passe est trop long.';
    }
    if ($password !== $confirmation) {
        return 'Les deux mots de passe ne correspondent pas.';
    }
    return null;
}

/**
 * Filtre SQL : un élève voit le contenu « tout le monde » et celui de sa classe,
 * les professeurs et admins voient tout.
 */
function visibility_sql(array $user, string $column = 'classe_id'): array
{
    if (is_staff($user)) {
        return ['1=1', []];
    }
    if ($user['classe_id']) {
        return ["($column IS NULL OR $column = ?)", [(int) $user['classe_id']]];
    }
    return ["$column IS NULL", []];
}

// --- Limitation des tentatives -------------------------------------------

function too_many_attempts(string $key, int $max, int $minutes): bool
{
    $since = date('Y-m-d H:i:s', time() - $minutes * 60);
    return (int) db_value('SELECT COUNT(*) FROM attempts WHERE cle = ? AND created_at > ?', [$key, $since]) >= $max;
}

function record_attempt(string $key): void
{
    db_run('INSERT INTO attempts (cle, created_at) VALUES (?, ?)', [$key, now()]);
    if (random_int(1, 50) === 1) {
        db_run('DELETE FROM attempts WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);
    }
}

function clear_attempts(string $key): void
{
    db_run('DELETE FROM attempts WHERE cle = ?', [$key]);
}

// --- Codes d'inscription --------------------------------------------------

const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

function generate_invite_code(): string
{
    $raw = '';
    for ($i = 0; $i < 12; $i++) {
        $raw .= CODE_ALPHABET[random_int(0, strlen(CODE_ALPHABET) - 1)];
    }
    return implode('-', str_split($raw, 4));
}

function normalize_code(string $code): string
{
    return preg_replace('/[^A-Z0-9]/', '', strtoupper($code));
}

function hash_code(string $code): string
{
    return hash_hmac('sha256', normalize_code($code), (string) config('app.key'));
}
