-- ============================================================
-- Migracion: devoluciones con forma de devolucion del dinero
-- Fecha: 2026-09-13
--
-- ANTES: una devolucion no decia CÓMO se le devolvio la plata al cliente,
--        asi que el cuadre de caja no sabia cuanto efectivo salio realmente.
-- AHORA: cada devolucion guarda con que metodo(s) se devolvio, igual que las
--        facturas. Se puede devolver parte en efectivo y parte por Nequi.
--
--   vb_devoluciones.tipo_pago         -> resumen ('EFECTIVO', 'NEQUI', ... o 'MIXTO')
--   vb_devoluciones_pagos             -> el detalle de cada metodo
-- ============================================================

USE inventario_ventas;

ALTER TABLE `vb_devoluciones`
  ADD COLUMN `tipo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO' COMMENT 'Resumen: metodo unico o MIXTO' AFTER `total`;

CREATE TABLE IF NOT EXISTS `vb_devoluciones_pagos` (
  `id_pago_devolucion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_devolucion` int(10) unsigned NOT NULL,
  `metodo` varchar(50) NOT NULL COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `monto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N° de transaccion (opcional)',
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago_devolucion`),
  KEY `idx_dpago_dev` (`id_devolucion`),
  KEY `idx_dpago_metodo` (`metodo`),
  CONSTRAINT `fk_dpago_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `vb_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Pasar al detalle las devoluciones que ya existian (en efectivo)
-- ------------------------------------------------------------
INSERT INTO `vb_devoluciones_pagos` (`id_devolucion`, `metodo`, `monto`, `id_usuario`, `created_at`)
SELECT d.id_devolucion,
       COALESCE(NULLIF(d.tipo_pago, ''), 'EFECTIVO'),
       d.total,
       d.id_usuario,
       d.created_at
FROM `vb_devoluciones` d
LEFT JOIN `vb_devoluciones_pagos` p ON p.id_devolucion = d.id_devolucion
WHERE p.id_devolucion IS NULL
  AND d.total > 0;
