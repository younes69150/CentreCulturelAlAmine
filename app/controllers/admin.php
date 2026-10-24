<?php
declare(strict_types=1);

function admin_dashboard(): void
{
    require_staff();
    $now = now();
    $stats = [
        'eleves'    => (int) db_value("SELECT COUNT(*) FROM users WHERE role = 'eleve' AND actif = 1"),
        'codes'     => (int) db_value('SELECT COUNT(*) FROM invite_codes WHERE used_at IS NULL AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > ?)', [$now]),
        'news'      => (int) db_value('SELECT COUNT(*) FROM news'),
        'documents' => (int) db_value('SELECT COUNT(*) FROM documents'),
        'annonces'  => (int) db_value('SELECT COUNT(*) FROM annonces WHERE actif = 1 AND (fin IS NULL OR fin >= ?)', [$now]),
        'classes'   => (int) db_value('SELECT COUNT(*) FROM classes'),
    ];
    $recent = db_all("SELECT u.prenom, u.nom, u.role, u.created_at, c.nom AS classe_nom FROM users u LEFT JOIN classes c ON c.id = u.classe_id ORDER BY u.created_at DESC LIMIT 5");
    render('admin/dashboard', ['title' => 'Administration', 'stats' => $stats, 'recent' => $recent]);
}

// --- Actualités -----------------------------------------------------------

function admin_news_index(): void
{
    require_staff();
    $pager = paginate('SELECT COUNT(*) FROM news', [], 20);
    $news = db_all(
        "SELECT n.id, n.titre, n.epingle, n.created_at, c.nom AS classe_nom FROM news n
         LEFT JOIN classes c ON c.id = n.classe_id
         ORDER BY n.created_at DESC LIMIT {$pager['limit']} OFFSET {$pager['offset']}"
    );
    render('admin/news_index', ['title' => 'Actualités', 'news' => $news, 'pager' => $pager]);
}

function admin_news_create(): void
{
    require_staff();
    render('admin/news_form', ['title' => 'Nouvelle actualité', 'item' => ['titre' => '', 'contenu' => '', 'classe_id' => null, 'epingle' => 0], 'errors' => [], 'classes' => all_classes()]);
}

function news_from_post(): array
{
    [$classeId, $classeError] = input_classe();
    $item = [
        'titre'     => input('titre', 200),
        'contenu'   => input('contenu', 60000),
        'classe_id' => $classeId,
        'epingle'   => isset($_POST['epingle']) ? 1 : 0,
    ];
    $errors = [];
    if ($item['titre'] === '') $errors['titre'] = 'Le titre est obligatoire.';
    if ($item['contenu'] === '') $errors['contenu'] = 'Le texte est obligatoire.';
    if ($classeError) $errors['classe_id'] = $classeError;
    return [$item, $errors];
}

function admin_news_store(): void
{
    $user = require_staff();
    [$item, $errors] = news_from_post();
    if ($errors) {
        render('admin/news_form', ['title' => 'Nouvelle actualité', 'item' => $item, 'errors' => $errors, 'classes' => all_classes()]);
        return;
    }
    db_insert(
        'INSERT INTO news (titre, contenu, classe_id, epingle, auteur_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$item['titre'], $item['contenu'], $item['classe_id'], $item['epingle'], $user['id'], now(), now()]
    );
    flash('success', 'Actualité publiée.');
    redirect('/admin/actualites');
}

function admin_news_edit(int $id): void
{
    require_staff();
    $item = db_one('SELECT * FROM news WHERE id = ?', [$id]) ?? abort(404);
    render('admin/news_form', ['title' => 'Modifier l\'actualité', 'item' => $item, 'errors' => [], 'classes' => all_classes()]);
}

