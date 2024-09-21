<?php

// Load .env file
/* if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
} else {
    die('Error: .env file not found.');
} */

// require __DIR__ . '/vendor/autoload.php';
require_once '../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// require_once '../vendor/autoload.php';
require_once '../config/Database.php';
require_once '../app/router/Router.php';
require_once '../app/router/api.php';
// use App\Router\Router;

// require_once '../../router/api.php';

?>
