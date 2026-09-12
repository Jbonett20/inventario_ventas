-- ============================================================
-- Migracion: recalcular la rentabilidad de los productos
-- Fecha: 2026-09-12
--
-- PROBLEMA
--   Los productos se cargaron con el volcado inicial del sistema,
--   que inserto las filas directamente sin pasar por el calculo de
--   rentabilidad. Por eso los 7 productos quedaron en 0.
--
--   Ese 0 es peligroso: al registrar un ingreso con precio de compra
--   nuevo, Ingreso::aplicarPrecioPromedio() hace
--       precio_venta = costo / (1 - rentabilidad/100)   si rentabilidad > 0
--       precio_venta = 0                                 si rentabilidad = 0
--   o sea que el precio de venta terminaba en $0.
--
-- FORMULA (la misma que usa Producto::calcularRentabilidad())
--   rentabilidad = ((valor_venta - valor_compra) / valor_venta) * 100
--   (margen sobre el precio de venta)
-- ============================================================

USE inventario_ventas;

UPDATE `vb_productos`
SET `rentabilidad` = CASE
        WHEN `valor_compra` > 0 AND `valor_venta` > 0
            THEN ROUND(((`valor_venta` - `valor_compra`) / `valor_venta`) * 100, 2)
        ELSE 0
    END;
