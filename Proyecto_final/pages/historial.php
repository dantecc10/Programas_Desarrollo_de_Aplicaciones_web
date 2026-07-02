<?php
$titulo = 'Historial';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Resena.php';
$reservacionModel = new Reservacion();
$resenaModel = new Resena();
$historial = $reservacionModel->historialPorUsuario($_SESSION['usuario_id']);

foreach ($historial as &$h) {
    $h['resena'] = $resenaModel->obtenerPorReservacion($h['id']);
}
unset($h);

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
                <th>Calificación</th>
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
                <td>
                    <?php if ($h['resena']): ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= $h['resena']['puntuacion'] ? '-fill text-warning' : ''; ?>"></i>
                        <?php endfor; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($h['estado'] === 'pendiente'): ?>
                        <a href="pago.php?reservacion_id=<?php echo $h['id']; ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-credit-card"></i> Pagar
                        </a>
                    <?php elseif ($h['estado'] === 'completada' && !$h['resena']): ?>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalCalificar<?php echo $h['id']; ?>">
                            <i class="bi bi-star"></i> Calificar
                        </button>
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

<?php foreach ($historial as $h): ?>
<?php if ($h['estado'] === 'completada' && !$h['resena']): ?>
<div class="modal fade" id="modalCalificar<?php echo $h['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" onsubmit="return calificar(this, <?php echo $h['id']; ?>)">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Calificar: <?php echo htmlspecialchars($h['cancha_nombre']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted"><?php echo date('d/m/Y', strtotime($h['fecha'])); ?> - <?php echo substr($h['hora_inicio'],0,5); ?> a <?php echo substr($h['hora_fin'],0,5); ?></p>
                <div class="mb-3">
                    <label class="form-label">Puntuación</label>
                    <div class="rating-stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star fs-3 text-warning" style="cursor:pointer" data-value="<?php echo $i; ?>" onclick="seleccionarStar(this, <?php echo $i; ?>)"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="puntuacion" id="puntuacion_<?php echo $h['id']; ?>" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Comentario (opcional)</label>
                    <textarea name="comentario" class="form-control" rows="3" maxlength="500" placeholder="Comparte tu experiencia..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning">Enviar Calificación</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<script>
function seleccionarStar(el, value) {
    const container = el.parentElement;
    container.querySelectorAll('i').forEach(function(star, idx) {
        star.className = idx < value ? 'bi bi-star-fill fs-3 text-warning' : 'bi bi-star fs-3 text-warning';
    });
    container.parentElement.querySelector('input[name="puntuacion"]').value = value;
}

function calificar(form, id) {
    event.preventDefault();
    const formData = new FormData(form);
    formData.append('accion', 'crear');
    formData.append('reservacion_id', id);

    fetch('../api/resena.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.exito) {
                location.reload();
            } else {
                alert(data.mensaje);
            }
        })
        .catch(() => alert('Error de conexión.'));
    return false;
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
