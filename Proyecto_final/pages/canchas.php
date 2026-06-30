<?php
$titulo = 'Canchas';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Cancha.php';
$canchaModel = new Cancha();
$tipos = $canchaModel->obtenerTipos();
$tipoFiltro = $_GET['tipo'] ?? '';
$busqueda = trim($_GET['buscar'] ?? '');
$canchas = $canchaModel->obtenerTodas(true);
if ($tipoFiltro) {
    $canchas = array_filter($canchas, function($c) use ($tipoFiltro) {
        return $c['tipo'] === $tipoFiltro;
    });
}
if ($busqueda) {
    $canchas = array_filter($canchas, function($c) use ($busqueda) {
        return stripos($c['nombre'], $busqueda) !== false;
    });
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Canchas Disponibles</h2>
    <div>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <select name="tipo" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los tipos</option>
                <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo $tipo; ?>" <?php echo $tipoFiltro === $tipo ? 'selected' : ''; ?>>
                    <?php echo $tipo; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($canchas)): ?>
    <div class="col-12">
        <div class="alert alert-info">No hay canchas disponibles para el filtro seleccionado.</div>
    </div>
    <?php endif; ?>
    <?php foreach ($canchas as $cancha): ?>
    <?php $imgCancha = $canchaModel->resolverImagen($cancha); ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <?php if ($imgCancha): ?>
            <img src="<?php echo SITE_URL; ?>/assets/img/canchas/<?php echo $imgCancha; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($cancha['nombre']); ?>" style="height:180px;object-fit:cover;">
            <?php else: ?>
            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height:180px;border-radius:10px 10px 0 0;">
                <i class="bi bi-image text-white" style="font-size:3rem;"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title"><?php echo htmlspecialchars($cancha['nombre']); ?></h5>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($cancha['tipo']); ?></span>
                </div>
                <p class="card-text text-muted small"><?php echo htmlspecialchars($cancha['descripcion']); ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary fs-5">$<?php echo number_format($cancha['precio_por_hora'], 2); ?>/h</span>
                    <span class="text-muted small"><i class="bi bi-people"></i> Cap. <?php echo $cancha['capacidad']; ?></span>
                </div>
                <hr>
                <a href="reservar.php?cancha_id=<?php echo $cancha['id']; ?>" class="btn btn-primary w-100">
                    <i class="bi bi-calendar-plus"></i> Reservar Ahora
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>