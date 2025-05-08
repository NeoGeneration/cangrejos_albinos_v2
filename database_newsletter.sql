-- Crear tabla de suscriptores al newsletter
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  confirmation_token VARCHAR(255) NOT NULL,
  is_confirmed TINYINT(1) NOT NULL DEFAULT 0,
  subscribe_date DATETIME NOT NULL,
  confirm_date DATETIME NULL,
  ip_address VARCHAR(45) NULL,
  UNIQUE KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla para campañas de newsletter
CREATE TABLE IF NOT EXISTS newsletter_campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  send_date DATETIME NULL,
  created_date DATETIME NOT NULL,
  status ENUM('draft', 'sent', 'scheduled') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;