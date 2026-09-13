-- ============================================================
-- Migracion: pagos fraccionados en una factura
-- Fecha: 2026-09-13
--
-- ANTES: vb_facturas.tipo_pago era un solo texto, asi que una venta pagada
--        con parte en efectivo y parte en Nequi quedaba mal registrada.
-- AHORA: cada factura puede tener VARIOS pagos, uno por cada forma con que
--        el cliente pagó. La suma se controla contra el total de la factura.
--
--   vb_facturas.tipo_pago    -> queda como resumen ('EFECTIVO', 'NEQUI', ... o 'MIXTO')
--   vb_facturas.pago_recibido-> sigue siendo el total recibido (suma de los pagos)
--   vb_facturas_pagos        -> el detalle de cada pago
--
-- Las facturas viejas siguen funcionando: se les crea un pago con su tipo_pago.
-- ============================================================

USE inventario_ventas;

CREATE TABLE IF NOT EXISTS `vb_facturas_pagos` (
  `id_pago` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_factura` int(10) unsigned NOT NULL,
  `metodo` varchar(50) NOT NULL COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `monto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N° de aprobacion / transaccion (opcional)',
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `idx_fpagofactura` (`id_factura`),
  KEY `idx_fpagometodo` (`metodo`),
  KEY `idx_fpagousuario` (`id_usuario`),
  CONSTRAINT `fk_fpago_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Pasar al detalle las facturas que ya existian (un solo pago cada una)
-- ------------------------------------------------------------
INSERT INTO `vb_facturas_pagos` (`id_factura`, `metodo`, `monto`, `id_usuario`, `created_at`)
SELECT f.id_factura,
       COALESCE(NULLIF(f.tipo_pago, ''), 'EFECTIVO'),
       f.total,
       f.id_vendedor,
       f.created_at
FROM `vb_facturas` f
LEFT JOIN `vb_facturas_pagos` p ON p.id_factura = f.id_factura
WHERE p.id_factura IS NULL
  AND f.total > 0;
