<?php
declare(strict_types=1);

function home(): void
{
    if (current_user()) {
        redirect('/actualites');
    }
    render('home', ['title' => 'Accueil']);
}

function login_form(): void
{
    if (current_user()) {
        redirect('/actualites');
    }
    render('auth/login', ['title' => 'Connexion', 'email' => '', 'error' => null]);
}

function login_submit(): void
{
    $email = mb_strtolower(input('email', 190));
    $password = (string) ($_POST['password'] ?? '');
    $ipKey = 'login-ip:' . client_ip();
    $emailKey = 'login:' . $email;

    if (too_many_attempts($ipKey, 30, 15) || too_many_attempts($emailKey, 5, 15)) {
        http_response_code(429);
        render('auth/login', ['title' => 'Connexion', 'email' => $email,
            'error' => 'Trop de tentatives. Réessayez dans 15 minutes.']);
        return;
    }

    $user = db_one('SELECT id, password_hash, actif FROM users WHERE email = ?', [$email]);
    // Vérification même sans compte, pour ne pas révéler les e-mails existants par le temps de réponse.
    $hash = $user['password_hash'] ?? '$argon2id$v=19$m=65536,t=4,p=1$cmQvNE1YU3RScFYzVi5TUg$BTgCHKmz3W1g5ul8cNl8hiLvpfvJ41ZEJRL/gO44RJw';
    $valid = password_verify($password, $hash);

    if (!$user || !$valid || !$user['actif']) {
        record_attempt($ipKey);
        record_attempt($emailKey);
        render('auth/login', ['title' => 'Connexion', 'email' => $email,
            'error' => $user && $valid ? 'Ce compte est désactivé. Contactez l\'école.' : 'E-mail ou mot de passe incorrect.']);
        return;
    }

    if (password_needs_rehash($user['password_hash'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT)) {
        db_run('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($password), $user['id']]);
    }
    clear_attempts($emailKey);
    login_user($user);

    $target = $_SESSION['intended'] ?? '/actualites';
    unset($_SESSION['intended']);
    // Seulement un chemin local.
    if (!is_string($target) || !preg_match('#^/[^/\\\\]#', $target)) {
        $target = '/actualites';
    }
    header('Location: ' . $target, true, 303);
    exit;
}

function register_form(): void
{
    if (current_user()) {
        redirect('/actualites');
    }
    render('auth/register', ['title' => 'Inscription', 'old' => [], 'errors' => []]);
}

function register_submit(): void
{
    $old = [
        'code'   => input('code', 40),
        'prenom' => input('prenom', 100),
        'nom'    => input('nom', 100),
        'email'  => mb_strtolower(input('email', 190)),
        'consent' => isset($_POST['consent']),
    ];
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    $ipKey = 'register-ip:' . client_ip();
    $errors = [];

    if (too_many_attempts($ipKey, 10, 15)) {
        http_response_code(429);
        $errors['code'] = 'Trop de codes invalides. Réessayez dans 15 minutes.';
        render('auth/register', ['title' => 'Inscription', 'old' => $old, 'errors' => $errors]);
        return;
    }

    if ($old['prenom'] === '') $errors['prenom'] = 'Indiquez votre prénom.';
    if ($old['nom'] === '') $errors['nom'] = 'Indiquez votre nom.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Adresse e-mail invalide.';
    if ($err = password_error($password, $confirm)) $errors['password'] = $err;
    if (!$old['consent']) $errors['consent'] = 'Merci de confirmer avoir lu la politique de confidentialité.';
    if (strlen(normalize_code($old['code'])) !== 12) $errors['code'] = 'Le code doit contenir 12 caractères (ex. ABCD-EFGH-JKLM).';

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // Verrou sur la ligne : un code ne peut servir qu'une seule fois, même en cas de double envoi.
            $code = db_one(
                'SELECT id, role, classe_id, expires_at, used_at, revoked_at FROM invite_codes WHERE code_hash = ? FOR UPDATE',
                [hash_code($old['code'])]
            );
            if (!$code || $code['used_at'] || $code['revoked_at'] || ($code['expires_at'] && $code['expires_at'] < now())) {
                $errors['code'] = 'Code invalide, expiré ou déjà utilisé.';
            } elseif (db_one('SELECT id FROM users WHERE email = ?', [$old['email']])) {
                $errors['email'] = 'Un compte existe déjà avec cette adresse.';
            }

            if (!$errors) {
                $userId = db_insert(
                    'INSERT INTO users (prenom, nom, email, password_hash, role, classe_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [$old['prenom'], $old['nom'], $old['email'], hash_password($password), $code['role'], $code['classe_id'], now()]
                );
                db_run('UPDATE invite_codes SET used_by = ?, used_at = ? WHERE id = ?', [$userId, now(), $code['id']]);
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    if ($errors) {
        if (isset($errors['code'])) {
            record_attempt($ipKey);
        }
        render('auth/register', ['title' => 'Inscription', 'old' => $old, 'errors' => $errors]);
        return;
    }

    login_user(['id' => $userId]);
    flash('success', 'Bienvenue ' . $old['prenom'] . ', votre compte est créé.');
    redirect('/actualites');
}

function logout(): void
{
    logout_user();
    session_start();
    flash('success', 'Vous êtes déconnecté.');
    redirect('/');
}

// --- Création du premier administrateur -----------------------------------

function install_available(): bool
{
    return (int) db_value('SELECT COUNT(*) FROM users') === 0;
}

function install_form(): void
{
    if (!install_available()) {
        abort(404);
    }
    render('install', ['title' => 'Installation', 'old' => [], 'errors' => []]);
}

function install_submit(): void
{
    if (!install_available()) {
        abort(404);
    }
    $ipKey = 'install-ip:' . client_ip();
    if (too_many_attempts($ipKey, 5, 15)) {
        abort(429, 'Trop de tentatives. Réessayez dans 15 minutes.');
    }
    $old = ['prenom' => input('prenom', 100), 'nom' => input('nom', 100), 'email' => mb_strtolower(input('email', 190))];
    $errors = [];
    $setupKey = (string) config('app.setup_key', '');
    if ($setupKey === '' || $setupKey === 'CHANGER_MOI_AUSSI' || !hash_equals($setupKey, (string) ($_POST['setup_key'] ?? ''))) {
        $errors['setup_key'] = 'Clé d\'installation incorrecte (voir config.php).';
        record_attempt($ipKey);
    }
    if ($old['prenom'] === '') $errors['prenom'] = 'Indiquez votre prénom.';
    if ($old['nom'] === '') $errors['nom'] = 'Indiquez votre nom.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Adresse e-mail invalide.';
    if ($err = password_error((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? ''))) $errors['password'] = $err;

    if ($errors) {
        render('install', ['title' => 'Installation', 'old' => $old, 'errors' => $errors]);
        return;
    }
    $id = db_insert(
        'INSERT INTO users (prenom, nom, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, \'admin\', ?)',
        [$old['prenom'], $old['nom'], $old['email'], hash_password((string) $_POST['password']), now()]
    );
    login_user(['id' => $id]);
    flash('success', 'Compte administrateur créé. Commencez par créer vos classes.');
    redirect('/admin/classes');
}

function privacy(): void
{
    render('privacy', ['title' => 'Confidentialité et mentions légales']);
}
