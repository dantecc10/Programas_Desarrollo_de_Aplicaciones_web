<?php
require_once __DIR__ . '/../config/config.php';

$usuarioActual = null;
if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../classes/Usuario.php';
    $userModel = new Usuario();
    $usuarioActual = $userModel->obtenerPorId($_SESSION['usuario_id']);
    if (!$usuarioActual || !$usuarioActual['activo']) {
        session_destroy();
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit;
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>/index.php">
            <i class="bi bi-trophy"></i> Canchas Deportivas
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/canchas.php">
                        <i class="bi bi-building"></i> Canchas
                    </a>
                </li>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/mis_reservaciones.php">
                        <i class="bi bi-calendar-check"></i> Mis Reservaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/historial.php">
                        <i class="bi bi-clock-history"></i> Historial
                    </a>
                </li>
                <?php if ($usuarioActual && $usuarioActual['rol'] === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-shield-lock"></i> Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/canchas.php"><i class="bi bi-building"></i> Gestionar Canchas</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/precios.php"><i class="bi bi-tags"></i> Precios</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/horarios.php"><i class="bi bi-clock"></i> Gestionar Horarios</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/reservaciones.php"><i class="bi bi-list-check"></i> Reservaciones</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/usuarios.php"><i class="bi bi-people"></i> Usuarios</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/resenas.php"><i class="bi bi-star"></i> Reseñas</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/reportes.php"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/festivos.php"><i class="bi bi-calendar-event"></i> Festivos</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="darkModeToggle" role="button" aria-label="Cambiar modo oscuro/claro">
                        <i class="bi bi-moon-fill"></i>
                    </a>
                </li>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <?php if ($usuarioActual && $usuarioActual['foto_perfil']): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/img/usuarios/<?php echo $usuarioActual['foto_perfil']; ?>" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">
                        <?php else: ?>
                        <i class="bi bi-person-circle fs-5"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small"><?php echo $_SESSION['usuario_rol']; ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/perfil.php"><i class="bi bi-person-gear"></i> Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/login.php"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/registro.php"><i class="bi bi-person-plus"></i> Registrarse</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
