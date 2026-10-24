<?php partial('partials/page_head', [
    'eyebrow' => $user['classe_nom'] ? 'Classe ' . $user['classe_nom'] : null,
    'heading' => 'Actualités',
    'sub'     => 'Bonjour ' . $user['prenom'] . ', voici les dernières nouvelles.',
    'action'  => is_staff($user) ? ['admin/actualites/nouvelle', 'Publier'] : null,
]) ?>
<section class="container page-pad">
    <?php if (!$news): ?>
        <div class="empty">
            <p>Aucune actualité pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="news-list">
            <?php foreach ($news as $n): ?>
                <article class="news-card<?= $n['epingle'] ? ' is-pinned' : '' ?>">
                    <div class="news-meta">
                        <?php if ($n['epingle']): ?><span class="badge badge-gold">Épinglé</span><?php endif; ?>
                        <?php partial('partials/audience', ['classe' => $n['classe_nom']]) ?>
                        <time datetime="<?= e($n['created_at']) ?>"><?= e(format_date($n['created_at'])) ?></time>
                    </div>
                    <h2><a href="<?= url('actualites/' . (int) $n['id']) ?>"><?= e($n['titre']) ?></a></h2>
                    <p><?= e(excerpt($n['contenu'])) ?></p>
                    <a class="read-more" href="<?= url('actualites/' . (int) $n['id']) ?>">Lire la suite <span aria-hidden="true">→</span></a>
                </article>
            <?php endforeach; ?>
        </div>
        <?php partial('partials/pagination', ['pager' => $pager]) ?>
    <?php endif; ?>
</section>
