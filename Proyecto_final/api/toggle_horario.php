<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { echo json_encode(['ok' => false, 'error' => 'No autorizado']); exit; }
require_once __DIR__ . '/../classes/Horario.php';

$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? null;

if (!$id || !$estado) {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
    exit;
}

$horarioModel = new Horario();
$horarioModel->actualizar($id, ['estado' => $estado]);
echo json_encode(['ok' => true, 'id' => $id, 'estado' => $estado]);
