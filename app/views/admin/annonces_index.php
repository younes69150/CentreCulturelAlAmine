<?php partial('admin/_head', ['user' => $user, 'heading' => 'Messages à la connexion', 'sub' => 'Ces messages s\'affichent dans une fenêtre juste après la connexion.', 'action' => ['admin/messages/nouveau', 'Nouveau message']]) ?>
<section class="container page-pad">
    <?php if (!$annonces): ?>
        <div class="empty"><p>Aucun message.</p></div>
    <?php else: $now = now(); ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Message</th><th>Visible par</th><th>Période</th><th>État</th><th class="right">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($annonces as $a):
                    $state = !$a['actif'] ? ['Désactivé', 'badge-muted']
                        : ($a['fin'] && $a['fin'] < $now ? ['Terminé', 'badge-muted']
                        : ($a['debut'] && $a['debut'] > $now ? ['Programmé', 'badge-gold'] : ['En ligne', 'badge-green'])); ?>
                    <tr>
                        <td><?= e($a['titre']) ?><?php if ($a['audio_stockage']): ?> <span class="badge badge-gold">Vocal</span><?php endif; ?></td>
                        <td><?php partial('partials/audience', ['classe' => $a['classe_nom']]) ?></td>
                        <td class="small">
                            <?= $a['debut'] ? 'Du ' . e(format_date($a['debut'], true)) : 'Dès maintenant' ?><br>
                            <?= $a['fin'] ? 'au ' . e(format_date($a['fin'], true)) : 'sans fin' ?>
                        </td>
                        <td><span class="badge <?= $state[1] ?>"><?= $state[0] ?></span></td>
                        <td class="right actions">
                            <a class="btn btn-ghost btn-sm" href="<?= url('admin/messages/' . (int) $a['id']) ?>">Modifier</a>
<details class="row-menu">
                                <summary class="btn btn-ghost btn-sm" aria-label="Plus d'actions">⋯</summary>
                                <div class="row-menu-pop">
                                    <form method="post" action="<?= url('admin/messages/' . (int) $a['id'] . '/supprimer') ?>" data-confirm="Supprimer ce message ?">
                                        <?= csrf_field() ?><button class="menu-item menu-danger">Supprimer</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
