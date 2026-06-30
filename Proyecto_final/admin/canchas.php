<?php
$titulo = 'Gestionar Canchas';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Horario.php';
require_once __DIR__ . '/../classes/Precio.php';
require_once __DIR__ . '/../classes/Historial.php';
$canchaModel = new Cancha();
$horarioModel = new Horario();
$precioModel = new Precio();
$historial = new Historial();

function subirImagen($archivo, $id) {
    $targetDir = CANCHAS_IMG_DIR;
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $permitidas)) return null;
    $nombreArchivo = 'cancha_' . $id . '.' . $extension;
    $rutaCompleta = $targetDir . $nombreArchivo;
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return $nombreArchivo;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $id = $canchaModel->crear($_POST);
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $img = subirImagen($_FILES['imagen'], $id);
            if ($img) $canchaModel->actualizar($id, ['imagen' => $img]);
        }
        for ($dia = 1; $dia <= 7; $dia++) {
            $slots = $horarioModel->generarHorarios($id, $dia, '08:00', '22:00', 60);
            foreach ($slots as $slot) {
                $horarioModel->crear($slot);
            }
        }
        $precioModel->crear(['cancha_id' => $id, 'tipo_precio' => 'regular', 'nombre' => 'Hora Regular', 'precio' => $_POST['precio_por_hora'], 'dia_semana_inicio' => 1, 'dia_semana_fin' => 5, 'hora_inicio' => '08:00', 'hora_fin' => '18:00']);
        $precioModel->crear(['cancha_id' => $id, 'tipo_precio' => 'pico', 'nombre' => 'Hora Pico', 'precio' => $_POST['precio_por_hora'] * 1.3, 'dia_semana_inicio' => 1, 'dia_semana_fin' => 5, 'hora_inicio' => '18:00', 'hora_fin' => '22:00']);
        $precioModel->crear(['cancha_id' => $id, 'tipo_precio' => 'finde', 'nombre' => 'Fin de Semana', 'precio' => $_POST['precio_por_hora'] * 1.4, 'dia_semana_inicio' => 6, 'dia_semana_fin' => 7, 'hora_inicio' => '08:00', 'hora_fin' => '22:00']);
        $historial->registrar($_SESSION['usuario_id'], 'Cancha creada', "Cancha #$id creada");
        $_SESSION['mensaje'] = 'Cancha creada exitosamente.'; $_SESSION['tipo_mensaje'] = 'success';
    } elseif ($accion === 'editar') {
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $canchaActual = $canchaModel->obtenerPorId($_POST['id']);
            if ($canchaActual && $canchaActual['imagen']) {
                $rutaVieja = CANCHAS_IMG_DIR . $canchaActual['imagen'];
                if (file_exists($rutaVieja)) unlink($rutaVieja);
            }
            $img = subirImagen($_FILES['imagen'], $_POST['id']);
            if ($img) $_POST['imagen'] = $img;
        }
        $canchaModel->actualizar($_POST['id'], $_POST);
        $historial->registrar($_SESSION['usuario_id'], 'Cancha actualizada', "Cancha #{$_POST['id']} actualizada");
        $_SESSION['mensaje'] = 'Cancha actualizada.'; $_SESSION['tipo_mensaje'] = 'success';
    } elseif ($accion === 'eliminar') {
        $cancha = $canchaModel->obtenerPorId($_POST['id']);
        if ($cancha && $cancha['imagen']) {
            $ruta = CANCHAS_IMG_DIR . $cancha['imagen'];
            if (file_exists($ruta)) unlink($ruta);
        }
        $canchaModel->eliminar($_POST['id']);
        $historial->registrar($_SESSION['usuario_id'], 'Cancha eliminada', "Cancha #{$_POST['id']} eliminada");
        $_SESSION['mensaje'] = 'Cancha eliminada.'; $_SESSION['tipo_mensaje'] = 'warning';
    }
    header('Location: canchas.php'); exit;
}

