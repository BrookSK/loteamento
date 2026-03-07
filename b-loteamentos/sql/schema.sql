-- B Loteamentos - Schema completo
-- MySQL 8.0+

-- TABELA: users
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','profissional','corretor') NOT NULL,
    active      TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- TABELA: projects
CREATE TABLE projects (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    location        VARCHAR(500),
    description     TEXT,
    cover_image     VARCHAR(500),
    original_image  VARCHAR(500),
    svg_file        VARCHAR(500),
    svg_raw         LONGTEXT,
    status          ENUM('draft','active','inactive') DEFAULT 'draft',
    created_by      INT UNSIGNED,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- TABELA: lots
CREATE TABLE lots (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      INT UNSIGNED NOT NULL,
    polygon_id      VARCHAR(100) NOT NULL,
    numero_lote     VARCHAR(50),
    quadra          VARCHAR(50),
    area_m2         DECIMAL(10,2),
    frente_m        DECIMAL(8,2),
    fundo_m         DECIMAL(8,2),
    lateral_esq_m   DECIMAL(8,2),
    lateral_dir_m   DECIMAL(8,2),
    valor           DECIMAL(12,2),
    status          ENUM('disponivel','reservado','vendido','indisponivel') DEFAULT 'disponivel',
    observacoes     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- TABELA: reservations
CREATE TABLE reservations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lot_id          INT UNSIGNED NOT NULL,
    corretor_id     INT UNSIGNED NOT NULL,
    buyer_name      VARCHAR(255),
    buyer_phone     VARCHAR(50),
    buyer_email     VARCHAR(255),
    expires_at      DATETIME NOT NULL,
    status          ENUM('active','expired','converted','cancelled') DEFAULT 'active',
    notes           TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE CASCADE,
    FOREIGN KEY (corretor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- TABELA: sales
CREATE TABLE sales (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lot_id          INT UNSIGNED NOT NULL,
    reservation_id  INT UNSIGNED,
    corretor_id     INT UNSIGNED NOT NULL,
    buyer_name      VARCHAR(255) NOT NULL,
    buyer_document  VARCHAR(50),
    buyer_phone     VARCHAR(50),
    buyer_email     VARCHAR(255),
    sale_date       DATE NOT NULL,
    final_value     DECIMAL(12,2) NOT NULL,
    payment_method  VARCHAR(100),
    notes           TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE CASCADE,
    FOREIGN KEY (corretor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
);

-- TABELA: lot_history
CREATE TABLE lot_history (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lot_id      INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED,
    action      VARCHAR(100) NOT NULL,
    old_value   JSON,
    new_value   JSON,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
