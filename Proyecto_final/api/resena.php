<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Resena.php';
require_once __DIR__ . '/../classes/Reservacion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$resenaModel = new Resena();
$reservacionModel = new Reservacion();

$accion = $_POST['accion'] ?? '';

if ($accion === 'crear') {
    $reservacionId = (int)($_POST['reservacion_id'] ?? 0);
    $puntuacion = (int)($_POST['puntuacion'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($puntuacion < 1 || $puntuacion > 5) {
        echo json_encode(['exito' => false, 'mensaje' => 'La puntuación debe ser entre 1 y 5.']);
        exit;
    }

    $reservacion = $reservacionModel->obtenerPorId($reservacionId);
    if (!$reservacion || $reservacion['usuario_id'] != $_SESSION['usuario_id']) {
        echo json_encode(['exito' => false, 'mensaje' => 'Reservación no encontrada.']);
        exit;
    }

    if ($reservacion['estado'] !== 'completada') {
        echo json_encode(['exito' => false, 'mensaje' => 'Solo puedes calificar reservaciones completadas.']);
        exit;
    }

    $existente = $resenaModel->obtenerPorReservacion($reservacionId);
    if ($existente) {
        echo json_encode(['exito' => false, 'mensaje' => 'Ya calificaste esta reservación.']);
        exit;
    }

    $resenaModel->crear([
        'reservacion_id' => $reservacionId,
        'usuario_id' => $_SESSION['usuario_id'],
        'cancha_id' => $reservacion['cancha_id'],
        'puntuacion' => $puntuacion,
        'comentario' => $comentario,
    ]);

    echo json_encode(['exito' => true, 'mensaje' => 'Gracias por tu calificación.']);
    exit;
}

echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida.']);
