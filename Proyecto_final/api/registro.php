<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../classes/Historial.php';

$usuarioModel = new Usuario();
$historial = new Historial();
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar_password'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');

if (!$nombre || !$email || !$password) {
    $_SESSION['mensaje'] = 'Todos los campos obligatorios deben ser llenados.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/registro.php');
    exit;
}
if ($password !== $confirmar) {
    $_SESSION['mensaje'] = 'Las contraseñas no coinciden.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/registro.php');
    exit;
}
if (strlen($password) < 6) {
    $_SESSION['mensaje'] = 'La contraseña debe tener al menos 6 caracteres.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/registro.php');
    exit;
}
if ($usuarioModel->emailExiste($email)) {
    $_SESSION['mensaje'] = 'El correo electrónico ya está registrado.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/registro.php');
    exit;
}

$id = $usuarioModel->registrar([
    'nombre' => $nombre,
    'email' => $email,
    'password' => $password,
    'telefono' => $telefono
]);

$_SESSION['usuario_id'] = $id;
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_rol'] = 'cliente';
$_SESSION['usuario_email'] = $email;
$historial->registrar($id, 'Registro', 'Nuevo usuario registrado');

$_SESSION['mensaje'] = 'Registro exitoso. ¡Bienvenido!';
$_SESSION['tipo_mensaje'] = 'success';
header('Location: ../index.php');
exit;
