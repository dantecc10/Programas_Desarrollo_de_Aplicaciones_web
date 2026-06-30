<?php
$titulo = 'Reservar Cancha';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../classes/Cancha.php';
require_once __DIR__ . '/../classes/Horario.php';
require_once __DIR__ . '/../classes/Precio.php';

$canchaModel = new Cancha();
$horarioModel = new Horario();
$precioModel = new Precio();
$canchaId = $_GET['cancha_id'] ?? null;
$cancha = $canchaId ? $canchaModel->obtenerPorId($canchaId) : null;
if (!$cancha) {
    $_SESSION['mensaje'] = 'Cancha no encontrada.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: canchas.php');
    exit;
}
$precios = $precioModel->obtenerPorCancha($canchaId);
require_once __DIR__ . '/../includes/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
.fc-event { cursor: pointer !important; }
.fc .fc-daygrid-day.fc-day-today { background-color: rgba(13,110,253,0.08); }
.slot-disponible { background: #198754; color: white; padding: 5px; border-radius: 4px; margin: 2px; cursor: pointer; text-align: center; font-size: 0.85rem; }
.slot-ocupado { background: #dc3545; color: white; padding: 5px; border-radius: 4px; margin: 2px; text-align: center; font-size: 0.85rem; opacity: 0.6; }
.slot-seleccionado { background: #0d6efd !important; color: white !important; }
</style>

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calendar3"></i> Calendario de Disponibilidad</h5>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
                <div class="mt-3 d-flex gap-3 justify-content-center">
                    <span><span class="badge bg-success">&nbsp;&nbsp;</span> Disponible</span>
                    <span><span class="badge bg-danger">&nbsp;&nbsp;</span> Ocupado</span>
                    <span><span class="badge bg-primary">&nbsp;&nbsp;</span> Seleccionado</span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h6><i class="bi bi-clock"></i> Horarios para <span id="fechaSeleccionadaTexto">selecciona una fecha</span></h6>
            </div>
            <div class="card-body" id="slotsContainer">
                <div class="text-muted text-center py-3">Selecciona una fecha en el calendario para ver los horarios disponibles.</div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($cancha['nombre']); ?></h5>
            </div>
            <div class="card-body">
                <?php $imgCancha = $canchaModel->resolverImagen($cancha); ?>
                <?php if ($imgCancha): ?>
                <img src="<?php echo SITE_URL; ?>/assets/img/canchas/<?php echo $imgCancha; ?>" alt="" class="w-100 rounded mb-3" style="max-height:200px;object-fit:cover;">
                <?php endif; ?>
                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($cancha['tipo']); ?></p>
                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($cancha['descripcion']); ?></p>
                <p><strong>Capacidad:</strong> <?php echo $cancha['capacidad']; ?> personas</p>

                <?php if (!empty($precios)): ?>
                <div class="mb-3">
                    <strong>Precios:</strong>
                    <div class="table-responsive mt-1">
                        <table class="table table-sm table-bordered mb-0">
                            <?php foreach ($precios as $p): ?>
                            <tr>
                                <td><span class="badge bg-<?php echo $p['tipo_precio'] === 'regular' ? 'success' : ($p['tipo_precio'] === 'pico' ? 'warning' : 'info'); ?>"><?php echo ucfirst($p['tipo_precio']); ?></span></td>
                                <td class="fw-bold text-primary">$<?php echo number_format($p['precio'], 2); ?></td>
                                <td><small><?php echo htmlspecialchars($p['nombre']); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <p class="fs-4 fw-bold text-primary">$<?php echo number_format($cancha['precio_por_hora'], 2); ?> / hora</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-calendar-plus"></i> Nueva Reservación</h5>
            </div>
            <div class="card-body">
                <form id="formReservar" action="../api/reservar.php" method="POST">
                    <input type="hidden" name="cancha_id" value="<?php echo $cancha['id']; ?>">
                    <input type="hidden" name="fecha" id="fechaReserva">
                    <input type="hidden" name="hora_inicio" id="horaInicio">
                    <input type="hidden" name="hora_fin" id="horaFin">
                    <input type="hidden" name="precio_por_hora" id="precioHora" value="<?php echo $cancha['precio_por_hora']; ?>">

                    <div class="mb-3" id="divTipoUso" style="display:<?php echo $cancha['tipo'] === 'multiusos' ? 'block' : 'none'; ?>;">
                        <label class="form-label">Tipo de Uso</label>
                        <select name="tipo_uso" class="form-select" id="tipoUso">
                            <option value="">Seleccione el tipo de uso</option>
                            <option value="Fútbol">Fútbol</option>
                            <option value="Tenis">Tenis</option>
                            <option value="Basquetbol">Basquetbol</option>
                            <option value="Voleibol">Voleibol</option>
                            <option value="Squash">Squash</option>
                            <option value="Pádel">Pádel</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>

                    <div class="alert alert-info mb-0" id="resumenReserva" style="display:none;">
                        <strong>Resumen:</strong>
                        <p class="mb-0 mt-1" id="textoResumen"></p>
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="btnReservar" disabled>
                        <i class="bi bi-check-circle"></i> Confirmar Reservación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/es.global.min.js"></script>
<script>
let calendar;
let fechaSeleccionada = null;
let horaSeleccionada = null;
const canchaId = <?php echo $cancha['id']; ?>;
const precios = <?php echo json_encode($precios); ?>;
const precioBase = <?php echo $cancha['precio_por_hora']; ?>;

function obtenerDia(fechaStr) {
    const partes = fechaStr.split('-');
    return new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2])).getDay() || 7;
}
function obtenerPrecio(fecha, hora) {
    if (!precios || precios.length === 0) return precioBase;
    const dia = obtenerDia(fecha);
    for (const p of precios) {
        if (p.dia_semana_inicio && p.dia_semana_fin) {
            if (dia >= parseInt(p.dia_semana_inicio) && dia <= parseInt(p.dia_semana_fin)) {
                if (p.hora_inicio && hora >= p.hora_inicio && hora <= p.hora_fin) {
                    return parseFloat(p.precio);
                }
                if (!p.hora_inicio) {
                    return parseFloat(p.precio);
                }
            }
        }
    }
    return precioBase;
}

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        firstDay: 1,
        height: 'auto',
        selectable: true,
        dateClick: function(info) {
            cargarSlots(info.dateStr);
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('../api/disponibilidad.php?cancha_id=' + canchaId + '&start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr)
                .then(r => r.json())
                .then(data => {
                    const events = data.map(e => ({
                        start: e.start,
                        end: e.end,
                        display: 'background',
                        backgroundColor: e.ocupado ? '#dc3545' : '#198754',
                        borderColor: e.ocupado ? '#dc3545' : '#198754',
                        extendedProps: { ocupado: e.ocupado, hora_inicio: e.hora_inicio, hora_fin: e.hora_fin }
                    }));
                    successCallback(events);
                })
                .catch(() => successCallback([]));
        },
        dayCellDidMount: function(info) {
            if (info.date < new Date()) {
                info.el.style.backgroundColor = '#f0f0f0';
                info.el.style.pointerEvents = 'none';
                info.el.style.opacity = '0.5';
            }
        }
    });
    calendar.render();
});

