-- ============================================================
-- Migracion: precio de compra por producto en la factura de compra
-- Fecha: 2026-09-12
--
-- Antes el detalle de la factura solo guardaba CANTIDADES, por lo que al
-- pasar la factura a inventario el sistema tomaba el costo promedio que ya
-- tenia el producto y el costo nunca cambiaba (no quedaba historial real).
-- Con esta columna cada linea de la factura guarda lo que realmente se pago.
-- ============================================================

USE inventario_ventas;

ALTER TABLE `vb_ingresos_detalle_factura`
  ADD COLUMN `valor_compra` decimal(12,2) DEFAULT NULL COMMENT 'Precio de compra por unidad cerrada segun esta factura' AFTER `cantidad_fraccion`;
