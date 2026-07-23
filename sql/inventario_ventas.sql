-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-07-2026 a las 23:28:59
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

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
  `base` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto base del d??a',
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
(2, 'Producto Varios', 'Productos de consumo general', 1, '2026-07-23 17:08:29', '2026-07-23 17:08:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_cierres_inventario`
--

CREATE TABLE `vb_cierres_inventario` (
  `id_cierre` int(10) UNSIGNED NOT NULL,
  `total_invertido` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_capital` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_utilidad` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_unidad` int(11) NOT NULL DEFAULT 0,
  `total_fraccion` int(11) NOT NULL DEFAULT 0,
  `fecha_cierre` date NOT NULL,
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
(1, 'CC', '12345', 'CLIENTE GENERAL', 'Calle Coveñas-Sucre #123', '1234', NULL, NULL, NULL, 1, '2026-07-23 17:08:30', '2026-07-23 21:12:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_configuraciones`
--

CREATE TABLE `vb_configuraciones` (
  `id_config` int(10) UNSIGNED NOT NULL,
  `grupo` varchar(50) NOT NULL COMMENT 'Grupo de configuraci??n',
  `clave` varchar(100) NOT NULL COMMENT 'Clave ??nica dentro del grupo',
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
  `valor_aumento` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Aumento por cr??dito',
  `saldo_pendiente` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('ACTIVO','PAGADO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
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
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripci??n al momento de facturar',
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en unidades',
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en fracciones',
  `precio_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '% IVA aplicado',
  `iva_valor` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor del IVA calculado',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal sin IVA',
  `total` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total con IVA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
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
  `valor_unitario` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
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
  `fecha` date NOT NULL,
  `mes` varchar(7) DEFAULT NULL COMMENT 'AAAA-MM para reportes',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
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
  `logo` varchar(255) DEFAULT NULL,
  `regimen` varchar(50) DEFAULT NULL,
  `actividad_economica` varchar(10) DEFAULT NULL COMMENT 'C??digo CIIU',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vb_empresa`
--

INSERT INTO `vb_empresa` (`id_empresa`, `nit`, `nombre`, `direccion`, `telefono`, `email`, `logo`, `regimen`, `actividad_economica`, `created_at`, `updated_at`) VALUES
(1, '1193242195-8', 'SUPER DROGAS LA 30', 'CRA 26 No. 31-30 BARRIO SAN JUAN CENTRO', '2858727-3126568609', NULL, NULL, NULL, NULL, '2026-07-23 17:08:30', '2026-07-23 17:08:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_facturas`
--

CREATE TABLE `vb_facturas` (
  `id_factura` int(10) UNSIGNED NOT NULL,
  `uuid` varchar(36) DEFAULT NULL COMMENT 'UUID ??nico de la factura',
  `codigo` bigint(20) NOT NULL COMMENT 'N??mero consecutivo de factura',
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `id_vendedor` int(10) UNSIGNED DEFAULT NULL,
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
  `cufe` varchar(250) DEFAULT NULL COMMENT 'CUFE para factura electr??nica',
  `qr` text DEFAULT NULL COMMENT 'QR para factura electr??nica',
  `estado` enum('ACTIVA','ANULADA') NOT NULL DEFAULT 'ACTIVA',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ingresos`
--

CREATE TABLE `vb_ingresos` (
  `id_ingreso` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad_unidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_fraccion` int(11) NOT NULL DEFAULT 0,
  `tipo` enum('MANUAL','FACTURA_COMPRA') NOT NULL DEFAULT 'MANUAL',
  `id_factura_compra` int(10) UNSIGNED DEFAULT NULL COMMENT 'Si viene de una factura de compra',
  `observacion` text DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_ingresos_factura`
--

CREATE TABLE `vb_ingresos_factura` (
  `id_ingreso_factura` int(10) UNSIGNED NOT NULL,
  `nombre_factura` varchar(200) DEFAULT NULL,
  `id_proveedor` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pendiente, 1=completado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vb_inventario`
--

CREATE TABLE `vb_inventario` (
  `id_inventario` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `unidad` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en unidades (cajas)',
  `fraccion` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad en fracciones (unidades sueltas)',
  `ultimo_ingreso` datetime DEFAULT NULL,
  `ultimo_egreso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `codigo_lote` varchar(100) NOT NULL COMMENT 'N??mero de lote',
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
-- Estructura de tabla para la tabla `vb_productos`
--

CREATE TABLE `vb_productos` (
  `id_producto` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL COMMENT 'C??digo interno del producto',
  `codigo_barras_1` varchar(100) DEFAULT NULL COMMENT 'C??digo de barras principal',
  `codigo_barras_2` varchar(100) DEFAULT NULL COMMENT 'C??digo de barras secundario',
  `codigo_barras_3` varchar(100) DEFAULT NULL COMMENT 'C??digo de barras terciario',
  `descripcion` text NOT NULL,
  `presentacion` varchar(100) DEFAULT NULL COMMENT 'Ej. "X30ML", "X24 tabletas"',
  `marca` varchar(100) DEFAULT NULL,
  `id_proveedor` int(10) UNSIGNED DEFAULT NULL,
  `id_categoria` int(10) UNSIGNED DEFAULT NULL,
  `id_iva` int(10) UNSIGNED DEFAULT NULL,
  `id_seccion` int(10) UNSIGNED DEFAULT NULL,
  `unidad_cerrada` int(11) NOT NULL DEFAULT 1 COMMENT 'Unidades por caja/empaque',
  `fraccion` int(11) NOT NULL DEFAULT 0 COMMENT '0=no fraccionable, >0=cantidad por unidad',
  `valor_compra` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de compra',
  `valor_venta` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de venta p??blico',
  `valor_unidad` decimal(12,2) DEFAULT 0.00 COMMENT 'Precio por unidad suelta (si fraccionable)',
  `rentabilidad` decimal(8,2) DEFAULT 0.00 COMMENT '% de rentabilidad calculado',
  `stock_minimo` int(11) NOT NULL DEFAULT 1 COMMENT 'Alerta de stock m??nimo',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta de imagen del producto',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `dias_pago` int(11) DEFAULT NULL COMMENT 'D??as de cr??dito',
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
(5, '55', 'OTROS', NULL, NULL, '0', NULL, 'SUCRE', NULL, NULL, 10.00, 0, NULL, '2026-07-23 17:08:29', '2026-07-23 21:02:30');

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
  `resolucion` varchar(250) DEFAULT NULL COMMENT 'N??mero de resoluci??n DIAN',
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
(1, 'admin', '$2y$10$naco4gG..pBox8HqFFudI.pxdbMpA7d81aS9Bbf2.5Ju8Pype.a56', 'Administrador', NULL, 0, 'assets/img/user.png', '2026-01-01', '2030-12-31', 0, 0, '2026-07-23 16:15:14', NULL, '2026-07-23 17:08:30', '2026-07-23 21:15:14'),
(2, 'caja01', '$2y$10$naco4gG..pBox8HqFFudI.pxdbMpA7d81aS9Bbf2.5Ju8Pype.a56', 'Cajero 1', NULL, 1, 'assets/img/user.png', '2026-01-01', '2028-12-31', 0, 0, '2026-07-23 16:14:57', NULL, '2026-07-23 17:08:30', '2026-07-23 21:14:57'),
(3, 'superadmin', '$2y$10$9tfC67GXEUz0L./xxZ0Q1eRNf26PGyDO2x15JCPCAnR3gXNNwbKEG', 'Super Administrador', 'superadmin@sistema.com', 3, 'assets/img/user.png', NULL, NULL, 0, 0, '2026-07-23 16:27:47', NULL, '2026-07-23 21:22:11', '2026-07-23 21:27:47');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vb_base_diaria`
--
ALTER TABLE `vb_base_diaria`
  ADD PRIMARY KEY (`id_base`),
  ADD UNIQUE KEY `fecha` (`fecha`),
  ADD KEY `idx_base_fecha` (`fecha`);

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
  ADD KEY `idx_cierre_fecha` (`fecha_cierre`);

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
  ADD KEY `idx_cred_estado` (`estado`);

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
  ADD KEY `idx_dev_fecha` (`fecha`);

--
-- Indices de la tabla `vb_devoluciones_detalle`
--
ALTER TABLE `vb_devoluciones_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_devdet_devolucion` (`id_devolucion`);

--
-- Indices de la tabla `vb_egresos`
--
ALTER TABLE `vb_egresos`
  ADD PRIMARY KEY (`id_egreso`),
  ADD KEY `idx_egreso_tipo` (`id_tipo_egreso`),
  ADD KEY `idx_egreso_fecha` (`fecha`),
  ADD KEY `idx_egreso_mes` (`mes`),
  ADD KEY `fk_egreso_usuario` (`id_usuario`);

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
  ADD KEY `idx_fact_codigo` (`codigo`),
  ADD KEY `idx_fact_fecha` (`fecha`),
  ADD KEY `idx_fact_cliente` (`id_cliente`),
  ADD KEY `idx_fact_vendedor` (`id_vendedor`),
  ADD KEY `idx_fact_estado` (`estado`),
  ADD KEY `idx_fact_tipo` (`tipo`),
  ADD KEY `fk_fact_empresa` (`id_empresa`);

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
  ADD KEY `idx_ingfac_proveedor` (`id_proveedor`);

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
-- Indices de la tabla `vb_productos`
--
ALTER TABLE `vb_productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_prod_codigo` (`codigo`),
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
-- Indices de la tabla `vb_usuarios`
--
ALTER TABLE `vb_usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD KEY `idx_usr_tipo` (`tipo`),
  ADD KEY `idx_usr_estado` (`estado`);

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
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vb_cierres_inventario`
--
ALTER TABLE `vb_cierres_inventario`
  MODIFY `id_cierre` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_clientes`
--
ALTER TABLE `vb_clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vb_configuraciones`
--
ALTER TABLE `vb_configuraciones`
  MODIFY `id_config` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `vb_creditos`
--
ALTER TABLE `vb_creditos`
  MODIFY `id_credito` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_creditos_abonos`
--
ALTER TABLE `vb_creditos_abonos`
  MODIFY `id_abono` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_creditos_detalle`
--
ALTER TABLE `vb_creditos_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_detalle_facturas`
--
ALTER TABLE `vb_detalle_facturas`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_devoluciones`
--
ALTER TABLE `vb_devoluciones`
  MODIFY `id_devolucion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_devoluciones_detalle`
--
ALTER TABLE `vb_devoluciones_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_egresos`
--
ALTER TABLE `vb_egresos`
  MODIFY `id_egreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_empresa`
--
ALTER TABLE `vb_empresa`
  MODIFY `id_empresa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vb_facturas`
--
ALTER TABLE `vb_facturas`
  MODIFY `id_factura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos`
--
ALTER TABLE `vb_ingresos`
  MODIFY `id_ingreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos_detalle_factura`
--
ALTER TABLE `vb_ingresos_detalle_factura`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_ingresos_factura`
--
ALTER TABLE `vb_ingresos_factura`
  MODIFY `id_ingreso_factura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_inventario`
--
ALTER TABLE `vb_inventario`
  MODIFY `id_inventario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_movimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_plan_separe`
--
ALTER TABLE `vb_plan_separe`
  MODIFY `id_plan_separe` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_plan_separe_abonos`
--
ALTER TABLE `vb_plan_separe_abonos`
  MODIFY `id_abono` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_productos`
--
ALTER TABLE `vb_productos`
  MODIFY `id_producto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vb_proveedores`
--
ALTER TABLE `vb_proveedores`
  MODIFY `id_proveedor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id_tipo_egreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `vb_usuarios`
--
ALTER TABLE `vb_usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `fk_dev_factura` FOREIGN KEY (`id_factura`) REFERENCES `vb_facturas` (`id_factura`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_devoluciones_detalle`
--
ALTER TABLE `vb_devoluciones_detalle`
  ADD CONSTRAINT `fk_devdet_devolucion` FOREIGN KEY (`id_devolucion`) REFERENCES `vb_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_egresos`
--
ALTER TABLE `vb_egresos`
  ADD CONSTRAINT `fk_egreso_tipo` FOREIGN KEY (`id_tipo_egreso`) REFERENCES `vb_tipos_egreso` (`id_tipo_egreso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_egreso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `vb_facturas`
--
ALTER TABLE `vb_facturas`
  ADD CONSTRAINT `fk_fact_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `vb_clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fact_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `vb_empresa` (`id_empresa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fact_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `vb_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

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
-- Filtros para la tabla `vb_productos`
--
ALTER TABLE `vb_productos`
  ADD CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `vb_categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_iva` FOREIGN KEY (`id_iva`) REFERENCES `vb_ivas` (`id_iva`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `vb_proveedores` (`id_proveedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `vb_secciones` (`id_seccion`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
