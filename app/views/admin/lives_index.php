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
                                <a class="btn btn-ghost btn-sm" href="<?= url('admin/direct/' . (int) $l['id']) ?>">Modifier</a>
                                <form method="post" action="<?= url('admin/direct/' . (int) $l['id'] . '/terminer') ?>" class="inline" data-confirm="Terminer ce cours ? Le lien ne sera plus affiché.">
                                    <?= csrf_field() ?><button class="btn btn-ghost btn-sm">Terminer</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('admin/direct/' . (int) $l['id'] . '/supprimer') ?>" class="inline" data-confirm="Supprimer ce cours ?">
                                <?= csrf_field() ?><button class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
