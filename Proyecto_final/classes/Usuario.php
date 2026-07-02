<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function registrar($datos)
    {
        $sql = "INSERT INTO usuarios (nombre, email, password, telefono, rol) VALUES (:nombre, :email, :password, :telefono, :rol)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':password' => password_hash($datos['password'], PASSWORD_DEFAULT),
            ':telefono' => $datos['telefono'] ?? '',
            ':rol' => 'cliente'
        ]);
        return $this->db->lastInsertId();
    }

    public function login($email, $password)
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            return $usuario;
        }
        return false;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerTodos()
    {
        return $this->obtenerPaginas(0, 0)['datos'];
    }

    public function obtenerPaginas($pagina = 1, $porPagina = 20, $filtros = [])
    {
        $where = [];
        $params = [];

        if (!empty($filtros['busqueda'])) {
            $where[] = "(nombre LIKE :busqueda OR email LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }
        if (!empty($filtros['rol'])) {
            $where[] = "rol = :rol";
            $params[':rol'] = $filtros['rol'];
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $where[] = "activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(*) FROM usuarios $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        if ($porPagina <= 0) {
            $pagina = 1;
            $porPagina = $total > 0 ? $total : 1;
        }

        $totalPaginas = max(1, (int)ceil($total / $porPagina));
        $pagina = max(1, min($pagina, $totalPaginas));
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT * FROM usuarios $whereClause ORDER BY fecha_registro DESC LIMIT $porPagina OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll();

        return [
            'datos' => $datos,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => $totalPaginas,
        ];
    }

    public function actualizar($id, $datos)
    {
        $campos = [];
        $params = [':id' => $id];
        foreach (['nombre', 'email', 'telefono', 'foto_perfil', 'rol', 'activo'] as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $datos[$campo];
            }
        }
        if (!empty($campos)) {
            $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        return false;
    }

    public function cambiarPassword($id, $password)
    {
        $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $id
        ]);
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerPorEmail($email)
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function cambiarPasswordPorEmail($email, $password)
    {
        $sql = "UPDATE usuarios SET password = :password WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':email' => $email
        ]);
    }

    public function actualizarUltimoAcceso($id)
    {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function solicitarEliminacion($id)
    {
        $sql = "UPDATE usuarios SET solicitud_eliminacion = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function cancelarSolicitudEliminacion($id)
    {
        $sql = "UPDATE usuarios SET solicitud_eliminacion = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function autoEliminarCuentas($diasInactividad = 90, $diasSolicitud = 30)
    {
        $total = 0;

        $sql = "DELETE FROM usuarios WHERE solicitud_eliminacion IS NOT NULL 
                AND solicitud_eliminacion < DATE_SUB(NOW(), INTERVAL :dias DAY)
                AND rol = 'cliente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dias' => $diasSolicitud]);
        $total += $stmt->rowCount();

        $sql = "DELETE FROM usuarios WHERE ultimo_acceso IS NULL 
                AND fecha_registro < DATE_SUB(NOW(), INTERVAL :dias2 DAY)
                AND rol = 'cliente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dias2' => $diasInactividad]);
        $total += $stmt->rowCount();

        $sql = "DELETE FROM usuarios WHERE ultimo_acceso IS NOT NULL 
                AND ultimo_acceso < DATE_SUB(NOW(), INTERVAL :dias3 DAY)
                AND rol = 'cliente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dias3' => $diasInactividad]);
        $total += $stmt->rowCount();

        return $total;
    }

    public function emailExiste($email)
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    public function contarClientes()
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'";
        return $this->db->query($sql)->fetchColumn();
    }
}
