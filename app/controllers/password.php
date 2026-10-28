<?php
declare(strict_types=1);

const RESET_TTL_MINUTES = 60;

function forgot_form(): void
{
    render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => '', 'sent' => false, 'error' => null]);
}

function forgot_submit(): void
{
    $email = mb_strtolower(input('email', 190));
    $ipKey = 'forgot-ip:' . client_ip();
    $emailKey = 'forgot:' . $email;

    if (looks_like_spam(2)) {
        // Même réponse qu'un envoi normal : le robot n'apprend rien.
        render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => $email, 'sent' => true, 'error' => null]);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => $email, 'sent' => false, 'error' => 'Adresse e-mail invalide.']);
        return;
    }
    if (too_many_attempts($ipKey, 10, 60) || too_many_attempts($emailKey, 3, 60)) {
        http_response_code(429);
        render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => $email, 'sent' => false, 'error' => 'Trop de demandes. Réessayez dans une heure.']);
        return;
    }
    record_attempt($ipKey);
    record_attempt($emailKey);

    $user = db_one('SELECT id, prenom, email FROM users WHERE email = ? AND actif = 1', [$email]);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        db_run('DELETE FROM password_resets WHERE user_id = ?', [$user['id']]);
        db_insert(
            'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)',
            [$user['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + RESET_TTL_MINUTES * 60), now()]
        );
        // Lien construit depuis la configuration, jamais depuis l'en-tête Host de la requête.
        $link = rtrim((string) config('app.url'), '/') . '/mot-de-passe/nouveau?jeton=' . $token;
        send_mail($user['email'], 'Réinitialisation de votre mot de passe', implode("\n", [
            'Bonjour ' . $user['prenom'] . ',',
            '',
            'Vous avez demandé à changer votre mot de passe sur le site du ' . config('app.name') . '.',
            'Cliquez sur ce lien pour en choisir un nouveau (valable ' . RESET_TTL_MINUTES . ' minutes) :',
            '',
            $link,
            '',
            'Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet e-mail : votre mot de passe ne change pas.',
            '',
            config('app.name'),
        ]));
    }
    // Même réponse que le compte existe ou non.
    render('auth/forgot', ['title' => 'Mot de passe oublié', 'email' => $email, 'sent' => true, 'error' => null]);
}

function find_reset(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    return db_one(
        'SELECT r.id, r.user_id, u.email FROM password_resets r JOIN users u ON u.id = r.user_id
         WHERE r.token_hash = ? AND r.expires_at > ? AND u.actif = 1',
        [hash('sha256', $token), now()]
    );
}

function reset_form(): void
{
    $token = is_string($_GET['jeton'] ?? null) ? $_GET['jeton'] : '';
    header('Referrer-Policy: no-referrer');
    if (!find_reset($token)) {
        render('auth/reset_invalid', ['title' => 'Lien expiré']);
        return;
    }
    render('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token, 'errors' => []]);
}

function reset_submit(): void
{
    $token = input('jeton', 64);
    $reset = find_reset($token);
    if (!$reset) {
        render('auth/reset_invalid', ['title' => 'Lien expiré']);
        return;
    }
    if ($err = password_error((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? ''))) {
        render('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token, 'errors' => ['password' => $err]]);
        return;
    }
    db_run('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password((string) $_POST['password']), $reset['user_id']]);
    db_run('DELETE FROM password_resets WHERE user_id = ?', [$reset['user_id']]);
    clear_attempts('login:' . $reset['email']);
    flash('success', 'Mot de passe modifié. Vous pouvez vous connecter.');
    redirect('/connexion');
}
