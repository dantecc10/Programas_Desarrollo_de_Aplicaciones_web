<?php
$titulo = 'Historial';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Reservacion.php';
$reservacionModel = new Reservacion();
$historial = $reservacionModel->historialPorUsuario($_SESSION['usuario_id']);
require_once __DIR__ . '/../includes/header.php';
?>
<h2><i class="bi bi-clock-history"></i> Historial de Reservaciones</h2>
<hr>

<?php if (empty($historial)): ?>
<div class="alert alert-info">No hay registros en tu historial.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Cancha</th>
                <th>Tipo</th>
                <th>Uso</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Pago</th>
                <th>Fecha Reservación</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historial as $h): ?>
            <tr>
                <td><?php echo $h['id']; ?></td>
                <td><?php echo htmlspecialchars($h['cancha_nombre']); ?></td>
                <td><span class="badge bg-info"><?php echo htmlspecialchars($h['cancha_tipo']); ?></span></td>
                <td><?php echo $h['tipo_uso'] ? '<span class="badge bg-secondary">' . htmlspecialchars($h['tipo_uso']) . '</span>' : '-'; ?></td>
                <td><?php echo date('d/m/Y', strtotime($h['fecha'])); ?></td>
                <td><?php echo substr($h['hora_inicio'], 0, 5); ?> - <?php echo substr($h['hora_fin'], 0, 5); ?></td>
                <td class="fw-bold">$<?php echo number_format($h['total'], 2); ?></td>
                <td>
                    <?php
                    $mapa = ['pendiente'=>'warning','confirmada'=>'success','cancelada'=>'danger','completada'=>'secondary'];
                    $clase = $mapa[$h['estado']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $clase; ?>"><?php echo ucfirst($h['estado']); ?></span>
                </td>
                <td>
                    <?php if ($h['pago_monto']): ?>
                        <span class="badge bg-success">Pagado</span>
                        <small class="d-block text-muted"><?php echo $h['metodo_pago']; ?></small>
                    <?php else: ?>
                        <span class="badge bg-warning">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($h['fecha_reservacion'])); ?></td>
                <td>
                    <?php if ($h['estado'] === 'pendiente'): ?>
                        <a href="pago.php?reservacion_id=<?php echo $h['id']; ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-credit-card"></i> Pagar
                        </a>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
