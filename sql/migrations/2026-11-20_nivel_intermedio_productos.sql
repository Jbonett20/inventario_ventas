-- Migracion: tercer nivel de venta (caja -> nivel intermedio -> unidad)
-- Ejemplo: caja x 50 sobres, cada sobre x 10 pastillas.
-- El inventario se sigue llevando en la unidad mas pequena (pastilla).
--
-- Es opcional: los productos con unidades_intermedias = 0 conservan el
-- comportamiento anterior (caja -> unidad interna).

USE inventario_ventas;

ALTER TABLE `vb_productos`
  ADD COLUMN IF NOT EXISTS `unidades_intermedias` int(11) NOT NULL DEFAULT 0
    COMMENT 'Cantidad de niveles intermedios dentro de una caja (ej: sobres). 0 = sin nivel intermedio' AFTER `unidad_cerrada`,
  ADD COLUMN IF NOT EXISTS `unidades_por_intermedio` int(11) NOT NULL DEFAULT 1
    COMMENT 'Unidades minimas dentro de cada nivel intermedio (ej: pastillas por sobre)' AFTER `unidades_intermedias`,
  ADD COLUMN IF NOT EXISTS `nombre_intermedio` varchar(30) DEFAULT NULL
    COMMENT 'Nombre del nivel intermedio (ej: sobre, ampolla)' AFTER `unidades_por_intermedio`,
  ADD COLUMN IF NOT EXISTS `valor_intermedio` decimal(12,2) NOT NULL DEFAULT 0.00
    COMMENT 'Precio de venta de un nivel intermedio (ej: precio del sobre)' AFTER `unidad_medida`;

ALTER TABLE `vb_detalle_facturas`
  ADD COLUMN IF NOT EXISTS `cantidad_intermedia` int(11) NOT NULL DEFAULT 0
    COMMENT 'Niveles intermedios vendidos (ej: sobres)' AFTER `cantidad_fraccion`;

ALTER TABLE `vb_devoluciones_detalle`
  ADD COLUMN IF NOT EXISTS `cantidad_intermedia` int(11) NOT NULL DEFAULT 0
    COMMENT 'Niveles intermedios devueltos (ej: sobres)' AFTER `cantidad_fraccion`;

-- Al vender un sobre de una caja cerrada, la caja se abre: el detalle guarda
-- la caja vendida en cantidad_unidad y las unidades minimas que realmente
-- salen del inventario en unidades_inventario.
ALTER TABLE `vb_detalle_facturas`
  ADD COLUMN IF NOT EXISTS `unidades_inventario` decimal(12,4) DEFAULT NULL
    COMMENT 'Unidades minimas descontadas del inventario (NULL = calcular con la equivalencia)' AFTER `cantidad_intermedia`;
