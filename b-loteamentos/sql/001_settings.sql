-- Migration: 001_settings
-- Tabela de configurações do sistema (chaves de API, parâmetros globais)

CREATE TABLE IF NOT EXISTS settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`       VARCHAR(100) NOT NULL UNIQUE,
    `value`     TEXT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (`key`, `value`) VALUES
('vectorizer_api_id', NULL),
('vectorizer_api_secret', NULL),
('reservation_hours', '48')
ON DUPLICATE KEY UPDATE `key` = VALUES(`key`);