function admin_news_update(int $id): void
{
    require_staff();
    db_one('SELECT id FROM news WHERE id = ?', [$id]) ?? abort(404);
    [$item, $errors] = news_from_post();
    if ($errors) {
        $item['id'] = $id;
        render('admin/news_form', ['title' => 'Modifier l\'actualité', 'item' => $item, 'errors' => $errors, 'classes' => all_classes()]);
        return;
    }
    db_run(
        'UPDATE news SET titre = ?, contenu = ?, classe_id = ?, epingle = ?, updated_at = ? WHERE id = ?',
        [$item['titre'], $item['contenu'], $item['classe_id'], $item['epingle'], now(), $id]
    );
    flash('success', 'Actualité modifiée.');
    redirect('/admin/actualites');
}

function admin_news_delete(int $id): void
{
    require_staff();
    db_run('DELETE FROM news WHERE id = ?', [$id]);
    flash('success', 'Actualité supprimée.');
    redirect('/admin/actualites');
}

// --- Documents ------------------------------------------------------------

function admin_documents_index(array $errors = [], array $old = []): void
{
    require_staff();
    $pager = paginate('SELECT COUNT(*) FROM documents', [], 25);
    $documents = db_all(
        "SELECT d.id, d.titre, d.nom_original, d.taille, d.created_at, c.nom AS classe_nom
         FROM documents d LEFT JOIN classes c ON c.id = d.classe_id
         ORDER BY d.created_at DESC LIMIT {$pager['limit']} OFFSET {$pager['offset']}"
    );
    render('admin/documents', [
        'title' => 'Documents', 'documents' => $documents, 'pager' => $pager,
        'classes' => all_classes(), 'errors' => $errors, 'old' => $old,
    ]);
}

function admin_documents_store(): void
{
    $user = require_staff();
    [$classeId, $classeError] = input_classe();
    $old = ['titre' => input('titre', 200), 'description' => input('description', 2000), 'classe_id' => $classeId];
    $errors = [];
    if ($old['titre'] === '') $errors['titre'] = 'Le titre est obligatoire.';
    if ($classeError) $errors['classe_id'] = $classeError;

    if (!$errors) {
        $upload = store_upload($_FILES['fichier'] ?? null);
        if (isset($upload['error'])) {
            $errors['fichier'] = $upload['error'];
        }
    }
    if ($errors) {
        admin_documents_index($errors, $old);
        return;
    }
    $f = $upload['ok'];
    db_insert(
        'INSERT INTO documents (titre, description, nom_original, nom_stockage, mime, taille, classe_id, auteur_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$old['titre'], $old['description'] ?: null, $f['nom_original'], $f['nom_stockage'], $f['mime'], $f['taille'], $classeId, $user['id'], now()]
    );
    flash('success', 'Document ajouté.');
    redirect('/admin/documents');
}

function admin_documents_delete(int $id): void
{
    require_staff();
    $doc = db_one('SELECT nom_stockage FROM documents WHERE id = ?', [$id]) ?? abort(404);
    db_run('DELETE FROM documents WHERE id = ?', [$id]);
    delete_upload($doc['nom_stockage']);
    flash('success', 'Document supprimé.');
    redirect('/admin/documents');
}

// --- Messages à la connexion ---------------------------------------------

function admin_annonces_index(): void
{
    require_staff();
    $annonces = db_all(
        'SELECT a.id, a.titre, a.debut, a.fin, a.actif, c.nom AS classe_nom FROM annonces a
         LEFT JOIN classes c ON c.id = a.classe_id ORDER BY a.created_at DESC'
    );
    render('admin/annonces_index', ['title' => 'Messages à la connexion', 'annonces' => $annonces]);
}

