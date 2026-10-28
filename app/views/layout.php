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
<html lang="fr"<?= isset($htmlClass) ? ' class="' . e($htmlClass) . '"' : '' ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
    $pageTitle = ($title ?? '') !== '' && ($title ?? '') !== 'Accueil' ? $title . ' · ' . config('app.name') : config('app.name') . ' · Espace élèves et professeurs';
    $pageDescription = $description ?? 'Espace en ligne des élèves et professeurs du Centre Culturel Al Amine : actualités, documents de cours et messages de l\'école.';
    $siteUrl = rtrim((string) config('app.url'), '/');
    $canonical = $siteUrl . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="robots" content="<?= !empty($indexable) ? 'index, follow' : 'noindex, nofollow' ?>">
    <?php if (!empty($indexable)): ?><link rel="canonical" href="<?= e($canonical) ?>"><?php endif; ?>
    <?php if (config('seo.google_verification')): ?><meta name="google-site-verification" content="<?= e(config('seo.google_verification')) ?>"><?php endif; ?>
    <meta name="theme-color" content="#14223F">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(config('app.name')) ?>">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($siteUrl) ?>/assets/img/og-image.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Logo du Centre Culturel Al Amine">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="<?= url('favicon.ico') ?>" sizes="32x32">
    <link rel="icon" href="<?= url('assets/img/logo.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= url('assets/img/apple-touch-icon.png') ?>">
    <link rel="manifest" href="<?= url('site.webmanifest') ?>">
    <link rel="preload" href="<?= url('assets/fonts/inter-400-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
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
                <a href="<?= url('direct') ?>"<?= $active('/direct') ?>>Direct</a>
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
    <?php if ($user): foreach (current_lives($user) as $live): ?>
        <div class="live-bar" role="status">
            <div class="container live-bar-inner">
                <span class="live-dot" aria-hidden="true"></span>
                <span><strong>En direct</strong> · <?= e($live['titre']) ?><?= $live['classe_nom'] ? ' · ' . e($live['classe_nom']) : '' ?> · <?= e(date('H\hi', strtotime($live['debut']))) ?></span>
                <a class="btn btn-gold btn-sm" href="<?= e($live['lien']) ?>" target="_blank" rel="noopener noreferrer">Rejoindre le direct</a>
            </div>
        </div>
    <?php endforeach; endif; ?>
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
        <nav class="footer-links" aria-label="Informations légales">
            <a href="<?= url('mentions-legales') ?>">Mentions légales</a>
            <a href="<?= url('confidentialite') ?>">Confidentialité</a>
            <a href="<?= url('cgu') ?>">CGU</a>
        </nav>
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