$canchas = $canchaModel->obtenerTodas();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-building"></i> Gestionar Canchas</h3>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCancha">
        <i class="bi bi-plus-circle"></i> Nueva Cancha
    </button>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr><th>ID</th><th>Imagen</th><th>Nombre</th><th>Tipo</th><th>Precio/h</th><th>Capacidad</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($canchas as $c): ?>
            <?php $imgCancha = $canchaModel->resolverImagen($c); ?>
            <tr>
                <td><?php echo $c['id']; ?></td>
                <td>
                    <?php if ($imgCancha): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/img/canchas/<?php echo $imgCancha; ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                    <?php else: ?>
                        <span class="text-muted"><i class="bi bi-image fs-3"></i></span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                <td><span class="badge bg-info"><?php echo $c['tipo']; ?></span></td>
                <td>$<?php echo number_format($c['precio_por_hora'], 2); ?></td>
                <td><?php echo $c['capacidad']; ?></td>
                <td>
                    <?php $mapa = ['disponible'=>'success','mantenimiento'=>'warning','inactivo'=>'danger']; $clase = $mapa[$c['estado']] ?? 'secondary'; ?>
                    <span class="badge bg-<?php echo $clase; ?>"><?php echo ucfirst($c['estado']); ?></span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary editar-cancha"
                        data-id="<?php echo $c['id']; ?>"
                        data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>"
                        data-tipo="<?php echo $c['tipo']; ?>"
                        data-descripcion="<?php echo htmlspecialchars($c['descripcion']); ?>"
                        data-precio="<?php echo $c['precio_por_hora']; ?>"
                        data-capacidad="<?php echo $c['capacidad']; ?>"
                        data-estado="<?php echo $c['estado']; ?>"
                        data-imagen="<?php echo $c['imagen']; ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar esta cancha?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    <a href="precios.php?cancha_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-tags"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalCancha" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-building"></i> <span id="modalTitle">Nueva Cancha</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="accion" id="formAccion" value="crear">
                <input type="hidden" name="id" id="formId">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="formNombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" id="formTipo" class="form-select" required>
                        <option value="Fútbol">Fútbol</option>
                        <option value="Tenis">Tenis</option>
                        <option value="Basquetbol">Basquetbol</option>
                        <option value="Voleibol">Voleibol</option>
                        <option value="Squash">Squash</option>
                        <option value="Pádel">Pádel</option>
                        <option value="Multiusos">Multiusos</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="formDescripcion" class="form-control" rows="2"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Precio por Hora ($)</label>
                        <input type="number" name="precio_por_hora" id="formPrecio" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Capacidad</label>
                        <input type="number" name="capacidad" id="formCapacidad" class="form-control" min="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagen de la Cancha</label>
                    <input type="file" name="imagen" id="formImagen" class="form-control" accept="image/*">
                    <div id="previewImagen" class="mt-2" style="display:none;">
                        <img id="imgPreview" src="" alt="Preview" style="max-width:200px;max-height:150px;border-radius:8px;border:2px solid #dee2e6;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="formEstado" class="form-select">
                        <option value="disponible">Disponible</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.editar-cancha').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Editar Cancha';
        document.getElementById('formAccion').value = 'editar';
        document.getElementById('formId').value = this.dataset.id;
        document.getElementById('formNombre').value = this.dataset.nombre;
        document.getElementById('formTipo').value = this.dataset.tipo;
        document.getElementById('formDescripcion').value = this.dataset.descripcion;
        document.getElementById('formPrecio').value = this.dataset.precio;
        document.getElementById('formCapacidad').value = this.dataset.capacidad;
        document.getElementById('formEstado').value = this.dataset.estado;
        if (this.dataset.imagen) {
            const preview = document.getElementById('previewImagen');
            const img = document.getElementById('imgPreview');
            img.src = '<?php echo SITE_URL; ?>/assets/img/canchas/' + this.dataset.imagen;
            preview.style.display = 'block';
        }
        new bootstrap.Modal(document.getElementById('modalCancha')).show();
    });
});
document.getElementById('modalCancha').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalTitle').textContent = 'Nueva Cancha';
    document.getElementById('formAccion').value = 'crear';
    document.getElementById('formId').value = '';
    document.getElementById('formNombre').value = '';
    document.getElementById('formDescripcion').value = '';
    document.getElementById('formPrecio').value = '';
    document.getElementById('formCapacidad').value = '10';
    document.getElementById('formEstado').value = 'disponible';
    document.getElementById('previewImagen').style.display = 'none';
    document.getElementById('formImagen').value = '';
});
document.getElementById('formImagen').addEventListener('change', function(e) {
    const preview = document.getElementById('previewImagen');
    const img = document.getElementById('imgPreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>