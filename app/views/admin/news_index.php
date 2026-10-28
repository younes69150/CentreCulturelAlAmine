<?php partial('admin/_head', ['user' => $user, 'heading' => 'Actualités', 'action' => ['admin/actualites/nouvelle', 'Nouvelle actualité']]) ?>
<section class="container page-pad">
    <?php if (!$news): ?>
        <div class="empty"><p>Aucune actualité. Publiez la première !</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Titre</th><th>Visible par</th><th>Date</th><th class="right">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($news as $n): ?>
                    <tr>
                        <td><?php if ($n['epingle']): ?><span class="badge badge-gold">Épinglé</span> <?php endif; ?><?= e($n['titre']) ?></td>
                        <td><?php partial('partials/audience', ['classe' => $n['classe_nom']]) ?></td>
                        <td class="nowrap"><?= e(format_date($n['created_at'])) ?></td>
                        <td class="right actions">
                            <a class="btn btn-ghost btn-sm" href="<?= url('admin/actualites/' . (int) $n['id']) ?>">Modifier</a>
                            <form method="post" action="<?= url('admin/actualites/' . (int) $n['id'] . '/supprimer') ?>" class="inline" data-confirm="Supprimer cette actualité ?">
                                <?= csrf_field() ?><button class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php partial('partials/pagination', ['pager' => $pager]) ?>
    <?php endif; ?>
</section>
