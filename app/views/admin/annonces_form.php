<?php $editing = !empty($item['id']); ?>
<?php partial('admin/_head', ['user' => $user, 'heading' => $title]) ?>
<section class="container narrow page-pad">
    <form method="post" action="<?= url($editing ? 'admin/messages/' . (int) $item['id'] : 'admin/messages/nouveau') ?>" class="form card">
        <?= csrf_field() ?>
        <div class="field">
            <label for="titre">Titre</label>
            <input id="titre" name="titre" value="<?= e($item['titre']) ?>" maxlength="200" required>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'titre']) ?>
        </div>
        <div class="field">
            <label for="contenu">Message</label>
            <textarea id="contenu" name="contenu" rows="6" required><?= e($item['contenu']) ?></textarea>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'contenu']) ?>
        </div>
        <?php partial('partials/classe_select', ['classes' => $classes, 'selected' => $item['classe_id'], 'errors' => $errors]) ?>
        <div class="grid-2">
            <div class="field">
                <label for="debut">Afficher à partir du <span class="muted">(facultatif)</span></label>
                <input id="debut" name="debut" type="datetime-local" value="<?= e(to_datetime_local($item['debut'])) ?>">
            </div>
            <div class="field">
                <label for="fin">Jusqu'au <span class="muted">(facultatif)</span></label>
                <input id="fin" name="fin" type="datetime-local" value="<?= e(to_datetime_local($item['fin'])) ?>">
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'fin']) ?>
            </div>
        </div>
        <label class="check"><input type="checkbox" name="actif" value="1"<?= $item['actif'] ? ' checked' : '' ?>> Message actif</label>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= url('admin/messages') ?>">Annuler</a>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </div>
    </form>
</section>
