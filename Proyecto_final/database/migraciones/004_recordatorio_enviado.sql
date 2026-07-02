ALTER TABLE reservaciones
ADD COLUMN recordatorio_enviado TINYINT(1) NOT NULL DEFAULT 0 AFTER observaciones;
