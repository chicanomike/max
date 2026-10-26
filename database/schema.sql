CREATE DATABASE IF NOT EXISTS sistema_web
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sistema_web;

CREATE TABLE IF NOT EXISTS solicitudes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(254) NOT NULL,
    servicio VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_registro DATETIME NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'completada') NOT NULL DEFAULT 'pendiente',
    whatsapp_estado ENUM('pendiente', 'enviado', 'error') NOT NULL DEFAULT 'pendiente',
    whatsapp_error TEXT NULL,
    whatsapp_intentos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    whatsapp_actualizado_en DATETIME NULL,
    clave_envio CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_solicitudes_clave_envio (clave_envio),
    KEY idx_solicitudes_nombre (nombre),
    KEY idx_solicitudes_telefono (telefono),
    KEY idx_solicitudes_servicio (servicio),
    KEY idx_solicitudes_fecha (fecha_registro),
    KEY idx_solicitudes_estado (estado),
    KEY idx_solicitudes_whatsapp (whatsapp_estado)
) ENGINE=InnoDB;