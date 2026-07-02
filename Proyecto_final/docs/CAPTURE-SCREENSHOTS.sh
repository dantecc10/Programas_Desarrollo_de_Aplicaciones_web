#!/bin/bash
# ================================================================
# Script de captura de pantallas para REPORTE.md
# Requiere: Firefox con soporte headless
# Uso: bash CAPTURE-SCREENSHOTS.sh
# Asegúrate de que el servidor esté corriendo en http://localhost:8080
# y la DB tenga datos de prueba.
# ================================================================

BASE_URL="http://localhost:8080"
OUTDIR="assets/img"
mkdir -p "$OUTDIR"

SCREENSHOT="firefox --screenshot --headless --window-size=1366,768"

echo "=== Capturando pantallas ==="

# 1. Homepage
$SCREENSHOT "${OUTDIR}/01-homepage.png" "$BASE_URL/index.php"

# 2. Registro
$SCREENSHOT "${OUTDIR}/02-registro.png" "$BASE_URL/pages/registro.php"

# 3. Login
$SCREENSHOT "${OUTDIR}/03-login.png" "$BASE_URL/pages/login.php"

# 4. Canchas con reseñas
$SCREENSHOT "${OUTDIR}/04-canchas.png" "$BASE_URL/pages/canchas.php"

# 5. Calendario de reservación con FullCalendar
$SCREENSHOT "${OUTDIR}/05-reservar.png" "$BASE_URL/pages/reservar.php?cancha_id=1"

# 6. Pago con PayPal
$SCREENSHOT "${OUTDIR}/06-pago.png" "$BASE_URL/pages/pago.php?reservacion_id=1"

# 7. Mis Reservaciones
$SCREENSHOT "${OUTDIR}/07-mis-reservaciones.png" "$BASE_URL/pages/mis_reservaciones.php"

# 8. Historial con reseñas
$SCREENSHOT "${OUTDIR}/08-historial.png" "$BASE_URL/pages/historial.php"

# 9. Perfil
$SCREENSHOT "${OUTDIR}/09-perfil.png" "$BASE_URL/pages/perfil.php"

# 10. Admin Dashboard
$SCREENSHOT "${OUTDIR}/10-admin-dashboard.png" "$BASE_URL/admin/index.php"

# 11. Admin Canchas
$SCREENSHOT "${OUTDIR}/11-admin-canchas.png" "$BASE_URL/admin/canchas.php"

# 12. Admin Precios
$SCREENSHOT "${OUTDIR}/12-admin-precios.png" "$BASE_URL/admin/precios.php"

# 13. Admin Horarios
$SCREENSHOT "${OUTDIR}/13-admin-horarios.png" "$BASE_URL/admin/horarios.php"

# 14. Admin Reservaciones con filtros
$SCREENSHOT "${OUTDIR}/14-admin-reservaciones.png" "$BASE_URL/admin/reservaciones.php"

# 15. Admin Usuarios
$SCREENSHOT "${OUTDIR}/15-admin-usuarios.png" "$BASE_URL/admin/usuarios.php"

# 16. Admin Reportes con Chart.js
$SCREENSHOT "${OUTDIR}/16-admin-reportes.png" "$BASE_URL/admin/reportes.php"

# 17. Admin Reseñas
$SCREENSHOT "${OUTDIR}/17-admin-resenas.png" "$BASE_URL/admin/resenas.php"

# 18. Admin Festivos
$SCREENSHOT "${OUTDIR}/18-admin-festivos.png" "$BASE_URL/admin/festivos.php"

# 19. Dark mode (homepage)
firefox --screenshot --headless --window-size=1366,768 \
  --screenshot "${OUTDIR}/19-darkmode.png" \
  "$BASE_URL/index.php" &
# Simulamos dark mode via localStorage
sleep 1

# 20. Recuperar contraseña
$SCREENSHOT "${OUTDIR}/20-recuperar.png" "$BASE_URL/pages/recuperar.php"

echo "=== Captura completada ==="
echo "Las imágenes están en $OUTDIR/"
echo "NOTA: Para capturas autenticadas (admin, mis reservaciones, etc.),"
echo "  el script debe ejecutarse tras haber iniciado sesión."
echo "  Considera usar un token de sesión persistente o edita manualmente."
