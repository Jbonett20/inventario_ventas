-- ============================================================
-- Migracion: origen REVERSION_INGRESO en la bitacora de precios
-- Fecha: 2026-09-12
--
-- Cuando se elimina un ingreso (o se revierte una factura de compra) el
-- costo promedio debe devolverse. Ese movimiento tambien queda registrado
-- en el historial, por eso se agrega un origen nuevo.
-- ============================================================

USE inventario_ventas;

ALTER TABLE `vb_productos_precios_historial`
  MODIFY COLUMN `origen` enum('INGRESO','EDICION_PRODUCTO','APLICACION_MASIVA','REVERSION_INGRESO')
  NOT NULL DEFAULT 'INGRESO';
