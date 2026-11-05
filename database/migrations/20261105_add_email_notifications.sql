ALTER TABLE solicitudes
    ADD COLUMN email_estado ENUM('pendiente', 'enviado', 'error') NOT NULL DEFAULT 'pendiente' AFTER whatsapp_actualizado_en,
    ADD COLUMN email_error TEXT NULL AFTER email_estado,
    ADD COLUMN email_intentos SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER email_error,
    ADD COLUMN email_actualizado_en DATETIME NULL AFTER email_intentos,
    ADD KEY idx_solicitudes_email (email_estado);
