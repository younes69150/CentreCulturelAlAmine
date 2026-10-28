<section class="container narrow page-pad center">
    <p class="eyebrow">Erreur <?= (int) $code ?></p>
    <h1><?= e($title) ?></h1>
    <?php if ($message): ?>
        <p class="lead"><?= e($message) ?></p>
    <?php elseif ($code === 404): ?>
        <p class="lead">Cette page n'existe pas ou a été déplacée. Vérifiez l'adresse, ou repartez d'ici :</p>
    <?php endif; ?>
    <div class="error-links">
        <a class="btn btn-primary" href="<?= url('/') ?>">Retour à l'accueil</a>
        <?php if ($user): ?>
            <a class="btn btn-ghost" href="<?= url('actualites') ?>">Actualités</a>
            <a class="btn btn-ghost" href="<?= url('documents') ?>">Documents</a>
        <?php else: ?>
            <a class="btn btn-ghost" href="<?= url('connexion') ?>">Se connecter</a>
        <?php endif; ?>
    </div>
</section>
