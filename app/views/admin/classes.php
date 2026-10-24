<?php partial('admin/_head', ['user' => $user, 'heading' => 'Classes']) ?>
<section class="container narrow page-pad">
    <form method="post" action="<?= url('admin/classes') ?>" class="form card form-row">
        <?= csrf_field() ?>
        <div class="field grow">
            <label for="nom">Nouvelle classe</label>
            <input id="nom" name="nom" maxlength="100" placeholder="ex. Niveau 1 – samedi matin" required>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'nom']) ?>
        </div>
        <button class="btn btn-primary" type="submit">Ajouter</button>
    </form>
    <?php if (!$classes): ?>
        <div class="empty"><p>Aucune classe. Créez-en une pour pouvoir inscrire des élèves.</p></div>
    <?php else: ?>
        <ul class="class-list">
            <?php foreach ($classes as $c): ?>
                <li class="card class-item">
                    <form method="post" action="<?= url('admin/classes/' . (int) $c['id']) ?>" class="form-row grow">
                        <?= csrf_field() ?>
                        <label class="sr-only" for="classe-<?= (int) $c['id'] ?>">Nom</label>
                        <input id="classe-<?= (int) $c['id'] ?>" name="nom" value="<?= e($c['nom']) ?>" maxlength="100" required>
                        <span class="muted small nowrap"><?= (int) $c['eleves'] ?> élève<?= $c['eleves'] > 1 ? 's' : '' ?></span>
                        <button class="btn btn-ghost btn-sm">Renommer</button>
                    </form>
                    <form method="post" action="<?= url('admin/classes/' . (int) $c['id'] . '/supprimer') ?>" class="inline" data-confirm="Supprimer cette classe ?">
                        <?= csrf_field() ?><button class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
