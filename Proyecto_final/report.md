# Sistema de Reservación de Canchas Deportivas

## Proyecto Final - Desarrollo de Aplicaciones Web

---

## Índice

1. [Descripción General](#descripción-general)
2. [Tecnologías Utilizadas](#tecnologías-utilizadas)
3. [Estructura del Proyecto](#estructura-del-proyecto)
4. [Modelo de Base de Datos](#modelo-de-base-de-datos)
5. [Diagrama de Flujo del Sistema](#diagrama-de-flujo-del-sistema)
6. [Módulos y Funcionalidades](#módulos-y-funcionalidades)
7. [Guía de Instalación y Ejecución](#guía-de-instalación-y-ejecución)
8. [Manual de Usuario](#manual-de-usuario)
9. [Manual de Administrador](#manual-de-administrador)
10. [Posibles Errores y Soluciones](#posibles-errores-y-soluciones)
11. [Mejoras Propuestas](#mejoras-propuestas)
12. [Capturas de Pantalla](#capturas-de-pantalla)

---

## Descripción General

El Sistema de Reservación de Canchas Deportivas es una aplicación web desarrollada en PHP con MySQL que permite a los usuarios registrarse, iniciar sesión, consultar canchas deportivas disponibles, realizar reservaciones por hora con calendario visual (FullCalendar), simular pagos con cronómetro de expiración, consultar su historial y editar su perfil con foto. Por otro lado, los administradores pueden gestionar canchas, horarios, precios diferenciados, usuarios, reservaciones y generar reportes de ingresos.

### Objetivos

- Facilitar la reservación de canchas deportivas en línea.
- Automatizar el control de horarios y disponibilidad.
- Proveer un panel administrativo para la gestión completa del sistema.
- Generar reportes de ingresos y ocupación.

---

## Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **PHP** | 8.x | Lenguaje de programación backend (OOP) |
| **MySQL/MariaDB** | 10.4 | Base de datos relacional |
| **HTML5** | - | Estructura de las vistas |
| **CSS3** | - | Estilos visuales (Bootstrap 5 + personalizados) |
| **JavaScript** | ES6 | Interactividad en cliente (AJAX, FullCalendar, Bootstrap JS) |
| **Bootstrap** | 5.3.3 | Framework CSS responsivo |
| **Bootstrap Icons** | 1.11 | Iconografía |
| **FullCalendar** | 6.1.15 | Calendario interactivo de disponibilidad |
| **PDO** | - | Conexión segura a base de datos |
| **Apache** | 2.4 | Servidor web (XAMPP) |
| **XAMPP** | 8.x | Entorno de desarrollo integrado |

### Arquitectura

El sistema sigue un patrón MVC simplificado:

- **Modelos**: Clases PHP en `classes/` que encapsulan la lógica de negocio y acceso a datos.
- **Vistas**: Archivos PHP en `pages/`, `admin/` e `includes/` que renderizan HTML.
- **Controladores**: Archivos PHP en `api/` que procesan peticiones POST/GET y redireccionan.

---

## Estructura del Proyecto

```
C:\xampp\htdocs\proyect\Proyecto_final\
│
├── index.php                         # Página principal con estadísticas y canchas
├── logout.php                        # Cierre de sesión
├── .htaccess                         # Configuración Apache (ocultar directorios)
│
├── config/
│   ├── config.php                    # Constantes globales (DB, URL, timezone, sesión)
│   └── database.php                  # Clase Database (Singleton con PDO)
│
├── classes/                          # MODELOS OOP (POO)
│   ├── Usuario.php                   # Gestión de usuarios (login, registro, CRUD)
│   ├── Cancha.php                    # Gestión de canchas (CRUD, filtros, imagen)
│   ├── Horario.php                   # Gestión de horarios y disponibilidad
│   ├── Precio.php                    # Gestión de precios diferenciados
│   ├── Reservacion.php               # Gestión de reservaciones y reportes
│   ├── Pago.php                      # Procesamiento simulado de pagos
│   ├── Historial.php                 # Registro de auditoría de actividades
│   └── Mailer.php                    # Envío de correos (PHPMailer)
│
├── includes/                         # COMPONENTES REUTILIZABLES
│   ├── header.php                    # <head> HTML, apertura body, navbar
│   ├── footer.php                    # Scripts JS, cierre body/html
│   ├── navbar.php                    # Barra de navegación dinámica por rol
│   └── auth_check.php                # Verificación de autenticación y permisos
│
├── pages/                            # VISTAS PÚBLICAS
│   ├── login.php                     # Formulario de inicio de sesión
│   ├── registro.php                  # Formulario de registro de usuario
│   ├── canchas.php                   # Catálogo de canchas con filtro por tipo
│   ├── reservar.php                  # Reservación con FullCalendar y AJAX
│   ├── mis_reservaciones.php         # Listado de reservaciones con acciones
│   ├── historial.php                 # Historial con estado de pago y botón Pagar
│   ├── pago.php                      # Pago simulado con cronómetro de 15 min
│   └── perfil.php                    # Perfil de usuario editable con foto
│
├── admin/                            # VISTAS DE ADMINISTRACIÓN
│   ├── index.php                     # Dashboard con tarjetas de estadísticas
│   ├── canchas.php                   # CRUD de canchas (modal Bootstrap)
│   ├── horarios.php                  # Generación y gestión de horarios (AJAX toggle)
│   ├── precios.php                   # Precios diferenciados (regular, pico, finde)
│   ├── reservaciones.php             # Gestión de todas las reservaciones
│   ├── usuarios.php                  # Gestión de usuarios (roles, activos)
│   ├── reportes.php                  # Reportes de ingresos y ocupación
│   └── fotos_canchas/                # Directorio de imágenes de canchas
│
├── api/                              # ENDPOINTS (Procesamiento de formularios)
│   ├── login.php                     # Autenticación de usuarios
│   ├── registro.php                  # Registro de nuevos usuarios
│   ├── horarios.php                  # Endpoint JSON para horarios disponibles
│   ├── disponibilidad.php            # Endpoint JSON para FullCalendar
│   ├── reservar.php                  # Creación de nueva reservación
│   ├── pago.php                      # Procesamiento de pago simulado
│   ├── cancelar_reservacion.php      # Cancelación de reservación
│   ├── perfil.php                    # Actualización de perfil y cambio de contraseña
│   └── toggle_horario.php            # Endpoint AJAX para activar/desactivar horario
│
├── assets/                           # RECURSOS ESTÁTICOS
│   ├── css/
│   │   └── style.css                 # Estilos personalizados
│   ├── js/
│   │   └── main.js                   # Scripts JavaScript (tooltips, alerts, confirm)
│   └── img/                          # Imágenes de canchas y usuarios
│
└── database/
    └── schema.sql                    # Esquema completo de la base de datos
```

---

## Modelo de Base de Datos

### Diagrama Entidad-Relación

```
┌─────────────┐       ┌──────────────┐       ┌─────────────────┐
│  usuarios   │       │  canchas     │       │   horarios      │
├─────────────┤       ├──────────────┤       ├─────────────────┤
│ id (PK)     │       │ id (PK)      │       │ id (PK)         │
│ nombre      │       │ nombre       │       │ cancha_id (FK)  │
│ email (UQ)  │       │ tipo         │       │ dia_semana      │
│ password    │       │ descripcion  │       │ hora_inicio     │
│ telefono    │       │ precio_hora  │       │ hora_fin        │
│ foto_perfil │       │ capacidad    │       │ estado          │
│ rol         │       │ imagen       │       └────────┬────────┘
│ fecha_reg   │       │ estado       │                │
│ activo      │       │ creada_en    │                │
└──────┬──────┘       └──────┬───────┘                │
       │                     │                        │
       │                     │    ┌───────────────────┘
       │                     │    │
       │                     │    │  ┌────────────────────┐
       │                     │    │  │     precios        │
       │                     │    │  ├────────────────────┤
       │                     │    └──┤ cancha_id (FK)     │
       │                     │       │ tipo_precio        │
       │   ┌─────────────────┼───────┤ nombre             │
       │   │                 │       │ precio             │
       │   │    ┌────────────────────┐│ dia_semana_inicio │
       │   │    │  reservaciones     ││ dia_semana_fin    │
       │   │    ├────────────────────┤│ hora_inicio       │
       │   └────┤ cancha_id (FK)    ││ hora_fin          │
       └────────┤ usuario_id (FK)   ││ activo            │
                │ fecha              │└────────────────────┘
                │ hora_inicio        │
                │ hora_fin           │
                │ tipo_uso           │
                │ estado             │
                │ total              │
                │ fecha_reservacion  │
                │ observaciones      │
                └────────┬───────────┘
                         │
                ┌────────┴───────────┐
                │      pagos         │
                ├────────────────────┤
                │ id (PK)            │
                │ reservacion_id(FK) │
                │ monto              │
                │ metodo_pago        │
                │ estado_pago        │
                │ referencia         │
                │ fecha_pago         │
                └────────────────────┘

┌───────────────────┐
│    historial      │
├───────────────────┤
│ id (PK)           │
│ usuario_id (FK)   │
│ accion            │
│ detalle           │
│ fecha             │
└───────────────────┘
```

### Descripción de Tablas

#### `usuarios`
Almacena los datos de todos los usuarios del sistema (clientes y administradores).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| nombre | VARCHAR(100) | Nombre completo |
| email | VARCHAR(100) (UNIQUE) | Correo electrónico (usado para login) |
| password | VARCHAR(255) | Hash bcrypt de la contraseña |
| telefono | VARCHAR(20) | Número de contacto |
| foto_perfil | VARCHAR(255) | Ruta de foto de perfil (opcional) |
| rol | ENUM('cliente','admin') | Rol del usuario |
| fecha_registro | DATETIME | Fecha de creación |
| activo | TINYINT(1) | Estado del usuario (1=activo, 0=inactivo) |

#### `canchas`
Catálogo de canchas deportivas disponibles para reservación.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| nombre | VARCHAR(100) | Nombre de la cancha |
| tipo | VARCHAR(50) | Tipo de deporte (Fútbol, Tenis, etc.) |
| descripcion | TEXT | Descripción detallada |
| precio_por_hora | DECIMAL(10,2) | Costo por hora (base) |
| capacidad | INT | Número máximo de personas |
| imagen | VARCHAR(255) | Ruta de imagen (opcional) |
| estado | ENUM('disponible','mantenimiento','inactivo') | Estado actual |
| creada_en | DATETIME | Fecha de creación |

#### `precios`
Precios diferenciados por cancha según tipo de horario (regular, pico, fin de semana).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| cancha_id | INT (FK) | Referencia a `canchas.id` |
| tipo_precio | ENUM('regular','pico','finde') | Categoría del precio |
| nombre | VARCHAR(100) | Nombre descriptivo |
| precio | DECIMAL(10,2) | Precio por hora |
| dia_semana_inicio | TINYINT | Día inicio del rango (1=Lun...7=Dom) |
| dia_semana_fin | TINYINT | Día fin del rango |
| hora_inicio | TIME | Hora inicio del rango |
| hora_fin | TIME | Hora fin del rango |
| activo | TINYINT(1) | Si el precio está activo |

#### `horarios`
Define los bloques horarios disponibles para cada cancha por día de la semana.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| cancha_id | INT (FK) | Referencia a `canchas.id` |
| dia_semana | TINYINT | Día de la semana (1=Lunes ... 7=Domingo) |
| hora_inicio | TIME | Hora de inicio del bloque |
| hora_fin | TIME | Hora de fin del bloque |
| estado | ENUM('disponible','no_disponible') | Disponibilidad del bloque |

#### `reservaciones`
Registro de todas las reservaciones realizadas en el sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| usuario_id | INT (FK) | Referencia a `usuarios.id` |
| cancha_id | INT (FK) | Referencia a `canchas.id` |
| fecha | DATE | Fecha de la reservación |
| hora_inicio | TIME | Hora de inicio |
| hora_fin | TIME | Hora de fin |
| tipo_uso | VARCHAR(50) | Tipo de uso (para canchas multiusos) |
| estado | ENUM('pendiente','confirmada','cancelada','completada') | Estado |
| total | DECIMAL(10,2) | Monto total |
| fecha_reservacion | DATETIME | Fecha en que se creó la reserva |
| observaciones | TEXT | Comentarios adicionales |

#### `pagos`
Registro de pagos asociados a cada reservación (relación 1:1).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| reservacion_id | INT (FK, UNIQUE) | Referencia a `reservaciones.id` |
| monto | DECIMAL(10,2) | Monto pagado |
| metodo_pago | VARCHAR(50) | Método (tarjeta, transferencia, efectivo) |
| estado_pago | ENUM('pendiente','completado','rechazado','reembolsado') | Estado |
| referencia | VARCHAR(100) | Código de referencia del pago |
| fecha_pago | DATETIME | Fecha del pago |

#### `historial`
Registro de auditoría para rastrear actividades de los usuarios.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| usuario_id | INT (FK, NULL) | Referencia a `usuarios.id` (nullable) |
| accion | VARCHAR(100) | Acción realizada |
| detalle | TEXT | Descripción detallada |
| fecha | DATETIME | Fecha del evento |

---

## Diagrama de Flujo del Sistema

```
                    ┌───────────────────────────────┐
                    │        index.php               │
                    │   Página Principal / Landing   │
                    └───────────┬───────────────────┘
                                │
                    ┌───────────┴───────────┐
                    │   ¿Sesión iniciada?   │
                    └───────────┬───────────┘
                               │
            ┌──────────────────┼──────────────────┐
            │ NO               │ SI                │
            ▼                  ▼                   │
┌──────────────────────┐ ┌──────────────────┐     │
│  pages/login.php     │ │   Ver canchas    │     │
│  pages/registro.php  │ │   Reservar       │     │
└──────────┬───────────┘ │   Mis reservas   │     │
           │             │   Historial      │     │
           ▼             │   Perfil         │     │
    ┌──────────────┐     └────────┬─────────┘     │
    │ Autenticación│              │               │
    │ (API login/  │              ▼               │
    │  registro)   │     ┌──────────────────┐     │
    └──────┬───────┘     │  Seleccionar     │     │
           │             │  cancha          │     │
           ▼             └────────┬─────────┘     │
    ┌──────────────────┐          │               │
    │   Redirigir a    │          ▼               │
    │   index.php      │   ┌──────────────────┐   │
    └──────────────────┘   │  FullCalendar    │   │
                           │  + AJAX slots   │   │
                           └────────┬─────────┘   │
                                    │             │
                                    ▼             │
                           ┌──────────────────┐   │
                           │ Confirmar reserva│   │
                           └────────┬─────────┘   │
                                    │             │
                                    ▼             │
                           ┌──────────────────┐   │
                           │  Pago simulado   │   │
                           │  (cronómetro     │   │
                           │   15 min)        │   │
                           └────────┬─────────┘   │
                                    │             │
                                    ▼             │
                           ┌──────────────────┐   │
                           │  Reservación     │   │
                           │  Confirmada      │   │
                           └──────────────────┘   │
                                                   │
                 ┌─────────────────────────────────┘
                 │
                 ▼
        ┌────────────────┐
        │  ¿Es admin?    │
        └───────┬────────┘
               │
    ┌──────────┴──────────┐
    │ NO                  │ SI
    ▼                     ▼
┌──────────────┐  ┌───────────────────────┐
│ Páginas de   │  │  admin/index.php      │
│ cliente      │  │  Dashboard + stats    │
└──────────────┘  └───────────┬───────────┘
                              │
                    ┌─────────┴──────────────────┐
                    │  Gestión:                   │
                    │  - Canchas (CRUD)           │
                    │  - Horarios (AJAX toggle)   │
                    │  - Precios diferenciados    │
                    │  - Reservaciones            │
                    │  - Usuarios                 │
                    │  - Reportes                 │
                    └────────────────────────────┘
```

### Flujo de Reservación (Detallado)

1. **Usuario visita** `index.php` → ve estadísticas y canchas destacadas.
2. **Se autentica** via `pages/login.php` o se registra en `pages/registro.php`.
3. **Navega a** `pages/canchas.php` → filtra por tipo de deporte si lo desea.
4. **Selecciona** una cancha y hace clic en "Reservar Ahora".
5. **Elige fecha** en el calendario FullCalendar (`pages/reservar.php`) → los bloques horarios se colorean verde (disponible) / rojo (ocupado) via AJAX (`api/disponibilidad.php`).
6. **Selecciona horario** → se muestra resumen con total a pagar (considerando precios diferenciados si existen).
7. **Confirma reservación** → se crea registro en `reservaciones` con estado `pendiente` y registro en `pagos` con estado `pendiente`. Se redirige a `pages/pago.php`.
8. **Simula pago** con cronómetro de 15 minutos (`pages/pago.php`) → selecciona método de pago. Si el tiempo expira, el botón se deshabilita.
9. **Sistema procesa** via `api/pago.php` → genera referencia única, actualiza `pagos.estado_pago = 'completado'` y `reservaciones.estado = 'confirmada'`.
10. **Redirige** a `pages/mis_reservaciones.php` donde ve su reservación confirmada.

---

## Módulos y Funcionalidades

### 1. Autenticación de Usuarios
- **Registro**: Validación de campos obligatorios, confirmación de contraseña, verificación de email único.
- **Login**: Verificación de credenciales con `password_verify()` (bcrypt).
- **Protección de rutas**: `includes/auth_check.php` redirige a login si no hay sesión. Verifica rol de administrador con `esAdmin()`.
- **Logout**: Destrucción de sesión.

### 2. Catálogo de Canchas
- Visualización en tarjetas responsivas (3 columnas en desktop).
- Filtro por tipo de deporte (Fútbol, Tenis, Basquetbol, etc.).
- Información: nombre, tipo, descripción, precio por hora, capacidad, imagen.
- Enlace directo a reservación.

### 3. Reservación con FullCalendar y Disponibilidad en Tiempo Real
- Calendario interactivo **FullCalendar 6** con vista mensual.
- Los horarios se colorean: **verde** = disponible, **rojo** = ocupado (vía `api/disponibilidad.php`).
- Al hacer clic en una fecha, se cargan los slots disponibles via AJAX (`api/horarios.php`).
- Precios diferenciados: se calcula automáticamente según tipo de horario (regular/pico/finde).
- Canchas multiusos: muestran selector de tipo de uso.
- Validación de duplicados en servidor.

### 4. Simulación de Pago con Cronómetro
- Interfaz con datos de tarjeta simulados (solo lectura).
- Selección de método: tarjeta, transferencia, efectivo.
- **Cronómetro regresivo de 15 minutos** desde la creación de la reserva.
- Si el tiempo expira: botón de pago se deshabilita, se muestra "Tiempo Expirado".
- Cuando faltan ≤60 segundos: el cronómetro cambia a color amarillo de advertencia.
- Confirmación con diálogo de confirmación.
- Generación de referencia única (`PAG-XXXXXXXX`).
- Transacción atómica (BEGIN/COMMIT/ROLLBACK).

### 5. Perfil de Usuario
- Página editable con nombre, email, teléfono.
- Subida de foto de perfil (formatos: jpg, png, webp, gif).
- Cambio de contraseña con verificación de contraseña actual.
- Actualización via AJAX (`api/perfil.php`) sin recargar página.

### 6. Panel de Administración
- **Dashboard**: Estadísticas (canchas, reservaciones, pendientes, ingresos del mes).
- **Gestión de Canchas**: CRUD completo con modal Bootstrap. Crear, editar (cargar datos con data-attributes), eliminar.
- **Gestión de Horarios**: Generación automática de bloques horarios por día con intervalo de 60 min. Activación/desactivación individual via AJAX (`api/toggle_horario.php`).
- **Gestión de Precios**: Precios diferenciados por tipo (regular, pico, fin de semana) con rangos de día y hora.
- **Gestión de Reservaciones**: Cambio de estado masivo, filtro por estado, eliminación.
- **Gestión de Usuarios**: Edición de perfil, cambio de rol, activación/desactivación, cambio de contraseña.
- **Reportes**: Filtros por rango de fechas y cancha específica. Tabla de ingresos por día/tipo. Totales acumulados.

### 7. Historial y Auditoría
- Registro automático de acciones: login, registro, creación de reserva, pago, cancelación, gestión de canchas/usuarios, actualización de perfil, cambio de contraseña.
- Visible en dashboard admin (últimas 5 actividades).
- Historial detallado por usuario en `pages/historial.php`.
- **Botón "Pagar"** en el historial para reservaciones pendientes (las canceladas no lo muestran).

### 8. Programación Orientada a Objetos (POO)

Todas las clases siguen principios de POO:

```php
// Ejemplo: Database.php - Patrón Singleton
class Database {
    private static $instancia = null;
    private $conn;

    private function __construct() { /* ... */ }
    public static function conectar() {
        if (self::$instancia === null)
            self::$instancia = new self();
        return self::$instancia->conn;
    }
}

// Ejemplo: Reservacion.php - Métodos de negocio
class Reservacion {
    private $db;
    public function __construct() { $this->db = Database::conectar(); }
    public function crear($datos) { /* transacción atómica */ }
    public function verificarDisponibilidad($cancha, $fecha, $hora) { /* ... */ }
    public function reporteIngresos($inicio, $fin) { /* agregaciones SQL */ }
}
```

---

## Guía de Instalación y Ejecución

### Requisitos

- **XAMPP** 8.x o superior (Apache + PHP + MySQL/MariaDB)
- Navegador web moderno (Chrome, Firefox, Edge)
- Git (opcional)

### Instalación Paso a Paso

#### 1. Clonar o copiar el proyecto

```
Copiar la carpeta Proyecto_final a C:\xampp\htdocs\proyect\
```

O si usas Git:

```bash
cd C:\xampp\htdocs\proyect\
git clone <url-del-repositorio> Proyecto_final
```

#### 2. Iniciar servicios de XAMPP

1. Abrir **XAMPP Control Panel** como Administrador.
2. Iniciar **Apache** (Start).
3. Iniciar **MySQL** (Start).

#### 3. Crear la base de datos

**Opción A - Usando phpMyAdmin:**
1. Abrir `http://localhost/phpmyadmin/`
2. Ir a la pestaña "Importar"
3. Seleccionar el archivo `C:\xampp\htdocs\proyect\Proyecto_final\database\schema.sql`
4. Click en "Continuar"

**Opción B - Usando línea de comandos:**
```bash
cd C:\xampp\mysql\bin
mysql -u root -p < "C:\xampp\htdocs\proyect\Proyecto_final\database\schema.sql"
```

#### 4. Configurar el proyecto

El archivo `config/config.php` ya viene preconfigurado para XAMPP estándar:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'canchas_deportivas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('SITE_URL', 'http://localhost:8080/proyect/Proyecto_final');
```

**Importante**: La zona horaria de PHP se configura automáticamente a `America/Mexico_City` para coincidir con la zona horaria de MySQL.

Si tu configuración de XAMPP es diferente (contraseña de root, puerto distinto), modifica estos valores.

#### 5. Verificar la instalación

1. Abrir `http://localhost:8080/proyect/Proyecto_final/`
2. Deberías ver la página principal con estadísticas y canchas.
3. Iniciar sesión como administrador:
   - **Email:** `admin@canchas.com`
   - **Contraseña:** `password`

#### 6. Credenciales por defecto

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@canchas.com | password |
| Cliente | (registro desde el formulario) | (la que elijas) |

---

## Manual de Usuario

### Registro de Nuevo Usuario

1. En la página principal, haz clic en **"Registrarse"** (esquina superior derecha).
2. Completa el formulario: nombre, email, teléfono (opcional), contraseña y confirmación.
3. Haz clic en **"Registrarse"**.
4. El sistema iniciará sesión automáticamente y te redirigirá al inicio.

### Inicio de Sesión

1. Haz clic en **"Iniciar Sesión"**.
2. Ingresa tu email y contraseña.
3. Haz clic en **"Ingresar"**.

### Explorar Canchas

1. Navega a **"Canchas"** en el menú superior.
2. Usa el filtro desplegable para seleccionar un tipo de deporte.
3. Cada cancha muestra: nombre, tipo, descripción, precio por hora, capacidad e imagen.
4. Haz clic en **"Reservar Ahora"** en la cancha deseada.

### Realizar una Reservación

1. En la página de reservación, verás un **calendario interactivo** (FullCalendar).
2. Los días con horarios disponibles se ven con bloques verdes; los ocupados en rojo.
3. Haz clic en una **fecha** del calendario.
4. Aparecerán los **horarios disponibles** como botones con precio.
5. Selecciona un horario haciendo clic en él (se resalta en azul).
6. Si la cancha es **multiusos**, selecciona el tipo de uso.
7. Revisa el **resumen** que aparece debajo.
8. Haz clic en **"Confirmar Reservación"**.

### Pagar una Reservación

1. Después de crear la reserva, serás redirigido a la página de pago.
2. Verás un **cronómetro de 15 minutos** para completar el pago.
3. Selecciona el **método de pago** (tarjeta, transferencia, efectivo).
4. Los datos de la tarjeta están simulados (solo lectura).
5. Haz clic en **"Pagar"** y confirma en el diálogo.
6. El sistema generará un número de referencia y confirmará la reservación.
7. Si el tiempo se agota, el botón de pago se deshabilitará y la reserva se cancelará automáticamente.

### Ver Mis Reservaciones

1. Navega a **"Mis Reservaciones"** en el menú.
2. Verás una tabla con: cancha, tipo, fecha, horario, total y estado.
3. Desde aquí puedes **pagar** (si está pendiente), **ver el pago** (si está confirmada) o **cancelar**.

### Ver Historial

1. Navega a **"Historial"** en el menú.
2. Muestra el registro completo de tus reservaciones, incluyendo estado de pago.
3. Las reservaciones **pendientes** tienen un botón **"Pagar"** para completar el pago directamente desde el historial.

### Editar Perfil

1. Navega a **"Mi Perfil"** en el menú.
2. Puedes cambiar tu **nombre**, **email**, **teléfono** y **foto de perfil**.
3. También puedes **cambiar tu contraseña** (requiere la contraseña actual).
4. Los cambios se guardan via AJAX sin recargar la página.

---

## Manual de Administrador

### Acceso al Panel

1. Inicia sesión con credenciales de administrador (`admin@canchas.com` / `password`).
2. En el menú aparecerá la opción **"Admin"** con un submenú desplegable.

### Dashboard

Muestra un resumen general del sistema:
- **Total de canchas** y cuántas están disponibles.
- **Total de reservaciones** y cuántas están confirmadas.
- **Pendientes de pago**.
- **Ingresos del mes**.
- **Acciones rápidas** (accesos directos a cada módulo).
- **Últimas actividades** (historial de acciones).

### Gestión de Canchas

1. **Ver**: Tabla con todas las canchas (ID, nombre, tipo, precio, capacidad, estado).
2. **Crear**: Botón "Nueva Cancha" → modal con formulario.
3. **Editar**: Icono de lápiz → modal carga datos automáticamente.
4. **Eliminar**: Icono de papelera → confirmación.

### Gestión de Horarios

1. Seleccionar una cancha del menú desplegable.
2. **Generar horarios**: Configurar hora de inicio y fin para cada día de la semana (L-D). El sistema genera bloques de 1 hora automáticamente.
3. **Activar/Desactivar**: Botón que cambia entre disponible/no disponible via AJAX, sin recargar la página.

### Gestión de Precios Diferenciados

1. Seleccionar una cancha del menú desplegable.
2. **Agregar precio**: Elegir tipo (regular, pico, finde), nombre, precio, rango de días y rango de horas.
3. **Eliminar**: Botón de papelera con confirmación.
4. Los precios se aplican automáticamente al calcular el total en la página de reservación.

### Gestión de Reservaciones

1. **Filtrar** por estado (todos, pendiente, confirmada, completada, cancelada).
2. **Cambiar estado**: Seleccionar nuevo estado en el menú desplegable.
3. **Eliminar**: Botón de papelera con confirmación.

### Gestión de Usuarios

1. **Ver**: Tabla con todos los usuarios (ID, nombre, email, teléfono, rol, activo, registro).
2. **Editar**: Modal para cambiar nombre, email, teléfono, rol (cliente/admin), estado activo/inactivo.
3. **Cambiar contraseña**: Modal independiente.

### Reportes

1. **Filtrar por rango de fechas** (inicio y fin) y cancha específica (opcional).
2. **Resumen**: Total de reservaciones e ingresos en el período.
3. **Tabla de ingresos**: Agrupado por día y tipo de cancha.
4. **Detalle de reservaciones**: Listado completo con datos de usuario y cancha.

---

## Capturas de Pantalla

*(Espacio reservado para capturas de pantalla)*

Las siguientes vistas están disponibles en la aplicación:

1. **Página Principal** (`index.php`)
   - Hero section con estadísticas (total canchas, disponibles, reserva rápida)
   - Tarjetas de canchas disponibles con botón "Reservar"

2. **Catálogo de Canchas** (`pages/canchas.php`)
   - Filtro por tipo de deporte
   - Tarjetas responsivas con precio y capacidad

3. **Reservación** (`pages/reservar.php`)
   - FullCalendar con disponibilidad visual (verde/rojo)
   - Slots horarios con precio
   - Canchas multiusos con selector de tipo de uso

4. **Pago Simulado** (`pages/pago.php`)
   - Cronómetro regresivo de 15 minutos
   - Resumen de la reservación
   - Datos de tarjeta simulados
   - Métodos de pago

5. **Perfil de Usuario** (`pages/perfil.php`)
   - Foto de perfil con carga de imagen
   - Formulario de edición de datos
   - Cambio de contraseña

6. **Dashboard Admin** (`admin/index.php`)
   - Cards con estadísticas numéricas
   - Acciones rápidas
   - Últimas actividades

7. **Gestión de Canchas** (`admin/canchas.php`)
   - Tabla CRUD
   - Modal de edición/creación

8. **Gestión de Horarios** (`admin/horarios.php`)
   - Selector de cancha
   - Formulario de generación por día
   - Tabla de horarios con toggle AJAX

9. **Gestión de Precios** (`admin/precios.php`)
   - Selector de cancha
   - Formulario de creación de precio diferenciado
   - Tabla de precios por tipo

10. **Reportes** (`admin/reportes.php`)
    - Filtros de fecha y cancha
    - Resumen de ingresos
    - Tabla de detalle

---

## Conclusión

El Sistema de Reservación de Canchas Deportivas es una aplicación web funcional que cubre el ciclo completo de reservación: desde el registro de usuarios hasta la generación de reportes administrativos. Está construido con PHP Orientado a Objetos, utiliza el patrón Singleton para la conexión a base de datos, implementa transacciones atómicas para operaciones críticas (pago + confirmación), y usa FullCalendar + AJAX para mejorar la experiencia de usuario en la selección de horarios.

La arquitectura modular (separación en clases, includes, páginas, API y assets) facilita el mantenimiento, la depuración y la escalabilidad futura del sistema.

---

*Documento generado el 29 de junio de 2026*
*Proyecto Final - Desarrollo de Aplicaciones Web*
