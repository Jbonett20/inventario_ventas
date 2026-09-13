-- ============================================================
-- Migracion: codigo de producto numerico y automatico
-- Fecha: 2026-09-13
--
-- ANTES: `codigo` era varchar(30), lo digitaba el usuario a mano y podia
--        repetirse (solo tenia indice normal, no unico).
-- AHORA: `codigo` es BIGINT UNSIGNED, unico, y lo asigna el sistema con un
--        consecutivo largo (1000001, 1000002, ...). No se llena nunca.
--
-- QUE PASA CON LOS CODIGOS VIEJOS (texto)
--   El codigo de texto NO se pierde: se copia a `codigo_barras_2`, de modo
--   que siga apareciendo en las busquedas por codigo de barras. Ademas el
--   producto pasa a tener numero (1000001, 1000002, ...).
-- ============================================================

USE inventario_ventas;

-- ------------------------------------------------------------
-- 1. Respaldo: el codigo de texto pasa a codigo_barras_2
--    (solo si el campo esta libre, para no pisar un codigo de barras real)
-- ------------------------------------------------------------
UPDATE vb_productos
SET codigo_barras_2 = codigo
WHERE (codigo_barras_2 IS NULL OR codigo_barras_2 = '')
  AND codigo NOT REGEXP '^[0-9]+$';

-- ------------------------------------------------------------
-- 2. Renumerar los codigos que no sean numericos: 1000001, 1000002, ...
-- ------------------------------------------------------------
SET @sig := 1000000;

UPDATE vb_productos
SET codigo = (@sig := @sig + 1)
WHERE codigo NOT REGEXP '^[0-9]+$'
ORDER BY id_producto;

-- ------------------------------------------------------------
-- 3. El codigo pasa a ser un numero grande (BIGINT UNSIGNED)
-- ------------------------------------------------------------
ALTER TABLE vb_productos
  MODIFY COLUMN codigo BIGINT UNSIGNED NOT NULL
  COMMENT 'Codigo interno numerico asignado por el sistema';

-- ------------------------------------------------------------
-- 4. Sin repetidos: se cambia el indice normal por uno UNIQUE
-- ------------------------------------------------------------
ALTER TABLE vb_productos DROP INDEX idx_prod_codigo;
ALTER TABLE vb_productos ADD UNIQUE KEY uq_prod_codigo (codigo);
