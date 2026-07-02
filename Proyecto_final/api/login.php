<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../classes/Historial.php';

$usuarioModel = new Usuario();
$historial = new Historial();
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$usuario = $usuarioModel->login($email, $password);
if ($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $usuarioModel->actualizarUltimoAcceso($usuario['id']);
    $historial->registrar($usuario['id'], 'Inicio de sesión', 'Usuario inició sesión');
    $_SESSION['mensaje'] = 'Bienvenido ' . htmlspecialchars($usuario['nombre']);
    $_SESSION['tipo_mensaje'] = 'success';
    header('Location: ../index.php');
} else {
    $_SESSION['mensaje'] = 'Credenciales incorrectas.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/login.php');
}
exit;
