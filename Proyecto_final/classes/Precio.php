<?php
require_once __DIR__ . '/../config/database.php';

class Precio
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function obtenerPorCancha($canchaId)
    {
        $sql = "SELECT * FROM precios WHERE cancha_id = :cancha_id AND activo = 1 ORDER BY FIELD(tipo_precio, 'regular', 'pico', 'finde')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cancha_id' => $canchaId]);
        return $stmt->fetchAll();
    }

    public function obtenerPorCanchaAdmin($canchaId)
    {
        $sql = "SELECT * FROM precios WHERE cancha_id = :cancha_id ORDER BY FIELD(tipo_precio, 'regular', 'pico', 'finde'), id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cancha_id' => $canchaId]);
        return $stmt->fetchAll();
    }

    public function obtenerPrecio($canchaId, $fecha, $hora)
    {
        $diaSemana = date('N', strtotime($fecha));
        $sql = "SELECT * FROM precios 
                WHERE cancha_id = :cancha_id AND activo = 1
                AND (dia_semana_inicio IS NULL OR dia_semana_inicio <= :dia)
                AND (dia_semana_fin IS NULL OR dia_semana_fin >= :dia)
                AND (hora_inicio IS NULL OR hora_fin IS NULL OR (:hora BETWEEN hora_inicio AND hora_fin))
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cancha_id' => $canchaId,
            ':dia' => $diaSemana,
            ':hora' => $hora
        ]);
        $resultado = $stmt->fetch();
        if ($resultado) {
            return $resultado;
        }
        $sqlDefault = "SELECT * FROM precios WHERE cancha_id = :cancha_id AND tipo_precio = 'regular' AND activo = 1 LIMIT 1";
        $stmtDefault = $this->db->prepare($sqlDefault);
        $stmtDefault->execute([':cancha_id' => $canchaId]);
        return $stmtDefault->fetch();
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO precios (cancha_id, tipo_precio, nombre, precio, dia_semana_inicio, dia_semana_fin, hora_inicio, hora_fin, fecha_inicio, fecha_fin) 
                VALUES (:cancha_id, :tipo_precio, :nombre, :precio, :dia_semana_inicio, :dia_semana_fin, :hora_inicio, :hora_fin, :fecha_inicio, :fecha_fin)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cancha_id' => $datos['cancha_id'],
            ':tipo_precio' => $datos['tipo_precio'],
            ':nombre' => $datos['nombre'],
            ':precio' => $datos['precio'],
            ':dia_semana_inicio' => $datos['dia_semana_inicio'] ?? null,
            ':dia_semana_fin' => $datos['dia_semana_fin'] ?? null,
            ':hora_inicio' => $datos['hora_inicio'] ?? null,
            ':hora_fin' => $datos['hora_fin'] ?? null,
            ':fecha_inicio' => $datos['fecha_inicio'] ?? null,
            ':fecha_fin' => $datos['fecha_fin'] ?? null
        ]);
    }

    public function actualizar($id, $datos)
    {
        $campos = [];
        $params = [':id' => $id];
        foreach (['tipo_precio', 'nombre', 'precio', 'dia_semana_inicio', 'dia_semana_fin', 'hora_inicio', 'hora_fin', 'fecha_inicio', 'fecha_fin', 'activo'] as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $datos[$campo];
            }
        }
        if (!empty($campos)) {
            $sql = "UPDATE precios SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        return false;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM precios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerTodos()
    {
        $sql = "SELECT p.*, c.nombre as cancha_nombre FROM precios p JOIN canchas c ON p.cancha_id = c.id ORDER BY c.nombre, p.tipo_precio";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}