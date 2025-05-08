-- Alteraciones directas a la tabla reservations

-- Agregar columna confirmation_token si no existe
ALTER TABLE reservations ADD COLUMN confirmation_token VARCHAR(64) DEFAULT NULL;

-- Agregar columna email_confirmed si no existe
ALTER TABLE reservations ADD COLUMN email_confirmed BOOLEAN DEFAULT 0;

-- Agregar índice para búsquedas rápidas
ALTER TABLE reservations ADD INDEX idx_confirmation_token (confirmation_token);

-- Modificar el tipo de la columna status - eliminamos "pending" ya que no lo usamos
ALTER TABLE reservations MODIFY COLUMN status ENUM('confirmed', 'cancelled', 'email_pending') DEFAULT 'email_pending';

-- Nota: Este archivo contiene instrucciones directas que pueden fallar si las columnas ya existen
-- Cada instrucción se debe ejecutar individualmente si hay errores