<?php
$titulo = 'Gestionar Horarios';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Horario.php';
require_once __DIR__ . '/../classes/Historial.php';
$canchaModel = new Cancha();
$horarioModel = new Horario();
$historial = new Historial();
$dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'generar') {
        $canchaId = $_POST['cancha_id'];
        $horarioModel->eliminarPorCancha($_POST['cancha_id']);
        $canchaModel->obtenerPorId($canchaId);
        for ($dia = 1; $dia <= 7; $dia++) {
            $horaInicio = $_POST["hora_inicio_$dia"] ?? '';
            $horaFin = $_POST["hora_fin_$dia"] ?? '';
            if ($horaInicio && $horaFin) {
                $bloques = $horarioModel->generarHorarios($canchaId, $dia, $horaInicio, $horaFin, 60);
                foreach ($bloques as $b) {
                    $horarioModel->crear($b);
                }
            }
        }
        $historial->registrar($_SESSION['usuario_id'], 'Horarios generados', "Horarios generados para cancha #$canchaId");
        $_SESSION['mensaje'] = 'Horarios generados exitosamente.'; $_SESSION['tipo_mensaje'] = 'success';
    }
    header('Location: horarios.php'); exit;
}

$canchaSeleccionada = $_GET['cancha_id'] ?? null;
$canchas = $canchaModel->obtenerTodas();
$horarios = $canchaSeleccionada ? $horarioModel->obtenerPorCancha($canchaSeleccionada) : [];
$horariosPorDia = [];
foreach ($horarios as $h) {
    $horariosPorDia[$h['dia_semana']][] = $h;
}
require_once __DIR__ . '/../includes/header.php';
?>
<h3><i class="bi bi-clock"></i> Gestionar Horarios</h3>
<hr>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5>Seleccionar Cancha</h5></div>
            <div class="card-body">
                <form method="GET">
                    <select name="cancha_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Seleccione una cancha</option>
                        <?php foreach ($canchas as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $canchaSeleccionada == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nombre']); ?> - <?php echo $c['tipo']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if ($canchaSeleccionada): ?>
                <hr>
                <h6>Generar Horarios</h6>
                <form method="POST">
                    <input type="hidden" name="accion" value="generar">
                    <input type="hidden" name="cancha_id" value="<?php echo $canchaSeleccionada; ?>">
                    <?php foreach ($dias as $num => $dia): if ($num === 0) continue; ?>
                    <div class="mb-2">
                        <label class="small"><?php echo $dia; ?></label>
                        <div class="row g-1">
                            <div class="col-5">
                                <input type="time" name="hora_inicio_<?php echo $num; ?>" class="form-control form-control-sm" placeholder="Inicio" value="08:00">
                            </div>
                            <div class="col-1 text-center pt-1">-</div>
                            <div class="col-5">
                                <input type="time" name="hora_fin_<?php echo $num; ?>" class="form-control form-control-sm" placeholder="Fin" value="22:00">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-success w-100 mt-2" onclick="return confirm('¿Generar horarios? Esto reemplazará los existentes.')">
                        <i class="bi bi-gear"></i> Generar Horarios
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <?php if ($canchaSeleccionada && !empty($horariosPorDia)): ?>
        <div class="card shadow-sm">
            <div class="card-header"><h5>Horarios Actuales</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr><th>Día</th><th>Horario</th><th>Estado</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                            <?php for ($d = 1; $d <= 7; $d++): ?>
                            <?php $hDia = $horariosPorDia[$d] ?? []; ?>
                            <?php foreach ($hDia as $h): ?>
                            <tr>
                                <td><?php echo $dias[$d]; ?></td>
                                <td><?php echo substr($h['hora_inicio'],0,5); ?> - <?php echo substr($h['hora_fin'],0,5); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $h['estado'] === 'disponible' ? 'success' : 'danger'; ?>">
                                        <?php echo $h['estado'] === 'disponible' ? 'Disponible' : 'No Disponible'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-toggle btn-<?php echo $h['estado'] === 'disponible' ? 'warning' : 'success'; ?>" data-id="<?php echo $h['id']; ?>" data-estado="<?php echo $h['estado'] === 'disponible' ? 'no_disponible' : 'disponible'; ?>">
                                        <i class="bi bi-<?php echo $h['estado'] === 'disponible' ? 'x-circle' : 'check-circle'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif ($canchaSeleccionada): ?>
        <div class="alert alert-info">No hay horarios configurados. Use el formulario "Generar Horarios" para crearlos.</div>
        <?php else: ?>
        <div class="alert alert-info">Seleccione una cancha para ver sus horarios.</div>
        <?php endif; ?>
    </div>
</div>
<script>
document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const estado = this.dataset.estado;
        fetch('../api/toggle_horario.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&estado=' + estado
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                const tr = this.closest('tr');
                const badge = tr.querySelector('.badge');
                const isDisponible = data.estado === 'disponible';
                badge.className = 'badge bg-' + (isDisponible ? 'success' : 'danger');
                badge.textContent = isDisponible ? 'Disponible' : 'No Disponible';
                this.className = 'btn btn-sm btn-toggle btn-' + (isDisponible ? 'warning' : 'success');
                this.querySelector('i').className = 'bi bi-' + (isDisponible ? 'x-circle' : 'check-circle');
                this.dataset.estado = isDisponible ? 'no_disponible' : 'disponible';
            }
        });
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