function annonce_from_post(): array
{
    [$classeId, $classeError] = input_classe();
    $item = [
        'titre'     => input('titre', 200),
        'contenu'   => input('contenu', 5000),
        'classe_id' => $classeId,
        'debut'     => parse_datetime_local(input('debut', 20)),
        'fin'       => parse_datetime_local(input('fin', 20)),
        'actif'     => isset($_POST['actif']) ? 1 : 0,
    ];
    $errors = [];
    if ($item['titre'] === '') $errors['titre'] = 'Le titre est obligatoire.';
    if ($item['contenu'] === '') $errors['contenu'] = 'Le message est obligatoire.';
    if ($classeError) $errors['classe_id'] = $classeError;
    if ($item['debut'] && $item['fin'] && $item['fin'] < $item['debut']) $errors['fin'] = 'La fin doit être après le début.';
    return [$item, $errors];
}

function admin_annonces_create(): void
{
    require_staff();
    render('admin/annonces_form', ['title' => 'Nouveau message', 'item' => ['titre' => '', 'contenu' => '', 'classe_id' => null, 'debut' => null, 'fin' => null, 'actif' => 1], 'errors' => [], 'classes' => all_classes()]);
}

function admin_annonces_store(): void
{
    $user = require_staff();
    [$item, $errors] = annonce_from_post();
    if ($errors) {
        render('admin/annonces_form', ['title' => 'Nouveau message', 'item' => $item, 'errors' => $errors, 'classes' => all_classes()]);
        return;
    }
    db_insert(
        'INSERT INTO annonces (titre, contenu, classe_id, debut, fin, actif, auteur_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$item['titre'], $item['contenu'], $item['classe_id'], $item['debut'], $item['fin'], $item['actif'], $user['id'], now(), now()]
    );
    flash('success', 'Message enregistré. Il s\'affichera à la prochaine connexion des destinataires.');
    redirect('/admin/messages');
}

function admin_annonces_edit(int $id): void
{
    require_staff();
    $item = db_one('SELECT * FROM annonces WHERE id = ?', [$id]) ?? abort(404);
    render('admin/annonces_form', ['title' => 'Modifier le message', 'item' => $item, 'errors' => [], 'classes' => all_classes()]);
}

function admin_annonces_update(int $id): void
{
    require_staff();
    db_one('SELECT id FROM annonces WHERE id = ?', [$id]) ?? abort(404);
    [$item, $errors] = annonce_from_post();
    if ($errors) {
        $item['id'] = $id;
        render('admin/annonces_form', ['title' => 'Modifier le message', 'item' => $item, 'errors' => $errors, 'classes' => all_classes()]);
        return;
    }
    db_run(
        'UPDATE annonces SET titre = ?, contenu = ?, classe_id = ?, debut = ?, fin = ?, actif = ?, updated_at = ? WHERE id = ?',
        [$item['titre'], $item['contenu'], $item['classe_id'], $item['debut'], $item['fin'], $item['actif'], now(), $id]
    );
    flash('success', 'Message modifié.');
    redirect('/admin/messages');
}

function admin_annonces_delete(int $id): void
{
    require_staff();
    db_run('DELETE FROM annonces WHERE id = ?', [$id]);
    flash('success', 'Message supprimé.');
    redirect('/admin/messages');
}

// --- Codes d'inscription --------------------------------------------------

function code_roles(array $user): array
{
    return is_admin($user) ? ['eleve', 'prof', 'admin'] : ['eleve'];
}

function admin_codes_index(array $errors = []): void
{
    $user = require_staff();
    $filter = $_GET['etat'] ?? 'disponibles';
    $now = now();
    $where = match ($filter) {
        'utilises' => 'ic.used_at IS NOT NULL',
        'tous'     => '1=1',
        default    => 'ic.used_at IS NULL AND ic.revoked_at IS NULL AND (ic.expires_at IS NULL OR ic.expires_at > :now)',
    };
    $params = str_contains($where, ':now') ? ['now' => $now] : [];
    $codes = db_all(
        "SELECT ic.*, c.nom AS classe_nom, CONCAT(u.prenom, ' ', u.nom) AS utilise_par
         FROM invite_codes ic
         LEFT JOIN classes c ON c.id = ic.classe_id
         LEFT JOIN users u ON u.id = ic.used_by
         WHERE $where ORDER BY ic.created_at DESC LIMIT 300",
        $params
    );
    render('admin/codes', [
        'title' => 'Codes d\'inscription', 'codes' => $codes, 'filter' => $filter,
        'classes' => all_classes(), 'roles' => code_roles($user), 'errors' => $errors, 'now' => $now,
    ]);
}

