-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-11-2026 a las 14:42:15
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inventario_ventas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_base_diaria`
--

CREATE TABLE `vb_base_diaria` (
  `id_base` int(10) UNSIGNED NOT NULL,
  `base` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto base del día',
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_categorias`
--

CREATE TABLE `vb_categorias` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_categorias`
--

INSERT INTO `vb_categorias` (`id_categoria`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Producto Farmacia', 'Medicamentos y productos farmacéuticos', 1, '2026-07-23 17:08:29', '2026-07-23 21:13:59'),
(2, 'Producto Varios', 'Productos de consumo general', 1, '2026-07-23 17:08:29', '2026-07-23 17:08:29'),
(4, 'RESPUESTOS', '', 1, '2026-09-15 21:01:15', '2026-09-15 21:01:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_cierres_inventario`
--

CREATE TABLE `vb_cierres_inventario` (
  `id_cierre` int(10) UNSIGNED NOT NULL,
  `periodo` enum('DIARIO','SEMANAL','QUINCENAL','MENSUAL','PERSONALIZADO') NOT NULL DEFAULT 'DIARIO' COMMENT 'Cada cuanto se cierra',
  `fecha_desde` date DEFAULT NULL COMMENT 'Primer dia del periodo',
  `fecha_hasta` date DEFAULT NULL COMMENT 'Ultimo dia del periodo',
  `total_invertido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_capital` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_utilidad` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_ventas` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Ventas del periodo (facturas activas)',
  `total_devoluciones` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Devoluciones del periodo',
  `total_ingresos` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Compras / mercancia que entro',
  `total_egresos` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Gastos del periodo',
  `total_ganancia` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Utilidad de las ventas',
  `total_facturas` int(11) NOT NULL DEFAULT 0 COMMENT 'Cuantas facturas',
  `total_anuladas` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de facturas anuladas',
  `utilidad_neta` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Ganancia - egresos - devoluciones',
  `id_usuario` int(10) UNSIGNED DEFAULT NULL COMMENT 'Quien hizo el cierre',
  `observacion` text DEFAULT NULL,
  `total_unidad` int(11) NOT NULL DEFAULT 0,
  `total_fraccion` int(11) NOT NULL DEFAULT 0,
  `fecha_cierre` date NOT NULL,
  `estado` enum('PROGRAMADO','CERRADO','ANULADO') NOT NULL DEFAULT 'CERRADO' COMMENT 'PROGRAMADO = agendado a futuro',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_clientes`
--

CREATE TABLE `vb_clientes` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `tipo_documento` varchar(5) NOT NULL DEFAULT 'CC' COMMENT 'CC, NIT, CE, PP',
  `documento` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `regimen` varchar(50) DEFAULT NULL COMMENT 'Regimen Simple, Comun, etc.',
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_clientes`
--

INSERT INTO `vb_clientes` (`id_cliente`, `tipo_documento`, `documento`, `nombre`, `direccion`, `telefono`, `email`, `regimen`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'CC', '12345', 'CLIENTE GENERAL', 'Calle Coveñas-Sucre #123', '1234', NULL, NULL, NULL, 1, '2026-07-23 17:08:30', '2026-07-23 21:12:20'),
(3, 'NIT', '900748644-9', 'DISTRAMOTOR', 'CR 9W N 23-34', '3113834864', '', '', '', 1, '2026-09-22 16:39:51', '2026-09-22 16:39:51'),
(4, 'CC', '1063315194', 'KAREN PAMPLONA', 'CL 7 # 4 - 16 BARRIO 27 DE JULIO', '3045532556', 'compulibano@hotmail.com', 'Común', '', 1, '2026-10-31 20:21:36', '2026-10-31 20:21:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_configuraciones`
--

CREATE TABLE `vb_configuraciones` (
  `id_config` int(10) UNSIGNED NOT NULL,
  `grupo` varchar(50) NOT NULL COMMENT 'Grupo de configuración',
  `clave` varchar(100) NOT NULL COMMENT 'Clave única dentro del grupo',
  `valor` text DEFAULT NULL COMMENT 'Valor almacenado',
  `tipo` enum('TEXTO','NUMERO','BOOLEANO','JSON') NOT NULL DEFAULT 'TEXTO',
  `comentario` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_configuraciones`
--

INSERT INTO `vb_configuraciones` (`id_config`, `grupo`, `clave`, `valor`, `tipo`, `comentario`, `created_at`, `updated_at`) VALUES
(1, 'fe', 'nit', '1193242195', 'TEXTO', 'NIT de la empresa para facturación electrónica', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(2, 'fe', 'software_id', '', 'TEXTO', 'Software ID registrado en DIAN', '2026-07-23 17:08:30', '2026-07-23 17:08:30'),
(3, 'fe', 'clave_certificado', '', 'TEXTO', 'Clave del certificado digital', '2026-07-23 17:08:30', '2026-07-23 17:08:30'),
(4, 'fe', 'llave_envio', '', 'TEXTO', 'Llave de envío a la DIAN', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(5, 'fe', 'usuario_api', '', 'TEXTO', 'Usuario de la API de facturación', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(6, 'fe', 'llave_api', '', 'TEXTO', 'Llave de la API de facturación', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(7, 'fe', 'url_api', 'https://www.factin.app:8443/Movimientoapi', 'TEXTO', 'URL de la API de facturación electrónica', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(8, 'fe', 'url_api_articulos', 'http://159.65.32.58:5041/Inarticulosapi', 'TEXTO', 'URL de la API de artículos', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(9, 'fe', 'prefijo', 'SD30', 'TEXTO', 'Prefijo de factura electrónica', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(10, 'fe', 'resolucion', '18764106894836', 'TEXTO', 'Número de resolución DIAN', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(11, 'fe', 'rango_inicio', '1', 'NUMERO', 'Inicio del rango de facturación electrónica', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(12, 'fe', 'rango_fin', '2000', 'NUMERO', 'Fin del rango de facturación electrónica', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(13, 'empresa', 'nombre', 'SUPER DROGAS LA 30', 'TEXTO', 'Nombre de la empresa', '2026-07-23 17:08:30', '2026-07-23 17:08:30'),
(14, 'empresa', 'direccion', 'CRA 26 No. 31-30', 'TEXTO', 'Dirección de la empresa', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(15, 'empresa', 'telefono', '2858727', 'TEXTO', 'Teléfono de la empresa', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(16, 'sistema', 'timeout_sesion', '30', 'NUMERO', 'Timeout de sesión en minutos', '2026-07-23 17:08:30', '2026-07-23 21:13:59'),
(17, 'sistema', 'intentos_maximos', '5', 'NUMERO', 'Máximos intentos de login fallidos', '2026-07-23 17:08:30', '2026-07-23 21:13:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_creditos`
--

CREATE TABLE `vb_creditos` (
  `id_credito` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `valor_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `valor_aumento` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Aumento por crédito',
  `saldo_pendiente` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','PAGADO','VENCIDO','INHABILITADO') NOT NULL DEFAULT 'ACTIVO',
  `motivo_inhabilitado` varchar(255) DEFAULT NULL COMMENT 'Por que se inhabilito',
  `fecha_inhabilitado` datetime DEFAULT NULL COMMENT 'Cuando se inhabilito',
  `id_usuario_inhabilitado` int(10) UNSIGNED DEFAULT NULL COMMENT 'Quien lo inhabilito',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_creditos_abonos`
--

CREATE TABLE `vb_creditos_abonos` (
  `id_abono` int(10) UNSIGNED NOT NULL,
  `id_credito` int(10) UNSIGNED NOT NULL,
  `valor` decimal(14,2) NOT NULL,
  `fecha` date NOT NULL,
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_creditos_detalle`
--

CREATE TABLE `vb_creditos_detalle` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_credito` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_detalle_facturas`
--

CREATE TABLE `vb_detalle_facturas` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_factura` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción al momento de facturar',
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en unidades',
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en fracciones',
  `cantidad_intermedia` int(11) NOT NULL DEFAULT 0 COMMENT 'Niveles intermedios vendidos (ej: sobres)',
  `unidades_inventario` decimal(12,4) DEFAULT NULL COMMENT 'Unidades minimas descontadas del inventario (NULL = calcular con la equivalencia)',
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '% IVA aplicado',
  `iva_valor` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor del IVA calculado',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal sin IVA',
  `total` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total con IVA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_decimal` decimal(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cantidad fraccionada decimal (ej: 0.5 varilla, 0.25 thinner)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_detalle_facturas`
--

INSERT INTO `vb_detalle_facturas` (`id_detalle`, `id_factura`, `id_producto`, `descripcion`, `cantidad_unidad`, `cantidad_fraccion`, `cantidad_intermedia`, `unidades_inventario`, `precio_unitario`, `iva`, `iva_valor`, `subtotal`, `total`, `created_at`, `cantidad_decimal`) VALUES
(54, 50, 16, 'Acetaminofén caja x 50 sobres x 10 pastillas', 0, 5, 0, 5.0000, 55000.00, 0.00, 0.00, 600.00, 600.00, '2026-11-22 22:00:25', 0.0000),
(55, 50, 15, 'Atún en lata caja x 24 latas', 0, 4, 0, 4.0000, 96000.00, 0.00, 0.00, 18000.00, 18000.00, '2026-11-22 22:00:25', 0.0000),
(56, 50, 14, 'Detergente en polvo caja x 12 bolsas', 1, 0, 0, 12.0000, 72000.00, 19.00, 13680.00, 72000.00, 85680.00, '2026-11-22 22:00:25', 0.0000),
(57, 50, 17, 'Alcohol 70% por litro', 0, 0, 0, 0.5000, 8000.00, 19.00, 760.00, 4000.00, 4760.00, '2026-11-22 22:00:25', 0.5000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_devoluciones`
--

CREATE TABLE `vb_devoluciones` (
  `id_devolucion` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `id_factura` int(10) UNSIGNED DEFAULT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tipo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO' COMMENT 'Resumen: metodo unico o MIXTO',
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `id_turno` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_devoluciones_detalle`
--

CREATE TABLE `vb_devoluciones_detalle` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_devolucion` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED DEFAULT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `cantidad_intermedia` int(11) NOT NULL DEFAULT 0 COMMENT 'Niveles intermedios devueltos (ej: sobres)',
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_devoluciones_pagos`
--

CREATE TABLE `vb_devoluciones_pagos` (
  `id_pago_devolucion` int(10) UNSIGNED NOT NULL,
  `id_devolucion` int(10) UNSIGNED NOT NULL,
  `metodo` varchar(50) NOT NULL COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `monto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N┬░ de transaccion (opcional)',
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `id_turno` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_egresos`
--

CREATE TABLE `vb_egresos` (
  `id_egreso` int(10) UNSIGNED NOT NULL,
  `id_tipo_egreso` int(10) UNSIGNED NOT NULL,
  `pagado_a` varchar(200) DEFAULT NULL,
  `valor` decimal(14,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO',
  `fecha` date NOT NULL,
  `mes` varchar(7) DEFAULT NULL COMMENT 'AAAA-MM para reportes',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `id_turno` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_empresa`
--

CREATE TABLE `vb_empresa` (
  `id_empresa` int(10) UNSIGNED NOT NULL,
  `nit` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mostrar_fe` tinyint(1) NOT NULL DEFAULT 1,
  `logo` varchar(255) DEFAULT NULL,
  `regimen` varchar(50) DEFAULT NULL,
  `actividad_economica` varchar(10) DEFAULT NULL COMMENT 'Código CIIU',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_empresa`
--

INSERT INTO `vb_empresa` (`id_empresa`, `nit`, `nombre`, `direccion`, `telefono`, `email`, `mostrar_fe`, `logo`, `regimen`, `actividad_economica`, `created_at`, `updated_at`) VALUES
(1, '1193242195-8', 'Drogas la rebaja', 'Calle 6 #13-15', '2858727-3126568609', '', 1, NULL, NULL, NULL, '2026-07-23 17:08:30', '2026-07-26 20:42:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_facturas`
--

CREATE TABLE `vb_facturas` (
  `id_factura` int(10) UNSIGNED NOT NULL,
  `uuid` varchar(36) DEFAULT NULL COMMENT 'UUID único de la factura',
  `codigo` bigint(20) NOT NULL COMMENT 'Número consecutivo de factura',
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `id_vendedor` int(10) UNSIGNED DEFAULT NULL,
  `id_turno` int(10) UNSIGNED DEFAULT NULL,
  `id_credito` int(10) UNSIGNED DEFAULT NULL,
  `id_vendedor_registrado` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `tipo` enum('NORMAL','ELECTRONICA') NOT NULL DEFAULT 'NORMAL',
  `tipo_pago` varchar(50) NOT NULL DEFAULT 'EFECTIVO' COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_iva` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total a pagar',
  `pago_recibido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cambio` decimal(14,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ganancia` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cufe` varchar(250) DEFAULT NULL COMMENT 'CUFE para factura electrónica',
  `qr` text DEFAULT NULL COMMENT 'QR para factura electrónica',
  `estado` enum('ACTIVA','ANULADA') NOT NULL DEFAULT 'ACTIVA',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_facturas`
--

INSERT INTO `vb_facturas` (`id_factura`, `uuid`, `codigo`, `id_empresa`, `id_cliente`, `id_vendedor`, `id_turno`, `id_credito`, `id_vendedor_registrado`, `fecha`, `hora`, `tipo`, `tipo_pago`, `subtotal`, `total_iva`, `total`, `pago_recibido`, `cambio`, `descuento`, `ganancia`, `cufe`, `qr`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(50, '106c3936-c33d-4e9f-ac46-6592f841511b', 46500, 1, 1, 1, 8, NULL, NULL, '2026-11-22', '17:00:25', 'NORMAL', 'EFECTIVO', 94600.00, 14440.00, 109040.00, 109040.00, 0.00, 0.00, 24950.00, NULL, NULL, 'ACTIVA', NULL, '2026-11-22 22:00:25', '2026-11-22 22:00:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_facturas_pagos`
--

CREATE TABLE `vb_facturas_pagos` (
  `id_pago` int(10) UNSIGNED NOT NULL,
  `id_factura` int(10) UNSIGNED NOT NULL,
  `metodo` varchar(50) NOT NULL COMMENT 'EFECTIVO, NEQUI, DAVIPLATA, ADDI, TRANSFERENCIA, OTROS',
  `monto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'N┬░ de aprobacion / transaccion (opcional)',
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `id_turno` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_facturas_pagos`
--

INSERT INTO `vb_facturas_pagos` (`id_pago`, `id_factura`, `metodo`, `monto`, `referencia`, `id_usuario`, `id_turno`, `created_at`) VALUES
(63, 50, 'EFECTIVO', 109040.00, NULL, 1, 8, '2026-11-22 22:00:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ingresos`
--

CREATE TABLE `vb_ingresos` (
  `id_ingreso` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `valor_compra` decimal(12,2) DEFAULT NULL COMMENT 'Precio de compra pagado en este ingreso',
  `tipo` enum('MANUAL','FACTURA_COMPRA') NOT NULL DEFAULT 'MANUAL',
  `id_factura_compra` int(10) UNSIGNED DEFAULT NULL COMMENT 'Si viene de una factura de compra',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_ingresos`
--

INSERT INTO `vb_ingresos` (`id_ingreso`, `id_producto`, `cantidad_unidad`, `cantidad_fraccion`, `valor_compra`, `tipo`, `id_factura_compra`, `observacion`, `id_usuario`, `fecha`, `created_at`) VALUES
(67, 13, 100, 0, 1200.00, 'MANUAL', NULL, 'Stock inicial | Ingreso por unidades: 100 und | Compra: $1.200', 1, '2026-11-22', '2026-11-22 21:12:14'),
(68, 14, 120, 0, 4500.00, 'MANUAL', NULL, 'Stock inicial | Ingreso por cajas: 10 cajas × 12 und = 120 und | Compra: $4.500 por caja', 1, '2026-11-22', '2026-11-22 21:12:23'),
(69, 15, 120, 0, 3200.00, 'MANUAL', NULL, 'Stock inicial | Ingreso por cajas: 5 cajas × 24 und = 120 und | Compra: $3.200 por caja', 1, '2026-11-22', '2026-11-22 21:12:40'),
(70, 16, 1000, 0, 70.00, 'MANUAL', NULL, 'Stock inicial | Ingreso por cajas: 2 cajas × 500 und = 1000 und | Compra: $70 por caja', 1, '2026-11-22', '2026-11-22 21:12:51'),
(71, 17, 20, 0, 5000.00, 'MANUAL', NULL, 'Stock inicial | Ingreso por unidades: 20 und | Compra: $5.000', 1, '2026-11-22', '2026-11-22 21:13:00'),
(74, 16, 10, 0, 70.00, 'MANUAL', NULL, 'Ingreso por unidades: 10 und | Compra: $70', 1, '2026-11-22', '2026-11-22 22:11:11'),
(75, 16, 500, 0, 70.00, 'MANUAL', NULL, 'Ingreso por cajas: 1 cajas × 500 und = 500 und | Compra: $70 por unidad interna', 1, '2026-11-22', '2026-11-22 22:11:56'),
(76, 16, 7, 0, 70.00, 'MANUAL', NULL, 'Ingreso por cajas y unidades: 0 cajas × 500 und + 7 und sueltas = 7 und | Compra: $70 por unidad interna', 1, '2026-11-22', '2026-11-22 22:12:21'),
(77, 16, 2000, 0, 70.00, 'FACTURA_COMPRA', 8, 'Factura de compra: comprando productos | 4 caja(s) × 500 und = 2000 und | Compra: $70 por unidad interna', 1, '2026-11-22', '2026-11-22 22:17:10'),
(78, 15, 576, 0, 3200.00, 'FACTURA_COMPRA', 8, 'Factura de compra: comprando productos | 24 caja(s) × 24 und = 576 und | Compra: $3.200 por unidad interna', 1, '2026-11-22', '2026-11-22 22:17:10');

--
-- Disparadores `vb_ingresos`
--
DELIMITER $$
CREATE TRIGGER `trg_ingreso_after_insert` AFTER INSERT ON `vb_ingresos` FOR EACH ROW BEGIN
    UPDATE `vb_inventario` SET `ultimo_ingreso` = NOW() WHERE `id_producto` = NEW.id_producto;
END
$$
DELIMITER ;
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ingresos_detalle_factura`
--

CREATE TABLE `vb_ingresos_detalle_factura` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_ingreso_factura` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `valor_compra` decimal(12,2) DEFAULT NULL COMMENT 'Precio de compra por unidad cerrada segun esta factura',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_ingresos_detalle_factura`
--

INSERT INTO `vb_ingresos_detalle_factura` (`id_detalle`, `id_ingreso_factura`, `id_producto`, `cantidad_unidad`, `cantidad_fraccion`, `valor_compra`, `created_at`) VALUES
(8, 8, 16, 2000, 0, 70.00, '2026-11-22 22:16:44'),
(9, 8, 15, 576, 0, 3200.00, '2026-11-22 22:17:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ingresos_factura`
--

CREATE TABLE `vb_ingresos_factura` (
  `id_ingreso_factura` int(10) UNSIGNED NOT NULL,
  `nombre_factura` varchar(200) DEFAULT NULL,
  `id_proveedor` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pendiente, 1=completado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_ingresos_factura`
--

INSERT INTO `vb_ingresos_factura` (`id_ingreso_factura`, `nombre_factura`, `id_proveedor`, `fecha`, `fecha_vencimiento`, `estado`, `created_at`, `updated_at`) VALUES
(8, 'comprando productos', 3, '2026-11-22', '2027-01-01', 1, '2026-11-22 22:14:33', '2026-11-22 22:17:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ingresos_factura_pagos`
--

CREATE TABLE `vb_ingresos_factura_pagos` (
  `id_pago_ingreso` int(10) UNSIGNED NOT NULL,
  `id_ingreso_factura` int(10) UNSIGNED NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `id_turno` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_inventario`
--

CREATE TABLE `vb_inventario` (
  `id_inventario` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `unidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en unidades (cajas)',
  `fraccion` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `ultimo_ingreso` datetime DEFAULT NULL,
  `ultimo_egreso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_inventario`
--

INSERT INTO `vb_inventario` (`id_inventario`, `id_producto`, `unidad`, `fraccion`, `ultimo_ingreso`, `ultimo_egreso`, `created_at`, `updated_at`) VALUES
(54, 13, 100, 0.0000, '2026-11-22 16:12:14', NULL, '2026-11-22 21:10:38', '2026-11-22 21:12:14'),
(55, 14, 108, 0.0000, '2026-11-22 16:12:23', '2026-11-22 17:00:25', '2026-11-22 21:10:38', '2026-11-22 22:00:25'),
(56, 15, 692, 0.0000, '2026-11-22 17:17:10', '2026-11-22 17:00:25', '2026-11-22 21:10:50', '2026-11-22 22:17:10'),
(57, 16, 3517, 0.0000, '2026-11-22 17:17:10', '2026-11-22 17:00:25', '2026-11-22 21:10:50', '2026-11-22 22:17:10'),
(58, 17, 19, 0.5000, '2026-11-22 16:13:00', '2026-11-22 17:00:25', '2026-11-22 21:10:50', '2026-11-22 22:00:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ivas`
--

CREATE TABLE `vb_ivas` (
  `id_iva` int(10) UNSIGNED NOT NULL,
  `iva` decimal(5,2) NOT NULL COMMENT 'Porcentaje del IVA (0, 5, 19)',
  `nombre` varchar(50) DEFAULT NULL COMMENT 'Nombre del IVA ej. "IVA 19%"',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_ivas`
--

INSERT INTO `vb_ivas` (`id_iva`, `iva`, `nombre`, `activo`, `created_at`, `updated_at`) VALUES
(1, 19.00, 'IVA 19%', 1, '2026-07-23 17:08:29', '2026-07-23 17:08:29'),
(2, 5.00, 'IVA 5%', 1, '2026-07-23 17:08:29', '2026-07-23 17:08:29'),
(3, 0.00, 'IVA 0% (Exento)', 1, '2026-07-23 17:08:29', '2026-07-23 17:08:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_lotes`
--

CREATE TABLE `vb_lotes` (
  `id_lote` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `codigo_lote` varchar(100) NOT NULL COMMENT 'Número de lote',
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `fecha_vencimiento` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_movimientos_inventario`
--

CREATE TABLE `vb_movimientos_inventario` (
  `id_movimiento` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `tipo` enum('INGRESO','EGRESO','AJUSTE','DEVOLUCION') NOT NULL,
  `unidad` int(11) NOT NULL DEFAULT 0,
  `fraccion` int(11) NOT NULL DEFAULT 0,
  `unidad_resultante` int(11) NOT NULL DEFAULT 0 COMMENT 'Stock resultante en unidades',
  `fraccion_resultante` int(11) NOT NULL DEFAULT 0 COMMENT 'Stock resultante en fracciones',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_movimientos_inventario`
--

INSERT INTO `vb_movimientos_inventario` (`id_movimiento`, `id_producto`, `tipo`, `unidad`, `fraccion`, `unidad_resultante`, `fraccion_resultante`, `observacion`, `id_usuario`, `created_at`) VALUES
(142, 13, 'INGRESO', 100, 0, 0, 0, 'Stock inicial | Ingreso por unidades: 100 und | Compra: $1.200', 1, '2026-11-22 21:12:14'),
(143, 14, 'INGRESO', 120, 0, 0, 0, 'Stock inicial | Ingreso por cajas: 10 cajas × 12 und = 120 und | Compra: $4.500 por caja', 1, '2026-11-22 21:12:23'),
(144, 15, 'INGRESO', 120, 0, 0, 0, 'Stock inicial | Ingreso por cajas: 5 cajas × 24 und = 120 und | Compra: $3.200 por caja', 1, '2026-11-22 21:12:40'),
(145, 16, 'INGRESO', 1000, 0, 0, 0, 'Stock inicial | Ingreso por cajas: 2 cajas × 500 und = 1000 und | Compra: $70 por caja', 1, '2026-11-22 21:12:51'),
(146, 17, 'INGRESO', 20, 0, 0, 0, 'Stock inicial | Ingreso por unidades: 20 und | Compra: $5.000', 1, '2026-11-22 21:13:00'),
(148, 16, 'EGRESO', -5, 0, 995, 0, NULL, 1, '2026-11-22 22:00:25'),
(149, 15, 'EGRESO', -4, 0, 116, 0, NULL, 1, '2026-11-22 22:00:25'),
(150, 14, 'EGRESO', -12, 0, 108, 0, NULL, 1, '2026-11-22 22:00:25'),
(151, 17, 'EGRESO', 0, -1, 19, 1, NULL, 1, '2026-11-22 22:00:25'),
(154, 16, 'INGRESO', 10, 0, 1000, 0, 'Ingreso por unidades: 10 und | Compra: $70', 1, '2026-11-22 22:11:11'),
(155, 16, 'INGRESO', 500, 0, 1010, 0, 'Ingreso por cajas: 1 cajas × 500 und = 500 und | Compra: $70 por unidad interna', 1, '2026-11-22 22:11:56'),
(156, 16, 'INGRESO', 7, 0, 1510, 0, 'Ingreso por cajas y unidades: 0 cajas × 500 und + 7 und sueltas = 7 und | Compra: $70 por unidad interna', 1, '2026-11-22 22:12:21'),
(157, 16, 'INGRESO', 2000, 0, 1517, 0, 'Factura de compra: comprando productos | 4 caja(s) × 500 und = 2000 und | Compra: $70 por unidad interna', 1, '2026-11-22 22:17:10'),
(158, 15, 'INGRESO', 576, 0, 116, 0, 'Factura de compra: comprando productos | 24 caja(s) × 24 und = 576 und | Compra: $3.200 por unidad interna', 1, '2026-11-22 22:17:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_plan_separe`
--

CREATE TABLE `vb_plan_separe` (
  `id_plan_separe` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `valor_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `valor_abonado` decimal(14,2) NOT NULL DEFAULT 0.00,
  `saldo_pendiente` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','COMPLETADO','CANCELADO') NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_plan_separe`
--

INSERT INTO `vb_plan_separe` (`id_plan_separe`, `codigo`, `id_cliente`, `valor_total`, `valor_abonado`, `saldo_pendiente`, `fecha_inicio`, `fecha_fin`, `estado`, `created_at`, `updated_at`) VALUES
(8, 'SEP-20261122-2690', 4, 55000.00, 0.00, 55000.00, '2026-11-22', '2027-02-22', 'ACTIVO', '2026-11-22 23:47:16', '2026-11-22 23:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_plan_separe_abonos`
--

CREATE TABLE `vb_plan_separe_abonos` (
  `id_abono` int(10) UNSIGNED NOT NULL,
  `id_plan_separe` int(10) UNSIGNED NOT NULL,
  `valor` decimal(14,2) NOT NULL,
  `fecha` date NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_plan_separe_detalle`
--

CREATE TABLE `vb_plan_separe_detalle` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_plan_separe` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED DEFAULT NULL,
  `cantidad` decimal(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cantidad de presentaciones separadas (admite decimales en fraccion decimal)',
  `tipo_venta` varchar(20) NOT NULL DEFAULT 'UNIDAD' COMMENT 'Forma de venta del producto: UNIDAD | CAJA | CAJA_UNIDAD | CAJA_INTERMEDIO | FRACCION_DECIMAL',
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `unidades_inventario` decimal(12,4) NOT NULL DEFAULT 0.0000 COMMENT 'Unidades minimas que representa el detalle (para descargar inventario)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_plan_separe_detalle`
--

INSERT INTO `vb_plan_separe_detalle` (`id_detalle`, `id_plan_separe`, `id_producto`, `cantidad`, `tipo_venta`, `valor_unitario`, `subtotal`, `unidades_inventario`, `created_at`) VALUES
(17, 8, 16, 1.0000, 'CAJA_INTERMEDIO', 55000.00, 55000.00, 500.0000, '2026-11-22 23:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_productos`
--

CREATE TABLE `vb_productos` (
  `id_producto` int(10) UNSIGNED NOT NULL,
  `codigo` bigint(20) UNSIGNED NOT NULL COMMENT 'Codigo interno numerico asignado por el sistema',
  `codigo_barras_1` varchar(100) DEFAULT NULL COMMENT 'Código de barras principal',
  `codigo_barras_2` varchar(100) DEFAULT NULL COMMENT 'Código de barras secundario',
  `codigo_barras_3` varchar(100) DEFAULT NULL COMMENT 'Código de barras terciario',
  `descripcion` text NOT NULL,
  `presentacion` varchar(100) DEFAULT NULL COMMENT 'Ej. "X30ML", "X24 tabletas"',
  `marca` varchar(100) DEFAULT NULL,
  `id_proveedor` int(10) UNSIGNED DEFAULT NULL,
  `id_categoria` int(10) UNSIGNED DEFAULT NULL,
  `id_iva` int(10) UNSIGNED DEFAULT NULL,
  `id_seccion` int(10) UNSIGNED DEFAULT NULL,
  `unidad_cerrada` int(11) NOT NULL DEFAULT 1 COMMENT 'Unidades por caja/empaque',
  `unidades_intermedias` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de niveles intermedios dentro de una caja (ej: sobres). 0 = sin nivel intermedio',
  `unidades_por_intermedio` int(11) NOT NULL DEFAULT 1 COMMENT 'Unidades minimas dentro de cada nivel intermedio (ej: pastillas por sobre)',
  `nombre_intermedio` varchar(30) DEFAULT NULL COMMENT 'Nombre del nivel intermedio (ej: sobre, ampolla)',
  `fraccion` int(11) NOT NULL DEFAULT 0 COMMENT '0=no fraccionable, >0=cantidad por unidad',
  `valor_compra` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de compra',
  `valor_venta` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de venta público',
  `valor_unidad` decimal(12,2) DEFAULT 0.00 COMMENT 'Precio por unidad suelta (si fraccionable)',
  `precio_maximo_regulado` decimal(12,2) DEFAULT NULL COMMENT 'Tope legal de venta (medicamentos). NULL = no aplica',
  `rentabilidad` decimal(8,2) DEFAULT 0.00 COMMENT '% de rentabilidad calculado',
  `stock_minimo` int(11) NOT NULL DEFAULT 1 COMMENT 'Alerta de stock mínimo',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta de imagen del producto',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipo_venta` enum('UNIDAD','CAJA','FRACCION_DECIMAL') NOT NULL DEFAULT 'UNIDAD' COMMENT 'UNIDAD=unidad suelta, CAJA=solo caja completa, FRACCION_DECIMAL=venta por fracción (ej: 0.5, 0.25)',
  `unidad_medida` varchar(30) DEFAULT NULL COMMENT 'Ej: METRO, LITRO, KILO, VARILLA, BOTELLA, LIBRA',
  `valor_intermedio` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de venta de un nivel intermedio (ej: precio del sobre)',
  `cantidad_por_unidad` decimal(12,4) NOT NULL DEFAULT 1.0000 COMMENT 'Cuánto equivale 1 unidad (ej: 1 varilla = 6 metros, 1 thinner = 1 litro)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_productos`
--

INSERT INTO `vb_productos` (`id_producto`, `codigo`, `codigo_barras_1`, `codigo_barras_2`, `codigo_barras_3`, `descripcion`, `presentacion`, `marca`, `id_proveedor`, `id_categoria`, `id_iva`, `id_seccion`, `unidad_cerrada`, `unidades_intermedias`, `unidades_por_intermedio`, `nombre_intermedio`, `fraccion`, `valor_compra`, `valor_venta`, `valor_unidad`, `precio_maximo_regulado`, `rentabilidad`, `stock_minimo`, `imagen`, `activo`, `created_at`, `updated_at`, `tipo_venta`, `unidad_medida`, `valor_intermedio`, `cantidad_por_unidad`) VALUES
(13, 1000001, '', '', '', 'Agua mineral 600 ml', 'Unidad', '', 6, 2, 1, 1, 1, 0, 1, NULL, 0, 1200.00, 2500.00, 0.00, NULL, 52.00, 6, '', 1, '2026-11-22 21:10:38', '2026-11-22 21:10:38', 'UNIDAD', '', 0.00, 1.0000),
(14, 1000002, '', '', '', 'Detergente en polvo caja x 12 bolsas', 'Caja x 12 bolsas', '', 6, 2, 1, 3, 12, 0, 1, NULL, 0, 4500.00, 72000.00, 0.00, NULL, 25.00, 3, '', 1, '2026-11-22 21:10:38', '2026-11-22 21:55:38', 'CAJA', '', 0.00, 12.0000),
(15, 1000003, '', '', '', 'Atún en lata caja x 24 latas', 'Caja x 24 latas', '', 6, 2, 3, 1, 24, 0, 1, NULL, 0, 3200.00, 96000.00, 0.00, NULL, 20.00, 3, '', 1, '2026-11-22 21:10:50', '2026-11-22 23:29:47', 'CAJA', '', 0.00, 24.0000),
(16, 1000004, '', '', '', 'Acetaminofén caja x 50 sobres x 10 pastillas', 'Caja x 50 sobres x 10 pastillas', '', 1, 1, 3, 2, 500, 50, 10, 'Sobre', 1, 70.00, 55000.00, 120.00, NULL, 36.36, 3, '', 1, '2026-11-22 21:10:50', '2026-11-22 21:55:38', 'UNIDAD', '', 1200.00, 500.0000),
(17, 1000005, '', '', '', 'Alcohol 70% por litro', '1 L', '', 1, 1, 1, 2, 1, 0, 1, NULL, 0, 5000.00, 8000.00, 0.00, NULL, 37.50, 2, '', 1, '2026-11-22 21:10:50', '2026-11-22 21:10:50', 'FRACCION_DECIMAL', 'LITRO', 0.00, 1.0000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_productos_precios_historial`
--

CREATE TABLE `vb_productos_precios_historial` (
  `id_historial` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `origen` enum('INGRESO','EDICION_PRODUCTO','APLICACION_MASIVA','REVERSION_INGRESO') NOT NULL DEFAULT 'INGRESO',
  `id_referencia` int(10) UNSIGNED DEFAULT NULL COMMENT 'id_ingreso que origino el cambio',
  `cantidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad ingresada en esta compra',
  `precio_compra` decimal(12,2) DEFAULT NULL COMMENT 'Lo que se pago en esta compra',
  `costo_anterior` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo promedio antes',
  `costo_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo promedio despues',
  `precio_venta_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_venta_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unidad_anterior` decimal(12,2) DEFAULT NULL,
  `precio_unidad_nuevo` decimal(12,2) DEFAULT NULL,
  `rentabilidad` decimal(8,2) DEFAULT NULL COMMENT 'Margen usado (%)',
  `motivo` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_productos_precios_historial`
--

INSERT INTO `vb_productos_precios_historial` (`id_historial`, `id_producto`, `origen`, `id_referencia`, `cantidad`, `precio_compra`, `costo_anterior`, `costo_nuevo`, `precio_venta_anterior`, `precio_venta_nuevo`, `precio_unidad_anterior`, `precio_unidad_nuevo`, `rentabilidad`, `motivo`, `id_usuario`, `created_at`) VALUES
(50, 15, 'EDICION_PRODUCTO', NULL, 0, NULL, 3200.00, 3200.00, 96000.00, 96000.00, 4500.00, 0.00, 20.00, 'Cambio manual desde Productos', 1, '2026-11-22 23:29:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_proveedores`
--

CREATE TABLE `vb_proveedores` (
  `id_proveedor` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `dias_pago` int(11) DEFAULT NULL COMMENT 'Días de crédito',
  `rentabilidad` decimal(5,2) DEFAULT NULL COMMENT '% rentabilidad por defecto',
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_proveedores`
--

INSERT INTO `vb_proveedores` (`id_proveedor`, `codigo`, `nombre`, `responsable`, `direccion`, `telefono`, `email`, `departamento`, `ciudad`, `dias_pago`, `rentabilidad`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, '01', 'MEDIMARCAS', NULL, NULL, '3145070639', NULL, 'SUCRE', NULL, NULL, 10.00, 1, NULL, '2026-07-23 17:08:29', '2026-07-23 17:08:29'),
(2, '02', 'MULTIDROGAS', NULL, NULL, '3117090563', NULL, 'CORDOBA', NULL, NULL, 10.00, 0, NULL, '2026-07-23 17:08:29', '2026-07-23 21:04:08'),
(3, '30', 'COOPI DROGAS', NULL, NULL, '3664400', NULL, 'ATLANTICO', NULL, NULL, 10.00, 1, NULL, '2026-07-23 17:08:29', '2026-07-23 17:08:29'),
(4, '04', 'MASSALUD', NULL, NULL, '3183352013', NULL, 'SUCRE', NULL, NULL, 10.00, 1, NULL, '2026-07-23 17:08:29', '2026-07-23 17:08:29'),
(5, '55', 'OTROS', NULL, NULL, '0', NULL, 'SUCRE', NULL, NULL, 10.00, 0, NULL, '2026-07-23 17:08:29', '2026-07-23 21:02:30'),
(6, '000', 'AKITA', 'VICTOR', '', '', '', '', '', 0, NULL, 1, NULL, '2026-09-15 20:09:49', '2026-09-15 20:09:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_rangos_facturacion`
--

CREATE TABLE `vb_rangos_facturacion` (
  `id_rango` int(10) UNSIGNED NOT NULL,
  `tipo` enum('NORMAL','ELECTRONICA') NOT NULL DEFAULT 'NORMAL',
  `prefijo` varchar(10) DEFAULT NULL,
  `inicio` bigint(20) NOT NULL,
  `fin` bigint(20) NOT NULL,
  `resolucion` varchar(250) DEFAULT NULL COMMENT 'Número de resolución DIAN',
  `fecha_resolucion` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_rangos_facturacion`
--

INSERT INTO `vb_rangos_facturacion` (`id_rango`, `tipo`, `prefijo`, `inicio`, `fin`, `resolucion`, `fecha_resolucion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'NORMAL', '', 46500, 10000000, '', NULL, 1, '2026-07-23 17:08:30', '2026-07-23 17:08:30'),
(2, 'ELECTRONICA', 'SD30', 1, 2000, '18764106894836', NULL, 1, '2026-07-23 17:08:30', '2026-07-23 17:08:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_secciones`
--

CREATE TABLE `vb_secciones` (
  `id_seccion` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_secciones`
--

INSERT INTO `vb_secciones` (`id_seccion`, `nombre`, `activo`, `created_at`) VALUES
(1, 'General', 1, '2026-07-23 17:08:29'),
(2, 'Farmacia', 1, '2026-07-23 17:08:29'),
(3, 'Aseo', 1, '2026-07-23 17:08:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_tipos_egreso`
--

CREATE TABLE `vb_tipos_egreso` (
  `id_tipo_egreso` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `concepto` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_tipos_egreso`
--

INSERT INTO `vb_tipos_egreso` (`id_tipo_egreso`, `codigo`, `nombre`, `concepto`, `activo`, `created_at`) VALUES
(1, '1001', 'SERVICIOS PUBLICOS', 'PAGO DE SERVICIOS PUBLICOS', 1, '2026-07-23 17:08:30'),
(2, '1002', 'HONORARIOS', 'HONORARIOS PROFESIONALES', 1, '2026-07-23 17:08:30'),
(3, '1003', 'FACTURAS DE VENTAS', 'FACTURAS DE VENTAS', 1, '2026-07-23 17:08:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_turnos_caja`
--

CREATE TABLE `vb_turnos_caja` (
  `id_turno` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `base_inicial` decimal(14,2) NOT NULL DEFAULT 0.00,
  `abierto_en` datetime NOT NULL DEFAULT current_timestamp(),
  `cerrado_en` datetime DEFAULT NULL,
  `estado` enum('ABIERTO','CERRADO') NOT NULL DEFAULT 'ABIERTO',
  `efectivo_esperado` decimal(14,2) DEFAULT NULL,
  `efectivo_contado` decimal(14,2) DEFAULT NULL,
  `diferencia` decimal(14,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_turnos_caja`
--

INSERT INTO `vb_turnos_caja` (`id_turno`, `id_usuario`, `fecha`, `base_inicial`, `abierto_en`, `cerrado_en`, `estado`, `efectivo_esperado`, `efectivo_contado`, `diferencia`, `created_at`, `updated_at`) VALUES
(7, 1, '2026-11-21', 500000.00, '2026-11-21 12:07:16', '2026-11-21 13:14:26', 'CERRADO', 515000.00, 515000.00, 0.00, '2026-11-21 17:07:16', '2026-11-21 18:14:26'),
(8, 1, '2026-11-21', 800000.00, '2026-11-21 13:14:50', NULL, 'ABIERTO', NULL, NULL, NULL, '2026-11-21 18:14:50', '2026-11-21 18:14:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_usuarios`
--

CREATE TABLE `vb_usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `nombre_completo` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tipo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=admin, 1=cajero, 2=inventario',
  `imagen` varchar(255) DEFAULT 'assets/img/user.png',
  `fecha_inicial` date DEFAULT NULL COMMENT 'Inicio de vigencia',
  `fecha_final` date DEFAULT NULL COMMENT 'Fin de vigencia',
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=activo, 1=inactivo, 2=bloqueado',
  `intentos_fallidos` tinyint(1) NOT NULL DEFAULT 0,
  `ultimo_login` datetime DEFAULT NULL,
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_usuarios`
--

INSERT INTO `vb_usuarios` (`id_usuario`, `nombre_usuario`, `password_hash`, `nombre_completo`, `email`, `tipo`, `imagen`, `fecha_inicial`, `fecha_final`, `estado`, `intentos_fallidos`, `ultimo_login`, `token_recuperacion`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$naco4gG..pBox8HqFFudI.pxdbMpA7d81aS9Bbf2.5Ju8Pype.a56', 'Administrador', NULL, 0, 'assets/img/user.png', '2026-01-01', '2030-12-31', 0, 0, '2026-11-22 18:41:13', NULL, '2026-07-23 17:08:30', '2026-11-22 23:42:48'),
(2, 'caja01', '$2y$10$bfCQoMrxXanuvPdmVMDb/.QhqrSE/.RwMzGlBF451.X97Wt0auv.O', 'Cajero 1', NULL, 1, 'assets/img/user.png', '2026-01-01', '2028-12-31', 0, 0, '2026-09-12 15:52:44', NULL, '2026-07-23 17:08:30', '2026-09-12 20:52:44'),
(3, 'superadmin', '$2y$10$Alrah55aX2YokLIArulyquGUkmTBoHI/nf2RdgduFhwG1RVPNCeq2', 'Super Administrador', 'superadmin@sistema.com', 3, 'assets/img/user.png', NULL, NULL, 0, 0, '2026-11-22 13:51:54', NULL, '2026-07-23 21:22:11', '2026-11-22 18:51:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_vendedores`
--

CREATE TABLE `vb_vendedores` (
  `id_vendedor` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `comision` decimal(5,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_vendedores`
--

INSERT INTO `vb_vendedores` (`id_vendedor`, `codigo`, `cedula`, `nombre`, `telefono`, `email`, `direccion`, `comision`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'V0001', '1234565789', 'PABLO', '3004554688', NULL, NULL, 0.00, 1, '2026-09-15 20:55:17', '2026-09-15 20:55:17'),
(2, 'V0002', '2234556', 'LUIS', '3013851992', NULL, NULL, 0.00, 1, '2026-10-31 19:38:04', '2026-10-31 19:40:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vb_base_diaria`
--
ALTER TABLE `vb_base_diaria`
  ADD PRIMARY KEY (`id_base`),
  ADD UNIQUE KEY `fecha` (`fecha`),
  ADD KEY `idx_base_fecha` (`fecha`),
  ADD KEY `idx_base_usuario` (`id_usuario`);

--
-- Indices de la tabla `vb_categorias`
--
ALTER TABLE `vb_categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `vb_cierres_inventario`
--
ALTER TABLE `vb_cierres_inventario`
  ADD PRIMARY KEY (`id_cierre`),
  ADD KEY `idx_cierre_fecha` (`fecha_cierre`),
  ADD KEY `idx_cierre_rango` (`fecha_desde`,`fecha_hasta`),
  ADD KEY `idx_cierre_estado` (`estado`),
  ADD KEY `fk_cierre_usuario` (`id_usuario`);

--
-- Indices de la tabla `vb_clientes`
--
ALTER TABLE `vb_clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `idx_cliente_doc` (`tipo_documento`,`documento`),
  ADD KEY `idx_cliente_nombre` (`nombre`(100)),
  ADD KEY `idx_cliente_activo` (`activo`);

--
-- Indices de la tabla `vb_configuraciones`
--
ALTER TABLE `vb_configuraciones`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `idx_config_grupo_clave` (`grupo`,`clave`);

--
-- Indices de la tabla `vb_creditos`
--
ALTER TABLE `vb_creditos`
  ADD PRIMARY KEY (`id_credito`),
  ADD KEY `idx_cred_cliente` (`id_cliente`),
  ADD KEY `idx_cred_estado` (`estado`),
  ADD KEY `idx_cred_usuario_inhab` (`id_usuario_inhabilitado`);

--
-- Indices de la tabla `vb_creditos_abonos`
--
ALTER TABLE `vb_creditos_abonos`
  ADD PRIMARY KEY (`id_abono`),
  ADD KEY `idx_abono_credito` (`id_credito`);

--
-- Indices de la tabla `vb_creditos_detalle`
--
ALTER TABLE `vb_creditos_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_cred_det_credito` (`id_credito`);

--
-- Indices de la tabla `vb_detalle_facturas`
--
ALTER TABLE `vb_detalle_facturas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_det_factura` (`id_factura`),
  ADD KEY `idx_det_producto` (`id_producto`);

--
-- Indices de la tabla `vb_devoluciones`
--
ALTER TABLE `vb_devoluciones`
  ADD PRIMARY KEY (`id_devolucion`),
  ADD KEY `idx_dev_factura` (`id_factura`),
  ADD KEY `idx_dev_cliente` (`id_cliente`),
  ADD KEY `idx_dev_fecha` (`fecha`),
  ADD KEY `idx_devolucion_turno` (`id_turno`);

--
-- Indices de la tabla `vb_devoluciones_detalle`
--
ALTER TABLE `vb_devoluciones_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_devdet_devolucion` (`id_devolucion`);

--
-- Indices de la tabla `vb_devoluciones_pagos`
--
ALTER TABLE `vb_devoluciones_pagos`
  ADD PRIMARY KEY (`id_pago_devolucion`),
  ADD KEY `idx_dpago_dev` (`id_devolucion`),
  ADD KEY `idx_dpago_metodo` (`metodo`),
  ADD KEY `fk_dpago_usuario` (`id_usuario`),
  ADD KEY `idx_dpago_turno` (`id_turno`);

--
-- Indices de la tabla `vb_egresos`
--
ALTER TABLE `vb_egresos`
  ADD PRIMARY KEY (`id_egreso`),
  ADD KEY `idx_egreso_tipo` (`id_tipo_egreso`),
  ADD KEY `idx_egreso_fecha` (`fecha`),
  ADD KEY `idx_egreso_mes` (`mes`),
  ADD KEY `fk_egreso_usuario` (`id_usuario`),
  ADD KEY `idx_egreso_turno` (`id_turno`);

--
-- Indices de la tabla `vb_empresa`
--
ALTER TABLE `vb_empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `vb_facturas`
--
ALTER TABLE `vb_facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `uq_factura_credito` (`id_credito`),
  ADD KEY `idx_fact_codigo` (`codigo`),
  ADD KEY `idx_fact_fecha` (`fecha`),
  ADD KEY `idx_fact_cliente` (`id_cliente`),
  ADD KEY `idx_fact_vendedor` (`id_vendedor`),
  ADD KEY `idx_fact_estado` (`estado`),
  ADD KEY `idx_fact_tipo` (`tipo`),
  ADD KEY `fk_fact_empresa` (`id_empresa`),
  ADD KEY `idx_fact_vendedor_reg` (`id_vendedor_registrado`),
  ADD KEY `idx_factura_turno` (`id_turno`);

--
-- Indices de la tabla `vb_facturas_pagos`
--
ALTER TABLE `vb_facturas_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `idx_fpagofactura` (`id_factura`),
  ADD KEY `idx_fpagometodo` (`metodo`),
  ADD KEY `idx_fpagousuario` (`id_usuario`),
  ADD KEY `idx_fpago_turno` (`id_turno`);

--
-- Indices de la tabla `vb_ingresos`
--
ALTER TABLE `vb_ingresos`
  ADD PRIMARY KEY (`id_ingreso`),
  ADD KEY `idx_ing_producto` (`id_producto`),
  ADD KEY `idx_ing_fecha` (`fecha`),
  ADD KEY `idx_ing_tipo` (`tipo`),
  ADD KEY `fk_ing_usuario` (`id_usuario`);

--
-- Indices de la tabla `vb_ingresos_detalle_factura`
--
ALTER TABLE `vb_ingresos_detalle_factura`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_ingdet_factura` (`id_ingreso_factura`),
  ADD KEY `idx_ingdet_producto` (`id_producto`);

--
-- Indices de la tabla `vb_ingresos_factura`
--
ALTER TABLE `vb_ingresos_factura`
  ADD PRIMARY KEY (`id_ingreso_factura`),
  ADD KEY `idx_ingfac_proveedor` (`id_proveedor`),
  ADD KEY `idx_ingreso_factura_vencimiento` (`fecha_vencimiento`);

--
-- Indices de la tabla `vb_ingresos_factura_pagos`
--
ALTER TABLE `vb_ingresos_factura_pagos`
  ADD PRIMARY KEY (`id_pago_ingreso`),
  ADD KEY `idx_ifpago_factura` (`id_ingreso_factura`),
  ADD KEY `idx_ifpago_fecha` (`fecha_pago`),
  ADD KEY `idx_ifpago_turno` (`id_turno`),
  ADD KEY `fk_ifpago_usuario` (`id_usuario`);

--
-- Indices de la tabla `vb_inventario`
--
ALTER TABLE `vb_inventario`
  ADD PRIMARY KEY (`id_inventario`),
  ADD UNIQUE KEY `idx_inv_producto` (`id_producto`);

--
-- Indices de la tabla `vb_ivas`
--
ALTER TABLE `vb_ivas`
  ADD PRIMARY KEY (`id_iva`);

--
-- Indices de la tabla `vb_lotes`
--
ALTER TABLE `vb_lotes`
  ADD PRIMARY KEY (`id_lote`),
  ADD KEY `idx_lote_producto` (`id_producto`),
  ADD KEY `idx_lote_vencimiento` (`fecha_vencimiento`);

--
-- Indices de la tabla `vb_movimientos_inventario`
--
ALTER TABLE `vb_movimientos_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_mov_producto` (`id_producto`),
  ADD KEY `idx_mov_tipo` (`tipo`),
  ADD KEY `idx_mov_fecha` (`created_at`),
  ADD KEY `fk_mov_usuario` (`id_usuario`);

--
-- Indices de la tabla `vb_plan_separe`
--
ALTER TABLE `vb_plan_separe`
  ADD PRIMARY KEY (`id_plan_separe`),
  ADD KEY `idx_plan_cliente` (`id_cliente`);

--
-- Indices de la tabla `vb_plan_separe_abonos`
--
ALTER TABLE `vb_plan_separe_abonos`
  ADD PRIMARY KEY (`id_abono`),
  ADD KEY `idx_planabono_plan` (`id_plan_separe`);

--
-- Indices de la tabla `vb_plan_separe_detalle`
--
ALTER TABLE `vb_plan_separe_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_plan_separe` (`id_plan_separe`);

--
-- Indices de la tabla `vb_productos`
--
ALTER TABLE `vb_productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `uq_prod_codigo` (`codigo`),
  ADD KEY `idx_prod_barras1` (`codigo_barras_1`),
  ADD KEY `idx_prod_barras2` (`codigo_barras_2`),
  ADD KEY `idx_prod_descripcion` (`descripcion`(100)),
  ADD KEY `idx_prod_activo` (`activo`),
  ADD KEY `idx_prod_proveedor` (`id_proveedor`),
  ADD KEY `idx_prod_categoria` (`id_categoria`),
  ADD KEY `idx_prod_iva` (`id_iva`),
  ADD KEY `fk_prod_seccion` (`id_seccion`);
ALTER TABLE `vb_productos` ADD FULLTEXT KEY `ft_prod_descripcion` (`descripcion`,`presentacion`);

--
-- Indices de la tabla `vb_productos_precios_historial`
--
ALTER TABLE `vb_productos_precios_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_ph_producto` (`id_producto`),
  ADD KEY `idx_ph_fecha` (`created_at`),
  ADD KEY `fk_ph_usuario` (`id_usuario`);

--
-- Indices de la tabla `vb_proveedores`
--
ALTER TABLE `vb_proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD KEY `idx_prov_codigo` (`codigo`),
  ADD KEY `idx_prov_nombre` (`nombre`),
  ADD KEY `idx_prov_estado` (`estado`);

--
-- Indices de la tabla `vb_rangos_facturacion`
--
ALTER TABLE `vb_rangos_facturacion`
  ADD PRIMARY KEY (`id_rango`),
  ADD KEY `idx_rango_tipo` (`tipo`,`activo`);

--
-- Indices de la tabla `vb_secciones`
--
ALTER TABLE `vb_secciones`
  ADD PRIMARY KEY (`id_seccion`);

--
-- Indices de la tabla `vb_tipos_egreso`
--
ALTER TABLE `vb_tipos_egreso`
  ADD PRIMARY KEY (`id_tipo_egreso`);

--
-- Indices de la tabla `vb_turnos_caja`
--
ALTER TABLE `vb_turnos_caja`
  ADD PRIMARY KEY (`id_turno`),
  ADD KEY `idx_turno_fecha_usuario` (`fecha`,`id_usuario`),
  ADD KEY `idx_turno_usuario_estado` (`id_usuario`,`estado`);

--
-- Indices de la tabla `vb_usuarios`
--
ALTER TABLE `vb_usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD KEY `idx_usr_tipo` (`tipo`),
  ADD KEY `idx_usr_estado` (`estado`);

--
-- Indices de la tabla `vb_vendedores`
--
ALTER TABLE `vb_vendedores`
  ADD PRIMARY KEY (`id_vendedor`),
  ADD UNIQUE KEY `uk_vend_codigo` (`codigo`),
  ADD KEY `idx_vend_cedula` (`cedula`),
  ADD KEY `idx_vend_nombre` (`nombre`),
  ADD KEY `idx_vend_activo` (`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vb_base_diaria`
--
ALTER TABLE `vb_base_diaria`
  MODIFY `id_base` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_categorias`
--
ALTER TABLE `vb_categorias`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vb_cierres_inventario`
--
ALTER TABLE `vb_cierres_inventario`
  MODIFY `id_cierre` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vb_clientes`
--
ALTER TABLE `vb_clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vb_configuraciones`
--
ALTER TABLE `vb_configuraciones`
  MODIFY `id_config` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `vb_creditos`
--
ALTER TABLE `vb_creditos`
  MODIFY `id_credito` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vb_creditos_abonos`
--
ALTER TABLE `vb_creditos_abonos`
  MODIFY `id_abono` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vb_creditos_detalle`
--
ALTER TABLE `vb_creditos_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_detalle_facturas`
--
ALTER TABLE `vb_detalle_facturas`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `vb_devoluciones`
--
ALTER TABLE `vb_devoluciones`
  MODIFY `id_devolucion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `vb_devoluciones_detalle`
--
ALTER TABLE `vb_devoluciones_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `vb_devoluciones_pagos`
--
ALTER TABLE `vb_devoluciones_pagos`
  MODIFY `id_pago_devolucion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vb_egresos`
--
ALTER TABLE `vb_egresos`
  MODIFY `id_egreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vb_empresa`
--
ALTER TABLE `vb_empresa`
  MODIFY `id_empresa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vb_facturas`
--
ALTER TABLE `vb_facturas`
  MODIFY `id_factura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `vb_facturas_pagos`
--
ALTER TABLE `vb_facturas_pagos`
  MODIFY `id_pago` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos`
--
ALTER TABLE `vb_ingresos`
  MODIFY `id_ingreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos_detalle_factura`
--
ALTER TABLE `vb_ingresos_detalle_factura`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos_factura`
--
ALTER TABLE `vb_ingresos_factura`
  MODIFY `id_ingreso_factura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos_factura_pagos`
--
ALTER TABLE `vb_ingresos_factura_pagos`
  MODIFY `id_pago_ingreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_inventario`
--
ALTER TABLE `vb_inventario`
  MODIFY `id_inventario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `vb_ivas`
--
ALTER TABLE `vb_ivas`
  MODIFY `id_iva` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vb_lotes`
--
ALTER TABLE `vb_lotes`
  MODIFY `id_lote` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_movimientos_inventario`
--
ALTER TABLE `vb_movimientos_inventario`
  MODIFY `id_movimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT de la tabla `vb_plan_separe`
--
ALTER TABLE `vb_plan_separe`
  MODIFY `id_plan_separe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vb_plan_separe_abonos`
--
ALTER TABLE `vb_plan_separe_abonos`
  MODIFY `id_abono` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vb_plan_separe_detalle`
--
ALTER TABLE `vb_plan_separe_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `vb_productos`
--
ALTER TABLE `vb_productos`
  MODIFY `id_producto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `vb_productos_precios_historial`
--
ALTER TABLE `vb_productos_precios_historial`
  MODIFY `id_historial` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `vb_proveedores`
--
ALTER TABLE `vb_proveedores`
  MODIFY `id_proveedor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vb_rangos_facturacion`
--
ALTER TABLE `vb_rangos_facturacion`
  MODIFY `id_rango` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vb_secciones`
--
ALTER TABLE `vb_secciones`
  MODIFY `id_seccion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vb_tipos_egreso`
--
ALTER TABLE `vb_tipos_egreso`
  MODIFY `id_tipo_egreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vb_turnos_caja`
--
ALTER TABLE `vb_turnos_caja`
  MODIFY `id_turno` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vb_usuarios`
--
ALTER TABLE `vb_usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vb_vendedores`
--
ALTER TABLE `vb_vendedores`
  MODIFY `id_vendedor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `vb_creditos`
--
ALTER TABLE `vb_creditos`
  ADD CONSTRAINT `fk_cred_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_creditos_abonos`
--
ALTER TABLE `vb_creditos_abonos`
  ADD CONSTRAINT `fk_abono_credito` FOREIGN KEY (`id_credito`) REFERENCES `vb_creditos` (`id_credito`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_creditos_detalle`
--
ALTER TABLE `vb_creditos_detalle`
  ADD CONSTRAINT `fk_cred_det_credito` FOREIGN KEY (`id_credito`) REFERENCES `vb_creditos` (`id_credito`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_detalle_facturas`
--
ALTER TABLE `vb_detalle_facturas`
  ADD CONSTRAINT `fk_det_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_det_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_devoluciones`
--
ALTER TABLE `vb_devoluciones`
  ADD CONSTRAINT `fk_dev_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_devolucion_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_devoluciones_detalle`
--
ALTER TABLE `vb_devoluciones_detalle`
  ADD CONSTRAINT `fk_devdet_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `vb_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_devoluciones_pagos`
--
ALTER TABLE `vb_devoluciones_pagos`
  ADD CONSTRAINT `fk_dpago_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `vb_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dpago_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_egresos`
--
ALTER TABLE `vb_egresos`
  ADD CONSTRAINT `fk_egreso_tipo` FOREIGN KEY (`id_tipo_egreso`) REFERENCES `vb_tipos_egreso` (`id_tipo_egreso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_egreso_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_egreso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_facturas`
--
ALTER TABLE `vb_facturas`
  ADD CONSTRAINT `fk_fact_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fact_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `vb_empresa` (`id_empresa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fact_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fact_vendedor_reg` FOREIGN KEY (`id_vendedor_registrado`) REFERENCES `vb_vendedores` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_credito` FOREIGN KEY (`id_credito`) REFERENCES `vb_creditos` (`id_credito`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_facturas_pagos`
--
ALTER TABLE `vb_facturas_pagos`
  ADD CONSTRAINT `fk_fpago_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fpago_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_ingresos`
--
ALTER TABLE `vb_ingresos`
  ADD CONSTRAINT `fk_ing_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ing_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_ingresos_detalle_factura`
--
ALTER TABLE `vb_ingresos_detalle_factura`
  ADD CONSTRAINT `fk_ingdet_factura` FOREIGN KEY (`id_ingreso_factura`) REFERENCES `vb_ingresos_factura` (`id_ingreso_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ingdet_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_ingresos_factura`
--
ALTER TABLE `vb_ingresos_factura`
  ADD CONSTRAINT `fk_ingfac_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `vb_proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_ingresos_factura_pagos`
--
ALTER TABLE `vb_ingresos_factura_pagos`
  ADD CONSTRAINT `fk_ifpago_factura` FOREIGN KEY (`id_ingreso_factura`) REFERENCES `vb_ingresos_factura` (`id_ingreso_factura`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ifpago_turno` FOREIGN KEY (`id_turno`) REFERENCES `vb_turnos_caja` (`id_turno`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ifpago_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_inventario`
--
ALTER TABLE `vb_inventario`
  ADD CONSTRAINT `fk_inv_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_lotes`
--
ALTER TABLE `vb_lotes`
  ADD CONSTRAINT `fk_lote_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_movimientos_inventario`
--
ALTER TABLE `vb_movimientos_inventario`
  ADD CONSTRAINT `fk_mov_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_plan_separe`
--
ALTER TABLE `vb_plan_separe`
  ADD CONSTRAINT `fk_plan_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_plan_separe_abonos`
--
ALTER TABLE `vb_plan_separe_abonos`
  ADD CONSTRAINT `fk_planabono_plan` FOREIGN KEY (`id_plan_separe`) REFERENCES `vb_plan_separe` (`id_plan_separe`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_plan_separe_detalle`
--
ALTER TABLE `vb_plan_separe_detalle`
  ADD CONSTRAINT `vb_plan_separe_detalle_ibfk_1` FOREIGN KEY (`id_plan_separe`) REFERENCES `vb_plan_separe` (`id_plan_separe`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vb_productos`
--
ALTER TABLE `vb_productos`
  ADD CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `vb_categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_iva` FOREIGN KEY (`id_iva`) REFERENCES `vb_ivas` (`id_iva`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `vb_proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `vb_secciones` (`id_seccion`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_productos_precios_historial`
--
ALTER TABLE `vb_productos_precios_historial`
  ADD CONSTRAINT `fk_ph_producto` FOREIGN KEY (`id_producto`) REFERENCES `vb_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ph_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_turnos_caja`
--
ALTER TABLE `vb_turnos_caja`
  ADD CONSTRAINT `fk_turno_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
