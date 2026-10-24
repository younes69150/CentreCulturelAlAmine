<section class="auth">
    <div class="auth-card">
        <img class="auth-logo" src="<?= url('assets/img/logo.svg') ?>" alt="" width="64" height="64">
        <h1>Mot de passe oublié</h1>
        <?php if ($sent): ?>
            <div class="alert alert-success" role="status">Si un compte existe avec l'adresse <strong><?= e($email) ?></strong>, un e-mail vient d'être envoyé avec un lien valable une heure. Pensez à regarder dans les spams.</div>
            <p class="auth-alt"><a href="<?= url('connexion') ?>">Retour à la connexion</a></p>
        <?php else: ?>
            <p class="muted">Indiquez l'adresse e-mail de votre compte. Vous recevrez un lien pour choisir un nouveau mot de passe.</p>
            <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="<?= url('mot-de-passe-oublie') ?>" class="form">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="email" required autofocus>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Recevoir le lien</button>
            </form>
            <p class="auth-alt"><a href="<?= url('connexion') ?>">Retour à la connexion</a></p>
        <?php endif; ?>
    </div>
</section>
