<?php $htmlClass = 'page-auth'; ?>
<section class="auth">
    <div class="auth-card auth-card-wide">
        <h1>Inscription</h1>
        <p class="muted">Avec le code remis par l'école. Déjà inscrit ? <a href="<?= url('connexion') ?>">Se connecter</a></p>
        <form method="post" action="<?= url('inscription') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <?= antispam_fields() ?>
            <div class="grid-2">
                <div class="field">
                    <label for="code">Code d'inscription</label>
                    <input id="code" name="code" class="input-code" value="<?= e($old['code'] ?? '') ?>" placeholder="ABCD-EFGH-JKLM" autocomplete="off" autocapitalize="characters" spellcheck="false" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'code']) ?>
                </div>
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'email']) ?>
                </div>
                <div class="field">
                    <label for="prenom">Prénom</label>
                    <input id="prenom" name="prenom" value="<?= e($old['prenom'] ?? '') ?>" autocomplete="given-name" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'prenom']) ?>
                </div>
                <div class="field">
                    <label for="nom">Nom</label>
                    <input id="nom" name="nom" value="<?= e($old['nom'] ?? '') ?>" autocomplete="family-name" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'nom']) ?>
                </div>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="password">Mot de passe <span class="muted">(10 caractères min.)</span></label>
                    <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
                    <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'password']) ?>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirmation</label>
                    <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
                </div>
            </div>
            <div class="field">
                <label class="check check-consent">
                    <input type="checkbox" name="consent" value="1" required<?= !empty($old['consent']) ? ' checked' : '' ?>>
                    <span>J'accepte les <a href="<?= url('cgu') ?>" target="_blank">CGU</a> et j'ai lu la <a href="<?= url('confidentialite') ?>" target="_blank">politique de confidentialité</a>. Si l'élève a moins de 15 ans, je suis son parent ou j'ai son accord.</span>
                </label>
                <?php partial('partials/field_error', ['errors' => $errors, 'field' => 'consent']) ?>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Créer mon compte</button>
        </form>
    </div>
</section>
