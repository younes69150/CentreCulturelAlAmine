<?php
// Création d'un administrateur en ligne de commande (alternative à /installation).
// Usage : php bin/create-admin.php email@exemple.fr Prénom Nom
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}
require dirname(__DIR__) . '/app/bootstrap.php';

[$script, $email, $prenom, $nom] = $argv + [null, null, null, null];
if (!$email || !$prenom || !$nom || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage : php bin/create-admin.php email Prénom Nom\n");
    exit(1);
}
echo 'Mot de passe (10 caractères min.) : ';
system('stty -echo 2>/dev/null');
$password = trim((string) fgets(STDIN));
system('stty echo 2>/dev/null');
echo "\n";
if (mb_strlen($password) < 10) {
    fwrite(STDERR, "Mot de passe trop court.\n");
    exit(1);
}
db_insert(
    "INSERT INTO users (prenom, nom, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, 'admin', ?)",
    [$prenom, $nom, mb_strtolower($email), hash_password($password), now()]
);
echo "Administrateur créé.\n";
