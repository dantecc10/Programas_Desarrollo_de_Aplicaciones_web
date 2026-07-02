<?php
$titulo = 'Gestionar Precios';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Precio.php';
require_once __DIR__ . '/../classes/Historial.php';
$canchaModel = new Cancha();
$precioModel = new Precio();
$historial = new Historial();
$dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $precioModel->crear($_POST);
        $historial->registrar($_SESSION['usuario_id'], 'Precio creado', "Precio creado para cancha #{$_POST['cancha_id']}");
        $_SESSION['mensaje'] = 'Precio agregado.'; $_SESSION['tipo_mensaje'] = 'success';
    } elseif ($accion === 'editar') {
        $precioModel->actualizar($_POST['id'], $_POST);
        $historial->registrar($_SESSION['usuario_id'], 'Precio actualizado', "Precio #{$_POST['id']} actualizado");
        $_SESSION['mensaje'] = 'Precio actualizado.'; $_SESSION['tipo_mensaje'] = 'success';
    } elseif ($accion === 'eliminar') {
        $precioModel->eliminar($_POST['id']);
        $_SESSION['mensaje'] = 'Precio eliminado.'; $_SESSION['tipo_mensaje'] = 'warning';
    }
    header('Location: precios.php' . (isset($_POST['cancha_id']) ? '?cancha_id=' . $_POST['cancha_id'] : ''));
    exit;
}

$canchaSeleccionada = $_GET['cancha_id'] ?? null;
$canchas = $canchaModel->obtenerTodas();
$precios = $canchaSeleccionada ? $precioModel->obtenerPorCanchaAdmin($canchaSeleccionada) : [];
require_once __DIR__ . '/../includes/header.php';
?>
<h3><i class="bi bi-tags"></i> Precios Diferenciados</h3>
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
                            <?php echo htmlspecialchars($c['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php if ($canchaSeleccionada): ?>
                <hr>
                <h6>Agregar Precio</h6>
                <form method="POST">
                    <input type="hidden" name="accion" value="crear">
                    <input type="hidden" name="cancha_id" value="<?php echo $canchaSeleccionada; ?>">
                    <div class="mb-2">
                        <label class="small">Tipo</label>
                        <select name="tipo_precio" class="form-select form-select-sm" required>
                            <option value="regular">Hora Regular</option>
                            <option value="pico">Hora Pico</option>
                            <option value="finde">Fin de Semana</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="small">Nombre</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="small">Precio ($)</label>
                        <input type="number" name="precio" class="form-control form-control-sm" step="0.01" min="0" required>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="small">Día inicio</label>
                            <select name="dia_semana_inicio" class="form-select form-select-sm">
                                <option value="">--</option>
                                <?php for ($i=1; $i<=7; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $dias[$i]; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small">Día fin</label>
                            <select name="dia_semana_fin" class="form-select form-select-sm">
                                <option value="">--</option>
                                <?php for ($i=1; $i<=7; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $dias[$i]; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="small">Hora inicio</label>
                            <input type="time" name="hora_inicio" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="small">Hora fin</label>
                            <input type="time" name="hora_fin" class="form-control form-control-sm">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-2"><i class="bi bi-plus"></i> Agregar Precio</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <?php if ($canchaSeleccionada && !empty($precios)): ?>
        <div class="card shadow-sm">
            <div class="card-header"><h5>Precios de <?php echo htmlspecialchars($canchaModel->obtenerPorId($canchaSeleccionada)['nombre']); ?></h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr><th>Tipo</th><th>Nombre</th><th>Precio</th><th>Días</th><th>Horario</th><th>Estado</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($precios as $p): ?>
                            <tr class="<?php echo !$p['activo'] ? 'table-secondary text-muted' : ''; ?>">
                                <td><span class="badge bg-<?php echo $p['tipo_precio'] === 'regular' ? 'success' : ($p['tipo_precio'] === 'pico' ? 'warning' : 'info'); ?>"><?php echo ucfirst($p['tipo_precio']); ?></span></td>
                                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td class="fw-bold">$<?php echo number_format($p['precio'], 2); ?></td>
                                <td><?php echo $p['dia_semana_inicio'] ? ($dias[$p['dia_semana_inicio']] . ' - ' . $dias[$p['dia_semana_fin']]) : 'Todos'; ?></td>
                                <td><?php echo $p['hora_inicio'] ? (substr($p['hora_inicio'],0,5) . ' - ' . substr($p['hora_fin'],0,5)) : 'Todo el día'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $p['activo'] ? 'success' : 'danger'; ?>">
                                        <?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $p['id']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este precio?')">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <input type="hidden" name="cancha_id" value="<?php echo $canchaSeleccionada; ?>">
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>

                                    <div class="modal fade" id="editarModal<?php echo $p['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Precio: <?php echo htmlspecialchars($p['nombre']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="accion" value="editar">
                                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                        <input type="hidden" name="cancha_id" value="<?php echo $canchaSeleccionada; ?>">
                                                        <div class="mb-2">
                                                            <label class="small">Tipo</label>
                                                            <select name="tipo_precio" class="form-select form-select-sm" required>
                                                                <option value="regular" <?php echo $p['tipo_precio'] === 'regular' ? 'selected' : ''; ?>>Hora Regular</option>
                                                                <option value="pico" <?php echo $p['tipo_precio'] === 'pico' ? 'selected' : ''; ?>>Hora Pico</option>
                                                                <option value="finde" <?php echo $p['tipo_precio'] === 'finde' ? 'selected' : ''; ?>>Fin de Semana</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="small">Nombre</label>
                                                            <input type="text" name="nombre" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['nombre']); ?>" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="small">Precio ($)</label>
                                                            <input type="number" name="precio" class="form-control form-control-sm" step="0.01" min="0" value="<?php echo $p['precio']; ?>" required>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <label class="small">Día inicio</label>
                                                                <select name="dia_semana_inicio" class="form-select form-select-sm">
                                                                    <option value="">--</option>
                                                                    <?php for ($i=1; $i<=7; $i++): ?>
                                                                    <option value="<?php echo $i; ?>" <?php echo $p['dia_semana_inicio'] == $i ? 'selected' : ''; ?>><?php echo $dias[$i]; ?></option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small">Día fin</label>
                                                                <select name="dia_semana_fin" class="form-select form-select-sm">
                                                                    <option value="">--</option>
                                                                    <?php for ($i=1; $i<=7; $i++): ?>
                                                                    <option value="<?php echo $i; ?>" <?php echo $p['dia_semana_fin'] == $i ? 'selected' : ''; ?>><?php echo $dias[$i]; ?></option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <label class="small">Hora inicio</label>
                                                                <input type="time" name="hora_inicio" class="form-control form-control-sm" value="<?php echo $p['hora_inicio'] ? substr($p['hora_inicio'], 0, 5) : ''; ?>">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small">Hora fin</label>
                                                                <input type="time" name="hora_fin" class="form-control form-control-sm" value="<?php echo $p['hora_fin'] ? substr($p['hora_fin'], 0, 5) : ''; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input type="hidden" name="activo" value="0">
                                                            <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo_<?php echo $p['id']; ?>" <?php echo $p['activo'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="activo_<?php echo $p['id']; ?>">Activo (habilitado)</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif ($canchaSeleccionada): ?>
        <div class="alert alert-info">No hay precios configurados. Use el formulario para agregar precios diferenciados.</div>
        <?php else: ?>
        <div class="alert alert-info">Seleccione una cancha para gestionar sus precios.</div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
