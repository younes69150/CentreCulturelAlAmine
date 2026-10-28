<?php
declare(strict_types=1);

// Cours en direct : la visio se fait sur Jitsi (ou Meet, Zoom...), le site
// ne fait que publier le lien aux classes concernées.

const LIVE_AVANCE_MIN = 15; // le bouton « Rejoindre » apparaît 15 min avant le début

function live_state(array $live): string
{
    $now = time();
    $debut = strtotime($live['debut']);
    if ($live['termine_at'] || $now > $debut + (int) $live['duree'] * 60) return 'termine';
    return $now >= $debut - LIVE_AVANCE_MIN * 60 ? 'en_direct' : 'programme';
}

function current_lives(array $user): array
{
    [$where, $params] = visibility_sql($user, 'l.classe_id');
    return db_all(
        "SELECT l.id, l.titre, l.lien, l.debut, c.nom AS classe_nom FROM lives l
         LEFT JOIN classes c ON c.id = l.classe_id
         WHERE $where AND l.termine_at IS NULL
           AND l.debut <= ? AND DATE_ADD(l.debut, INTERVAL l.duree MINUTE) >= ?
         ORDER BY l.debut",
        [...$params, date('Y-m-d H:i:s', time() + LIVE_AVANCE_MIN * 60), now()]
    );
}

function jitsi_link(string $titre): string
{
    $slug = preg_replace('/[^A-Za-z0-9]+/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $titre) ?: '') ?: 'Cours';
    return 'https://meet.jit.si/CCAlAmine-' . substr($slug, 0, 30) . '-' . bin2hex(random_bytes(6));
}

// --- Élèves ---------------------------------------------------------------

function lives_index(): void
{
    $user = require_login();
    [$where, $params] = visibility_sql($user, 'l.classe_id');
    $lives = db_all(
        "SELECT l.*, c.nom AS classe_nom, CONCAT(a.prenom, ' ', a.nom) AS auteur FROM lives l
         LEFT JOIN classes c ON c.id = l.classe_id
         LEFT JOIN users a ON a.id = l.auteur_id
         WHERE $where AND l.termine_at IS NULL AND DATE_ADD(l.debut, INTERVAL l.duree MINUTE) >= ?
         ORDER BY l.debut LIMIT 30",
        [...$params, now()]
    );
    render('lives/index', ['title' => 'Cours en direct', 'lives' => $lives]);
}

// --- Administration -------------------------------------------------------

function admin_lives_index(): void
{
    require_staff();
    $lives = db_all(
        'SELECT l.*, c.nom AS classe_nom FROM lives l LEFT JOIN classes c ON c.id = l.classe_id
         ORDER BY (l.termine_at IS NULL AND DATE_ADD(l.debut, INTERVAL l.duree MINUTE) >= ?) DESC,
                  CASE WHEN l.termine_at IS NULL AND DATE_ADD(l.debut, INTERVAL l.duree MINUTE) >= ? THEN l.debut END ASC,
                  l.debut DESC
         LIMIT 50',
        [now(), now()]
    );
    render('admin/lives_index', ['title' => 'Cours en direct', 'lives' => $lives]);
}

function live_from_post(): array
{
    [$classeId, $classeError] = input_classe();
    $item = [
        'titre'     => input('titre', 200),
        'classe_id' => $classeId,
        'debut'     => parse_datetime_local(input('debut', 20)),
        'duree'     => input_int('duree') ?? 0,
        'lien'      => input('lien', 500),
    ];
    $errors = [];
    if ($item['titre'] === '') $errors['titre'] = 'Le titre est obligatoire.';
    if ($classeError) $errors['classe_id'] = $classeError;
    if (!$item['debut']) $errors['debut'] = 'Indiquez la date et l\'heure du cours.';
    if ($item['duree'] < 10 || $item['duree'] > 480) $errors['duree'] = 'Durée entre 10 et 480 minutes.';
    if ($item['lien'] !== '' && (!filter_var($item['lien'], FILTER_VALIDATE_URL) || !str_starts_with($item['lien'], 'https://'))) {
        $errors['lien'] = 'Le lien doit commencer par https://';
    }
    return [$item, $errors];
}

function live_form(string $title, array $item, array $errors = []): void
{
    render('admin/lives_form', ['title' => $title, 'item' => $item, 'errors' => $errors, 'classes' => all_classes()]);
}

function admin_lives_create(): void
{
    require_staff();
    live_form('Nouveau cours en direct', ['titre' => '', 'classe_id' => null, 'debut' => date('Y-m-d H:i:00'), 'duree' => 60, 'lien' => '']);
}

function admin_lives_store(): void
{
    $user = require_staff();
    [$item, $errors] = live_from_post();
    if ($errors) {
        live_form('Nouveau cours en direct', $item, $errors);
        return;
    }
    $item['lien'] = $item['lien'] ?: jitsi_link($item['titre']);
    db_insert(
        'INSERT INTO lives (titre, classe_id, debut, duree, lien, auteur_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [$item['titre'], $item['classe_id'], $item['debut'], $item['duree'], $item['lien'], $user['id'], now(), now()]
    );
    flash('success', 'Cours en direct publié. Les élèves concernés voient le lien dès 15 minutes avant le début.');
    redirect('/admin/direct');
}

function admin_lives_edit(int $id): void
{
    require_staff();
    $item = db_one('SELECT * FROM lives WHERE id = ?', [$id]) ?? abort(404);
    live_form('Modifier le cours en direct', $item);
}

function admin_lives_update(int $id): void
{
    require_staff();
    $old = db_one('SELECT * FROM lives WHERE id = ?', [$id]) ?? abort(404);
    [$item, $errors] = live_from_post();
    if ($errors) {
        live_form('Modifier le cours en direct', $item + ['id' => $id], $errors);
        return;
    }
    $item['lien'] = $item['lien'] ?: $old['lien'];
    db_run(
        'UPDATE lives SET titre = ?, classe_id = ?, debut = ?, duree = ?, lien = ?, updated_at = ? WHERE id = ?',
        [$item['titre'], $item['classe_id'], $item['debut'], $item['duree'], $item['lien'], now(), $id]
    );
    flash('success', 'Cours en direct modifié.');
    redirect('/admin/direct');
}

function admin_lives_end(int $id): void
{
    require_staff();
    db_run('UPDATE lives SET termine_at = ?, updated_at = ? WHERE id = ? AND termine_at IS NULL', [now(), now(), $id]);
    flash('success', 'Cours terminé : le lien n\'est plus affiché aux élèves.');
    redirect('/admin/direct');
}

function admin_lives_delete(int $id): void
{
    require_staff();
    db_run('DELETE FROM lives WHERE id = ?', [$id]);
    flash('success', 'Cours supprimé.');
    redirect('/admin/direct');
}
