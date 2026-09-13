-- ============================================================
-- Migracion: creditos inhabilitados
-- Fecha: 2026-09-13
--
-- ANTES: vb_creditos.estado solo podia ser ACTIVO, PAGADO o VENCIDO.
-- AHORA: se agrega INHABILITADO, con el motivo, la fecha y el usuario que lo
--        hizo (trazabilidad). Un credito inhabilitado NO cuenta en cartera y
--        NO permite abonos, pero tampoco se borra.
--
-- Nota: el valor nuevo se agrega AL FINAL del enum para no alterar los
--       valores que ya estan guardados en las filas existentes.
-- ============================================================

USE inventario_ventas;

ALTER TABLE `vb_creditos`
  MODIFY COLUMN `estado` enum('ACTIVO','PAGADO','VENCIDO','INHABILITADO') NOT NULL DEFAULT 'ACTIVO';

ALTER TABLE `vb_creditos`
  ADD COLUMN `motivo_inhabilitado` varchar(255) DEFAULT NULL COMMENT 'Por que se inhabilito' AFTER `estado`,
  ADD COLUMN `fecha_inhabilitado` datetime DEFAULT NULL COMMENT 'Cuando se inhabilito' AFTER `motivo_inhabilitado`,
  ADD COLUMN `id_usuario_inhabilitado` int(10) unsigned DEFAULT NULL COMMENT 'Quien lo inhabilito' AFTER `fecha_inhabilitado`,
  ADD KEY `idx_cred_usuario_inhab` (`id_usuario_inhabilitado`);
