-- Payben-style Recharge Portal — Database Schema
-- Import this once into MySQL before using the app:
--   mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS payben_portal
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE payben_portal;

-- Retailers (distributors) and the single admin account
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('admin','retailer') NOT NULL DEFAULT 'retailer',
    full_name       VARCHAR(100) NOT NULL,
    shop_name       VARCHAR(150) DEFAULT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(15)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    wallet_balance  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status          ENUM('pending','active','blocked') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Telecom / DTH operators and the commission a retailer earns per recharge
CREATE TABLE IF NOT EXISTS operators (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(50) NOT NULL,
    type                ENUM('mobile','dth') NOT NULL,
    commission_percent  DECIMAL(5,2) NOT NULL DEFAULT 2.00,
    status              ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

-- Every recharge attempt a retailer makes
CREATE TABLE IF NOT EXISTS transactions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    txn_ref             VARCHAR(20) NOT NULL UNIQUE,
    user_id             INT NOT NULL,
    operator_id         INT NOT NULL,
    recharge_number     VARCHAR(15) NOT NULL,
    amount              DECIMAL(10,2) NOT NULL,
    commission_earned   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status              ENUM('success','failed','pending') NOT NULL DEFAULT 'pending',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (operator_id) REFERENCES operators(id)
) ENGINE=InnoDB;

-- Wallet top-up requests a retailer submits, approved by the admin
CREATE TABLE IF NOT EXISTS fund_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    method          VARCHAR(50) NOT NULL DEFAULT 'Bank Transfer (Demo)',
    reference_note  VARCHAR(150) DEFAULT NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at    TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Seed operators (safe to re-run: skips if already present)
INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'Airtel' AS name, 'mobile' AS type, 2.50 AS commission_percent) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'Airtel' AND type = 'mobile') LIMIT 1;

INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'Jio', 'mobile', 2.00) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'Jio' AND type = 'mobile') LIMIT 1;

INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'Vi (Vodafone Idea)', 'mobile', 2.25) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'Vi (Vodafone Idea)' AND type = 'mobile') LIMIT 1;

INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'BSNL', 'mobile', 3.00) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'BSNL' AND type = 'mobile') LIMIT 1;

INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'Tata Play DTH', 'dth', 4.00) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'Tata Play DTH' AND type = 'dth') LIMIT 1;

INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'Airtel Digital TV', 'dth', 4.00) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'Airtel Digital TV' AND type = 'dth') LIMIT 1;

INSERT INTO operators (name, type, commission_percent)
SELECT * FROM (SELECT 'Dish TV', 'dth', 3.50) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM operators WHERE name = 'Dish TV' AND type = 'dth') LIMIT 1;
