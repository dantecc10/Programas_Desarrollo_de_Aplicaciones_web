<?php
require_once __DIR__ . '/../config/database.php';

class Cancha
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO canchas (nombre, tipo, descripcion, precio_por_hora, capacidad, imagen, estado) 
                VALUES (:nombre, :tipo, :descripcion, :precio_por_hora, :capacidad, :imagen, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':tipo' => $datos['tipo'],
            ':descripcion' => $datos['descripcion'] ?? '',
            ':precio_por_hora' => $datos['precio_por_hora'],
            ':capacidad' => $datos['capacidad'] ?? 10,
            ':imagen' => $datos['imagen'] ?? '',
            ':estado' => $datos['estado'] ?? 'disponible'
        ]);
        return $this->db->lastInsertId();
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM canchas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerTodas($soloDisponibles = false)
    {
        $sql = "SELECT * FROM canchas";
        if ($soloDisponibles) {
            $sql .= " WHERE estado = 'disponible'";
        }
        $sql .= " ORDER BY tipo, nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerTipos()
    {
        $sql = "SELECT DISTINCT tipo FROM canchas ORDER BY tipo";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function actualizar($id, $datos)
    {
        $campos = [];
        $params = [':id' => $id];
        foreach (['nombre', 'tipo', 'descripcion', 'precio_por_hora', 'capacidad', 'imagen', 'estado'] as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $datos[$campo];
            }
        }
        if (!empty($campos)) {
            $sql = "UPDATE canchas SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        return false;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM canchas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function contar()
    {
        return $this->db->query("SELECT COUNT(*) FROM canchas")->fetchColumn();
    }

    public function contarDisponibles()
    {
        return $this->db->query("SELECT COUNT(*) FROM canchas WHERE estado = 'disponible'")->fetchColumn();
    }

    public function resolverImagen($cancha)
    {
        if (!empty($cancha['imagen'])) {
            return $cancha['imagen'];
        }
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($extensions as $ext) {
            $file = CANCHAS_IMG_DIR . 'cancha_' . $cancha['id'] . '.' . $ext;
            if (file_exists($file)) {
                return 'cancha_' . $cancha['id'] . '.' . $ext;
            }
        }
        return '';
    }
}
