<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Pago.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Historial.php';

$pagoModel = new Pago();
$reservacionModel = new Reservacion();
$historial = new Historial();

$reservacionId = $_POST['reservacion_id'] ?? null;
$metodoPago = $_POST['metodo_pago'] ?? 'tarjeta';

if (!$reservacionId) {
    $_SESSION['mensaje'] = 'Reservación no especificada.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/mis_reservaciones.php');
    exit;
}

$reservacion = $reservacionModel->obtenerPorId($reservacionId);
if (!$reservacion || $reservacion['usuario_id'] != $_SESSION['usuario_id']) {
    $_SESSION['mensaje'] = 'Reservación no válida.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../pages/mis_reservaciones.php');
    exit;
}

$resultado = $pagoModel->procesarPago($reservacionId, $metodoPago);

if ($resultado['exito']) {
    $historial->registrar($_SESSION['usuario_id'], 'Pago realizado', "Pago de reservación #$reservacionId - Ref: {$resultado['referencia']}");

    $_SESSION['mensaje'] = '¡Pago exitoso! Tu reservación está confirmada. Referencia: ' . $resultado['referencia'];
    $_SESSION['tipo_mensaje'] = 'success';
} else {
    $_SESSION['mensaje'] = 'Error al procesar el pago: ' . $resultado['mensaje'];
    $_SESSION['tipo_mensaje'] = 'danger';
}

header('Location: ../pages/mis_reservaciones.php');
exit;
