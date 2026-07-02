<?php
require_once __DIR__ . '/load_env.php';
require_once __DIR__ . '/../vendor/autoload.php';

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'canchas_deportivas');
define('DB_USER', getenv('DB_USER') ?: 'project_user');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
define('SITE_NAME', 'TU CANCHA - Canchas Deportivas');
define('SITE_URL', $protocol . '://' . $host);
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/img/');
define('CANCHAS_IMG_DIR', UPLOAD_DIR . 'canchas/');
define('USUARIOS_IMG_DIR', UPLOAD_DIR . 'usuarios/');

define('MAIL_HOST', getenv('MAIL_HOST') ?: '');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_SMTP_SECURE', getenv('MAIL_SECURE') ?: 'tls');
define('MAIL_USER', getenv('MAIL_USER') ?: '');
define('MAIL_PASS', getenv('MAIL_PASS') ?: '');
define('MAIL_FROM', getenv('MAIL_FROM') ?: '');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: SITE_NAME);

define('PAYPAL_MODE', getenv('PAYPAL_MODE') ?: 'sandbox');
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: '');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: '');

date_default_timezone_set('America/Mexico_City');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
