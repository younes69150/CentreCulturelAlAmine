<?php $editing = !empty($item['id']); ?>
<?php partial('admin/_head', ['user' => $user, 'heading' => $title]) ?>
<section class="container narrow page-pad">
    <form method="post" action="<?= url($editing ? 'admin/direct/' . (int) $item['id'] : 'admin/direct/nouveau') ?>" class="form card">
        <?= csrf_field() ?>
        <div class="field">
            <label for="titre">Titre du cours</label>
            <input id="titre" name="titre" value="<?= e($item['titre']) ?>" maxlength="200" required placeholder="Ex. : Révision de l'alphabet">
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'titre']) ?>
        </div>
        <?php partial('partials/classe_select', ['classes' => $classes, 'selected' => $item['classe_id'], 'errors' => $errors]) ?>
        <div class="grid-2">
            <div class="field">
                <label for="debut">Début</label>
                <input id="debut" name="debut" type="datetime-local" value="<?= e(to_datetime_local($item['debut'])) ?>" required>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'debut']) ?>
            </div>
            <div class="field">
                <label for="duree">Durée (minutes)</label>
                <input id="duree" name="duree" type="number" min="10" max="480" step="5" value="<?= (int) $item['duree'] ?>" required>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'duree']) ?>
            </div>
        </div>
        <div class="field">
            <label for="lien">Lien de la visio <span class="muted">(facultatif)</span></label>
            <input id="lien" name="lien" type="url" value="<?= e($item['lien']) ?>" maxlength="500" placeholder="https://meet.google.com/…">
            <p class="hint">Laissez vide : le site crée une salle Jitsi gratuite. Sinon collez votre lien Google Meet, Zoom ou Teams.</p>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'lien']) ?>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= url('admin/direct') ?>">Annuler</a>
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Enregistrer' : 'Publier le direct' ?></button>
        </div>
    </form>
</section>
