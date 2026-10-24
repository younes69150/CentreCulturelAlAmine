<?php
declare(strict_types=1);

function news_index(): void
{
    $user = require_login();
    [$where, $params] = visibility_sql($user, 'n.classe_id');
    $pager = paginate("SELECT COUNT(*) FROM news n WHERE $where", $params, 10);
    $news = db_all(
        "SELECT n.id, n.titre, n.contenu, n.epingle, n.created_at, c.nom AS classe_nom,
                CONCAT(a.prenom, ' ', a.nom) AS auteur
         FROM news n
         LEFT JOIN classes c ON c.id = n.classe_id
         LEFT JOIN users a ON a.id = n.auteur_id
         WHERE $where
         ORDER BY n.epingle DESC, n.created_at DESC
         LIMIT {$pager['limit']} OFFSET {$pager['offset']}",
        $params
    );
    render('news/index', ['title' => 'Actualités', 'news' => $news, 'pager' => $pager]);
}

function news_show(int $id): void
{
    $user = require_login();
    [$where, $params] = visibility_sql($user, 'n.classe_id');
    $item = db_one(
        "SELECT n.*, c.nom AS classe_nom, CONCAT(a.prenom, ' ', a.nom) AS auteur
         FROM news n
         LEFT JOIN classes c ON c.id = n.classe_id
         LEFT JOIN users a ON a.id = n.auteur_id
         WHERE n.id = ? AND $where",
        [$id, ...$params]
    );
    if (!$item) {
        abort(404);
    }
    render('news/show', ['title' => $item['titre'], 'item' => $item]);
}

function documents_index(): void
{
    $user = require_login();
    [$where, $params] = visibility_sql($user, 'd.classe_id');
    $pager = paginate("SELECT COUNT(*) FROM documents d WHERE $where", $params, 25);
    $documents = db_all(
        "SELECT d.id, d.titre, d.description, d.nom_original, d.taille, d.created_at, c.nom AS classe_nom
         FROM documents d LEFT JOIN classes c ON c.id = d.classe_id
         WHERE $where ORDER BY d.created_at DESC
         LIMIT {$pager['limit']} OFFSET {$pager['offset']}",
        $params
    );
    render('documents/index', ['title' => 'Documents', 'documents' => $documents, 'pager' => $pager]);
}

function documents_download(int $id): void
{
    $user = require_login();
    [$where, $params] = visibility_sql($user);
    $doc = db_one("SELECT * FROM documents WHERE id = ? AND $where", [$id, ...$params]);
    $path = $doc ? upload_dir() . '/' . $doc['nom_stockage'] : null;
    if (!$doc || !is_file($path)) {
        abort(404);
    }
    session_write_close();

    $ascii = preg_replace('/[^A-Za-z0-9._ -]/', '_', $doc['nom_original']);
    header('Content-Type: ' . $doc['mime']);
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($doc['nom_original']));
    header('Cache-Control: private, no-store');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    readfile($path);
}

function account_show(): void
{
    $user = require_login();
    render('account/index', ['title' => 'Mon compte', 'errors' => []]);
}

function account_password(): void
{
    $user = require_login();
    $errors = [];
    $row = db_one('SELECT password_hash FROM users WHERE id = ?', [$user['id']]);
    $key = 'password:' . $user['id'];
    if (too_many_attempts($key, 5, 15)) {
        $errors['current'] = 'Trop de tentatives. Réessayez dans 15 minutes.';
    } elseif (!password_verify((string) ($_POST['current'] ?? ''), $row['password_hash'])) {
        record_attempt($key);
        $errors['current'] = 'Mot de passe actuel incorrect.';
    }
    if ($err = password_error((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? ''))) {
        $errors['password'] = $err;
    }
    if ($errors) {
        render('account/index', ['title' => 'Mon compte', 'errors' => $errors]);
        return;
    }
    db_run('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password((string) $_POST['password']), $user['id']]);
    clear_attempts($key);
    session_regenerate_id(true);
    flash('success', 'Mot de passe modifié.');
    redirect('/mon-compte');
}

/** Messages à afficher juste après la connexion. */
function login_annonces(array $user): array
{
    if (is_staff($user)) {
        [$where, $params] = ['a.classe_id IS NULL', []];
    } else {
        [$where, $params] = visibility_sql($user, 'a.classe_id');
    }
    $now = now();
    return db_all(
        "SELECT a.titre, a.contenu, c.nom AS classe_nom
         FROM annonces a LEFT JOIN classes c ON c.id = a.classe_id
         WHERE a.actif = 1 AND (a.debut IS NULL OR a.debut <= ?) AND (a.fin IS NULL OR a.fin >= ?) AND $where
         ORDER BY a.created_at DESC",
        [$now, $now, ...$params]
    );
}
