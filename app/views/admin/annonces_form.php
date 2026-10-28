<?php $editing = !empty($item['id']); ?>
<?php partial('admin/_head', ['user' => $user, 'heading' => $title]) ?>
<section class="container narrow page-pad">
    <form method="post" action="<?= url($editing ? 'admin/messages/' . (int) $item['id'] : 'admin/messages/nouveau') ?>" enctype="multipart/form-data" class="form card">
        <?= csrf_field() ?>
        <div class="field">
            <label for="titre">Titre</label>
            <input id="titre" name="titre" value="<?= e($item['titre']) ?>" maxlength="200" required>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'titre']) ?>
        </div>
        <div class="field">
            <label for="contenu">Message écrit <span class="muted">(facultatif si vocal)</span></label>
            <textarea id="contenu" name="contenu" rows="5"><?= e($item['contenu']) ?></textarea>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'contenu']) ?>
        </div>
        <fieldset class="field voice" data-recorder>
            <legend>Message vocal <span class="muted">(facultatif)</span></legend>
            <?php if (!empty($item['id']) && !empty($item['audio_stockage'])): ?>
                <div class="voice-current">
                    <audio controls preload="metadata" src="<?= url('messages/' . (int) $item['id'] . '/audio') ?>"></audio>
                    <label class="check"><input type="checkbox" name="supprimer_audio" value="1"> Supprimer ce vocal</label>
                </div>
            <?php endif; ?>
            <div class="voice-recorder" hidden data-recorder-ui>
                <button type="button" class="btn btn-gold" data-rec-start>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3Zm-7 9a7 7 0 0 0 14 0M12 19v3"/></svg>
                    <span>Enregistrer un vocal</span>
                </button>
                <button type="button" class="btn btn-rec" data-rec-stop hidden><span class="rec-dot" aria-hidden="true"></span> Arrêter <span data-rec-time>0:00</span></button>
                <audio controls hidden data-rec-preview></audio>
                <button type="button" class="btn btn-ghost btn-sm" data-rec-reset hidden>Recommencer</button>
                <p class="hint" data-rec-status role="status"></p>
            </div>
            <label class="voice-file">
                <span class="hint">Ou choisissez un fichier audio (mp3, m4a, webm, ogg, wav · 15 Mo max)</span>
                <input type="file" name="audio" accept="audio/*,.mp3,.m4a,.webm,.ogg,.wav" data-rec-input>
            </label>
            <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'audio']) ?>
        </fieldset>
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
