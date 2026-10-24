<?php partial('admin/_head', ['user' => $user, 'heading' => 'Tableau de bord', 'sub' => 'Bienvenue ' . $user['prenom'] . '.']) ?>
<section class="container page-pad">
    <div class="stats">
        <a class="stat" href="<?= url(is_admin($user) ? 'admin/utilisateurs' : 'admin') ?>"><strong><?= $stats['eleves'] ?></strong><span>élèves inscrits</span></a>
        <a class="stat" href="<?= url('admin/codes') ?>"><strong><?= $stats['codes'] ?></strong><span>codes disponibles</span></a>
        <a class="stat" href="<?= url('admin/actualites') ?>"><strong><?= $stats['news'] ?></strong><span>actualités</span></a>
        <a class="stat" href="<?= url('admin/documents') ?>"><strong><?= $stats['documents'] ?></strong><span>documents</span></a>
        <a class="stat" href="<?= url('admin/messages') ?>"><strong><?= $stats['annonces'] ?></strong><span>messages actifs</span></a>
        <a class="stat" href="<?= url(is_admin($user) ? 'admin/classes' : 'admin') ?>"><strong><?= $stats['classes'] ?></strong><span>classes</span></a>
    </div>

    <?php if (is_admin($user)): ?>
        <?php if (!$lastBackup): ?>
            <div class="alert alert-error dashboard-alert">Aucune sauvegarde trouvée. Programmez la sauvegarde automatique (voir le README, « Sauvegardes »).</div>
        <?php elseif (time() - $lastBackup > 2 * 86400): ?>
            <div class="alert alert-error dashboard-alert">Dernière sauvegarde le <?= e(format_date(date('Y-m-d H:i:s', $lastBackup), true)) ?> : la sauvegarde automatique ne tourne plus.</div>
        <?php else: ?>
            <p class="muted small backup-ok">Dernière sauvegarde : <?= e(format_date(date('Y-m-d H:i:s', $lastBackup), true)) ?></p>
        <?php endif; ?>
    <?php endif; ?>
    <div class="grid-2 gap-lg">
        <div class="card">
            <h2>Actions rapides</h2>
            <div class="quick">
                <a class="btn btn-primary" href="<?= url('admin/codes') ?>">Générer des codes</a>
                <a class="btn btn-ghost" href="<?= url('admin/actualites/nouvelle') ?>">Publier une actualité</a>
                <a class="btn btn-ghost" href="<?= url('admin/documents') ?>">Déposer un fichier</a>
                <a class="btn btn-ghost" href="<?= url('admin/messages/nouveau') ?>">Message à la connexion</a>
            </div>
        </div>
        <div class="card">
            <h2>Dernières inscriptions</h2>
            <?php if (!$recent): ?>
                <p class="muted">Personne pour l'instant.</p>
            <?php else: ?>
                <ul class="plain-list">
                    <?php foreach ($recent as $r): ?>
                        <li><strong><?= e($r['prenom'] . ' ' . $r['nom']) ?></strong>
                            <span class="muted"><?= e(role_label($r['role'])) ?><?= $r['classe_nom'] ? ' · ' . e($r['classe_nom']) : '' ?> · <?= e(format_date($r['created_at'])) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
