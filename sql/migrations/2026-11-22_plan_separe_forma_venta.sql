-- Migracion: Plan Separe guarda la forma de venta de cada producto
--
-- Un producto puede venderse por unidad, por caja, por nivel intermedio
-- (sobre) o por fraccion decimal. El detalle del plan separe guarda que
-- presentacion se separo y cuantas unidades minimas representa, para que
-- el plan conserve el precio y el inventario correctos.

USE inventario_ventas;

-- La fraccion decimal (ej: 2.5 litros) necesita decimales en la cantidad
ALTER TABLE `vb_plan_separe_detalle`
  MODIFY COLUMN `cantidad` decimal(12,4) NOT NULL DEFAULT 0.0000
    COMMENT 'Cantidad de presentaciones separadas (admite decimales en fraccion decimal)';

ALTER TABLE `vb_plan_separe_detalle`
  ADD COLUMN IF NOT EXISTS `tipo_venta` varchar(20) NOT NULL DEFAULT 'UNIDAD'
    COMMENT 'Forma de venta del producto: UNIDAD | CAJA | CAJA_UNIDAD | CAJA_INTERMEDIO | FRACCION_DECIMAL' AFTER `cantidad`,
  ADD COLUMN IF NOT EXISTS `unidades_inventario` decimal(12,4) NOT NULL DEFAULT 0.0000
    COMMENT 'Unidades minimas que representa el detalle (para descargar inventario)' AFTER `subtotal`;
