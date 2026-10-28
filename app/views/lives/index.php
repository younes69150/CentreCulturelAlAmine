<?php partial('partials/page_head', ['eyebrow' => 'Espace élèves', 'heading' => 'Cours en direct', 'sub' => 'Le bouton « Rejoindre » apparaît 15 minutes avant le début du cours.']) ?>
<section class="container narrow page-pad">
    <?php if (!$lives): ?>
        <div class="empty"><p>Aucun cours en direct prévu pour l'instant.</p></div>
    <?php else: ?>
        <ul class="live-list">
            <?php foreach ($lives as $l): $etat = live_state($l); ?>
                <li class="card live-item">
                    <div>
                        <p class="live-when"><?php if ($etat === 'en_direct'): ?><span class="badge badge-green">En direct</span><?php else: ?><span class="badge badge-gold">Programmé</span><?php endif; ?>
                            <?= e(format_date($l['debut'], true)) ?> · <?= (int) $l['duree'] ?> min</p>
                        <h2><?= e($l['titre']) ?></h2>
                        <p class="muted small"><?php partial('partials/audience', ['classe' => $l['classe_nom']]) ?><?= $l['auteur'] ? ' · ' . e($l['auteur']) : '' ?></p>
                    </div>
                    <?php if ($etat === 'en_direct'): ?>
                        <a class="btn btn-gold" href="<?= e($l['lien']) ?>" target="_blank" rel="noopener noreferrer">Rejoindre le direct</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
