<?php
require_once __DIR__ . '/../config/database.php';

class Reservacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear($datos)
    {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO reservaciones (usuario_id, cancha_id, fecha, hora_inicio, hora_fin, tipo_uso, total, observaciones) 
                    VALUES (:usuario_id, :cancha_id, :fecha, :hora_inicio, :hora_fin, :tipo_uso, :total, :observaciones)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $datos['usuario_id'],
                ':cancha_id' => $datos['cancha_id'],
                ':fecha' => $datos['fecha'],
                ':hora_inicio' => $datos['hora_inicio'],
                ':hora_fin' => $datos['hora_fin'],
                ':tipo_uso' => $datos['tipo_uso'] ?? null,
                ':total' => $datos['total'],
                ':observaciones' => $datos['observaciones'] ?? ''
            ]);
            $reservacionId = $this->db->lastInsertId();

            $sqlPago = "INSERT INTO pagos (reservacion_id, monto, metodo_pago, estado_pago) 
                        VALUES (:reservacion_id, :monto, 'tarjeta', 'pendiente')";
            $stmtPago = $this->db->prepare($sqlPago);
            $stmtPago->execute([
                ':reservacion_id' => $reservacionId,
                ':monto' => $datos['total']
            ]);

            $this->db->commit();
            return $reservacionId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT r.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo, u.nombre as usuario_nombre, u.email 
                FROM reservaciones r 
                JOIN canchas c ON r.cancha_id = c.id 
                JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT r.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo 
                FROM reservaciones r 
                JOIN canchas c ON r.cancha_id = c.id 
                WHERE r.usuario_id = :usuario_id 
                ORDER BY r.fecha DESC, r.hora_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function obtenerTodas()
    {
        $sql = "SELECT r.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo, u.nombre as usuario_nombre, u.email 
                FROM reservaciones r 
                JOIN canchas c ON r.cancha_id = c.id 
                JOIN usuarios u ON r.usuario_id = u.id 
                ORDER BY r.fecha_reservacion DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function actualizarEstado($id, $estado)
    {
        $sql = "UPDATE reservaciones SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function autoCancelarExpiradas()
    {
        $sql = "UPDATE reservaciones SET estado = 'cancelada' WHERE estado = 'pendiente' AND fecha_reservacion < DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        return $this->db->exec($sql);
    }

    public function verificarDisponibilidad($canchaId, $fecha, $horaInicio, $horaFin, $excluirId = null)
    {
        $this->autoCancelarExpiradas();
        $sql = "SELECT COUNT(*) FROM reservaciones 
                WHERE cancha_id = :cancha_id 
                AND fecha = :fecha 
                AND hora_inicio = :hora_inicio
                AND estado IN ('pendiente', 'confirmada')";
        $params = [
            ':cancha_id' => $canchaId,
            ':fecha' => $fecha,
            ':hora_inicio' => $horaInicio
        ];
        if ($excluirId) {
            $sql .= " AND id != :excluir_id";
            $params[':excluir_id'] = $excluirId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    public function contarPorEstado($estado = null)
    {
        $sql = "SELECT COUNT(*) FROM reservaciones";
        if ($estado) {
            $sql .= " WHERE estado = :estado";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':estado' => $estado]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchColumn();
    }

    public function obtenerReservacionesPorFecha($fechaInicio, $fechaFin)
    {
        $sql = "SELECT r.*, c.nombre as cancha_nombre, u.nombre as usuario_nombre 
                FROM reservaciones r 
                JOIN canchas c ON r.cancha_id = c.id 
                JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.fecha BETWEEN :inicio AND :fin 
                ORDER BY r.fecha, r.hora_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
        return $stmt->fetchAll();
    }

    public function obtenerReservacionesCreadasEntre($fechaInicio, $fechaFin)
    {
        $sql = "SELECT r.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo, u.nombre as usuario_nombre, u.email
                FROM reservaciones r 
                JOIN canchas c ON r.cancha_id = c.id 
                JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.fecha_reservacion BETWEEN :inicio AND :fin 
                ORDER BY r.fecha_reservacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':inicio' => $fechaInicio . ' 00:00:00', ':fin' => $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll();
    }

    public function obtenerReservacionesPorCancha($canchaId, $fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT r.*, u.nombre as usuario_nombre FROM reservaciones r 
                JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.cancha_id = :cancha_id";
        $params = [':cancha_id' => $canchaId];
        if ($fechaInicio && $fechaFin) {
            $sql .= " AND r.fecha BETWEEN :inicio AND :fin";
            $params[':inicio'] = $fechaInicio;
            $params[':fin'] = $fechaFin;
        }
        $sql .= " ORDER BY r.fecha DESC, r.hora_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function historialPorUsuario($usuarioId)
    {
        $sql = "SELECT r.*, c.nombre as cancha_nombre, c.tipo as cancha_tipo, p.estado_pago, p.monto as pago_monto, p.metodo_pago
                FROM reservaciones r 
                JOIN canchas c ON r.cancha_id = c.id 
                LEFT JOIN pagos p ON r.id = p.reservacion_id 
                WHERE r.usuario_id = :usuario_id 
                ORDER BY r.fecha_reservacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM reservaciones WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function reporteIngresos($fechaInicio, $fechaFin)
    {
        $sql = "SELECT DATE(p.fecha_pago) as dia, c.tipo, COUNT(r.id) as total_reservaciones, SUM(p.monto) as ingreso_total
                FROM reservaciones r
                JOIN canchas c ON r.cancha_id = c.id
                JOIN pagos p ON r.id = p.reservacion_id
                WHERE p.estado_pago = 'completado'
                AND p.fecha_pago BETWEEN :inicio AND :fin
                GROUP BY DATE(p.fecha_pago), c.tipo
                ORDER BY dia";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':inicio' => $fechaInicio . ' 00:00:00', ':fin' => $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll();
    }
}
