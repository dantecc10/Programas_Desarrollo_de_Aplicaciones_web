ALTER TABLE usuarios
ADD COLUMN ultimo_acceso DATETIME DEFAULT NULL AFTER activo,
ADD COLUMN solicitud_eliminacion DATETIME DEFAULT NULL AFTER ultimo_acceso;
