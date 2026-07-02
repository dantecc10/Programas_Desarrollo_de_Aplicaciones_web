<?php
$titulo = 'Reservaciones';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Historial.php';
$reservacionModel = new Reservacion();
$canchaModel = new Cancha();
$historial = new Historial();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'estado') {
        $reservacionModel->actualizarEstado($_POST['id'], $_POST['estado']);
        $historial->registrar($_SESSION['usuario_id'], 'Reservación actualizada', "Reservación #{$_POST['id']} -> {$_POST['estado']}");
        $_SESSION['mensaje'] = 'Estado de reservación actualizado.'; $_SESSION['tipo_mensaje'] = 'success';
    } elseif ($accion === 'eliminar') {
        $reservacionModel->eliminar($_POST['id']);
        $historial->registrar($_SESSION['usuario_id'], 'Reservación eliminada', "Reservación #{$_POST['id']} eliminada");
        $_SESSION['mensaje'] = 'Reservación eliminada.'; $_SESSION['tipo_mensaje'] = 'warning';
    }
    $params = $_GET;
    unset($params['msg']);
    header('Location: reservaciones.php?' . http_build_query($params)); exit;
}

$pagina = max(1, (int)($_GET['pag'] ?? 1));
$porPagina = 20;

$filtros = [];
foreach (['estado', 'cancha_id', 'busqueda', 'fecha_desde', 'fecha_hasta'] as $k) {
    if (!empty($_GET[$k])) $filtros[$k] = $_GET[$k];
}

$resultado = $reservacionModel->obtenerPaginas($pagina, $porPagina, $filtros);
$reservaciones = $resultado['datos'];
$totalPaginas = $resultado['totalPaginas'];
$paginaActual = $resultado['pagina'];

$canchas = $canchaModel->obtenerTodas();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3><i class="bi bi-list-check"></i> Todas las Reservaciones</h3>
    <small class="text-muted"><?php echo $resultado['total']; ?> registro(s)</small>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="estado" class="form-select">
            <option value="">Todos los estados</option>
            <?php foreach (['pendiente','confirmada','completada','cancelada'] as $e): ?>
            <option value="<?php echo $e; ?>" <?php echo ($_GET['estado']??'') === $e ? 'selected' : ''; ?>><?php echo ucfirst($e); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="cancha_id" class="form-select">
            <option value="">Todas las canchas</option>
            <?php foreach ($canchas as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo ($_GET['cancha_id']??'') == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <input type="date" name="fecha_desde" class="form-control" value="<?php echo $_GET['fecha_desde'] ?? ''; ?>" placeholder="Desde">
    </div>
    <div class="col-auto">
        <input type="date" name="fecha_hasta" class="form-control" value="<?php echo $_GET['fecha_hasta'] ?? ''; ?>" placeholder="Hasta">
    </div>
    <div class="col-auto">
        <input type="text" name="busqueda" class="form-control" placeholder="Buscar..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
        <a href="reservaciones.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Limpiar</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr><th>#</th><th>Usuario</th><th>Cancha</th><th>Uso</th><th>Fecha</th><th>Horario</th><th>Total</th><th>Estado</th><th>Reservado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($reservaciones as $r): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['usuario_nombre']); ?><br><small class="text-muted"><?php echo $r['email']; ?></small></td>
                <td><?php echo htmlspecialchars($r['cancha_nombre']); ?></td>
                <td><?php echo $r['tipo_uso'] ? '<span class="badge bg-secondary">' . htmlspecialchars($r['tipo_uso']) . '</span>' : '-'; ?></td>
                <td><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
                <td><?php echo substr($r['hora_inicio'],0,5); ?> - <?php echo substr($r['hora_fin'],0,5); ?></td>
                <td class="fw-bold">$<?php echo number_format($r['total'],2); ?></td>
                <td>
                    <?php $mapa = ['pendiente'=>'warning','confirmada'=>'success','cancelada'=>'danger','completada'=>'secondary']; ?>
                    <span class="badge bg-<?php echo $mapa[$r['estado']] ?? 'secondary'; ?>"><?php echo ucfirst($r['estado']); ?></span>
                </td>
                <td><small><?php echo date('d/m/Y H:i', strtotime($r['fecha_reservacion'])); ?></small></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                        <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Cambiar a...</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="completada">Completada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar reservación #<?php echo $r['id']; ?>?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                        <button class="btn btn-sm btn-danger mt-1"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($reservaciones)): ?>
            <tr><td colspan="10" class="text-center text-muted">No hay reservaciones</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $paginaActual <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>1])); ?>"><i class="bi bi-chevron-double-left"></i></a>
        </li>
        <li class="page-item <?php echo $paginaActual <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$paginaActual-1])); ?>"><i class="bi bi-chevron-left"></i></a>
        </li>
        <?php for ($i = max(1, $paginaActual-2); $i <= min($totalPaginas, $paginaActual+2); $i++): ?>
        <li class="page-item <?php echo $i === $paginaActual ? 'active' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$i])); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $paginaActual >= $totalPaginas ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$paginaActual+1])); ?>"><i class="bi bi-chevron-right"></i></a>
        </li>
        <li class="page-item <?php echo $paginaActual >= $totalPaginas ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$totalPaginas])); ?>"><i class="bi bi-chevron-double-right"></i></a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
