<?php
// Remplit une base VIDE avec des données fictives pour tester l'interface en local.
// Usage : php bin/demo-data.php
// Refuse de tourner si debug est désactivé (production) ou si des comptes existent déjà.
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}
require dirname(__DIR__) . '/app/bootstrap.php';

if (!config('app.debug')) {
    fwrite(STDERR, "Refusé : réservé au test local (app.debug doit valoir true).\n");
    exit(1);
}
if ((int) db_value('SELECT COUNT(*) FROM users') > 0) {
    fwrite(STDERR, "Refusé : la base contient déjà des comptes.\n");
    exit(1);
}

const DEMO_PASSWORD = 'demo123456';
$ago = fn (string $rel) => date('Y-m-d H:i:s', strtotime($rel));

$classes = [];
foreach (['Niveau 1 – samedi matin', 'Niveau 2 – samedi après-midi', 'Adultes – mercredi soir'] as $nom) {
    $classes[] = db_insert('INSERT INTO classes (nom, created_at) VALUES (?, ?)', [$nom, now()]);
}
[$n1, $n2, $adultes] = $classes;

$hash = hash_password(DEMO_PASSWORD);
$user = fn (string $prenom, string $nom, string $email, string $role, ?int $classe, string $created) => db_insert(
    'INSERT INTO users (prenom, nom, email, password_hash, role, classe_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
    [$prenom, $nom, $email, $hash, $role, $classe, $created]
);
$admin = $user('Samir', 'Haddad', 'admin@demo.local', 'admin', null, $ago('-40 days'));
$prof  = $user('Leila', 'Mansouri', 'prof@demo.local', 'prof', null, $ago('-35 days'));
$user('Yasmine', 'Benali', 'eleve1@demo.local', 'eleve', $n1, $ago('-20 days'));
$user('Adam', 'Kaddour', 'eleve2@demo.local', 'eleve', $n2, $ago('-18 days'));
$user('Nadia', 'Rahmani', 'adulte@demo.local', 'eleve', $adultes, $ago('-10 days'));
foreach ([['Inès', 'Cherif', $n1], ['Rayan', 'Bouzid', $n1], ['Sofia', 'Amrani', $n2], ['Ilyes', 'Saidi', $n2], ['Karim', 'Toumi', $adultes]] as $i => [$p, $n, $c]) {
    $user($p, $n, 'eleve' . ($i + 10) . '@demo.local', 'eleve', $c, $ago('-' . (15 - $i) . ' days'));
}

$news = [
    ['Bienvenue sur le nouveau site de l\'école', "Chers élèves, chers parents,\n\nCe site remplace les messages papier. Vous y trouverez les actualités, les documents de cours et les informations importantes.\n\nBonne année scolaire à tous !\nL'équipe pédagogique", null, 1, '-9 days'],
    ['Rentrée des classes', "Les cours reprennent le samedi 7 novembre.\n\nNiveau 1 : 9h – 12h\nNiveau 2 : 14h – 17h\nAdultes : mercredi 19h – 21h\n\nMerci d'arriver 10 minutes en avance.", null, 0, '-7 days'],
    ['Sortie au musée de l\'Institut du monde arabe', "Une sortie est prévue le samedi 21 novembre pour le Niveau 1.\nL'autorisation parentale est à rendre avant le 14 novembre.", $n1, 0, '-5 days'],
    ['Contrôle de lecture', "Un petit contrôle de lecture aura lieu le 28 novembre. Révisez les leçons 1 à 4.", $n2, 0, '-3 days'],
    ['Nouveau cours de calligraphie', "Un atelier de calligraphie (style naskh) est proposé aux adultes un mercredi sur deux, à partir du 2 décembre.", $adultes, 0, '-2 days'],
    ['Fermeture pendant les vacances', "L'école sera fermée du 19 décembre au 4 janvier. Bonnes fêtes de fin d'année !", null, 0, '-1 day'],
];
foreach ($news as [$titre, $contenu, $classe, $epingle, $rel]) {
    db_insert(
        'INSERT INTO news (titre, contenu, classe_id, epingle, auteur_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$titre, $contenu, $classe, $epingle, $prof, $ago($rel), $ago($rel)]
    );
}

