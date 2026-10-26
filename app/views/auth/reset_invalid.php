<?php $htmlClass = 'page-auth'; ?>
<section class="auth">
    <div class="auth-card">
        <h1>Lien expiré</h1>
        <p class="muted">Ce lien n'est plus valable : il a déjà servi ou date de plus d'une heure.</p>
        <a class="btn btn-primary btn-block" href="<?= url('mot-de-passe-oublie') ?>">Demander un nouveau lien</a>
    </div>
</section>
