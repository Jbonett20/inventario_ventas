-- ============================================================
-- Migracion: control de precios de compra y venta por producto
-- Fecha: 2026-09-12
--
-- OBJETIVO
--   1. Saber a que precio se compro el producto cada vez (historial de compras).
--   2. Llevar un historial de cambios de costo y de precio de venta
--      (nunca se borra: trazabilidad para contabilidad).
--   3. Poder fijar el precio maximo regulado en medicamentos.
--
-- QUE ES CADA COSA
--   vb_productos.valor_compra ........ costo PROMEDIO PONDERADO (lo que usa contabilidad)
--   vb_ingresos.valor_compra ......... lo que se pago en ESA compra
--   vb_productos_precios_historial ... bitacora de cada cambio
-- ============================================================

USE inventario_ventas;

-- ------------------------------------------------------------
-- 1. Precio maximo regulado (medicamentos). NULL = no aplica.
-- ------------------------------------------------------------
ALTER TABLE `vb_productos`
  ADD COLUMN `precio_maximo_regulado` decimal(12,2) DEFAULT NULL COMMENT 'Tope legal de venta (medicamentos). NULL = no aplica' AFTER `valor_unidad`;

-- ------------------------------------------------------------
-- 2. Precio de compra de cada ingreso (historial de compras)
-- ------------------------------------------------------------
ALTER TABLE `vb_ingresos`
  ADD COLUMN `valor_compra` decimal(12,2) DEFAULT NULL COMMENT 'Precio de compra pagado en este ingreso' AFTER `cantidad_fraccion`;

-- ------------------------------------------------------------
-- 3. Bitacora de cambios de precio (no se borra nunca)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vb_productos_precios_historial` (
  `id_historial` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `origen` enum('INGRESO','EDICION_PRODUCTO','APLICACION_MASIVA') NOT NULL DEFAULT 'INGRESO',
  `id_referencia` int(10) UNSIGNED DEFAULT NULL COMMENT 'id_ingreso que origino el cambio',
  `cantidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad ingresada en esta compra',
  `precio_compra` decimal(12,2) DEFAULT NULL COMMENT 'Lo que se pago en esta compra',
  `costo_anterior` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo promedio antes',
  `costo_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo promedio despues',
  `precio_venta_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_venta_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unidad_anterior` decimal(12,2) DEFAULT NULL,
  `precio_unidad_nuevo` decimal(12,2) DEFAULT NULL,
  `rentabilidad` decimal(8,2) DEFAULT NULL COMMENT 'Margen usado (%)',
  `motivo` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `idx_ph_producto` (`id_producto`),
  KEY `idx_ph_fecha` (`created_at`),
  KEY `fk_ph_usuario` (`id_usuario`),
  CONSTRAINT `fk_ph_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ph_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
