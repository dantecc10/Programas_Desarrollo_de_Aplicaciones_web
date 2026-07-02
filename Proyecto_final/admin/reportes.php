<?php
$titulo = 'Reportes';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Pago.php';

$reservacionModel = new Reservacion();
$canchaModel = new Cancha();
$pagoModel = new Pago();

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
$canchaFiltro = $_GET['cancha_id'] ?? '';

$reporteIngresos = $reservacionModel->reporteIngresos($fechaInicio, $fechaFin);
$totalReservaciones = array_sum(array_column($reporteIngresos, 'total_reservaciones'));
$totalIngresos = array_sum(array_column($reporteIngresos, 'ingreso_total'));

if ($canchaFiltro) {
    $reservacionesCancha = $reservacionModel->obtenerReservacionesPorCancha($canchaFiltro, $fechaInicio, $fechaFin);
} else {
    $reservacionesCancha = $reservacionModel->obtenerReservacionesCreadasEntre($fechaInicio, $fechaFin);
}

$canchas = $canchaModel->obtenerTodas();
$ingresoTotalGeneral = $pagoModel->ingresoTotal();
$metodoPago = $pagoModel->metodoPagoMasUsado();

$ingresosPorDia = [];
foreach ($reporteIngresos as $r) {
    $dia = $r['dia'];
    if (!isset($ingresosPorDia[$dia])) {
        $ingresosPorDia[$dia] = ['reservaciones' => 0, 'ingresos' => 0];
    }
    $ingresosPorDia[$dia]['reservaciones'] += (int)$r['total_reservaciones'];
    $ingresosPorDia[$dia]['ingresos'] += (float)$r['ingreso_total'];
}
ksort($ingresosPorDia);

$chartLabels = json_encode(array_keys($ingresosPorDia));
$chartReservas = json_encode(array_column($ingresosPorDia, 'reservaciones'));
$chartIngresos = json_encode(array_column($ingresosPorDia, 'ingresos'));

require_once __DIR__ . '/../includes/header.php';
?>
<h3><i class="bi bi-file-earmark-bar-graph"></i> Reportes de Reservaciones</h3>
<hr>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fechaInicio; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fechaFin; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cancha</label>
                <select name="cancha_id" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($canchas as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $canchaFiltro == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary">
            <div class="card-body text-center">
                <h4><?php echo $totalReservaciones; ?></h4>
                <p class="mb-0">Reservaciones en el período</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success">
            <div class="card-body text-center">
                <h4>$<?php echo number_format($totalIngresos, 2); ?></h4>
                <p class="mb-0">Ingresos en el período</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-info">
            <div class="card-body text-center">
                <h4>$<?php echo number_format($ingresoTotalGeneral, 2); ?></h4>
                <p class="mb-0">Ingresos Totales (Histórico)</p>
                <?php if ($metodoPago): ?>
                <small>Método más usado: <?php echo ucfirst($metodoPago['metodo_pago']); ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h6><i class="bi bi-bar-chart"></i> Ingresos por Día</h6></div>
            <div class="card-body">
                <canvas id="chartIngresos"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h6><i class="bi bi-bar-chart"></i> Reservaciones por Día</h6></div>
            <div class="card-body">
                <canvas id="chartReservas"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5><i class="bi bi-table"></i> Reporte de Ingresos por Día y Tipo</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-dark">
                    <tr><th>Fecha</th><th>Tipo Cancha</th><th>Reservaciones</th><th>Ingreso</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($reporteIngresos)): ?>
                    <tr><td colspan="4" class="text-center text-muted">Sin datos en el período seleccionado</td></tr>
                    <?php endif; ?>
                    <?php foreach ($reporteIngresos as $r): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($r['dia'])); ?></td>
                        <td><span class="badge bg-info"><?php echo $r['tipo']; ?></span></td>
                        <td><?php echo $r['total_reservaciones']; ?></td>
                        <td class="fw-bold">$<?php echo number_format($r['ingreso_total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($reporteIngresos)): ?>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="2">Totales</th>
                        <th><?php echo $totalReservaciones; ?></th>
                        <th>$<?php echo number_format($totalIngresos, 2); ?></th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h5><i class="bi bi-list-ul"></i> Detalle de Reservaciones</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr><th>#</th><th>Usuario</th><th>Cancha</th><th>Fecha</th><th>Horario</th><th>Total</th><th>Estado</th><th>Observaciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reservacionesCancha as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['usuario_nombre'] ?? $r['nombre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($r['cancha_nombre']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
                        <td><?php echo substr($r['hora_inicio'],0,5); ?> - <?php echo substr($r['hora_fin'],0,5); ?></td>
                        <td class="fw-bold">$<?php echo number_format($r['total'],2); ?></td>
                        <td>
                            <?php $mapa = ['pendiente'=>'warning','confirmada'=>'success','cancelada'=>'danger','completada'=>'secondary']; ?>
                            <span class="badge bg-<?php echo $mapa[$r['estado']] ?? 'secondary'; ?>"><?php echo ucfirst($r['estado']); ?></span>
                        </td>
                        <td><small><?php echo htmlspecialchars($r['observaciones'] ?? '-'); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservacionesCancha)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Sin reservaciones en el período</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function isDark() {
        return document.documentElement.classList.contains('dark-mode');
    }
    var textColor = isDark() ? '#e0e0e0' : '#333';
    var gridColor = isDark() ? '#2d3039' : '#dee2e6';

    var labels = <?php echo $chartLabels; ?>;
    var dias = labels.map(function(d) {
        var parts = d.split('-');
        return parts[2] + '/' + parts[1];
    });

    new Chart(document.getElementById('chartIngresos'), {
        type: 'bar',
        data: {
            labels: dias,
            datasets: [{
                label: 'Ingresos ($)',
                data: <?php echo $chartIngresos; ?>,
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderColor: 'rgba(25,135,84,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: textColor } } },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, callback: function(v) { return '$' + v; } }, grid: { color: gridColor } }
            }
        }
    });

    new Chart(document.getElementById('chartReservas'), {
        type: 'bar',
        data: {
            labels: dias,
            datasets: [{
                label: 'Reservaciones',
                data: <?php echo $chartReservas; ?>,
                backgroundColor: 'rgba(13,110,253,0.7)',
                borderColor: 'rgba(13,110,253,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: textColor } } },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
            }
        }
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
