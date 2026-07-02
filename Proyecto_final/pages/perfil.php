<?php
$titulo = 'Mi Perfil';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Usuario.php';
$usuarioModel = new Usuario();
$usuario = $usuarioModel->obtenerPorId($_SESSION['usuario_id']);

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-person-gear"></i> Mi Perfil</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <?php if ($usuario['foto_perfil']): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/img/usuarios/<?php echo $usuario['foto_perfil']; ?>" alt="" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;border:3px solid #0d6efd;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width:120px;height:120px;border:3px solid #0d6efd;">
                            <i class="bi bi-person-fill text-white fs-1"></i>
                        </div>
                    <?php endif; ?>
                    <h5 class="mt-2"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                    <span class="badge bg-<?php echo $usuario['rol'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($usuario['rol']); ?></span>
                </div>

                <form id="formPerfil" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Foto de Perfil</label>
                            <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Guardar Cambios</button>
                </form>

                <hr class="my-4">
                <h5><i class="bi bi-key"></i> Cambiar Contraseña</h5>
                <form id="formPassword">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="password_nueva" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirmar</label>
                            <input type="password" name="password_confirmar" class="form-control" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100"><i class="bi bi-key"></i> Cambiar Contraseña</button>
                </form>

                <div id="mensajePerfil" class="mt-3"></div>
            </div>
        </div>

        <?php if ($usuario['rol'] === 'cliente'): ?>
        <div class="card shadow mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-exclamation-triangle"></i> Zona de Peligro</h5>
            </div>
            <div class="card-body">
                <?php if ($usuario['solicitud_eliminacion']): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-clock"></i> Solicitaste la eliminación de tu cuenta el
                        <strong><?php echo date('d/m/Y H:i', strtotime($usuario['solicitud_eliminacion'])); ?></strong>.
                        Tu cuenta será eliminada automáticamente en 30 días si no cancelas esta solicitud.
                    </div>
                    <button class="btn btn-success" id="btnCancelarEliminacion">
                        <i class="bi bi-arrow-counterclockwise"></i> Cancelar Solicitud
                    </button>
                <?php else: ?>
                    <p class="text-muted">Si deseas eliminar tu cuenta y todos tus datos, solicita la eliminación. Tendrás 30 días para cancelarlo.</p>
                    <button class="btn btn-danger" id="btnSolicitarEliminacion">
                        <i class="bi bi-trash"></i> Solicitar Eliminación de Cuenta
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('formPerfil')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('accion', 'actualizar');
    const msgDiv = document.getElementById('mensajePerfil');
    msgDiv.innerHTML = '<div class="alert alert-info">Guardando...</div>';
    fetch('../api/perfil.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.exito) {
                msgDiv.innerHTML = '<div class="alert alert-success">' + data.mensaje + '</div>';
                setTimeout(() => location.reload(), 1500);
            } else {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + data.mensaje + '</div>';
            }
        })
        .catch(() => {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
        });
});

document.getElementById('formPassword')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('accion', 'cambiar_password');
    const msgDiv = document.getElementById('mensajePerfil');
    msgDiv.innerHTML = '<div class="alert alert-info">Procesando...</div>';
    fetch('../api/perfil.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.exito) {
                msgDiv.innerHTML = '<div class="alert alert-success">' + data.mensaje + '</div>';
                this.reset();
            } else {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + data.mensaje + '</div>';
            }
        })
        .catch(() => {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
        });
});

document.getElementById('btnSolicitarEliminacion')?.addEventListener('click', function() {
    if (!confirm('¿Estás seguro de solicitar la eliminación de tu cuenta? Tendrás 30 días para cancelarlo.')) return;
    fetch('../api/perfil.php', { method: 'POST', body: new URLSearchParams({ accion: 'solicitar_eliminacion' }) })
        .then(r => r.json())
        .then(data => {
            if (data.exito) location.reload();
            else alert(data.mensaje);
        });
});

document.getElementById('btnCancelarEliminacion')?.addEventListener('click', function() {
    fetch('../api/perfil.php', { method: 'POST', body: new URLSearchParams({ accion: 'cancelar_eliminacion' }) })
        .then(r => r.json())
        .then(data => {
            if (data.exito) location.reload();
            else alert(data.mensaje);
        });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>