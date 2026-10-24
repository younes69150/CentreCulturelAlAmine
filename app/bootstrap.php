<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration manquante : copiez config/config.example.php en config/config.php.');
}
$GLOBALS['config'] = require $configFile;

require BASE_PATH . '/app/helpers.php';
require BASE_PATH . '/app/db.php';
require BASE_PATH . '/app/auth.php';
require BASE_PATH . '/app/csrf.php';
require BASE_PATH . '/app/view.php';
require BASE_PATH . '/app/uploads.php';
require BASE_PATH . '/app/mail.php';

date_default_timezone_set(config('app.timezone', 'Europe/Paris'));
error_reporting(E_ALL);
ini_set('display_errors', config('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

if (strlen((string) config('app.key')) < 32 || config('app.key') === 'CHANGER_MOI') {
    http_response_code(500);
    exit('Configuration : app.key doit être une valeur secrète d\'au moins 32 caractères.');
}

foreach (glob(BASE_PATH . '/app/controllers/*.php') as $controller) {
    require $controller;
}
