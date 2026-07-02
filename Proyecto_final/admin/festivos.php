<?php
$titulo = 'Festivos';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
if (!esAdmin()) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../classes/Historial.php';
$historial = new Historial();

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error DB");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $stmt = $db->prepare("INSERT INTO festivos (fecha, nombre) VALUES (:fecha, :nombre)");
        $stmt->execute([':fecha' => $_POST['fecha'], ':nombre' => $_POST['nombre']]);
        $historial->registrar($_SESSION['usuario_id'], 'Festivo creado', "Festivo {$_POST['nombre']} - {$_POST['fecha']}");
        $_SESSION['mensaje'] = 'Festivo agregado.'; $_SESSION['tipo_mensaje'] = 'success';
    } elseif ($accion === 'toggle') {
        $stmt = $db->prepare("UPDATE festivos SET activo = NOT activo WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
    } elseif ($accion === 'eliminar') {
        $stmt = $db->prepare("DELETE FROM festivos WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $historial->registrar($_SESSION['usuario_id'], 'Festivo eliminado', "Festivo #{$_POST['id']}");
        $_SESSION['mensaje'] = 'Festivo eliminado.'; $_SESSION['tipo_mensaje'] = 'warning';
    }
    header('Location: festivos.php'); exit;
}

$festivos = $db->query("SELECT * FROM festivos ORDER BY fecha DESC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<h3><i class="bi bi-calendar-event"></i> Días Festivos</h3>
<hr>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5>Agregar Festivo</h5></div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del festivo (ej. Navidad)" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="accion" value="crear" class="btn btn-primary w-100"><i class="bi bi-plus"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr><th>Fecha</th><th>Nombre</th><th>Activo</th><th>Acción</th></tr>
        </thead>
        <tbody>
            <?php foreach ($festivos as $f): ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($f['fecha'])); ?></td>
                <td><?php echo htmlspecialchars($f['nombre']); ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="accion" value="toggle">
                        <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                        <button type="submit" class="btn btn-sm <?php echo $f['activo'] ? 'btn-success' : 'btn-secondary'; ?>">
                            <?php echo $f['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </button>
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar festivo?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($festivos)): ?>
            <tr><td colspan="4" class="text-center text-muted">No hay festivos registrados</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
