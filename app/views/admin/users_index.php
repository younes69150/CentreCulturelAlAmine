<?php partial('admin/_head', ['user' => $user, 'heading' => 'Utilisateurs', 'sub' => $pager['total'] . ' compte' . ($pager['total'] > 1 ? 's' : '')]) ?>
<section class="container page-pad">
    <form method="get" class="filter-bar">
        <label for="classe">Classe</label>
        <select id="classe" name="classe" data-autosubmit>
            <option value="">Toutes</option>
            <?php foreach ($classes as $c): ?><option value="<?= (int) $c['id'] ?>"<?= $classeId === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['nom']) ?></option><?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-ghost btn-sm">Filtrer</button></noscript>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nom</th><th>E-mail</th><th>Statut</th><th>Classe</th><th>Dernière connexion</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr<?= $u['actif'] ? '' : ' class="is-disabled"' ?>>
                    <td><strong><?= e($u['nom'] . ' ' . $u['prenom']) ?></strong><?= $u['actif'] ? '' : ' <span class="badge badge-muted">Désactivé</span>' ?></td>
                    <td class="small"><?= e($u['email']) ?></td>
                    <td><?= e(role_label($u['role'])) ?></td>
                    <td><?= e($u['classe_nom'] ?? '—') ?></td>
                    <td class="small nowrap"><?= $u['last_login_at'] ? e(format_date($u['last_login_at'], true)) : 'Jamais' ?></td>
                    <td class="right"><a class="btn btn-ghost btn-sm" href="<?= url('admin/utilisateurs/' . (int) $u['id']) ?>">Gérer</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php partial('partials/pagination', ['pager' => $pager]) ?>
</section>
