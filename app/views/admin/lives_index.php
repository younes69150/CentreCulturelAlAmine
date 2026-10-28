<?php partial('admin/_head', ['user' => $user, 'heading' => 'Cours en direct', 'sub' => 'Publiez un lien de visio : les élèves des classes choisies voient « Rejoindre le direct » sur leur session.', 'action' => ['admin/direct/nouveau', 'Nouveau direct']]) ?>
<section class="container page-pad">
    <?php if (!$lives): ?>
        <div class="empty"><p>Aucun cours en direct pour l'instant.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Cours</th><th>Visible par</th><th>Horaire</th><th>État</th><th class="right">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($lives as $l):
                    $etat = live_state($l);
                    $badge = ['en_direct' => ['En direct', 'badge-green'], 'programme' => ['Programmé', 'badge-gold'], 'termine' => ['Terminé', 'badge-muted']][$etat]; ?>
                    <tr<?= $etat === 'termine' ? ' class="is-disabled"' : '' ?>>
                        <td><?= e($l['titre']) ?></td>
                        <td><?php partial('partials/audience', ['classe' => $l['classe_nom']]) ?></td>
                        <td class="small"><?= e(format_date($l['debut'], true)) ?><br><?= (int) $l['duree'] ?> min</td>
                        <td><span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
                        <td class="right actions">
                            <?php if ($etat !== 'termine'): ?>
                                <a class="btn btn-primary btn-sm" href="<?= e($l['lien']) ?>" target="_blank" rel="noopener noreferrer">Ouvrir la visio</a>
                            <?php endif; ?>
                            <details class="row-menu">
                                <summary class="btn btn-ghost btn-sm" aria-label="Plus d'actions">⋯</summary>
                                <div class="row-menu-pop">
                                    <?php if ($etat !== 'termine'): ?>
                                        <a class="menu-item" href="<?= url('admin/direct/' . (int) $l['id']) ?>">Modifier</a>
                                        <form method="post" action="<?= url('admin/direct/' . (int) $l['id'] . '/terminer') ?>" data-confirm="Terminer ce cours ? Le lien ne sera plus affiché.">
                                            <?= csrf_field() ?><button class="menu-item">Terminer maintenant</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?= url('admin/direct/' . (int) $l['id'] . '/supprimer') ?>" data-confirm="Supprimer ce cours ?">
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
