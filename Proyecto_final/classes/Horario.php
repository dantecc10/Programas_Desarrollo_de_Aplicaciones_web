<?php
require_once __DIR__ . '/../config/database.php';

class Horario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO horarios (cancha_id, dia_semana, hora_inicio, hora_fin, estado) 
                VALUES (:cancha_id, :dia_semana, :hora_inicio, :hora_fin, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cancha_id' => $datos['cancha_id'],
            ':dia_semana' => $datos['dia_semana'],
            ':hora_inicio' => $datos['hora_inicio'],
            ':hora_fin' => $datos['hora_fin'],
            ':estado' => $datos['estado'] ?? 'disponible'
        ]);
        return $this->db->lastInsertId();
    }

    public function obtenerPorCancha($canchaId)
    {
        $sql = "SELECT * FROM horarios WHERE cancha_id = :cancha_id ORDER BY dia_semana, hora_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cancha_id' => $canchaId]);
        return $stmt->fetchAll();
    }

    public function obtenerDisponibles($canchaId, $fecha)
    {
        $diaSemana = date('N', strtotime($fecha));

        $sql = "SELECT h.* FROM horarios h 
                WHERE h.cancha_id = :cancha_id 
                AND h.dia_semana = :dia_semana 
                AND h.estado = 'disponible'
                AND h.hora_inicio NOT IN (
                    SELECT r.hora_inicio FROM reservaciones r 
                    WHERE r.cancha_id = :cancha_id2 
                    AND r.fecha = :fecha 
                    AND r.estado IN ('pendiente', 'confirmada')
                )
                ORDER BY h.hora_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cancha_id' => $canchaId,
            ':dia_semana' => $diaSemana,
            ':cancha_id2' => $canchaId,
            ':fecha' => $fecha
        ]);
        return $stmt->fetchAll();
    }

    public function obtenerConDisponibilidad($canchaId, $fecha)
    {
        require_once __DIR__ . '/Reservacion.php';
        $reservaClean = new Reservacion();
        $reservaClean->autoCancelarExpiradas();

        $diaSemana = date('N', strtotime($fecha));

        $sql = "SELECT h.*, 
                    CASE WHEN h.estado != 'disponible' THEN 1
                         WHEN r.id IS NOT NULL THEN 1
                         ELSE 0 
                    END as ocupado
                FROM horarios h 
                LEFT JOIN reservaciones r ON r.cancha_id = h.cancha_id 
                    AND r.fecha = :fecha 
                    AND r.hora_inicio = h.hora_inicio
                    AND r.estado IN ('pendiente', 'confirmada')
                WHERE h.cancha_id = :cancha_id2 
                AND h.dia_semana = :dia_semana 
                ORDER BY h.hora_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha' => $fecha,
            ':cancha_id2' => $canchaId,
            ':dia_semana' => $diaSemana
        ]);
        return $stmt->fetchAll();
    }

    public function actualizar($id, $datos)
    {
        $campos = [];
        $params = [':id' => $id];
        foreach (['hora_inicio', 'hora_fin', 'estado'] as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $datos[$campo];
            }
        }
        if (!empty($campos)) {
            $sql = "UPDATE horarios SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        return false;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM horarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function eliminarPorCancha($canchaId)
    {
        $sql = "DELETE FROM horarios WHERE cancha_id = :cancha_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':cancha_id' => $canchaId]);
    }

    public function generarHorarios($canchaId, $diaSemana, $horaInicio, $horaFin, $intervaloMinutos = 60)
    {
        $inicio = new DateTime($horaInicio);
        $fin = new DateTime($horaFin);
        $intervalo = new DateInterval("PT{$intervaloMinutos}M");

        $horarios = [];
        while ($inicio < $fin) {
            $hasta = clone $inicio;
            $hasta->add($intervalo);
            if ($hasta > $fin) break;

            $horarios[] = [
                'cancha_id' => $canchaId,
                'dia_semana' => $diaSemana,
                'hora_inicio' => $inicio->format('H:i:s'),
                'hora_fin' => $hasta->format('H:i:s'),
                'estado' => 'disponible'
            ];
            $inicio = $hasta;
        }
        return $horarios;
    }
}
