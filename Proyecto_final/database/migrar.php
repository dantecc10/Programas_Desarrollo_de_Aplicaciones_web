<?php
/**
 * Sistema de migraciones SQL
 *
 * Uso: php migrar.php
 * Crea/actualiza la tabla migraciones y aplica archivos .sql
 * en database/migraciones/ que no se hayan ejecutado.
 */

require_once __DIR__ . '/../config/config.php';

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $db->exec("CREATE TABLE IF NOT EXISTS migraciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        archivo VARCHAR(255) NOT NULL UNIQUE,
        aplicada_en DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $aplicadas = $db->query("SELECT archivo FROM migraciones")->fetchAll(PDO::FETCH_COLUMN);

    $dir = __DIR__ . '/migraciones';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $archivos = glob($dir . '/*.sql');
    sort($archivos);

    $contador = 0;
    foreach ($archivos as $archivo) {
        $nombre = basename($archivo);
        if (in_array($nombre, $aplicadas, true)) {
            continue;
        }

        echo "Aplicando: $nombre ... ";

        $sql = file_get_contents($archivo);
        $sentencias = explode(';', $sql);

        try {
            foreach ($sentencias as $sentencia) {
                $sentencia = trim($sentencia);
                if (!empty($sentencia)) {
                    $db->exec($sentencia);
                }
            }
            $stmt = $db->prepare("INSERT INTO migraciones (archivo) VALUES (:archivo)");
            $stmt->execute([':archivo' => $nombre]);
            echo "OK\n";
            $contador++;
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    if ($contador === 0) {
        echo "Todo al día. No hay migraciones pendientes.\n";
    } else {
        echo "\n$contador migración(es) aplicada(s) correctamente.\n";
    }

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}
