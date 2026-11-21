-- Migracion: turnos de caja por cajero
-- Los vinculos son opcionales para conservar las transacciones historicas,
-- que no tienen datos suficientes para reconstruir a que turno pertenecian.

USE inventario_ventas;

CREATE TABLE IF NOT EXISTS `vb_turnos_caja` (
  `id_turno` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `base_inicial` decimal(14,2) NOT NULL DEFAULT 0.00,
  `abierto_en` datetime NOT NULL DEFAULT current_timestamp(),
  `cerrado_en` datetime DEFAULT NULL,
  `estado` enum('ABIERTO','CERRADO') NOT NULL DEFAULT 'ABIERTO',
  `efectivo_esperado` decimal(14,2) DEFAULT NULL,
  `efectivo_contado` decimal(14,2) DEFAULT NULL,
  `diferencia` decimal(14,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_turno`),
  KEY `idx_turno_fecha_usuario` (`fecha`, `id_usuario`),
  KEY `idx_turno_usuario_estado` (`id_usuario`, `estado`),
  CONSTRAINT `fk_turno_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `vb_facturas`
  ADD COLUMN `id_turno` int(10) unsigned DEFAULT NULL AFTER `id_vendedor`,
  ADD KEY `idx_factura_turno` (`id_turno`),
  ADD CONSTRAINT `fk_factura_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `vb_facturas_pagos`
  ADD COLUMN `id_turno` int(10) unsigned DEFAULT NULL AFTER `id_usuario`,
  ADD KEY `idx_fpago_turno` (`id_turno`),
  ADD CONSTRAINT `fk_fpago_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `vb_devoluciones`
  ADD COLUMN `id_turno` int(10) unsigned DEFAULT NULL AFTER `id_usuario`,
  ADD KEY `idx_devolucion_turno` (`id_turno`),
  ADD CONSTRAINT `fk_devolucion_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `vb_devoluciones_pagos`
  ADD COLUMN `id_turno` int(10) unsigned DEFAULT NULL AFTER `id_usuario`,
  ADD KEY `idx_dpago_turno` (`id_turno`),
  ADD CONSTRAINT `fk_dpago_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `vb_egresos`
  ADD COLUMN `metodo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO' AFTER `valor`,
  ADD COLUMN `id_turno` int(10) unsigned DEFAULT NULL AFTER `id_usuario`,
  ADD KEY `idx_egreso_turno` (`id_turno`),
  ADD CONSTRAINT `fk_egreso_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;
