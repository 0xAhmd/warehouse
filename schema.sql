-- ============================================================
-- Warehouse System — Database Schema (v2, JWT/API edition)
-- ============================================================

CREATE DATABASE IF NOT EXISTS warehouse_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warehouse_db;

-- ── Users ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)           NOT NULL,
    email      VARCHAR(150)           NOT NULL UNIQUE,
    password   VARCHAR(255)           NOT NULL,   -- bcrypt
    role       ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    is_active  TINYINT(1)             NOT NULL DEFAULT 1,
    created_at DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Default admin — password: admin123  (CHANGE THIS)
INSERT INTO users (name, email, password, role) VALUES (
    'Administrator',
    'admin@warehouse.local',
    '$2y$12$Kixh4L3n8F0Xfr5fNtF5QeO3wK7G1zRj5S6Jv8C9YtDQk8Px1mW6',
    'admin'
);

-- ── Products ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku             VARCHAR(100)   NULL UNIQUE,
    name            VARCHAR(200)   NOT NULL,
    price           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    quantity        INT UNSIGNED   NOT NULL DEFAULT 0,
    faulty_quantity INT UNSIGNED   NOT NULL DEFAULT 0,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ── Stock Movements ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS stock_movements (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    type       ENUM('in','out','return','faulty_in','faulty_out') NOT NULL,
    quantity   INT UNSIGNED NOT NULL,
    note       VARCHAR(255) NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
