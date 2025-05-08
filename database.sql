-- La creación de la base de datos y su selección ya se manejan en el script PHP
-- Este archivo solo contiene las definiciones de tablas

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    dni VARCHAR(15) NOT NULL,
    num_tickets INT NOT NULL DEFAULT 1,
    comments TEXT,
    privacy_accepted BOOLEAN NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    confirmation_code VARCHAR(32) NOT NULL,
    UNIQUE KEY (confirmation_code),
    INDEX (email),
    INDEX (dni)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (username),
    UNIQUE KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: change_me_immediately)
-- Esta consulta se gestionará directamente en PHP para garantizar que no haya duplicados