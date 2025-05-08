-- Consulta para verificar si la columna confirmation_token ya existe
SELECT COUNT(*) INTO @exists_confirmation_token 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'reservations' 
AND column_name = 'confirmation_token';

-- Agregar columna confirmation_token si no existe
SET @query = IF(@exists_confirmation_token = 0, 
                'ALTER TABLE reservations ADD COLUMN confirmation_token VARCHAR(64) DEFAULT NULL', 
                'SELECT "Column confirmation_token already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Consulta para verificar si la columna email_confirmed ya existe
SELECT COUNT(*) INTO @exists_email_confirmed 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'reservations' 
AND column_name = 'email_confirmed';

-- Agregar columna email_confirmed si no existe
SET @query = IF(@exists_email_confirmed = 0, 
                'ALTER TABLE reservations ADD COLUMN email_confirmed BOOLEAN DEFAULT 0', 
                'SELECT "Column email_confirmed already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Consulta para verificar si el índice idx_confirmation_token ya existe
SELECT COUNT(*) INTO @exists_idx_confirmation_token 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
AND table_name = 'reservations' 
AND index_name = 'idx_confirmation_token';

-- Agregar índice si no existe
SET @query = IF(@exists_idx_confirmation_token = 0, 
                'ALTER TABLE reservations ADD INDEX idx_confirmation_token (confirmation_token)', 
                'SELECT "Index idx_confirmation_token already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Consulta para verificar el tipo de la columna status
SELECT COLUMN_TYPE INTO @status_column_type 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'reservations' 
AND column_name = 'status';

-- Modificar la columna status si es necesario
SET @query = IF(@status_column_type != "enum('confirmed','cancelled','email_pending')", 
                'ALTER TABLE reservations MODIFY COLUMN status ENUM("confirmed", "cancelled", "email_pending") DEFAULT "email_pending"', 
                'SELECT "Column status already has the correct type"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Explicación de los estados:
-- - email_pending: Reserva registrada, pero email no confirmado
-- - pending: Email confirmado, pero pendiente de aprobación por admin
-- - confirmed: Reserva completamente confirmada
-- - cancelled: Reserva cancelada