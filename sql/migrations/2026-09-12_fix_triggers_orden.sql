-- ============================================================
-- Migracion: corregir el orden de los triggers de vb_ingresos
-- Fecha: 2026-09-12
--
-- PROBLEMA
--   Los triggers AFTER INSERT de vb_ingresos se ejecutan en el orden
--   en que fueron creados. En el volcado original el orden era:
--     1) trg_ingreso_after_insert
--     2) trg_ingreso_movimiento   <-- lee vb_inventario
--     3) trg_ingreso_stock        <-- actualiza vb_inventario
--   Por eso vb_movimientos_inventario.unidad_resultante /
--   fraccion_resultante guardaban el stock ANTERIOR al ingreso
--   en lugar del stock resultante.
--
-- SOLUCION
--   Recrear los triggers para que el de stock se ejecute primero y
--   el de movimiento registre el stock ya actualizado.
--
-- NOTA: MariaDB no soporta FOLLOWS / PRECEDES, por eso se eliminan
--       y se vuelven a crear en el orden deseado.
-- ============================================================

USE inventario_ventas;

DROP TRIGGER IF EXISTS trg_ingreso_movimiento;
DROP TRIGGER IF EXISTS trg_ingreso_stock;

-- 1) Primero actualiza el stock
DELIMITER $$
CREATE TRIGGER `trg_ingreso_stock` AFTER INSERT ON `vb_ingresos` FOR EACH ROW BEGIN
    DECLARE v_fraccion INT DEFAULT 0;
    DECLARE v_unidad_cerrada INT DEFAULT 1;

    SELECT `fraccion`, `unidad_cerrada` INTO v_fraccion, v_unidad_cerrada
    FROM `vb_productos` WHERE `id_producto` = NEW.id_producto;

    UPDATE `vb_inventario`
    SET
        `unidad` = `unidad` + NEW.cantidad_unidad,
        `fraccion` = `fraccion` + NEW.cantidad_fraccion
    WHERE `id_producto` = NEW.id_producto;
END
$$
DELIMITER ;

-- 2) Despues registra el movimiento (ya con el stock resultante)
DELIMITER $$
CREATE TRIGGER `trg_ingreso_movimiento` AFTER INSERT ON `vb_ingresos` FOR EACH ROW BEGIN
    INSERT INTO `vb_movimientos_inventario`
        (`id_producto`, `tipo`, `unidad`, `fraccion`, `unidad_resultante`, `fraccion_resultante`, `observacion`, `id_usuario`, `created_at`)
    SELECT
        NEW.id_producto, 'INGRESO', NEW.cantidad_unidad, NEW.cantidad_fraccion,
        `unidad`, `fraccion`, NEW.observacion, NEW.id_usuario, NOW()
    FROM `vb_inventario` WHERE `id_producto` = NEW.id_producto;
END
$$
DELIMITER ;
