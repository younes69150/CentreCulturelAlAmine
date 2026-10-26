<?php
$flashes = take_flashes();
$annonces = [];
if ($user && !empty($_SESSION['show_annonces'])) {
    $annonces = login_annonces($user);
    unset($_SESSION['show_annonces']);
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$active = fn (string $prefix) => str_starts_with($path, $prefix) ? ' aria-current="page"' : '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> · <?= e(config('app.name')) ?></title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="<?= url('assets/img/logo.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script src="<?= url('assets/js/app.js') ?>" defer></script>
    <noscript><link rel="stylesheet" href="<?= url('assets/css/noscript.css') ?>"></noscript>
</head>
<body class="<?= $user ? 'is-auth' : 'is-guest' ?>">
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= url('/') ?>">
            <img src="<?= url('assets/img/logo.svg') ?>" alt="" width="51" height="44">
            <span class="brand-text">
                <span class="brand-name">Centre Culturel Al&nbsp;Amine</span>
                <span class="brand-ar" lang="ar" dir="rtl">المركز الثقافي الأمين</span>
            </span>
        </a>
        <?php if ($user): ?>
            <button type="button" class="nav-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <nav class="main-nav" id="main-nav" aria-label="Navigation principale">
                <a href="<?= url('actualites') ?>"<?= $active('/actualites') ?>>Actualités</a>
                <a href="<?= url('documents') ?>"<?= $active('/documents') ?>>Documents</a>
                <?php if (is_staff($user)): ?>
                    <a href="<?= url('admin') ?>"<?= $active('/admin') ?>>Administration</a>
                <?php endif; ?>
                <a href="<?= url('mon-compte') ?>"<?= $active('/mon-compte') ?>>Mon compte</a>
                <form method="post" action="<?= url('deconnexion') ?>" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-button">Déconnexion</button>
                </form>
            </nav>
        <?php else: ?>
            <nav class="guest-nav">
                <a class="btn btn-ghost btn-sm" href="<?= url('connexion') ?>">Connexion</a>
            </nav>
        <?php endif; ?>
    </div>
</header>

<main id="contenu">
    <?php if ($flashes): ?>
        <div class="container flashes">
            <?php foreach ($flashes as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>© <?= date('Y') ?> Centre Culturel Al Amine</span>
        <a href="<?= url('confidentialite') ?>">Confidentialité et mentions légales</a>
    </div>
</footer>

<?php if ($annonces): ?>
    <dialog class="annonces" id="annonces" data-autoshow>
        <div class="annonces-head">
            <img src="<?= url('assets/img/logo.svg') ?>" alt="" width="42" height="36">
            <h2><?= count($annonces) > 1 ? 'Messages de l\'école' : 'Message de l\'école' ?></h2>
        </div>
        <div class="annonces-body">
            <?php foreach ($annonces as $a): ?>
                <article class="annonce">
                    <h3><?= e($a['titre']) ?></h3>
                    <?php if ($a['classe_nom']): ?><span class="badge"><?= e($a['classe_nom']) ?></span><?php endif; ?>
                    <p><?= text($a['contenu']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <form method="dialog" class="annonces-foot">
            <button class="btn btn-primary" autofocus>J'ai compris</button>
        </form>
    </dialog>
<?php endif; ?>
</body>
</html>
