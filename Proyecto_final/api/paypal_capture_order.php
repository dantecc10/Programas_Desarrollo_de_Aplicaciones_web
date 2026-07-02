<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Pago.php';
require_once __DIR__ . '/../classes/Historial.php';
require_once __DIR__ . '/../classes/Mailer.php';
require_once __DIR__ . '/../classes/PayPal.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$reservacionId = $input['reservacion_id'] ?? null;
$orderId = $input['order_id'] ?? null;

if (!$reservacionId || !$orderId) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros']);
    exit;
}

$reservacionModel = new Reservacion();
$pagoModel = new Pago();
$historial = new Historial();

$reservacion = $reservacionModel->obtenerPorId($reservacionId);

if (!$reservacion || $reservacion['usuario_id'] != $_SESSION['usuario_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Reservación no válida']);
    exit;
}

if ($reservacion['estado'] !== 'pendiente') {
    http_response_code(400);
    echo json_encode(['error' => 'La reservación ya fue procesada']);
    exit;
}

$paypal = new PayPal();
$capture = $paypal->captureOrder($orderId);

if (!$capture || ($capture['status'] ?? '') !== 'COMPLETED') {
    http_response_code(500);
    echo json_encode(['error' => 'Error al capturar el pago en PayPal', 'status' => $capture['status'] ?? 'unknown']);
    exit;
}

$payerEmail = $capture['payer']['email_address'] ?? '';
$captureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? '';

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE pagos SET metodo_pago = 'paypal', estado_pago = 'completado', referencia = :referencia, fecha_pago = NOW() WHERE reservacion_id = :reservacion_id");
    $stmt->execute([':referencia' => $orderId, ':reservacion_id' => $reservacionId]);

    $stmt = $db->prepare("UPDATE reservaciones SET estado = 'confirmada' WHERE id = :id");
    $stmt->execute([':id' => $reservacionId]);

    $db->commit();
} catch (PDOException $e) {
    $db->rollBack();
    error_log("paypal_capture_order DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la base de datos']);
    exit;
}

$historial->registrar($_SESSION['usuario_id'], 'Pago realizado', "Pago PayPal de reservación #$reservacionId - OrderID: $orderId");

$mailer = new Mailer();
$mailer->confirmacionReservacion(
    $reservacion['usuario_nombre'],
    $reservacion['email'],
    $reservacion['cancha_nombre'],
    $reservacion['fecha'],
    $reservacion['hora_inicio'],
    $reservacion['hora_fin'],
    $reservacion['total'],
    $orderId
);

echo json_encode(['status' => 'COMPLETED', 'capture_id' => $captureId, 'payer_email' => $payerEmail]);
