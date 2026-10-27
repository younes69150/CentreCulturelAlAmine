<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    // Formulaire vidé par PHP : fichier plus gros que post_max_size.
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        abort(400, 'Le fichier envoyé est trop volumineux.');
    }
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        abort(400, 'Le formulaire a expiré. Rechargez la page et réessayez.');
    }
}

// --- Anti-spam ----------------------------------------------------------------

/** Champ piège (que seuls les robots remplissent) et horodatage signé du formulaire. */
function antispam_fields(): string
{
    $t = (string) time();
    $sig = hash_hmac('sha256', 'form-time|' . $t, (string) config('app.key'));
    return '<div class="hp-field" aria-hidden="true"><label for="site_web">Ne pas remplir ce champ</label>'
        . '<input id="site_web" name="site_web" type="text" tabindex="-1" autocomplete="off"></div>'
        . '<input type="hidden" name="_t" value="' . e($t . '.' . $sig) . '">';
}

/** Vrai si l'envoi ressemble à un robot : champ piège rempli, ou formulaire envoyé en moins de $minSeconds. */
function looks_like_spam(int $minSeconds = 0): bool
{
    if (($_POST['site_web'] ?? '') !== '') {
        return true;
    }
    if ($minSeconds > 0) {
        [$t, $sig] = array_pad(explode('.', (string) ($_POST['_t'] ?? ''), 2), 2, '');
        $expected = hash_hmac('sha256', 'form-time|' . $t, (string) config('app.key'));
        if (!ctype_digit($t) || !hash_equals($expected, $sig) || time() - (int) $t < $minSeconds) {
            return true;
        }
    }
    return false;
}
