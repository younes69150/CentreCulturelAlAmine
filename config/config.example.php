<?php
// Copier ce fichier en config.php puis remplir les valeurs.
// config.php n'est jamais envoyé sur GitHub.

return [
    'app' => [
        'name'     => 'Centre Culturel Al Amine',
        'debug'    => false,               // true uniquement en local
        'timezone' => 'Europe/Paris',
        // Clé secrète : 64 caractères aléatoires. Générer avec :
        //   php -r "echo bin2hex(random_bytes(32));"
        'key'      => 'CHANGER_MOI',
        // Clé demandée une seule fois sur /installation pour créer le premier admin.
        // La page se désactive dès qu'un compte existe.
        'setup_key' => 'CHANGER_MOI_AUSSI',
    ],
    'db' => [
        'host'    => 'localhost',
        'name'    => 'utilisateur_ccalamine',
        'user'    => 'utilisateur_ccalamine',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'uploads' => [
        'max_mb' => 20,
    ],
];
