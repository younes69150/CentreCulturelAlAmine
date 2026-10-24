<?php partial('admin/_head', ['user' => $user, 'heading' => count($batch['codes']) > 1 ? count($batch['codes']) . ' codes générés' : 'Code généré']) ?>
<section class="container narrow page-pad">
    <div class="alert alert-info no-print">Notez ou imprimez ces codes maintenant : ils ne seront plus jamais affichés en clair.</div>
    <div class="code-sheet">
        <?php foreach ($batch['codes'] as $code): ?>
            <div class="code-ticket">
                <img src="<?= url('assets/img/logo.svg') ?>" alt="" width="36" height="36">
                <div>
                    <p class="code-school">Centre Culturel Al Amine</p>
                    <p class="code-value"><?= e($code) ?></p>
                    <p class="code-info">
                        <?= e(role_label($batch['role'])) ?><?= $batch['classe'] ? ' · Classe ' . e($batch['classe']) : '' ?>
                        <?= $batch['expires'] ? ' · Valable jusqu\'au ' . e(format_date($batch['expires'])) : '' ?>
                        <?= $batch['label'] ? '<br>' . e($batch['label']) : '' ?>
                    </p>
                    <p class="code-help">Inscription sur le site, rubrique « J'ai un code d'inscription ».</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="form-actions no-print">
        <button type="button" class="btn btn-ghost" data-copy="<?= e(implode("\n", $batch['codes'])) ?>">Copier</button>
        <button type="button" class="btn btn-primary" data-print>Imprimer</button>
        <a class="btn btn-ghost" href="<?= url('admin/codes') ?>">Terminé</a>
    </div>
</section>