function admin_codes_store(): void
{
    $user = require_staff();
    $role = input('role', 10) ?: 'eleve';
    [$classeId, $classeError] = input_classe();
    $count = input_int('nombre') ?? 1;
    $days = input_int('jours');
    $label = input('label', 150);
    $errors = [];
    if (!in_array($role, code_roles($user), true)) $errors['role'] = 'Rôle non autorisé.';
    if ($classeError) $errors['classe_id'] = $classeError;
    if ($role === 'eleve' && $classeId === null && all_classes()) $errors['classe_id'] = 'Choisissez la classe de l\'élève.';
    if ($count < 1 || $count > 100) $errors['nombre'] = 'Entre 1 et 100 codes.';
    if ($days !== null && ($days < 1 || $days > 365)) $errors['jours'] = 'Entre 1 et 365 jours.';
    if ($errors) {
        admin_codes_index($errors);
        return;
    }

    $expires = $days ? date('Y-m-d H:i:s', strtotime("+$days days")) : null;
    $created = [];
    for ($i = 0; $i < $count; $i++) {
        $code = generate_invite_code();
        db_insert(
            'INSERT INTO invite_codes (code_hash, code_hint, label, role, classe_id, expires_at, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [hash_code($code), substr($code, 0, 4), $label ?: null, $role, $role === 'eleve' ? $classeId : null, $expires, $user['id'], now()]
        );
        $created[] = $code;
    }
    // Les codes en clair ne sont montrés qu'une fois.
    $classe = $classeId ? db_value('SELECT nom FROM classes WHERE id = ?', [$classeId]) : null;
    $_SESSION['codes_generes'] = ['codes' => $created, 'role' => $role, 'classe' => $role === 'eleve' ? $classe : null, 'expires' => $expires, 'label' => $label];
    redirect('/admin/codes/generes');
}

function admin_codes_created(): void
{
    require_staff();
    $batch = $_SESSION['codes_generes'] ?? null;
    unset($_SESSION['codes_generes']);
    if (!$batch) {
        redirect('/admin/codes');
    }
    header('Cache-Control: no-store');
    render('admin/codes_generes', ['title' => 'Codes générés', 'batch' => $batch]);
}

function admin_codes_revoke(int $id): void
{
    $user = require_staff();
    $code = db_one('SELECT role FROM invite_codes WHERE id = ?', [$id]) ?? abort(404);
    if (!in_array($code['role'], code_roles($user), true)) {
        abort(403);
    }
    db_run('UPDATE invite_codes SET revoked_at = ? WHERE id = ? AND used_at IS NULL', [now(), $id]);
    flash('success', 'Code annulé.');
    redirect('/admin/codes');
}

// --- Classes (admin) ------------------------------------------------------

function admin_classes_index(array $errors = []): void
{
    require_admin();
    $classes = db_all(
        "SELECT c.id, c.nom,
                (SELECT COUNT(*) FROM users u WHERE u.classe_id = c.id AND u.actif = 1) AS eleves
         FROM classes c ORDER BY c.nom"
    );
    render('admin/classes', ['title' => 'Classes', 'classes' => $classes, 'errors' => $errors]);
}

function admin_classes_store(): void
{
    require_admin();
    $nom = input('nom', 100);
    if ($nom === '') {
        admin_classes_index(['nom' => 'Indiquez un nom.']);
        return;
    }
    if (db_one('SELECT id FROM classes WHERE nom = ?', [$nom])) {
        admin_classes_index(['nom' => 'Cette classe existe déjà.']);
        return;
    }
    db_insert('INSERT INTO classes (nom, created_at) VALUES (?, ?)', [$nom, now()]);
    flash('success', 'Classe créée.');
    redirect('/admin/classes');
}

