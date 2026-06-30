<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Horario.php';

$canchaId = $_GET['cancha_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;

if (!$canchaId || !$fecha) {
    echo json_encode([]);
    exit;
}

$horarioModel = new Horario();
$horarios = $horarioModel->obtenerConDisponibilidad($canchaId, $fecha);
echo json_encode($horarios);
