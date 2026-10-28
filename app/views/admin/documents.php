<?php partial('admin/_head', ['user' => $user, 'heading' => 'Documents', 'sub' => 'Formats : PDF, Word, Excel, PowerPoint, LibreOffice, images, audio, vidéo. ' . config('uploads.max_mb', 20) . ' Mo maximum.']) ?>
<section class="container page-pad">
    <div class="grid-sidebar">
        <form method="post" action="<?= url('admin/documents') ?>" enctype="multipart/form-data" class="form card">
            <?= csrf_field() ?>
            <h2>Déposer un fichier</h2>
            <div class="field">
                <label for="fichier">Fichier</label>
                <input id="fichier" name="fichier" type="file" required accept=".<?= e(implode(',.', array_keys(allowed_uploads()))) ?>">
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'fichier']) ?>
            </div>
            <div class="field">
                <label for="titre">Titre</label>
                <input id="titre" name="titre" value="<?= e($old['titre'] ?? '') ?>" maxlength="200" required>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'titre']) ?>
            </div>
            <div class="field">
                <label for="description">Description <span class="muted">(facultatif)</span></label>
                <textarea id="description" name="description" rows="3"><?= e($old['description'] ?? '') ?></textarea>
            </div>
            <?php partial('partials/classe_select', ['classes' => $classes, 'selected' => $old['classe_id'] ?? null, 'errors' => $errors]) ?>
            <button class="btn btn-primary btn-block" type="submit">Déposer</button>
        </form>

        <div>
            <?php if (!$documents): ?>
                <div class="empty"><p>Aucun document déposé.</p></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Document</th><th>Visible par</th><th>Taille</th><th class="right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($documents as $d): ?>
                            <tr>
                                <td><strong><?= e($d['titre']) ?></strong><br><span class="muted small"><?= e($d['nom_original']) ?> · <?= e(format_date($d['created_at'])) ?></span></td>
                                <td><?php partial('partials/audience', ['classe' => $d['classe_nom']]) ?></td>
                                <td class="nowrap"><?= e(human_size((int) $d['taille'])) ?></td>
                                <td class="right actions">
                                    <a class="btn btn-ghost btn-sm" href="<?= url('documents/' . (int) $d['id'] . '/telecharger') ?>">Ouvrir</a>
                                    <form method="post" action="<?= url('admin/documents/' . (int) $d['id'] . '/supprimer') ?>" class="inline" data-confirm="Supprimer ce document ?">
                                        <?= csrf_field() ?><button class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php partial('partials/pagination', ['pager' => $pager]) ?>
            <?php endif; ?>
        </div>
    </div>
</section>