$annonces = [
    ['Rentrée samedi 7 novembre', "Les cours reprennent à 9h. N'oubliez pas votre cahier et votre trousse.", null],
    ['Autorisation de sortie', "Pensez à rendre l'autorisation parentale pour la sortie du 21 novembre.", $n1],
    ['Changement de salle', "À partir de ce mercredi, le cours des adultes a lieu en salle 3.", $adultes],
];
foreach ($annonces as [$titre, $contenu, $classe]) {
    db_insert(
        'INSERT INTO annonces (titre, contenu, classe_id, debut, fin, actif, auteur_id, created_at, updated_at) VALUES (?, ?, ?, NULL, ?, 1, ?, ?, ?)',
        [$titre, $contenu, $classe, $ago('+30 days'), $admin, now(), now()]
    );
}

// Petits fichiers d'exemple (texte et PDF minimal) rangés comme de vrais dépôts.
$pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n4 0 obj<</Length 60>>stream\nBT /F1 24 Tf 72 760 Td (Document de demonstration) Tj ET\nendstream endobj\n5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";
$docs = [
    ['Alphabet arabe – fiche récapitulative', 'Les 28 lettres et leurs formes.', 'alphabet.pdf', 'application/pdf', $pdf, null],
    ['Leçon 3 : les lettres solaires', 'Cours et exercices à faire pour samedi.', 'lecon-3.pdf', 'application/pdf', $pdf, $n1],
    ['Vocabulaire – la famille', 'Liste de mots à apprendre.', 'vocabulaire-famille.txt', 'text/plain', "أب : père\nأم : mère\nأخ : frère\nأخت : sœur\n", $n2],
    ['Modèles de calligraphie', null, 'calligraphie.pdf', 'application/pdf', $pdf, $adultes],
];
foreach ($docs as [$titre, $desc, $nomFichier, $mime, $contenu, $classe]) {
    $stored = bin2hex(random_bytes(32));
    file_put_contents(upload_dir() . '/' . $stored, $contenu);
    db_insert(
        'INSERT INTO documents (titre, description, nom_original, nom_stockage, mime, taille, classe_id, auteur_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$titre, $desc, $nomFichier, $stored, $mime, strlen($contenu), $classe, $prof, now()]
    );
}

$codes = [];
foreach ([[$n1, 'Famille Benali (frère)'], [$n2, null], [$adultes, null]] as [$classe, $label]) {
    $code = generate_invite_code();
    db_insert(
        'INSERT INTO invite_codes (code_hash, code_hint, label, role, classe_id, expires_at, created_by, created_at) VALUES (?, ?, ?, \'eleve\', ?, ?, ?, ?)',
        [hash_code($code), substr($code, 0, 4), $label, $classe, $ago('+30 days'), $admin, now()]
    );
    $codes[] = $code;
}

// Statistiques de fréquentation fictives sur 30 jours.
$pages = ['/' => 25, '/connexion' => 18, '/actualites' => 30, '/actualites/{id}' => 14, '/documents' => 20, '/mon-compte' => 3, '(connexion réussie)' => 15, '(page introuvable)' => 1];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $weekend = in_array(date('N', strtotime($day)), ['6', '7'], true) ? 1.8 : 1;
    foreach ($pages as $path => $avg) {
        $views = (int) round($avg * $weekend * random_int(40, 130) / 100);
        if ($views > 0) db_run('INSERT INTO page_views (day, path, views) VALUES (?, ?, ?)', [$day, $path, $views]);
    }
}

echo "Données fictives créées. Mot de passe de tous les comptes : " . DEMO_PASSWORD . "\n";
echo "  Admin       : admin@demo.local\n";
echo "  Professeur  : prof@demo.local\n";
echo "  Élève N1    : eleve1@demo.local\n";
echo "  Élève N2    : eleve2@demo.local\n";
echo "  Adulte      : adulte@demo.local\n";
echo "Codes d'inscription libres (Niveau 1, Niveau 2, Adultes) : " . implode('  ', $codes) . "\n";
