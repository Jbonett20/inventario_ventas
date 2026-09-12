-- ============================================================
-- Migracion: catalogo de Vendedores
-- Fecha: 2026-09-12
--
-- CONTEXTO
--   El usuario logueado es quien maneja la caja (cobrador).
--   Al facturar se puede asignar la venta a:
--     - un vendedor REGISTRADO (vb_vendedores)  -> vb_facturas.id_vendedor_registrado
--     - el mismo usuario logueado (cobrador)     -> vb_facturas.id_vendedor (ya existe),
--                                                   con id_vendedor_registrado = NULL
--
-- NOTA: la seccion ALTER TABLE solo debe ejecutarse una vez.
-- ============================================================

USE inventario_ventas;

-- ------------------------------------------------------------
-- Catalogo de vendedores (independiente de vb_usuarios)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vb_vendedores` (
  `id_vendedor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `comision` decimal(5,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_vendedor`),
  UNIQUE KEY `uk_vend_codigo` (`codigo`),
  KEY `idx_vend_cedula` (`cedula`),
  KEY `idx_vend_nombre` (`nombre`),
  KEY `idx_vend_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Vendedor asignado a la venta
--   NULL  = la venta la hizo el usuario logueado (cobrador)
--   valor = vendedor registrado en vb_vendedores
-- ------------------------------------------------------------
ALTER TABLE `vb_facturas`
  ADD COLUMN `id_vendedor_registrado` int(10) UNSIGNED DEFAULT NULL AFTER `id_vendedor`,
  ADD KEY `idx_fact_vendedor_reg` (`id_vendedor_registrado`),
  ADD CONSTRAINT `fk_fact_vendedor_reg` FOREIGN KEY (`id_vendedor_registrado`)
      REFERENCES `vb_vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE;
