<?php partial('partials/page_head', ['heading' => 'Mon compte']) ?>
<section class="container narrow page-pad">
    <div class="card">
        <dl class="details">
            <dt>Nom</dt><dd><?= e($user['prenom'] . ' ' . $user['nom']) ?></dd>
            <dt>E-mail</dt><dd><?= e($user['email']) ?></dd>
            <dt>Statut</dt><dd><?= e(role_label($user['role'])) ?></dd>
            <?php if ($user['classe_nom']): ?><dt>Classe</dt><dd><?= e($user['classe_nom']) ?></dd><?php endif; ?>
        </dl>
        <p class="muted small">Pour corriger ou supprimer vos données, écrivez à <?= e(config('legal.email', '')) ?>. <a href="<?= url('confidentialite') ?>">En savoir plus</a></p>
    </div>
    <div class="card">
        <h2>Changer de mot de passe</h2>
        <form method="post" action="<?= url('mon-compte/mot-de-passe') ?>" class="form">
            <?= csrf_field() ?>
            <div class="field">
                <label for="current">Mot de passe actuel</label>
                <input id="current" name="current" type="password" autocomplete="current-password" required>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'current']) ?>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="password">Nouveau mot de passe</label>
                    <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'password']) ?>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirmation</label>
                    <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </form>
    </div>
</section>
