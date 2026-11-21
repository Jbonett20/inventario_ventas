-- Migracion: vencimientos y pagos de facturas de venta y compra
-- El estado operativo de las facturas se mantiene sin cambios.

USE inventario_ventas;

-- Una factura de venta puede estar relacionada con un credito de cliente.
-- La fecha de vencimiento de la venta se conserva en vb_creditos.fecha_fin.
ALTER TABLE `vb_facturas`
  ADD COLUMN `id_credito` int(10) unsigned DEFAULT NULL AFTER `id_turno`,
  ADD UNIQUE KEY `uq_factura_credito` (`id_credito`),
  ADD CONSTRAINT `fk_factura_credito` FOREIGN KEY (`id_credito`) REFERENCES `vb_creditos` (`id_credito`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- El vencimiento y los pagos son independientes de vb_ingresos_factura.estado,
-- que indica si los productos fueron aplicados al inventario.
ALTER TABLE `vb_ingresos_factura`
  ADD COLUMN `fecha_vencimiento` date DEFAULT NULL AFTER `fecha`,
  ADD KEY `idx_ingreso_factura_vencimiento` (`fecha_vencimiento`);

CREATE TABLE IF NOT EXISTS `vb_ingresos_factura_pagos` (
  `id_pago_ingreso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ingreso_factura` int(10) unsigned NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `id_turno` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago_ingreso`),
  KEY `idx_ifpago_factura` (`id_ingreso_factura`),
  KEY `idx_ifpago_fecha` (`fecha_pago`),
  KEY `idx_ifpago_turno` (`id_turno`),
  CONSTRAINT `fk_ifpago_factura` FOREIGN KEY (`id_ingreso_factura`) REFERENCES `vb_ingresos_factura` (`id_ingreso_factura`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ifpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ifpago_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
