<?php partial('admin/_head', ['user' => $user, 'heading' => $target['prenom'] . ' ' . $target['nom'], 'sub' => $target['email']]) ?>
<section class="container narrow page-pad">
    <?php if ($tempPassword): header('Cache-Control: no-store'); ?>
        <div class="alert alert-success">
            Nouveau mot de passe provisoire : <code class="code-inline"><?= e($tempPassword) ?></code><br>
            Transmettez-le à la personne, elle pourra le changer dans « Mon compte ». Il ne sera plus affiché.
        </div>
    <?php endif; ?>
    <form method="post" action="<?= url('admin/utilisateurs/' . (int) $target['id']) ?>" class="form card">
        <?= csrf_field() ?>
        <div class="grid-2">
            <div class="field">
                <label for="role">Statut</label>
                <select id="role" name="role">
                    <?php foreach (['eleve', 'prof', 'admin'] as $r): ?>
                        <option value="<?= $r ?>"<?= $target['role'] === $r ? ' selected' : '' ?>><?= e(role_label($r)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'role']) ?>
            </div>
            <div class="field">
                <label for="classe_id">Classe</label>
                <select id="classe_id" name="classe_id">
                    <option value="">Aucune</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"<?= (int) $target['classe_id'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'classe_id']) ?>
            </div>
        </div>
        <label class="check"><input type="checkbox" name="actif" value="1"<?= $target['actif'] ? ' checked' : '' ?>> Compte actif (décocher pour bloquer l'accès)</label>
        <p class="muted small">Inscrit le <?= e(format_date($target['created_at'])) ?> · Dernière connexion : <?= $target['last_login_at'] ? e(format_date($target['last_login_at'], true)) : 'jamais' ?></p>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= url('admin/utilisateurs') ?>">Retour</a>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </div>
    </form>
    <form method="post" action="<?= url('admin/utilisateurs/' . (int) $target['id'] . '/mot-de-passe') ?>" class="card form-row" data-confirm="Générer un nouveau mot de passe provisoire ?">
        <?= csrf_field() ?>
        <div class="grow"><strong>Mot de passe oublié ?</strong><br><span class="muted small">Génère un mot de passe provisoire à transmettre.</span></div>
        <button class="btn btn-ghost">Réinitialiser</button>
    </form>
</section>
