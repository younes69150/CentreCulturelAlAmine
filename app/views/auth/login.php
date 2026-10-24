<section class="auth">
    <div class="auth-card">
        <img class="auth-logo" src="<?= url('assets/img/logo.svg') ?>" alt="" width="64" height="64">
        <h1>Connexion</h1>
        <p class="muted">Accédez à votre espace élève ou professeur.</p>
        <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= url('connexion') ?>" class="form">
            <?= csrf_field() ?>
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Se connecter</button>
        </form>
        <p class="auth-alt">Pas encore de compte ? <a href="<?= url('inscription') ?>">Inscription avec un code</a></p>
        <p class="auth-alt small muted">Mot de passe oublié ? Demandez à l'école de le réinitialiser.</p>
    </div>
</section>
