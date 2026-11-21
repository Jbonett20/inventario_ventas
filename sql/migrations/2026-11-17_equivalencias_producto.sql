-- Migracion: tipos de venta y equivalencias de producto
-- La cantidad por caja existente se conserva; no se infiere un tipo de venta
-- ni una cantidad decimal a partir de datos historicos ambiguos.

USE inventario_ventas;

ALTER TABLE `vb_productos`
  MODIFY COLUMN `unidad_cerrada` int(11) NOT NULL DEFAULT 1 COMMENT 'Unidades internas vendibles por caja/empaque cerrado',
  ADD COLUMN IF NOT EXISTS `tipo_venta` enum('UNIDAD','CAJA','FRACCION_DECIMAL') NOT NULL DEFAULT 'UNIDAD' COMMENT 'Tipo de venta del producto' AFTER `updated_at`,
  ADD COLUMN IF NOT EXISTS `unidad_medida` varchar(30) DEFAULT NULL COMMENT 'Unidad de medida para venta decimal' AFTER `tipo_venta`,
  ADD COLUMN IF NOT EXISTS `cantidad_por_unidad` decimal(12,4) NOT NULL DEFAULT 1.0000 COMMENT 'Cantidad de medida equivalente a una unidad de venta' AFTER `unidad_medida`;
