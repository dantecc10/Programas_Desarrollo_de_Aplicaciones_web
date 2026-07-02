<?php
$titulo = 'Reseñas';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Resena.php';
$resenaModel = new Resena();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $resenaModel->eliminar($_POST['id']);
    $_SESSION['mensaje'] = 'Reseña eliminada.'; $_SESSION['tipo_mensaje'] = 'warning';
    header('Location: resenas.php'); exit;
}

$resenas = $resenaModel->obtenerTodas();
require_once __DIR__ . '/../includes/header.php';
?>
<h3><i class="bi bi-star"></i> Reseñas de Usuarios</h3>
<small class="text-muted">Total: <?php echo count($resenas); ?> reseña(s)</small>
<hr>
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr><th>#</th><th>Usuario</th><th>Cancha</th><th>Puntuación</th><th>Comentario</th><th>Fecha</th><th>Acción</th></tr>
        </thead>
        <tbody>
            <?php foreach ($resenas as $r): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['usuario_nombre']); ?></td>
                <td><?php echo htmlspecialchars($r['cancha_nombre']); ?></td>
                <td>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?php echo $i <= $r['puntuacion'] ? '-fill text-warning' : ''; ?>"></i>
                    <?php endfor; ?>
                </td>
                <td><?php echo htmlspecialchars($r['comentario'] ?: '-'); ?></td>
                <td><small><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></small></td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar esta reseña?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($resenas)): ?>
            <tr><td colspan="7" class="text-center text-muted">No hay reseñas</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
