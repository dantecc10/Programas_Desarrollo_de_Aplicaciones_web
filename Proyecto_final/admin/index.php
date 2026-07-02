<?php
$titulo = 'Panel de Administración';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) {
    $_SESSION['mensaje'] = 'Acceso denegado. Se requieren permisos de administrador.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Pago.php';

$usuarioModel = new Usuario();
$canchaModel = new Cancha();
$reservacionModel = new Reservacion();
$pagoModel = new Pago();

$totalUsuarios = $usuarioModel->contarClientes();
$totalCanchas = $canchaModel->contar();
$canchasDisponibles = $canchaModel->contarDisponibles();
$totalReservaciones = $reservacionModel->contarPorEstado();
$pendientes = $reservacionModel->contarPorEstado('pendiente');
$confirmadas = $reservacionModel->contarPorEstado('confirmada');
$ingresosMes = $pagoModel->ingresoTotal(date('Y-m-01'), date('Y-m-t 23:59:59'));
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body text-center">
                <h3><?php echo $totalCanchas; ?></h3>
                <p class="mb-0">Total Canchas</p>
                <small><?php echo $canchasDisponibles; ?> disponibles</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body text-center">
                <h3><?php echo $totalReservaciones; ?></h3>
                <p class="mb-0">Reservaciones</p>
                <small><?php echo $confirmadas; ?> confirmadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body text-center">
                <h3><?php echo $pendientes; ?></h3>
                <p class="mb-0">Pendientes de Pago</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body text-center">
                <h3>$<?php echo number_format($ingresosMes, 2); ?></h3>
                <p class="mb-0">Ingresos del Mes</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="canchas.php" class="btn btn-outline-primary"><i class="bi bi-plus-circle"></i> Gestionar Canchas</a>
                    <a href="precios.php" class="btn btn-outline-info"><i class="bi bi-tags"></i> Precios</a>
                    <a href="horarios.php" class="btn btn-outline-success"><i class="bi bi-clock"></i> Gestionar Horarios</a>
                    <a href="reservaciones.php" class="btn btn-outline-warning"><i class="bi bi-list-check"></i> Ver Reservaciones</a>
                    <a href="usuarios.php" class="btn btn-outline-info"><i class="bi bi-people"></i> Gestionar Usuarios</a>
                    <a href="reportes.php" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-bar-graph"></i> Ver Reportes</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Últimas Actividades</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php
                    require_once __DIR__ . '/../classes/Historial.php';
                    $historialModel = new Historial();
                    $actividades = $historialModel->obtenerTodos(5);
                    foreach ($actividades as $act):
                    ?>
                    <div class="list-group-item border-0 ps-0">
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($act['fecha'])); ?></small><br>
                        <strong><?php echo htmlspecialchars($act['usuario_nombre'] ?? 'Sistema'); ?></strong> - <?php echo htmlspecialchars($act['accion']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
