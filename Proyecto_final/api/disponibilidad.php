<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$canchaId = $_GET['cancha_id'] ?? null;
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

if (!$canchaId || !$start || !$end) {
    echo json_encode([]);
    exit;
}

$db = Database::conectar();

$db->exec("UPDATE reservaciones SET estado = 'cancelada' WHERE estado = 'pendiente' AND fecha_reservacion < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");

$eventos = [];

$inicio = new DateTime($start);
$fin = new DateTime($end);
$hoy = new DateTime('today');

$sqlReservas = "SELECT fecha, hora_inicio, hora_fin, estado FROM reservaciones 
                WHERE cancha_id = :cancha_id 
                AND fecha BETWEEN :start AND :end
                AND estado IN ('pendiente', 'confirmada')
                ORDER BY fecha, hora_inicio";
$stmt = $db->prepare($sqlReservas);
$stmt->execute([':cancha_id' => $canchaId, ':start' => $start, ':end' => $end]);
$reservas = $stmt->fetchAll();

$reservasPorFecha = [];
foreach ($reservas as $r) {
    $reservasPorFecha[$r['fecha']][] = $r;
}

$periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);
foreach ($periodo as $fechaObj) {
    $fechaStr = $fechaObj->format('Y-m-d');
    $diaSemana = (int)$fechaObj->format('N');
    $esFinde = ($diaSemana >= 6);

    $sqlHorarios = "SELECT hora_inicio, hora_fin FROM horarios 
                    WHERE cancha_id = :cancha_id AND dia_semana = :dia AND estado = 'disponible'
                    ORDER BY hora_inicio";
    $stmtH = $db->prepare($sqlHorarios);
    $stmtH->execute([':cancha_id' => $canchaId, ':dia' => $diaSemana]);
    $horarios = $stmtH->fetchAll();

    $ocupados = $reservasPorFecha[$fechaStr] ?? [];

    $horaOcupada = [];
    foreach ($ocupados as $occ) {
        $horaOcupada[$occ['hora_inicio']] = true;
    }

    foreach ($horarios as $h) {
        $horaInicio = $h['hora_inicio'];
        $horaFin = $h['hora_fin'];
        $ocupado = isset($horaOcupada[$horaInicio]);

        if ($fechaObj < $hoy) {
            continue;
        }

        if ($fechaObj == $hoy) {
            $horaActual = (int)date('H');
            $horaEvento = (int)substr($horaInicio, 0, 2);
            if ($horaEvento < $horaActual) continue;
        }

        $eventos[] = [
            'title' => $ocupado ? 'Ocupado' : 'Disponible',
            'start' => $fechaStr . 'T' . substr($horaInicio, 0, 5),
            'end' => $fechaStr . 'T' . substr($horaFin, 0, 5),
            'backgroundColor' => $ocupado ? '#dc3545' : '#198754',
            'borderColor' => $ocupado ? '#dc3545' : '#198754',
            'textColor' => '#ffffff',
            'display' => 'background',
            'ocupado' => $ocupado,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin
        ];
    }
}

echo json_encode($eventos);