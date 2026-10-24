<?php $l = fn (string $k) => e(config('legal.' . $k, 'À compléter')); ?>
<?php partial('partials/page_head', ['heading' => 'Confidentialité et mentions légales', 'sub' => 'Mise à jour le ' . format_date('2026-10-24')]) ?>
<article class="container narrow page-pad prose-page">
    <h2>Éditeur du site</h2>
    <p><?= $l('structure') ?><br><?= $l('adresse') ?><br>Responsable de la publication : <?= $l('responsable') ?><br>Contact : <?= $l('email') ?></p>

    <h2>Hébergement</h2>
    <p>O2switch, 222-224 boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France. Les données sont stockées en France.</p>

    <h2>Données personnelles</h2>
    <p>Le responsable du traitement est <?= $l('structure') ?>. Le site sert uniquement à informer les élèves, leurs familles et les professeurs : actualités, documents de cours et messages de l'école.</p>
    <h3>Données collectées</h3>
    <ul>
        <li>Prénom, nom, adresse e-mail, classe et statut (élève, professeur, administrateur) ;</li>
        <li>le mot de passe, stocké uniquement sous forme chiffrée (l'école ne peut pas le lire) ;</li>
        <li>la date de création du compte et de la dernière connexion ;</li>
        <li>l'adresse IP en cas d'échec de connexion, gardée 24 heures au plus pour bloquer les attaques.</li>
    </ul>
    <p>Aucune donnée n'est vendue, partagée avec des tiers ni utilisée pour de la publicité. Le site n'utilise aucun outil de mesure d'audience et ne charge aucune ressource externe.</p>
    <h3>Base légale et durée de conservation</h3>
    <p>Le traitement repose sur l'intérêt légitime de l'association à communiquer avec ses élèves et sur l'inscription volontaire avec un code remis par l'école. Les comptes sont supprimés à la fin de l'inscription de l'élève, et au plus tard un an après la dernière connexion.</p>
    <h3>Élèves mineurs</h3>
    <p>Pour un élève de moins de 15 ans, le compte doit être créé par l'un de ses parents ou avec son accord.</p>
    <h3>Vos droits</h3>
    <p>Vous pouvez consulter, corriger ou supprimer vos données, et vous opposer à leur traitement, en écrivant à <?= $l('email') ?>. Vous pouvez aussi saisir la CNIL (<span class="nowrap">www.cnil.fr</span>).</p>

    <h2>Cookies</h2>
    <p>Le site dépose un seul cookie, nécessaire à la connexion (« ccalamine »). Il est supprimé à la fermeture du navigateur ou à la déconnexion. Ce cookie étant indispensable au fonctionnement du service, il ne demande pas de consentement.</p>

    <h2>Sécurité</h2>
    <p>Les échanges sont chiffrés (HTTPS), les mots de passe sont hachés, les fichiers déposés ne sont accessibles qu'aux personnes autorisées, et les données sont sauvegardées chaque nuit.</p>
</article>
