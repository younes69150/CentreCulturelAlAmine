<article class="container narrow page-pad article">
    <a class="back" href="<?= url('actualites') ?>"><span aria-hidden="true">←</span> Toutes les actualités</a>
    <div class="news-meta">
        <?php partial('partials/audience', ['classe' => $item['classe_nom']]) ?>
        <time datetime="<?= e($item['created_at']) ?>"><?= e(format_date($item['created_at'])) ?></time>
        <?php if ($item['auteur']): ?><span>par <?= e($item['auteur']) ?></span><?php endif; ?>
    </div>
    <h1><?= e($item['titre']) ?></h1>
    <div class="prose"><?= text($item['contenu']) ?></div>
    <?php if (is_staff($user)): ?>
        <p><a class="btn btn-ghost btn-sm" href="<?= url('admin/actualites/' . (int) $item['id']) ?>">Modifier</a></p>
    <?php endif; ?>
</article>
