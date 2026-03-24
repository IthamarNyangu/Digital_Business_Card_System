CREATE DATABASE IF NOT EXISTS rtc_digital_cards
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rtc_digital_cards;

DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_number VARCHAR(50) NULL,
    honorific VARCHAR(20) NULL,
    suffix VARCHAR(30) NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    organization VARCHAR(150) NOT NULL,
    title VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    street VARCHAR(150) NOT NULL,
    city VARCHAR(120) NOT NULL,
    region VARCHAR(120) NOT NULL,
    postal_code VARCHAR(30) NOT NULL,
    country VARCHAR(120) NOT NULL,
    mecard_payload TEXT NULL,
    qr_code_path VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_employees_employee_number (employee_number),
    UNIQUE KEY uq_employees_phone (phone),
    UNIQUE KEY uq_employees_email (email),
    KEY idx_employees_name (last_name, first_name),
    KEY idx_employees_organization (organization),
    KEY idx_employees_title (title)
);
