<?php
$titulo = 'Pago';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Reservacion.php';
require_once __DIR__ . '/../classes/Pago.php';

$reservacionModel = new Reservacion();
$pagoModel = new Pago();
$reservacionId = $_GET['reservacion_id'] ?? null;
$soloVer = $_GET['ver'] ?? false;

$reservacion = $reservacionModel->obtenerPorId($reservacionId);
if (!$reservacion || $reservacion['usuario_id'] != $_SESSION['usuario_id']) {
    $_SESSION['mensaje'] = 'Reservación no encontrada.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: mis_reservaciones.php');
    exit;
}

$pago = $pagoModel->obtenerPorReservacion($reservacionId);

if ($soloVer || $reservacion['estado'] !== 'pendiente') {
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5><i class="bi bi-receipt"></i> Detalle de Pago</h5>
                </div>
                <div class="card-body">
                    <p><strong>Cancha:</strong> <?php echo htmlspecialchars($reservacion['cancha_nombre']); ?></p>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($reservacion['fecha'])); ?></p>
                    <p><strong>Horario:</strong> <?php echo substr($reservacion['hora_inicio'],0,5); ?> - <?php echo substr($reservacion['hora_fin'],0,5); ?></p>
                    <?php if ($reservacion['tipo_uso']): ?>
                    <p><strong>Uso:</strong> <?php echo htmlspecialchars($reservacion['tipo_uso']); ?></p>
                    <?php endif; ?>
                    <p class="fs-5 fw-bold text-success">Total: $<?php echo number_format($reservacion['total'], 2); ?></p>
                    <?php if ($pago): ?>
                    <hr>
                    <p><strong>Estado de Pago:</strong>
                        <span class="badge bg-<?php echo $pago['estado_pago'] === 'completado' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($pago['estado_pago']); ?>
                        </span>
                    </p>
                    <p><strong>Referencia:</strong> <?php echo $pago['referencia'] ?? 'N/A'; ?></p>
                    <p><strong>Método:</strong> <?php echo ucfirst($pago['metodo_pago']); ?></p>
                    <?php endif; ?>
                    <a href="mis_reservaciones.php" class="btn btn-primary w-100 mt-3">Volver a Mis Reservaciones</a>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<?php
$expiracion = strtotime($reservacion['fecha_reservacion']) + 900;
$segundosRestantes = max(0, $expiracion - time());
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-credit-card"></i> Procesar Pago</h5>
            </div>
            <div class="card-body">
                <div class="bg-danger text-white rounded-3 p-3 mb-3 text-center" id="cronometro">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Tiempo restante para pagar: <span id="tiempoRestante">--:--</span></h5>
                    <small class="text-white-50">Si no pagas dentro de este tiempo, la reserva se cancelará automáticamente</small>
                </div>

                <div class="bg-white text-dark border rounded-3 p-3 mb-3">
                    <h6 class="text-primary mb-3"><i class="bi bi-receipt"></i> Resumen de Reservación</h6>
                    <p class="mb-1"><strong>Cancha:</strong> <?php echo htmlspecialchars($reservacion['cancha_nombre']); ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($reservacion['fecha'])); ?></p>
                    <p class="mb-1"><strong>Horario:</strong> <?php echo substr($reservacion['hora_inicio'],0,5); ?> - <?php echo substr($reservacion['hora_fin'],0,5); ?></p>
                    <?php if ($reservacion['tipo_uso']): ?>
                    <p class="mb-1"><strong>Uso:</strong> <?php echo htmlspecialchars($reservacion['tipo_uso']); ?></p>
                    <?php endif; ?>
                    <hr>
                    <p class="fs-4 fw-bold text-success mb-0">Total a Pagar: $<?php echo number_format($reservacion['total'], 2); ?></p>
                </div>

                <?php if (PAYPAL_CLIENT_ID): ?>
                <div class="mb-4">
                    <h6 class="text-primary mb-3"><i class="bi bi-paypal"></i> Pago con PayPal</h6>
                    <div id="paypal-button-container"></div>
                </div>
                <hr>
                <?php endif; ?>

                <div class="mt-3">
                    <h6 class="text-muted mb-3"><i class="bi bi-other"></i> Otros métodos</h6>
                    <form action="../api/pago.php" method="POST" id="formPago">
                        <input type="hidden" name="reservacion_id" value="<?php echo $reservacion['id']; ?>">
                        <select name="metodo_pago" class="form-select mb-3">
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="efectivo">Efectivo (Pago en establecimiento)</option>
                        </select>
                        <button type="submit" class="btn btn-outline-success w-100" id="btnPagar" onclick="return confirm('¿Confirmar el pago de $<?php echo number_format($reservacion['total'], 2); ?>?')">
                            <i class="bi bi-check-circle"></i> Marcar como Pagado (simulado)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var segundos = <?php echo $segundosRestantes; ?>;
    var span = document.getElementById('tiempoRestante');
    var cronometro = document.getElementById('cronometro');
    var btnPagar = document.getElementById('btnPagar');
    var formPago = document.getElementById('formPago');

    function deshabilitarTodo() {
        span.textContent = 'EXPIRADO';
        cronometro.className = 'bg-secondary text-white rounded-3 p-3 mb-3 text-center';
        if (btnPagar) {
            btnPagar.disabled = true;
            btnPagar.className = 'btn btn-secondary w-100 btn-lg mt-3';
            btnPagar.innerHTML = '<i class="bi bi-x-circle"></i> Tiempo Expirado';
        }
        if (formPago) {
            var selects = formPago.querySelectorAll('select');
            for (var i = 0; i < selects.length; i++) selects[i].disabled = true;
        }
    }

    function actualizar() {
        if (segundos <= 0) {
            deshabilitarTodo();
            return;
        }
        var m = Math.floor(segundos / 60);
        var s = segundos % 60;
        span.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        if (segundos <= 60) {
            cronometro.className = 'bg-warning text-dark rounded-3 p-3 mb-3 text-center';
        }
    }

    actualizar();
    setInterval(function() {
        segundos--;
        actualizar();
    }, 1000);
})();
</script>

<?php if (PAYPAL_CLIENT_ID): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= PAYPAL_CLIENT_ID ?>&currency=MXN&intent=capture"></script>
<script>
paypal.Buttons({
    createOrder: function() {
        return fetch('../api/paypal_create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reservacion_id: <?= $reservacion['id'] ?> })
        }).then(function(res) {
            return res.json();
        }).then(function(data) {
            if (data.error) throw new Error(data.error);
            return data.orderID;
        });
    },
    onApprove: function(data) {
        return fetch('../api/paypal_capture_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                reservacion_id: <?= $reservacion['id'] ?>,
                order_id: data.orderID
            })
        }).then(function(res) {
            return res.json();
        }).then(function(data) {
            if (data.status === 'COMPLETED') {
                window.location.href = 'mis_reservaciones.php';
            } else {
                alert('Error al procesar el pago. Intenta de nuevo.');
            }
        });
    },
    onCancel: function() {
        alert('Pago cancelado. Puedes intentar de nuevo.');
    },
    onError: function(err) {
        alert('Ocurrió un error con PayPal. Intenta de nuevo.');
    }
}).render('#paypal-button-container');
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
