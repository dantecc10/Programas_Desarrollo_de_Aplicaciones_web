<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/PayPal.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$reservacionId = $input['reservacion_id'] ?? null;

if (!$reservacionId) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta reservacion_id']);
    exit;
}

$reservacionModel = new Reservacion();
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
$referencia = 'RES-' . $reservacionId . '-' . uniqid();
$order = $paypal->createOrder($reservacion['total'], 'MXN', $referencia);

if (!$order || !isset($order['id'])) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo crear la orden en PayPal']);
    exit;
}

$orderId = $order['id'];

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $db->prepare("UPDATE pagos SET referencia = :referencia WHERE reservacion_id = :reservacion_id");
    $stmt->execute([':referencia' => $orderId, ':reservacion_id' => $reservacionId]);
} catch (PDOException $e) {
    error_log("paypal_create_order: " . $e->getMessage());
}

echo json_encode(['orderID' => $orderId]);
