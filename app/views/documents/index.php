<?php partial('partials/page_head', [
    'heading' => 'Documents',
    'sub'     => 'Cours, exercices et fiches à télécharger.',
    'action'  => is_staff($user) ? ['admin/documents', 'Déposer un fichier'] : null,
]) ?>
<section class="container page-pad">
    <?php if (!$documents): ?>
        <div class="empty"><p>Aucun document pour le moment.</p></div>
    <?php else: ?>
        <ul class="doc-list">
            <?php foreach ($documents as $d):
                $ext = strtoupper(pathinfo($d['nom_original'], PATHINFO_EXTENSION)); ?>
                <li class="doc">
                    <span class="doc-ext" aria-hidden="true"><?= e($ext) ?></span>
                    <div class="doc-body">
                        <h2><?= e($d['titre']) ?></h2>
                        <?php if ($d['description']): ?><p><?= e($d['description']) ?></p><?php endif; ?>
                        <div class="news-meta">
                            <?php partial('partials/audience', ['classe' => $d['classe_nom']]) ?>
                            <span><?= e(format_date($d['created_at'])) ?></span>
                            <span><?= e(human_size((int) $d['taille'])) ?></span>
                        </div>
                    </div>
                    <a class="btn btn-primary btn-sm" href="<?= url('documents/' . (int) $d['id'] . '/telecharger') ?>">Télécharger</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php partial('partials/pagination', ['pager' => $pager]) ?>
    <?php endif; ?>
</section>