function formatearFecha(fechaStr) {
    const partes = fechaStr.split('-');
    const d = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
    return d.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}
function cargarSlots(fecha) {
    fechaSeleccionada = fecha;
    horaSeleccionada = null;
    document.getElementById('fechaSeleccionadaTexto').textContent = formatearFecha(fecha);
    document.getElementById('fechaReserva').value = fecha;
    document.getElementById('btnReservar').disabled = true;

    const container = document.getElementById('slotsContainer');
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div> Cargando horarios...</div>';

    fetch('../api/horarios.php?cancha_id=' + canchaId + '&fecha=' + fecha)
        .then(r => r.json())
        .then(data => {
            container.innerHTML = '';
            if (data.length === 0) {
                container.innerHTML = '<div class="alert alert-warning mb-0">No hay horarios configurados para esta fecha.</div>';
                return;
            }
            const row = document.createElement('div');
            row.className = 'row g-2';
            data.forEach(h => {
                const ocupado = parseInt(h.ocupado) === 1;
                const precio = obtenerPrecio(fecha, h.hora_inicio);
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 col-lg-3';
                const div = document.createElement('div');
                div.className = ocupado ? 'slot-ocupado' : 'slot-disponible';
                div.textContent = h.hora_inicio.substring(0,5) + ' - ' + h.hora_fin.substring(0,5) + ' ($' + precio.toFixed(2) + ')';
                if (ocupado) {
                    div.title = 'Horario ocupado';
                } else {
                    div.dataset.horaInicio = h.hora_inicio;
                    div.dataset.horaFin = h.hora_fin;
                    div.dataset.precio = precio;
                    div.addEventListener('click', function() {
                        document.querySelectorAll('.slot-disponible').forEach(s => s.classList.remove('slot-seleccionado'));
                        this.classList.add('slot-seleccionado');
                        horaSeleccionada = this.dataset.horaInicio;
                        document.getElementById('horaInicio').value = this.dataset.horaInicio;
                        document.getElementById('horaFin').value = this.dataset.horaFin;
                        document.getElementById('precioHora').value = this.dataset.precio;
                        actualizarResumen();
                    });
                }
                col.appendChild(div);
                row.appendChild(col);
            });
            container.appendChild(row);
        })
        .catch(() => {
            container.innerHTML = '<div class="alert alert-danger mb-0">Error al cargar horarios.</div>';
        });
}

document.getElementById('tipoUso')?.addEventListener('change', function() {
    if (horaSeleccionada) actualizarResumen();
});

function actualizarResumen() {
    const btn = document.getElementById('btnReservar');
    const resumen = document.getElementById('resumenReserva');
    const texto = document.getElementById('textoResumen');
    const tipoUso = document.getElementById('tipoUso');
    const precio = parseFloat(document.getElementById('precioHora').value);

    <?php if ($cancha['tipo'] === 'multiusos'): ?>
    if (!tipoUso.value) {
        btn.disabled = true;
        resumen.style.display = 'none';
        return;
    }
    <?php endif; ?>

    btn.disabled = false;
    resumen.style.display = 'block';
    let html = 'Cancha: <?php echo htmlspecialchars($cancha['nombre']); ?> | ' +
        'Fecha: ' + fechaSeleccionada + ' | ' +
        'Horario: ' + document.getElementById('horaInicio').value.substring(0,5) + ' - ' + document.getElementById('horaFin').value.substring(0,5);
    <?php if ($cancha['tipo'] === 'multiusos'): ?>
    html += ' | Uso: ' + tipoUso.options[tipoUso.selectedIndex].text;
    <?php endif; ?>
    html += ' | <strong>Total: $' + precio.toFixed(2) + '</strong>';
    texto.innerHTML = html;
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>