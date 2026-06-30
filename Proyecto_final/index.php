<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Cancha.php';
$canchaModel = new Cancha();
$canchasRecientes = $canchaModel->obtenerTodas(true);
$totalCanchas = $canchaModel->contar();
$disponibles = $canchaModel->contarDisponibles();
$titulo = 'Inicio';
require_once __DIR__ . '/includes/header.php';
?>
<div class="hero-section text-center py-5 rounded-3 mb-4" style="background:linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);">
    <div class="container">
        <h1 class="display-4 fw-bold text-white"><i class="bi bi-trophy"></i> Reserva tu Cancha Deportiva</h1>
        <p class="lead text-white-50">Encuentra y reserva la mejor cancha para tu deporte favorito al mejor precio.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="pages/canchas.php" class="btn btn-light btn-lg px-4">
                <i class="bi bi-search"></i> Ver Canchas
            </a>
            <a href="pages/registro.php" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-person-plus"></i> Registrarse
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body py-4">
                <i class="bi bi-building text-primary" style="font-size:2.5rem;"></i>
                <h3 class="text-primary mt-2"><?php echo $totalCanchas; ?></h3>
                <p class="mb-0">Canchas Registradas</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body py-4">
                <i class="bi bi-check-circle text-success" style="font-size:2.5rem;"></i>
                <h3 class="text-success mt-2"><?php echo $disponibles; ?></h3>
                <p class="mb-0">Canchas Disponibles</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-primary text-white text-center py-3 rounded-3 mb-4">
    <h3 class="mb-0"><i class="bi bi-star"></i> Servicios</h3>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="row g-3 text-center">
            <div class="col-4 col-md-2">
                <div class="p-3 bg-light rounded-3">
                    <i class="bi bi-sun fs-2 text-warning"></i>
                    <p class="small mb-0 mt-1"><strong>Campo Iluminado</strong></p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="p-3 bg-light rounded-3">
                    <i class="bi bi-people fs-2 text-primary"></i>
                    <p class="small mb-0 mt-1"><strong>Gradas</strong></p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="p-3 bg-light rounded-3">
                    <i class="bi bi-tv fs-2 text-success"></i>
                    <p class="small mb-0 mt-1"><strong>Marcador Digital</strong></p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="p-3 bg-light rounded-3">
                    <i class="bi bi-droplet fs-2 text-info"></i>
                    <p class="small mb-0 mt-1"><strong>Baños</strong></p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="p-3 bg-light rounded-3">
                    <i class="bi bi-shop fs-2 text-danger"></i>
                    <p class="small mb-0 mt-1"><strong>Zona Comercial</strong></p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="p-3 bg-light rounded-3">
                    <i class="bi bi-wifi fs-2 text-secondary"></i>
                    <p class="small mb-0 mt-1"><strong>WiFi Gratis</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">Canchas Disponibles</h3>
<div class="row">
    <?php foreach ($canchasRecientes as $cancha): ?>
    <?php $imgCancha = $canchaModel->resolverImagen($cancha); ?>
    <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm">
            <?php if ($imgCancha): ?>
            <img src="<?php echo SITE_URL; ?>/assets/img/canchas/<?php echo $imgCancha; ?>" class="card-img-top" alt="" style="height:150px;object-fit:cover;">
            <?php else: ?>
            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height:150px;border-radius:10px 10px 0 0;">
                <i class="bi bi-image text-white" style="font-size:2.5rem;"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($cancha['nombre']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($cancha['tipo']); ?></span>
                </h6>
                <p class="card-text small"><?php echo htmlspecialchars(substr($cancha['descripcion'], 0, 100)); ?></p>
                <p class="fw-bold text-primary">$<?php echo number_format($cancha['precio_por_hora'], 2); ?> / hora</p>
                <a href="pages/reservar.php?cancha_id=<?php echo $cancha['id']; ?>" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-calendar-plus"></i> Reservar
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <i class="bi bi-trophy fs-1 text-warning"></i>
            <h5>TORNEOS</h5>
            <p class="text-muted small">Inscribe a tu equipo y participa en los torneos para ganar y salir victorioso.</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <i class="bi bi-calendar-check fs-1 text-primary"></i>
            <h5>RENTA DE CANCHA</h5>
            <p class="text-muted small">Conoce nuestros precios y horarios, agenda tu visita y disfruta de las instalaciones.</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4">
            <i class="bi bi-megaphone fs-1 text-danger"></i>
            <h5>PUBLICIDAD EN CANCHA</h5>
            <p class="text-muted small">Plasma tu marca en nuestra cancha y da visibilidad a tu marca en un nuevo mercado.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>