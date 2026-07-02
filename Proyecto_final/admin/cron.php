<?php
/**
 * CRON: Tareas programadas
 *
 * Ejecutar cada hora via crontab:
 *   0 * * * * php /ruta/a/Proyecto_final/admin/cron.php
 *
 * También se accede via web: /admin/cron.php?token=xxx
 * (desde CLI ignora el token)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Mailer.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Usuario.php';

$esCLI = (php_sapi_name() === 'cli');

if (!$esCLI) {
    $tokenEsperado = getenv('CRON_TOKEN') ?: 'cambia-este-token';
    if (!isset($_GET['token']) || $_GET['token'] !== $tokenEsperado) {
        http_response_code(403);
        die('Acceso denegado');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

function logMsg($msg) {
    $ts = date('Y-m-d H:i:s');
    echo "[$ts] $msg\n";
}

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    logMsg("ERROR de conexión: " . $e->getMessage());
    exit(1);
}

$mailer = new Mailer();
$reservacionModel = new Reservacion();
$canchaModel = new Cancha();
$usuarioModel = new Usuario();

/* ─── 0. Auto-eliminar cuentas inactivas y solicitudes vencidas ─── */
$eliminadas = $usuarioModel->autoEliminarCuentas();
if ($eliminadas > 0) {
    logMsg("Auto-eliminadas $eliminadas cuenta(s) inactivas o con solicitud vencida.");
    $acciones += $eliminadas;
}

$acciones = 0;

/* ─── 1. Recordatorios 24h antes ─── */
$manana = date('Y-m-d', strtotime('+1 day'));

$stmt = $db->prepare("
    SELECT r.*, u.nombre AS usuario_nombre, u.email, c.nombre AS cancha_nombre
    FROM reservaciones r
    JOIN usuarios u ON r.usuario_id = u.id
    JOIN canchas c ON r.cancha_id = c.id
    WHERE r.fecha = :manana
      AND r.estado = 'confirmada'
      AND r.recordatorio_enviado = 0
");
$stmt->execute([':manana' => $manana]);
$reservaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($reservaciones as $res) {
    $ok = $mailer->recordatorioReservacion(
        $res['usuario_nombre'],
        $res['email'],
        $res['cancha_nombre'],
        $res['fecha'],
        $res['hora_inicio'],
        $res['hora_fin']
    );

    if ($ok) {
        $upd = $db->prepare("UPDATE reservaciones SET recordatorio_enviado = 1 WHERE id = :id");
        $upd->execute([':id' => $res['id']]);
        logMsg("Recordatorio enviado a {$res['email']} (reservación #{$res['id']})");
        $acciones++;
    } else {
        logMsg("ERROR al enviar recordatorio a {$res['email']} (reservación #{$res['id']})");
    }
}

/* ─── 2. Auto-cancelar pagos pendientes vencidos (30 min) ─── */
$limite = date('Y-m-d H:i:s', strtotime('-30 minutes'));

$stmt = $db->prepare("
    SELECT r.id, r.usuario_id, r.cancha_id, r.fecha, r.hora_inicio, r.hora_fin,
           u.nombre AS usuario_nombre, u.email, c.nombre AS cancha_nombre
    FROM reservaciones r
    JOIN usuarios u ON r.usuario_id = u.id
    JOIN canchas c ON r.cancha_id = c.id
    WHERE r.estado = 'pendiente'
      AND r.fecha_reservacion <= :limite
");
$stmt->execute([':limite' => $limite]);
$vencidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($vencidas as $res) {
    $upd = $db->prepare("UPDATE reservaciones SET estado = 'cancelada', observaciones = CONCAT(COALESCE(observaciones,''), ' | Auto-cancelada por vencimiento de pago') WHERE id = :id AND estado = 'pendiente'");
    $upd->execute([':id' => $res['id']]);

    if ($upd->rowCount() > 0) {
        logMsg("Auto-cancelada reservación #{$res['id']} de {$res['usuario_nombre']}");

        $mailer->cancelacionReservacion(
            $res['usuario_nombre'],
            $res['email'],
            $res['cancha_nombre'],
            $res['fecha'],
            $res['hora_inicio'],
            $res['hora_fin']
        );
        $acciones++;
    }
}

if ($acciones === 0) {
    logMsg("Sin acciones pendientes.");
}

logMsg("CRON finalizado.");
