<?php
// Sauvegarde de la base (SQL compressé) et des fichiers déposés (zip) dans storage/backups.
// Garde les 14 dernières sauvegardes de chaque type.
// À lancer chaque nuit par une tâche cron (voir README) :
//   php /home/UTILISATEUR/centreculturelalamine/bin/backup.php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}
require dirname(__DIR__) . '/app/bootstrap.php';

const KEEP = 14;
$dir = BASE_PATH . '/storage/backups';
if (!is_dir($dir) && !mkdir($dir, 0750, true)) {
    fwrite(STDERR, "Impossible de créer $dir\n");
    exit(1);
}
$stamp = date('Y-m-d_His');

// --- Base de données ---
$sqlFile = "$dir/base_$stamp.sql.gz";
$gz = gzopen($sqlFile, 'wb6');
gzwrite($gz, "-- Sauvegarde " . config('app.name') . " du " . now() . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");
$pdo = db();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$rows = 0;
foreach ($tables as $table) {
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
    gzwrite($gz, "DROP TABLE IF EXISTS `$table`;\n$create;\n\n");
    $stmt = $pdo->query("SELECT * FROM `$table`", PDO::FETCH_NUM);
    while ($row = $stmt->fetch()) {
        $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row);
        gzwrite($gz, "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n");
        $rows++;
    }
    gzwrite($gz, "\n");
}
gzwrite($gz, "SET FOREIGN_KEY_CHECKS = 1;\n");
gzclose($gz);
chmod($sqlFile, 0640);

// --- Fichiers déposés ---
$zipFile = "$dir/fichiers_$stamp.zip";
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "Impossible de créer $zipFile\n");
    exit(1);
}
$files = 0;
foreach (glob(upload_dir() . '/*') as $file) {
    if (is_file($file) && preg_match('/^[a-f0-9]{64}$/', basename($file))) {
        $zip->addFile($file, basename($file));
        $files++;
    }
}
if ($files === 0) {
    $zip->addFromString('LISEZMOI.txt', "Aucun fichier déposé au moment de la sauvegarde.\n");
}
$zip->close();
chmod($zipFile, 0640);

// --- Rotation ---
foreach (['base_*.sql.gz', 'fichiers_*.zip'] as $pattern) {
    $all = glob("$dir/$pattern");
    rsort($all);
    foreach (array_slice($all, KEEP) as $old) {
        unlink($old);
    }
}

printf("Sauvegarde OK : %d tables, %d lignes, %d fichiers.\n  %s\n  %s\n", count($tables), $rows, $files, $sqlFile, $zipFile);
