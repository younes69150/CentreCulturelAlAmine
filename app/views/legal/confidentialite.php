<?php $l = fn (string $k) => e(config('legal.' . $k) ?: 'À compléter'); ?>
<?php partial('partials/page_head', ['heading' => 'Politique de confidentialité', 'sub' => 'Mise à jour le ' . format_date('2026-10-27')]) ?>
<article class="container narrow page-pad prose-page">
    <h2>Qui est responsable de vos données ?</h2>
    <p><?= $l('structure') ?>, <?= $l('adresse') ?>. Contact : <?= $l('email') ?>.</p>
    <p>Le site sert uniquement à informer les élèves, leurs familles et les professeurs : actualités, documents de cours et messages de l'école.</p>

    <h2>Données collectées</h2>
    <ul>
        <li>Prénom, nom, adresse e-mail, classe et statut (élève, professeur, administrateur) ;</li>
        <li>le mot de passe, stocké uniquement sous forme chiffrée (l'école ne peut pas le lire) ;</li>
        <li>la date de création du compte et de la dernière connexion ;</li>
        <li>l'adresse IP en cas d'échec de connexion, gardée 24 heures au plus pour bloquer les attaques ;</li>
        <li>un compteur anonyme de pages vues par jour (voir « Mesure d'audience »).</li>
    </ul>
    <p>Aucune donnée n'est vendue, partagée avec des tiers ni utilisée pour de la publicité.</p>

    <h2>Base légale et durée de conservation</h2>
    <p>Le traitement repose sur l'intérêt légitime de l'association à communiquer avec ses élèves et sur l'inscription volontaire avec un code remis par l'école. Les comptes sont supprimés à la fin de l'inscription de l'élève, et au plus tard un an après la dernière connexion. Les sauvegardes sont conservées 14 jours.</p>

    <h2>Élèves mineurs</h2>
    <p>Pour un élève de moins de 15 ans, le compte doit être créé par l'un de ses parents ou avec son accord.</p>

    <h2>Cookies</h2>
    <p>Le site dépose un seul cookie, nécessaire à la connexion (« ccalamine »). Il est supprimé à la fermeture du navigateur ou à la déconnexion. Ce cookie étant indispensable au service, il ne demande pas de consentement : c'est pourquoi le site n'affiche pas de bandeau cookies.</p>

    <h2>Mesure d'audience</h2>
    <p>Le site compte le nombre de pages vues par jour, sans cookie, sans adresse IP et sans identifier les visiteurs. Ces chiffres anonymes sont gardés 13 mois et ne sont partagés avec personne. Le site ne charge aucune ressource externe (pas de Google Analytics, pas de polices Google).</p>

    <h2>Sécurité</h2>
    <p>Les échanges sont chiffrés (HTTPS), les mots de passe sont hachés, les fichiers déposés ne sont accessibles qu'aux personnes autorisées, et les données sont sauvegardées chaque nuit.</p>

    <h2>Vos droits</h2>
    <p>Vous pouvez consulter, corriger ou supprimer vos données, et vous opposer à leur traitement, en écrivant à <?= $l('email') ?>. Si vous estimez que vos droits ne sont pas respectés, vous pouvez saisir la CNIL (<span class="nowrap">www.cnil.fr</span>).</p>
</article>