function admin_classes_update(int $id): void
{
    require_admin();
    $nom = input('nom', 100);
    if ($nom === '' || db_one('SELECT id FROM classes WHERE nom = ? AND id <> ?', [$nom, $id])) {
        flash('error', 'Nom vide ou déjà utilisé.');
        redirect('/admin/classes');
    }
    db_run('UPDATE classes SET nom = ? WHERE id = ?', [$nom, $id]);
    flash('success', 'Classe renommée.');
    redirect('/admin/classes');
}

function admin_classes_delete(int $id): void
{
    require_admin();
    // Les contenus d'une classe supprimée ne doivent jamais devenir publics : on refuse tant qu'ils existent.
    try {
        db_run('DELETE FROM classes WHERE id = ?', [$id]);
        flash('success', 'Classe supprimée.');
    } catch (PDOException) {
        flash('error', 'Impossible : des élèves, codes, actualités, documents ou messages sont encore liés à cette classe.');
    }
    redirect('/admin/classes');
}

// --- Utilisateurs (admin) -------------------------------------------------

function admin_users_index(): void
{
    require_admin();
    $classeId = query_int('classe', 0);
    $where = $classeId ? 'WHERE u.classe_id = ?' : '';
    $params = $classeId ? [$classeId] : [];
    $pager = paginate("SELECT COUNT(*) FROM users u $where", $params, 50);
    $users = db_all(
        "SELECT u.id, u.prenom, u.nom, u.email, u.role, u.actif, u.last_login_at, c.nom AS classe_nom
         FROM users u LEFT JOIN classes c ON c.id = u.classe_id $where
         ORDER BY u.nom, u.prenom LIMIT {$pager['limit']} OFFSET {$pager['offset']}",
        $params
    );
    render('admin/users_index', ['title' => 'Utilisateurs', 'users' => $users, 'pager' => $pager, 'classes' => all_classes(), 'classeId' => $classeId]);
}

function admin_users_edit(int $id, array $errors = [], ?string $tempPassword = null): void
{
    require_admin();
    $target = db_one('SELECT id, prenom, nom, email, role, classe_id, actif, created_at, last_login_at FROM users WHERE id = ?', [$id]) ?? abort(404);
    render('admin/users_edit', ['title' => 'Modifier un utilisateur', 'target' => $target, 'classes' => all_classes(), 'errors' => $errors, 'tempPassword' => $tempPassword]);
}

function admin_users_update(int $id): void
{
    $admin = require_admin();
    db_one('SELECT id FROM users WHERE id = ?', [$id]) ?? abort(404);
    $role = input('role', 10);
    [$classeId, $classeError] = input_classe();
    $actif = isset($_POST['actif']) ? 1 : 0;
    $errors = [];
    if (!in_array($role, ['eleve', 'prof', 'admin'], true)) $errors['role'] = 'Rôle invalide.';
    if ($classeError) $errors['classe_id'] = $classeError;
    if ($id === (int) $admin['id'] && ($role !== 'admin' || !$actif)) {
        $errors['role'] = 'Vous ne pouvez pas retirer vos propres droits d\'administrateur.';
    }
    if ($errors) {
        admin_users_edit($id, $errors);
        return;
    }
    db_run('UPDATE users SET role = ?, classe_id = ?, actif = ? WHERE id = ?', [$role, $classeId, $actif, $id]);
    flash('success', 'Utilisateur modifié.');
    redirect('/admin/utilisateurs/' . $id);
}

function admin_users_reset_password(int $id): void
{
    require_admin();
    db_one('SELECT id FROM users WHERE id = ?', [$id]) ?? abort(404);
    $temp = generate_invite_code();
    db_run('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($temp), $id]);
    admin_users_edit($id, [], $temp);
}
