<?php $htmlClass = 'page-auth'; ?>
<section class="auth">
    <div class="auth-card">
        <h1>Nouveau mot de passe</h1>
        <form method="post" action="<?= url('mot-de-passe/nouveau') ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="jeton" value="<?= e($token) ?>">
            <div class="field">
                <label for="password">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required autofocus>
                <p class="hint">10 caractères minimum.</p>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'password']) ?>
            </div>
            <div class="field">
                <label for="password_confirm">Confirmation</label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Enregistrer</button>
        </form>
    </div>
</section>
