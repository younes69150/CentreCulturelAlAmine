<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$links = [
    ['admin', 'Tableau de bord', false],
    ['admin/codes', 'Codes', false],
    ['admin/actualites', 'Actualités', false],
    ['admin/documents', 'Documents', false],
    ['admin/messages', 'Messages', false],
    ['admin/classes', 'Classes', true],
    ['admin/utilisateurs', 'Utilisateurs', true],
    ['admin/statistiques', 'Statistiques', true],
];
?>
<nav class="admin-nav" aria-label="Administration">
    <div class="container admin-nav-inner">
        <?php foreach ($links as [$href, $label, $adminOnly]):
            if ($adminOnly && !is_admin($user)) continue;
            $current = $href === 'admin' ? $path === '/admin' : str_starts_with($path, '/' . $href); ?>
            <a href="<?= url($href) ?>"<?= $current ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</nav>
