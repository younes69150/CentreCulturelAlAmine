<?php $editing = !empty($item['id']); ?>
<?php partial('admin/_head', ['user' => $user, 'heading' => $title]) ?>
<section class="container narrow page-pad">
    <form method="post" action="<?= url($editing ? 'admin/actualites/' . (int) $item['id'] : 'admin/actualites/nouvelle') ?>" class="form card">
        <?= csrf_field() ?>
        <div class="field">
            <label for="titre">Titre</label>
            <input id="titre" name="titre" value="<?= e($item['titre']) ?>" maxlength="200" required>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'titre']) ?>
        </div>
        <div class="field">
            <label for="contenu">Texte</label>
            <textarea id="contenu" name="contenu" rows="12" required><?= e($item['contenu']) ?></textarea>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'contenu']) ?>
        </div>
        <?php partial('partials/classe_select', ['classes' => $classes, 'selected' => $item['classe_id'], 'errors' => $errors]) ?>
        <label class="check"><input type="checkbox" name="epingle" value="1"<?= $item['epingle'] ? ' checked' : '' ?>> Épingler en haut de la liste</label>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= url('admin/actualites') ?>">Annuler</a>
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Enregistrer' : 'Publier' ?></button>
        </div>
    </form>
</section>
