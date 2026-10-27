<?php $l = fn (string $k) => e(config('legal.' . $k) ?: 'À compléter'); ?>
<?php partial('partials/page_head', ['heading' => 'Mentions légales']) ?>
<article class="container narrow page-pad prose-page">
    <h2>Éditeur du site</h2>
    <p>
        <?= $l('structure') ?><br>
        Siège : <?= $l('adresse') ?><br>
        <?php if (config('legal.rna')): ?>Numéro RNA : <?= $l('rna') ?><br><?php endif; ?>
        Directeur ou directrice de la publication : <?= $l('responsable') ?><br>
        Contact : <?= $l('email') ?><?php if (config('legal.telephone')): ?> · <?= $l('telephone') ?><?php endif; ?>
    </p>

    <h2>Hébergement</h2>
    <p>O2switch SAS, 222-224 boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France. Téléphone : 04 44 44 60 40. Les données sont stockées en France.</p>

    <h2>Propriété intellectuelle</h2>
    <p>Le nom, le logo et les contenus publiés par l'école (textes, documents de cours) appartiennent à <?= $l('structure') ?> ou à leurs auteurs. Ils ne peuvent pas être reproduits ou diffusés hors du site sans autorisation.</p>
    <p>Les polices utilisées (Khand, Cairo, Inter) sont distribuées sous licence SIL Open Font License.</p>

    <h2>Données personnelles et cookies</h2>
    <p>Voir la <a href="<?= url('confidentialite') ?>">politique de confidentialité</a>.</p>

    <h2>Conditions d'utilisation</h2>
    <p>Voir les <a href="<?= url('cgu') ?>">conditions générales d'utilisation</a>.</p>
</article>
