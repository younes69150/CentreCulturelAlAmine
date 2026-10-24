<section class="container narrow page-pad center">
    <p class="eyebrow">Erreur <?= (int) $code ?></p>
    <h1><?= e($title) ?></h1>
    <?php if ($message): ?><p class="lead"><?= e($message) ?></p><?php endif; ?>
    <a class="btn btn-primary" href="<?= url('/') ?>">Retour à l'accueil</a>
</section>
