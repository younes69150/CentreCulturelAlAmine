<?php partial('admin/_head', ['user' => $user, 'heading' => 'Statistiques', 'sub' => 'Fréquentation des 30 derniers jours. Mesure anonyme, sans cookie.']) ?>
<?php $total = array_sum($days); $max = max(1, max($days)); ?>
<section class="container page-pad">
    <div class="stats">
        <div class="stat"><strong><?= $total ?></strong><span>pages vues</span></div>
        <div class="stat"><strong><?= $logins ?></strong><span>connexions</span></div>
        <div class="stat"><strong><?= str_replace('.', ',', (string) round($total / 30, 1)) ?></strong><span>pages vues par jour</span></div>
        <div class="stat"><strong><?= $notFound ?></strong><span>liens cassés visités</span></div>
    </div>

    <div class="card">
        <h2>Pages vues par jour</h2>
        <?php if ($total === 0): ?>
            <p class="muted">Aucune visite enregistrée pour l'instant.</p>
        <?php else: ?>
            <div class="bars" role="img" aria-label="Pages vues par jour sur 30 jours, maximum <?= $max ?>">
                <?php foreach ($days as $day => $v): ?>
                    <div class="bar" title="<?= e(format_date($day)) ?> : <?= $v ?>"><span class="h<?= (int) round($v / $max * 20) ?>"></span></div>
                <?php endforeach; ?>
            </div>
            <div class="bars-axis muted small"><span><?= e(format_date(array_key_first($days))) ?></span><span>Aujourd'hui</span></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Pages les plus vues</h2>
        <?php if (!$pages): ?>
            <p class="muted">Rien pour l'instant.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Page</th><th class="right">Vues</th></tr></thead>
                <tbody>
                <?php foreach ($pages as $p): ?>
                    <tr><td><?= e(STATS_LABELS[$p['path']] ?? $p['path']) ?></td><td class="right"><?= (int) $p['v'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
