<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'canchas_deportivas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'TU CANCHA - Canchas Deportivas');
define('SITE_URL', 'http://localhost:8080/proyect/Proyecto_final');
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/proyect/Proyecto_final/assets/img/');
define('CANCHAS_IMG_DIR', UPLOAD_DIR . 'canchas/');
define('USUARIOS_IMG_DIR', UPLOAD_DIR . 'usuarios/');

// Configuraci�n de correo
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'tu_correo@gmail.com');
define('MAIL_PASS', 'tu_contraseña');
define('MAIL_FROM', 'noreply@tucancha.com');
define('MAIL_FROM_NAME', 'TU CANCHA');

date_default_timezone_set('America/Mexico_City');

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
