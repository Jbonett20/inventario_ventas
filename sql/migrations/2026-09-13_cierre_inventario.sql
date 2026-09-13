-- ============================================================
-- Migracion: cierre de inventario por periodos
-- Fecha: 2026-09-13
--
-- La tabla ya existia pero SOLO guardaba el capital del inventario de un dia
-- (la usaba el balance diario). Ahora guarda un cierre completo de un periodo
-- (diario, semanal, quincenal, mensual o personalizado) con:
--   - el rango de fechas del periodo (desde / hasta)
--   - si esta programado o ya cerrado
--   - el movimiento del periodo: ventas, devoluciones, ingresos, egresos
--   - quien lo cerro y por que
--
-- El balance diario sigue funcionando: sus cierres entran como periodo DIARIO.
-- ============================================================

USE inventario_ventas;

ALTER TABLE `vb_cierres_inventario`
  ADD COLUMN `periodo` enum('DIARIO','SEMANAL','QUINCENAL','MENSUAL','PERSONALIZADO') NOT NULL DEFAULT 'DIARIO' COMMENT 'Cada cuanto se cierra' AFTER `id_cierre`,
  ADD COLUMN `fecha_desde` date DEFAULT NULL COMMENT 'Primer dia del periodo' AFTER `periodo`,
  ADD COLUMN `fecha_hasta` date DEFAULT NULL COMMENT 'Ultimo dia del periodo' AFTER `fecha_desde`,
  ADD COLUMN `estado` enum('PROGRAMADO','CERRADO','ANULADO') NOT NULL DEFAULT 'CERRADO' COMMENT 'PROGRAMADO = agendado a futuro' AFTER `fecha_cierre`,
  ADD COLUMN `total_ventas` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Ventas del periodo (facturas activas)' AFTER `total_utilidad`,
  ADD COLUMN `total_devoluciones` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Devoluciones del periodo' AFTER `total_ventas`,
  ADD COLUMN `total_ingresos` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Compras / mercancia que entro' AFTER `total_devoluciones`,
  ADD COLUMN `total_egresos` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Gastos del periodo' AFTER `total_ingresos`,
  ADD COLUMN `total_ganancia` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Utilidad de las ventas' AFTER `total_egresos`,
  ADD COLUMN `total_facturas` int(11) NOT NULL DEFAULT 0 COMMENT 'Cuantas facturas' AFTER `total_ganancia`,
  ADD COLUMN `total_anuladas` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de facturas anuladas' AFTER `total_facturas`,
  ADD COLUMN `utilidad_neta` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Ganancia - egresos - devoluciones' AFTER `total_anuladas`,
  ADD COLUMN `id_usuario` int(10) unsigned DEFAULT NULL COMMENT 'Quien hizo el cierre' AFTER `utilidad_neta`,
  ADD COLUMN `observacion` text DEFAULT NULL AFTER `id_usuario`,
  ADD KEY `idx_cierre_rango` (`fecha_desde`, `fecha_hasta`),
  ADD KEY `idx_cierre_estado` (`estado`),
  ADD KEY `fk_cierre_usuario` (`id_usuario`);
