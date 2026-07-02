<?php
require_once __DIR__ . '/../config/database.php';

class Resena
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO resenas (reservacion_id, usuario_id, cancha_id, puntuacion, comentario)
                VALUES (:reservacion_id, :usuario_id, :cancha_id, :puntuacion, :comentario)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reservacion_id' => $datos['reservacion_id'],
            ':usuario_id' => $datos['usuario_id'],
            ':cancha_id' => $datos['cancha_id'],
            ':puntuacion' => $datos['puntuacion'],
            ':comentario' => $datos['comentario'] ?? '',
        ]);
    }

    public function obtenerPorReservacion($reservacionId)
    {
        $sql = "SELECT * FROM resenas WHERE reservacion_id = :reservacion_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':reservacion_id' => $reservacionId]);
        return $stmt->fetch();
    }

    public function obtenerPorCancha($canchaId)
    {
        $sql = "SELECT re.*, u.nombre as usuario_nombre, u.foto_perfil
                FROM resenas re
                JOIN usuarios u ON re.usuario_id = u.id
                WHERE re.cancha_id = :cancha_id
                ORDER BY re.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cancha_id' => $canchaId]);
        return $stmt->fetchAll();
    }

    public function promedioPorCancha($canchaId)
    {
        $sql = "SELECT ROUND(AVG(puntuacion), 1) as promedio, COUNT(*) as total
                FROM resenas WHERE cancha_id = :cancha_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cancha_id' => $canchaId]);
        return $stmt->fetch();
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM resenas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerTodas()
    {
        $sql = "SELECT re.*, u.nombre as usuario_nombre, c.nombre as cancha_nombre
                FROM resenas re
                JOIN usuarios u ON re.usuario_id = u.id
                JOIN canchas c ON re.cancha_id = c.id
                ORDER BY re.fecha DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
