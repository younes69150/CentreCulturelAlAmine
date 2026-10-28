<?php $l = fn (string $k) => e(config('legal.' . $k) ?: 'À compléter'); ?>
<?php partial('partials/page_head', ['heading' => 'Conditions générales d\'utilisation', 'sub' => 'En vigueur au ' . format_date('2026-10-27')]) ?>
<article class="container narrow page-pad prose-page">
    <h2>1. Objet</h2>
    <p>Ces conditions encadrent l'utilisation du site de <?= $l('structure') ?>. Créer un compte vaut acceptation de ces conditions.</p>

    <h2>2. Accès au service</h2>
    <p>Le site est réservé aux élèves, à leurs familles et aux professeurs de l'école. Un compte ne peut être créé qu'avec un code personnel à usage unique remis par l'école. Le site est gratuit.</p>

    <h2>3. Compte</h2>
    <ul>
        <li>Les informations données à l'inscription doivent être exactes.</li>
        <li>Le compte est personnel. Le mot de passe ne doit pas être partagé.</li>
        <li>En cas d'oubli, le mot de passe peut être changé avec « Mot de passe oublié ».</li>
        <li>Pour un élève de moins de 15 ans, le compte est créé par un parent ou avec son accord.</li>
    </ul>

    <h2>4. Utilisation des contenus</h2>
    <p>Les actualités et documents sont fournis pour l'usage personnel des élèves et de leurs familles. Il est interdit de les diffuser publiquement, de les revendre ou de les modifier sans l'accord de l'école.</p>

    <h2>5. Comportement</h2>
    <p>Il est interdit d'essayer d'accéder aux comptes d'autres personnes, de contourner les protections du site ou de perturber son fonctionnement.</p>

    <h2>6. Suspension et suppression</h2>
    <p>L'école peut désactiver ou supprimer un compte en cas de non-respect de ces conditions ou à la fin de l'inscription de l'élève.</p>

    <h2>7. Responsabilité</h2>
    <p>L'école fait de son mieux pour que le site soit disponible et que les informations soient exactes, sans pouvoir le garantir en permanence. Pour toute information importante (horaires, fermetures), l'école reste joignable directement.</p>

    <h2>8. Données personnelles</h2>
    <p>Voir la <a href="<?= url('confidentialite') ?>">politique de confidentialité</a>.</p>

    <h2>9. Modification et droit applicable</h2>
    <p>Ces conditions peuvent évoluer ; la date en haut de page indique la dernière version. Elles sont soumises au droit français.</p>

    <h2>Contact</h2>
    <p><?= $l('email') ?></p>
</article>
