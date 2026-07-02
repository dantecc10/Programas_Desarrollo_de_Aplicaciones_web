<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../classes/Historial.php';

header('Content-Type: application/json');

$usuarioModel = new Usuario();
$historial = new Historial();
$userId = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';

if ($accion === 'actualizar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (!$nombre || !$email) {
        echo json_encode(['exito' => false, 'mensaje' => 'Nombre y email son obligatorios.']);
        exit;
    }

    $datos = [
        'nombre' => $nombre,
        'email' => $email,
        'telefono' => $telefono
    ];

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $targetDir = USUARIOS_IMG_DIR;
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $permitidas)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Formato de imagen no válido.']);
            exit;
        }
        $usuario = $usuarioModel->obtenerPorId($userId);
        if ($usuario && $usuario['foto_perfil']) {
            $rutaVieja = $targetDir . $usuario['foto_perfil'];
            if (file_exists($rutaVieja)) unlink($rutaVieja);
        }
        $nombreArchivo = 'user_' . $userId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $targetDir . $nombreArchivo)) {
            $datos['foto_perfil'] = $nombreArchivo;
        }
    }

    $usuarioModel->actualizar($userId, $datos);
    $_SESSION['usuario_nombre'] = $nombre;
    $historial->registrar($userId, 'Perfil actualizado', 'Usuario actualizó su perfil');
    echo json_encode(['exito' => true, 'mensaje' => 'Perfil actualizado correctamente.']);
    exit;
}

if ($accion === 'cambiar_password') {
    $passwordActual = $_POST['password_actual'] ?? '';
    $passwordNueva = $_POST['password_nueva'] ?? '';
    $passwordConfirmar = $_POST['password_confirmar'] ?? '';

    if (!$passwordActual || !$passwordNueva || !$passwordConfirmar) {
        echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if ($passwordNueva !== $passwordConfirmar) {
        echo json_encode(['exito' => false, 'mensaje' => 'Las contraseñas no coinciden.']);
        exit;
    }

    if (strlen($passwordNueva) < 6) {
        echo json_encode(['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
        exit;
    }

    $usuario = $usuarioModel->obtenerPorId($userId);
    if (!password_verify($passwordActual, $usuario['password'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta.']);
        exit;
    }

    $usuarioModel->cambiarPassword($userId, $passwordNueva);
    $historial->registrar($userId, 'Contraseña cambiada', 'Usuario cambió su contraseña');
    echo json_encode(['exito' => true, 'mensaje' => 'Contraseña cambiada correctamente.']);
    exit;
}

if ($accion === 'solicitar_eliminacion') {
    $usuarioModel->solicitarEliminacion($userId);
    $historial->registrar($userId, 'Eliminación solicitada', 'Usuario solicitó eliminación de cuenta');
    echo json_encode(['exito' => true, 'mensaje' => 'Solicitud de eliminación registrada. Tienes 30 días para cancelarla.']);
    exit;
}

if ($accion === 'cancelar_eliminacion') {
    $usuarioModel->cancelarSolicitudEliminacion($userId);
    $historial->registrar($userId, 'Eliminación cancelada', 'Usuario canceló solicitud de eliminación');
    echo json_encode(['exito' => true, 'mensaje' => 'Solicitud de eliminación cancelada.']);
    exit;
}

echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);