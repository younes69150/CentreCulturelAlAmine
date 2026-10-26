<section class="auth">
    <div class="auth-card auth-card-wide">
        <h1>Installation</h1>
        <p class="muted">Création du premier compte administrateur. Cette page disparaît ensuite.</p>
        <form method="post" action="<?= url('installation') ?>" class="form">
            <?= csrf_field() ?>
            <div class="field">
                <label for="setup_key">Clé d'installation (setup_key dans config.php)</label>
                <input id="setup_key" name="setup_key" type="password" autocomplete="off" required>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'setup_key']) ?>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="prenom">Prénom</label>
                    <input id="prenom" name="prenom" value="<?= e($old['prenom'] ?? '') ?>" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'prenom']) ?>
                </div>
                <div class="field">
                    <label for="nom">Nom</label>
                    <input id="nom" name="nom" value="<?= e($old['nom'] ?? '') ?>" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'nom']) ?>
                </div>
            </div>
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" required>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'email']) ?>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'password']) ?>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirmation</label>
                    <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
                </div>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Créer l'administrateur</button>
        </form>
    </div>
</section>
