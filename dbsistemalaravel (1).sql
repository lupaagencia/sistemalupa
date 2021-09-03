-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-08-2021 a las 16:32:06
-- Versión del servidor: 10.1.36-MariaDB
-- Versión de PHP: 7.3.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dbsistemalaravel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_item_padre` int(10) DEFAULT NULL,
  `idcategoria` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_producto` int(11) NOT NULL DEFAULT '1',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'noimagen',
  `rangos` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_venta` decimal(11,2) NOT NULL,
  `iva` tinyint(10) NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL,
  `descripcion` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `articulos`
--

INSERT INTO `articulos` (`id`, `id_item_padre`, `idcategoria`, `codigo`, `tipo_producto`, `nombre`, `imagen`, `rangos`, `precio_venta`, `iva`, `stock`, `descripcion`, `condicion`, `created_at`, `updated_at`) VALUES
(22, 0, 1, '1', 1, 'Caja L12', '1629367592_L12.jpg', '[{\"de\":\"1\",\"hasta\":\"1000\",\"descuento\":\"0\",\"rangoEdit\":0}]', '420.00', 0, 1000, 'null', 1, '2021-08-19 15:06:32', '2021-08-19 16:15:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributos`
--

CREATE TABLE `atributos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_articulo` int(10) UNSIGNED DEFAULT NULL,
  `valor` decimal(30,0) NOT NULL,
  `tipo_campo` int(11) DEFAULT NULL,
  `tipo_valor` int(11) DEFAULT NULL,
  `nombre` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_impresion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nota` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alerta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidad_medida` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operacion` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimo` int(10) DEFAULT NULL,
  `maximo` int(10) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atributos`
--

INSERT INTO `atributos` (`id`, `id_articulo`, `valor`, `tipo_campo`, `tipo_valor`, `nombre`, `tipo_impresion`, `nota`, `descripcion`, `alerta`, `unidad_medida`, `operacion`, `minimo`, `maximo`, `orden`) VALUES
(6, 22, '0', 1, 1, 'Papel', NULL, NULL, '', '', 'Cm', '+', 0, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`, `condicion`, `created_at`, `updated_at`) VALUES
(1, 'Cajas', 'Caja en todo tipo de material', 'cajas.jpg', 1, NULL, '2020-11-04 04:02:23'),
(2, 'Papel Antigrasa', NULL, 'antigrasa.jpg', 1, '2020-10-30 17:07:04', '2020-10-30 17:07:04'),
(3, 'Individuales', NULL, 'individuales.jpg', 1, '2020-10-30 17:07:22', '2020-10-30 17:07:22'),
(4, 'Bolsas Domicilio', NULL, 'bolsas.jpg', 1, '2020-10-30 17:08:57', '2020-10-30 17:08:57'),
(5, 'Porta Perro', NULL, 'portaperro.jpg', 1, '2020-10-30 17:09:18', '2020-10-30 17:09:18'),
(6, 'Hamburguesa', NULL, 'hamburguesa.jpg', 1, '2020-10-30 19:00:16', '2020-10-30 19:00:16'),
(7, 'Pizza', NULL, 'pizza.jpg', 1, '2020-10-30 19:00:26', '2020-10-30 19:00:26'),
(8, 'Papas', NULL, 'papas.jpg', 1, '2020-10-30 19:00:31', '2020-10-30 19:00:31'),
(9, 'LS9 - LS12', NULL, 'ls.jpg', 1, '2020-10-30 19:00:46', '2020-10-30 19:00:46'),
(10, 'DP001', NULL, 'dp.jpg', 1, '2020-10-30 19:00:53', '2020-10-30 19:00:53'),
(11, 'Cubiertos', NULL, 'cubiertos.jpg', 1, '2020-10-30 19:01:03', '2020-10-30 19:01:03'),
(12, 'Japonesa', NULL, 'japonesa.jpg', 1, '2020-10-30 19:01:17', '2020-10-30 19:01:17'),
(13, 'DP-PERF02', NULL, 'dp2.jpg', 1, '2020-10-30 19:01:34', '2020-10-30 19:01:34'),
(14, 'Boutique', NULL, 'boutique.jpg', 1, '2020-10-30 19:01:50', '2020-10-30 19:01:50'),
(15, 'Cono', NULL, 'cono.jpg', 1, '2020-10-30 19:02:11', '2020-10-30 19:02:11'),
(16, 'Etiquetas y Stickers', NULL, 'sticker.jpg', 1, '2020-10-30 19:02:24', '2020-10-30 19:02:24'),
(17, 'Imanes', NULL, 'iman.jpg', 1, '2020-10-30 19:02:30', '2020-10-30 19:02:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_cliente` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_contacto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_contacto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `tipo_cliente`, `ciudad`, `departamento`, `pais`, `contacto`, `telefono_contacto`, `email_contacto`) VALUES
(24, 'Natural', NULL, NULL, NULL, 'dfs', NULL, NULL),
(16, 'Natural', NULL, NULL, NULL, 'Juan David Gómez', NULL, NULL),
(39, 'Jurídica', NULL, NULL, 'Colombia', 'Fernando Ossa', '3163423277', NULL),
(40, 'Natural', 'Cali', 'Valle del Cauca', 'Colombia', 'Catherine Paez', '+573183953381', NULL),
(41, 'Juridico', 'La victoria', 'Valle del Cauca', 'Colombia', 'Viry Rebello', '3122682256', NULL),
(43, 'Natural', 'Cali', 'Valle del Cauca', 'Colombia', 'sdfa', NULL, NULL),
(44, NULL, NULL, NULL, NULL, 'Didier Valderrama', NULL, NULL),
(80, 'Jurídica', NULL, NULL, NULL, 'sdfa', NULL, NULL),
(99, 'Jurídica', NULL, NULL, NULL, 'sfad', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costois`
--

CREATE TABLE `costois` (
  `id` int(10) UNSIGNED NOT NULL,
  `idproveedor` int(10) UNSIGNED NOT NULL,
  `idpersona` int(10) UNSIGNED NOT NULL,
  `tipo_costo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidad_medida` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `costois`
--

INSERT INTO `costois` (`id`, `idproveedor`, `idpersona`, `tipo_costo`, `nombre`, `unidad_medida`, `valor`, `total`, `estado`, `created_at`, `updated_at`) VALUES
(8, 8, 8, 'Papel', 'Cartón Kraft calibre 48', 'pliego', '681.00', '0.00', '1', '2020-11-05 14:26:19', '2020-11-06 17:46:59'),
(10, 9, 9, 'Papel', 'Carton kraft calibre 48', 'Pliego', '641.00', '0.00', '1', '2020-11-06 17:34:41', '2020-11-06 17:34:41'),
(11, 8, 8, 'Papel', 'Earth Pack 295 grs', 'Pliego', '929.00', '0.00', '1', '2020-11-06 17:38:47', '2020-11-06 17:38:47'),
(12, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 25x12 cm', 'Millar', '62000.00', '0.00', '1', '2020-11-06 18:30:08', '2020-11-06 18:30:08'),
(13, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 24x12 cm', 'Millar', '57000.00', '0.00', '1', '2020-11-06 18:32:24', '2020-11-06 18:32:24'),
(14, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 22x14 cm', 'Millar', '57000.00', '0.00', '1', '2020-11-06 18:33:14', '2020-11-06 18:33:14'),
(15, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 21.5x14 cm', 'Millar', '54000.00', '0.00', '1', '2020-11-06 18:33:55', '2020-11-06 18:33:55'),
(16, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 21x14 cm', 'Millar', '52000.00', '0.00', '1', '2020-11-06 18:35:37', '2020-11-06 18:35:37'),
(17, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20.5x13.5 cm', 'Millar', '49000.00', '0.00', '1', '2020-11-06 18:36:12', '2020-11-06 18:37:15'),
(18, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20x13 cm', 'Millar', '47000.00', '0.00', '1', '2020-11-06 18:36:54', '2020-11-06 18:36:54'),
(19, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 25x10 cm', 'Millar', '42000.00', '0.00', '1', '2020-11-06 18:38:47', '2020-11-06 18:38:47'),
(20, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 30x10 cm', 'Millar', '47000.00', '0.00', '1', '2020-11-06 18:39:17', '2020-11-06 18:39:17'),
(21, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20x12.5 cm', 'Millar', '45000.00', '0.00', '1', '2020-11-06 18:39:40', '2020-11-06 18:39:40'),
(22, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20x11.5 cm', 'Millar', '39000.00', '0.00', '1', '2020-11-06 18:40:05', '2020-11-06 18:40:05'),
(23, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20x11 cm', 'Millar', '37000.00', '0.00', '1', '2020-11-06 18:41:06', '2020-11-06 18:41:06'),
(24, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 17x12 cm', 'Millar', '34000.00', '0.00', '1', '2020-11-06 18:41:44', '2020-11-06 18:41:44'),
(25, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 17x11 cm', 'Millar', '32000.00', '0.00', '1', '2020-11-06 18:43:49', '2020-11-06 18:43:49'),
(26, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20x10 cm', 'Millar', '32000.00', '0.00', '1', '2020-11-06 18:45:44', '2020-11-06 18:45:44'),
(27, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 20x9 cm', 'Millar', '27000.00', '0.00', '1', '2020-11-06 18:46:17', '2020-11-06 18:46:17'),
(28, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 17x10cm', 'Millar', '27000.00', '0.00', '1', '2020-11-06 18:46:46', '2020-11-06 18:46:46'),
(29, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 10x15 cm', 'Millar', '25000.00', '0.00', '1', '2020-11-06 18:48:31', '2020-11-06 18:48:31'),
(30, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 14x9 cm', 'Millar', '22000.00', '0.00', '1', '2020-11-06 19:04:53', '2020-11-06 19:04:53'),
(31, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 13x10 cm', 'Millar', '22000.00', '0.00', '1', '2020-11-06 19:05:31', '2020-11-06 19:05:31'),
(32, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 13x9 cm', 'Millar', '22000.00', '0.00', '1', '2020-11-06 19:06:07', '2020-11-06 19:06:07'),
(33, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 25x17 cm', 'Millar', '70000.00', '0.00', '1', '2020-11-06 19:06:40', '2020-11-06 19:06:40'),
(34, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 28x21 cm', 'Millar', '102000.00', '0.00', '1', '2020-11-06 19:07:15', '2020-11-06 19:07:15'),
(35, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 33x21 cm', 'Millar', '117000.00', '0.00', '1', '2020-11-06 19:08:11', '2020-11-06 19:08:11'),
(36, 11, 11, 'montaje', 'Volante 4x0 propalcote 115 grs 34x24 cm', 'Millar', '132000.00', '0.00', '1', '2020-11-06 19:08:41', '2020-11-06 19:08:41'),
(37, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 23x33 cm', 'Millar', '212000.00', '0.00', '1', '2020-11-06 19:10:09', '2020-11-06 19:10:09'),
(38, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 22x28 cm', 'Millar', '182000.00', '0.00', '1', '2020-11-06 19:11:12', '2020-11-06 19:11:12'),
(39, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 21x14 cm', 'Millar', '102000.00', '0.00', '1', '2020-11-06 19:11:51', '2020-11-06 19:11:51'),
(40, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 20x13 cm', 'Millar', '82000.00', '0.00', '1', '2020-11-06 19:12:23', '2020-11-06 19:12:23'),
(41, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 20x11 cm', 'Millar', '72000.00', '0.00', '1', '2020-11-06 19:12:54', '2020-11-06 19:12:54'),
(42, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 17x12 cm', 'Millar', '67000.00', '0.00', '1', '2020-11-06 19:13:21', '2020-11-06 19:13:21'),
(43, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 20x9 cm', 'Millar', '62000.00', '0.00', '1', '2020-11-06 19:13:54', '2020-11-06 19:13:54'),
(44, 11, 11, 'montaje', 'Volante 4x4 propalcote 150 grs 10x15 cm', 'Millar', '52000.00', '0.00', '1', '2020-11-06 19:14:21', '2020-11-06 19:14:21'),
(45, 11, 11, 'montaje', 'Tarjeta UV total 4x0 propalcote 280 grs', 'Millar', '16000.00', '0.00', '1', '2020-11-06 19:16:13', '2020-11-06 19:17:56'),
(46, 11, 11, 'montaje', 'Tarjeta UV total 4x1 propalcote 300 grs retiro 1 tinta (negro)', 'Millar', '17000.00', '0.00', '1', '2020-11-06 19:17:41', '2020-11-06 19:17:41'),
(47, 11, 11, 'montaje', 'Tarjeta UV total 4x4 propalcote 300 grs', 'Millar', '27000.00', '0.00', '1', '2020-11-06 19:19:23', '2020-11-06 19:19:23'),
(48, 11, 11, 'montaje', 'Tarjetas Imantadas 9x5.5 (sin troquelado)', 'Millar', '145000.00', '0.00', '1', '2020-11-06 19:20:47', '2020-11-06 19:20:47'),
(49, 11, 11, 'montaje', 'Tarjetas Mate UV Parcial prop 300 grs 4x0', 'Millar', '45000.00', '0.00', '1', '2020-11-06 19:21:58', '2020-11-06 19:21:58'),
(50, 11, 11, 'montaje', 'Tarjetas Mate UV Parcial prop 300 grs 4x1', 'Millar', '45000.00', '0.00', '1', '2020-11-06 19:22:18', '2020-11-06 19:22:18'),
(51, 11, 11, 'montaje', 'Tarjetas Mate UV Parcial prop 300 grs 4x4', 'Millar', '55000.00', '0.00', '1', '2020-11-06 19:22:40', '2020-11-06 19:22:40'),
(52, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 9x5.5', 'Millar', '30000.00', '0.00', '1', '2020-11-06 19:26:01', '2020-11-06 19:26:01'),
(53, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 10x3', 'Millar', '30000.00', '0.00', '1', '2020-11-06 19:26:33', '2020-11-06 19:26:33'),
(54, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 11x2', 'Millar', '30000.00', '0.00', '1', '2020-11-06 19:27:09', '2020-11-06 19:27:09'),
(55, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 10x6', 'Millar', '34000.00', '0.00', '1', '2020-11-06 19:27:50', '2020-11-06 19:27:50'),
(56, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 9x7', 'Millar', '34000.00', '0.00', '1', '2020-11-06 19:28:14', '2020-11-06 19:28:14'),
(57, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 8x7', 'Millar', '34000.00', '0.00', '1', '2020-11-06 19:28:35', '2020-11-06 19:28:35'),
(58, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 11x5', 'Millar', '340000.00', '0.00', '1', '2020-11-06 19:29:01', '2020-11-06 19:29:01'),
(59, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 9x11', 'Millar', '49000.00', '0.00', '1', '2020-11-06 19:29:25', '2020-11-06 19:29:25'),
(60, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 9x10', 'Millar', '49000.00', '0.00', '1', '2020-11-06 19:29:50', '2020-11-06 19:29:50'),
(61, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 12x8', 'Millar', '49000.00', '0.00', '1', '2020-11-06 19:30:19', '2020-11-06 19:30:19'),
(62, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 10x8', 'Millar', '42000.00', '0.00', '1', '2020-11-06 19:30:48', '2020-11-06 19:30:48'),
(63, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 11x7', 'Millar', '42000.00', '0.00', '1', '2020-11-06 19:31:16', '2020-11-06 19:31:16'),
(64, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 12x5', 'Millare', '42000.00', '0.00', '1', '2020-11-06 19:31:38', '2020-11-06 19:31:38'),
(65, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 12x12', 'Millar', '54000.00', '0.00', '1', '2020-11-06 19:32:05', '2020-11-06 19:32:05'),
(66, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 13x11', 'Millar', '54000.00', '0.00', '1', '2020-11-06 19:32:46', '2020-11-06 19:32:46'),
(67, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 14x9', 'Millar', '54000.00', '0.00', '1', '2020-11-06 19:33:19', '2020-11-06 19:33:19'),
(68, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 14x10', 'Millar', '59000.00', '0.00', '1', '2020-11-06 19:33:48', '2020-11-06 19:33:48'),
(69, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 15x9', 'Millar', '59000.00', '0.00', '1', '2020-11-06 19:34:13', '2020-11-06 19:34:13'),
(70, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 18x11', 'Millar', '74000.00', '0.00', '1', '2020-11-06 19:35:07', '2020-11-06 19:35:07'),
(71, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 12x17', 'Millar', '74000.00', '0.00', '1', '2020-11-06 19:35:39', '2020-11-06 19:35:39'),
(72, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 13x15', 'Millar', '74000.00', '0.00', '1', '2020-11-06 19:36:05', '2020-11-06 19:36:05'),
(73, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 20x10', 'Millar', '69000.00', '0.00', '1', '2020-11-06 19:37:13', '2020-11-06 19:37:13'),
(74, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 21x9', 'Millar', '69000.00', '0.00', '1', '2020-11-06 19:37:47', '2020-11-06 19:37:47'),
(75, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 21x8', 'Millar', '69000.00', '0.00', '1', '2020-11-06 19:38:54', '2020-11-06 19:38:54'),
(76, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 15x6', 'Millar', '44000.00', '0.00', '1', '2020-11-06 19:40:48', '2020-11-06 19:40:48'),
(77, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 14x7', 'Millar', '44000.00', '0.00', '1', '2020-11-06 19:41:21', '2020-11-06 19:41:21'),
(78, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 16x5', 'Millar', '44000.00', '0.00', '1', '2020-11-06 19:41:51', '2020-11-06 19:41:51'),
(79, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 20x13', 'Millar', '99000.00', '0.00', '1', '2020-11-06 19:43:06', '2020-11-06 19:43:06'),
(80, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 21x14', 'Millar', '114000.00', '0.00', '1', '2020-11-06 19:43:32', '2020-11-06 19:43:32'),
(81, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 20x6', 'Millar', '54000.00', '0.00', '1', '2020-11-06 19:43:57', '2020-11-06 19:43:57'),
(82, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 17x5', 'Millar', '44000.00', '0.00', '1', '2020-11-06 19:44:28', '2020-11-06 19:44:28'),
(83, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 11x10', 'Millar', '52000.00', '0.00', '1', '2020-11-06 19:44:57', '2020-11-06 19:44:57'),
(84, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 14x12', 'Millar', '64000.00', '0.00', '1', '2020-11-06 19:45:20', '2020-11-06 19:45:20'),
(85, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 10x7', 'Millar', '39000.00', '0.00', '1', '2020-11-06 19:45:42', '2020-11-06 19:45:42'),
(86, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 11x6', 'Millar', '39000.00', '0.00', '1', '2020-11-06 19:46:08', '2020-11-06 19:46:08'),
(87, 11, 11, 'montaje', 'Adhesivo Barniz Brillante 8x8', 'Millar', '39000.00', '0.00', '1', '2020-11-06 19:46:29', '2020-11-06 19:46:29'),
(88, 25, 25, 'Plancha', 'Plancha Octavo 25,5x38', 'unidad', '6000.00', '0.00', '1', '2020-11-14 05:38:34', '2020-11-14 05:38:34'),
(89, 25, 25, 'Plancha', 'Plancha de doble carta 28x46cm', 'unidad', '7000.00', '0.00', '1', '2020-11-14 05:39:13', '2020-11-14 05:39:13'),
(90, 25, 25, 'Plancha', 'Plancha ABD 360 47,5 c 27,5', 'unidad', '7000.00', '0.00', '1', '2020-11-14 05:39:50', '2020-11-14 05:39:50'),
(91, 25, 25, 'Plancha', 'Plancha de cuarto 52x40', 'unidad', '8500.00', '0.00', '1', '2020-11-14 05:40:38', '2020-11-14 05:40:38'),
(92, 25, 25, 'Plancha', 'Plancha de Cuarto Mayor 55x65', 'unidad', '13000.00', '0.00', '1', '2020-11-14 05:41:31', '2020-11-14 05:41:31'),
(93, 25, 25, 'Plancha', 'Plancha solna 125 64,5x50,8', 'unidad', '13000.00', '0.00', '1', '2020-11-14 05:42:12', '2020-11-14 05:42:12'),
(94, 25, 25, 'Plancha', 'Plancha de speedmaster 52,5x45,9', 'unidad', '10000.00', '0.00', '1', '2020-11-14 05:42:35', '2020-11-14 05:44:52'),
(95, 25, 25, 'Plancha', 'Plancha de Medio pliego', 'unidad', '20000.00', '0.00', '1', '2020-11-14 05:43:13', '2020-11-14 05:43:13'),
(96, 25, 25, 'Plancha', 'Plancha Harris 82,5x69,2', 'unidad', '35000.00', '0.00', '1', '2020-11-14 05:43:49', '2020-11-14 05:43:49'),
(97, 25, 25, 'Plancha', 'Plancha de pliego', 'unidad', '45000.00', '0.00', '1', '2020-11-14 05:44:08', '2020-11-14 05:44:08'),
(98, 8, 8, 'Papel', 'Bristol blanco', 'pliego', '399.00', '0.00', '1', '2020-11-18 01:22:17', '2020-11-18 01:22:17'),
(99, 9, 9, 'Papel', 'Bristol blanco', 'pliego', '400.00', '0.00', '1', '2020-11-18 01:22:41', '2020-11-18 01:22:41'),
(100, 8, 8, 'Papel', 'Bristol colores', 'pliego', '412.00', '0.00', '1', '2020-11-18 01:23:02', '2020-11-18 01:23:02'),
(101, 9, 9, 'Papel', 'Bristol colores', 'pliego', '422.00', '0.00', '1', '2020-11-18 01:23:17', '2020-11-18 01:23:17'),
(102, 26, 26, 'Papel', 'Periódico blanco', 'pliego', '105.00', '0.00', '1', '2020-11-18 05:53:49', '2020-11-18 05:53:49'),
(103, 26, 26, 'Papel', 'Bond 75 grs', 'pliego', '300.00', '0.00', '1', '2020-11-18 05:54:11', '2021-06-17 20:10:19'),
(104, 8, 8, 'Papel', 'Earth pack 70 grs', 'pliego', '218.00', '0.00', '1', '2020-11-18 16:48:57', '2020-11-18 16:48:57'),
(105, 26, 26, 'Papel', 'Earth pack 70 grs', 'pliego', '245.00', '0.00', '1', '2020-11-18 16:49:23', '2020-11-18 16:49:23'),
(106, 9, 9, 'Papel', 'Quimico', 'pliego', '321.00', '0.00', '1', '2020-11-18 19:07:48', '2020-11-18 19:07:48'),
(107, 9, 9, 'Papel', 'Quimico copia intermedia', 'pliego', '332.00', '0.00', '1', '2020-11-18 19:08:24', '2020-11-18 19:08:24'),
(108, 9, 9, 'Papel', 'Quimico copia final', 'pliego', '307.00', '0.00', '1', '2020-11-18 19:08:46', '2020-11-18 19:08:46'),
(109, 8, 8, 'Papel', 'Quimico', 'pliego', '337.00', '0.00', '1', '2020-11-18 19:09:19', '2020-11-18 19:09:19'),
(110, 8, 8, 'Papel', 'Quimico copia intermedia', 'pliego', '367.00', '0.00', '1', '2020-11-18 19:09:49', '2020-11-18 19:09:49'),
(111, 8, 8, 'Papel', 'Quimico copia final', 'pliego', '311.00', '0.00', '1', '2020-11-18 19:10:18', '2020-11-18 19:10:18'),
(112, 26, 26, 'Papel', 'Quimico', 'pliego', '335.00', '0.00', '1', '2020-11-18 19:10:48', '2020-11-18 19:10:48'),
(113, 26, 26, 'Papel', 'Quimico copia intermedia', 'pliego', '350.00', '0.00', '1', '2020-11-18 19:11:05', '2020-11-18 19:11:05'),
(114, 26, 26, 'Papel', 'Quimico copia final', 'pliego', '325.00', '0.00', '1', '2020-11-18 19:11:32', '2020-11-18 19:11:32'),
(115, 27, 27, 'Papel', 'Lamina imantada 0.3 mm', 'metro', '5400.00', '0.00', '1', '2020-11-18 19:58:18', '2020-11-18 19:58:18'),
(116, 8, 8, 'Papel', 'Adhesivo de seguridad', 'pliego', '1776.00', '0.00', '1', '2020-12-09 21:25:52', '2020-12-09 21:25:52'),
(117, 8, 8, 'Papel', 'Adhesivo p4 multiusos (refrigeración)', 'pliego', '1834.00', '0.00', '1', '2020-12-09 21:40:46', '2020-12-09 21:40:46'),
(118, 9, 9, 'Papel', 'Adhesivo de seguridad', 'pliego', '1805.00', '0.00', '1', '2020-12-10 18:58:17', '2020-12-10 18:58:17'),
(119, 9, 9, 'Papel', 'Adhesivo de seguridad refrigeración', 'pliego', '2175.00', '0.00', '1', '2020-12-10 18:58:57', '2020-12-10 18:58:57'),
(120, 9, 9, 'Papel', 'Propalcote 150 grs', 'pliego', '438.00', '0.00', '1', '2020-12-11 00:35:47', '2020-12-15 16:04:22'),
(121, 9, 9, 'Papel', 'Bond 75 grs', 'pliego', '382.00', '0.00', '1', '2020-12-17 20:00:42', '2021-06-17 20:10:03'),
(122, 33, 33, 'Papel', 'Kraft o chip 300 grs', 'pliego', '497.00', '0.00', '1', '2020-12-17 20:01:32', '2020-12-17 20:01:32'),
(123, 9, 9, 'Papel', 'Carton blanco reverso blanco claibre 49 (290 grs)', 'pliego', '1240.00', '0.00', '1', '2021-01-18 19:50:35', '2021-01-18 19:50:35'),
(124, 9, 9, 'Papel', 'Maule calibre 16', 'pliego', '992.00', '0.00', '1', '2021-01-19 00:21:10', '2021-01-19 00:21:10'),
(125, 35, 35, 'Papel', 'Kraft semiextensible 80 grs', 'metro cuadrado', '542.00', '0.00', '1', '2021-01-28 12:33:58', '2021-01-28 12:33:58'),
(126, 8, 8, 'Papel', 'Propalcote 280 grs', 'pliego', '871.00', '0.00', '1', '2021-02-11 00:46:47', '2021-02-11 00:46:47'),
(127, 10, 10, 'impresionf', 'Vinilo impresión y corte (mínimo 50x90)', 'cm cuadrado', '5.50', '0.00', '1', '2021-03-04 15:22:37', '2021-03-04 15:23:42'),
(128, 9, 9, 'Papel', 'Propalcote 200 grs 60x90', 'pliego', '450.00', '0.00', '1', '2021-03-11 23:27:08', '2021-03-11 23:27:08'),
(129, 9, 9, 'Papel', 'Propalcote 200 grs 70x100', 'pliego', '584.00', '0.00', '1', '2021-03-11 23:34:48', '2021-03-11 23:34:48'),
(130, 8, 8, 'Papel', 'Propalcote 300 grs una cara', 'pliego', '987.00', '0.00', '1', '2021-03-11 23:36:17', '2021-03-11 23:36:17'),
(131, 8, 8, 'Papel', 'Propalcote 320 grs 60x90', 'pliego', '829.00', '0.00', '1', '2021-03-11 23:36:54', '2021-04-26 21:42:10'),
(132, 8, 8, 'Papel', 'Propalcote 320 grs 70x100', 'pliego', '1034.00', '0.00', '1', '2021-03-11 23:37:19', '2021-03-11 23:37:19'),
(133, 8, 8, 'Papel', 'Propalcote 200 grs 60x90', 'pliego', '614.00', '0.00', '1', '2021-03-11 23:37:56', '2021-03-11 23:37:56'),
(134, 8, 8, 'Papel', 'Propalcote 200 grs 70x10', 'pliego', '476.00', '0.00', '1', '2021-03-11 23:38:19', '2021-03-11 23:38:19'),
(135, 9, 9, 'Papel', 'Propalcote 300 grs una cara', 'pliego', '910.00', '0.00', '1', '2021-03-12 00:28:34', '2021-03-12 00:28:34'),
(136, 9, 9, 'Papel', 'Propalcote 320 grs 70x100', 'pliego', '953.00', '0.00', '1', '2021-03-12 00:28:56', '2021-03-12 00:28:56'),
(137, 8, 8, 'Papel', 'Earth pack 90 grs', 'pliego', '290.00', '0.00', '1', '2021-03-24 20:58:27', '2021-03-24 20:58:27'),
(138, 9, 9, 'Papel', 'Earth pack 90 grs', 'pliego', '256.00', '0.00', '1', '2021-03-24 20:59:07', '2021-03-24 20:59:07'),
(139, 38, 38, 'Papel', 'Carton ultra 40', 'pliego', '1275.00', '0.00', '1', '2021-04-11 20:58:48', '2021-04-11 20:59:19'),
(140, 38, 38, 'Papel', 'Carton ultra 48', 'pliego', '1490.00', '0.00', '1', '2021-04-11 20:59:08', '2021-04-11 20:59:08'),
(141, 38, 38, 'Papel', 'Carton Came 40', 'pliego', '790.00', '0.00', '1', '2021-04-11 20:59:43', '2021-04-11 20:59:43'),
(142, 38, 38, 'Papel', 'Carton Came 48', 'pliego', '990.00', '0.00', '1', '2021-04-11 21:00:02', '2021-04-11 21:00:02'),
(143, 38, 38, 'Papel', 'Poly board 15', 'pliego', '1400.00', '0.00', '1', '2021-04-11 21:01:01', '2021-04-11 21:01:01'),
(144, 38, 38, 'Papel', 'Poly Board 18', 'pliego', '1440.00', '0.00', '1', '2021-04-11 21:01:27', '2021-04-11 21:01:27'),
(145, 38, 38, 'Papel', 'Poly Board 20', 'pliego', '1540.00', '0.00', '1', '2021-04-11 21:01:58', '2021-04-11 21:01:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos`
--

CREATE TABLE `costos` (
  `id` int(10) UNSIGNED NOT NULL,
  `idorden` int(10) UNSIGNED NOT NULL,
  `idcostois` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT NULL,
  `orden` int(10) DEFAULT NULL,
  `completado` tinyint(10) DEFAULT NULL,
  `fecha_termina` date DEFAULT NULL,
  `terminado` tinyint(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalletrabajos`
--

CREATE TABLE `detalletrabajos` (
  `id` int(10) UNSIGNED NOT NULL,
  `idorden` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ingresos`
--

CREATE TABLE `detalle_ingresos` (
  `id` int(10) UNSIGNED NOT NULL,
  `idingreso` int(10) UNSIGNED NOT NULL,
  `idarticulo` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `id` int(10) UNSIGNED NOT NULL,
  `idproveedor` int(10) UNSIGNED NOT NULL,
  `idusuario` int(10) UNSIGNED NOT NULL,
  `tipo_comprobante` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie_comprobante` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_comprobante` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `impuesto` decimal(4,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2020_09_28_093145_create_categorias_table', 1),
(3, '2020_10_02_111948_create_articulos_table', 1),
(4, '2020_10_08_095941_create_personas_table', 1),
(5, '2020_10_08_100909_create_proveedores_table', 1),
(6, '2020_10_08_110320_create_roles_table', 1),
(7, '2020_10_08_110500_create_users_table', 2),
(8, '2020_10_14_102526_create_ingresos_table', 2),
(9, '2020_10_14_102646_create_detalle_ingresos_table', 2),
(10, '2020_10_30_120111_add_imagen_to_categorias', 3),
(17, '2020_11_03_191007_create_costoproduccions_table', 4),
(18, '2020_11_05_001007_create_costois_table', 4),
(19, '2020_11_06_013122_create_costos_table', 5),
(20, '2020_11_06_013228_create_detalletrabajos_table', 5),
(21, '2020_11_06_161836_create_clientes_table', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opcion_atributos`
--

CREATE TABLE `opcion_atributos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_atributo` int(10) UNSIGNED DEFAULT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(20,2) DEFAULT NULL,
  `opciones` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alerta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `opcion_atributos`
--

INSERT INTO `opcion_atributos` (`id`, `id_atributo`, `label`, `valor`, `opciones`, `descripcion`, `alerta`, `orden`) VALUES
(2, 6, 'Carton kraft 300 grs', '0.00', NULL, '', '', 1),
(3, 6, 'Propalcote 280grs', '300.00', NULL, '', '', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordentrabajos`
--

CREATE TABLE `ordentrabajos` (
  `id` int(10) UNSIGNED NOT NULL,
  `idcliente` int(10) UNSIGNED NOT NULL,
  `idarticulo` int(10) UNSIGNED NOT NULL,
  `idcostois` int(10) DEFAULT NULL,
  `cantidad` decimal(20,2) DEFAULT NULL,
  `ancho_material` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `largo_material` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `impuesto` decimal(30,2) DEFAULT NULL,
  `produccion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalles_diseno` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carpeta_cliente` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `totalParcial` decimal(30,2) DEFAULT NULL,
  `descuento` decimal(20,2) DEFAULT NULL,
  `abono` decimal(20,2) DEFAULT NULL,
  `saldo` decimal(20,2) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_entrega` date NOT NULL DEFAULT '0000-00-00',
  `created_at` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_documento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombre`, `tipo_documento`, `num_documento`, `direccion`, `telefono`, `email`, `created_at`, `updated_at`) VALUES
(2, 'julian', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Julian Agudelo', 'DNI', '94506025', 'djfladsj', 'dsfadsjl', 'lupaproyectos@gmail.com', '2020-10-30 15:46:21', '2020-10-30 15:46:21'),
(8, 'Ashe', 'RUT', NULL, NULL, NULL, NULL, '2020-11-04 09:19:08', '2020-11-05 16:07:48'),
(9, 'Cooimpresores', 'RUT', NULL, 'Cra # 18-50', NULL, NULL, '2020-11-05 14:30:32', '2020-11-05 16:07:44'),
(10, 'Ecoprint', 'RUT', '4646', 'Calle 17 #4-50', NULL, NULL, '2020-11-05 16:06:48', '2020-11-05 16:07:39'),
(11, 'Number ONE', 'RUT', 'sdf', NULL, NULL, NULL, '2020-11-06 18:25:00', '2020-11-06 18:25:25'),
(16, 'Tomglen', 'CC', NULL, NULL, NULL, NULL, '2020-11-09 04:10:43', '2020-11-09 05:16:09'),
(24, 'La selecta 1', 'CC', 'sdfa', 'Carrera 31 #9c70', 'sdf', 'lupaproyectos@gmail.com', '2020-11-09 04:40:29', '2021-08-03 16:36:47'),
(25, 'CTP MAX FUXION', 'CC', NULL, 'Carrera 3 No. 20-01', '8816070', 'maxfuxionctp@gmail.com', '2020-11-14 05:37:43', '2020-11-14 05:37:43'),
(26, 'Servipapeles Colombia SAS', 'RUT', '9003046902', 'Cra 5 # 17-28', '8823677', 'servipapeles.colombia4761@gmail.com', '2020-11-18 05:53:18', '2020-12-05 19:09:59'),
(27, 'Proadhesivos', 'RUC', NULL, NULL, NULL, NULL, '2020-11-18 19:57:41', '2020-11-18 19:57:41'),
(28, 'Insumos Artes Graficas (Naranjo)', 'RUC', NULL, 'Cra 3 #18-17', '3156552542', 'insumosartesgraficas@live.com', '2020-11-19 19:41:37', '2020-11-19 19:41:37'),
(29, 'Tintascol SAS', 'RUC', NULL, 'Cra 3 #20-28', '3183396446', 'tintascols.a.s@hotmail.com', '2020-11-19 19:43:23', '2020-11-19 19:43:23'),
(30, 'Soluciones Empresariales y Logísticas SAS', 'RUC', NULL, 'Cra 5 #19-69', '3113440411', 'seylsas@hotmail.com', '2020-11-19 19:46:15', '2020-11-19 19:46:15'),
(31, 'Transprensa', 'RUC', NULL, 'Calle 8 #27-14 Alameda', '3146498146', 'crm.alameda@transprensa.com.co', '2020-12-05 21:21:36', '2020-12-05 21:21:36'),
(32, 'Incolpa', 'RUC', NULL, NULL, '6669515', NULL, '2020-12-14 18:31:16', '2020-12-14 18:31:16'),
(33, 'Mundo Materiales y Recuperados SAS', 'RUC', NULL, 'Calle 20 #2-47', '3166874233', 'mundomrsas@gmail.com', '2020-12-14 18:32:48', '2020-12-14 18:32:48'),
(34, 'Aircom', 'RUC', NULL, NULL, '3154901106', 'aircom20@hotmail.com', '2020-12-14 18:33:31', '2020-12-14 18:33:31'),
(35, 'Imexcyan Trading SAS', 'RUT', '901122956', 'Calle 26 con 5', '317 2737215', 'imexcyantrading@gmail.com', '2021-01-07 16:50:58', '2021-01-07 16:50:58'),
(36, 'Guillotina James', 'CC', NULL, 'Calle 19 # 2-37', '8893977 - 8963174', 'azserviciosgraficos@gmail.com', '2021-02-11 01:18:06', '2021-02-11 01:18:33'),
(37, 'Dispapeles', 'RUT', NULL, 'Calle 24 #4-63', '8851712', NULL, '2021-04-06 21:54:14', '2021-04-06 21:55:38'),
(38, 'JD Comercializadora', 'RUC', NULL, 'Cra 2 #19-73', '3899674 - 3142129822', 'comercializadora-jd@hotmail.com', '2021-04-06 22:24:51', '2021-04-06 22:24:51'),
(39, 'El Argentino', 'DNI', NULL, NULL, '3163423277', 'lupaproyectos@gmail.com', '2021-07-23 16:12:28', '2021-07-23 16:12:56'),
(40, 'La Mascoteria', NULL, NULL, NULL, '+573183953381', NULL, '2021-07-23 16:17:38', '2021-07-23 16:19:32'),
(41, 'La Piketa', 'NIT', NULL, 'Carrera 5 #6-26', '3122682256', NULL, '2021-08-02 16:34:41', '2021-08-02 16:34:41'),
(43, 'la burguesa', NULL, NULL, 'sdfa', NULL, 'sdfa', '2021-08-03 15:22:06', '2021-08-03 15:22:06'),
(44, 'Martin Pizza', NULL, NULL, NULL, NULL, NULL, '2021-08-10 15:48:37', '2021-08-10 15:48:37'),
(80, 'Martin', 'DNI', NULL, NULL, NULL, NULL, '2021-08-18 15:55:40', '2021-08-18 15:55:40'),
(99, 'la selecta', 'DNI', NULL, NULL, NULL, NULL, '2021-08-18 16:22:45', '2021-08-18 16:22:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(10) UNSIGNED NOT NULL,
  `contacto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_contacto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `contacto`, `telefono_contacto`) VALUES
(8, 'Elver', NULL),
(9, 'Vicent', NULL),
(10, NULL, NULL),
(11, NULL, NULL),
(25, 'Abelardo', '3148038624'),
(26, 'Doña Luz', NULL),
(27, NULL, NULL),
(28, NULL, NULL),
(29, 'Rodrigo Satizabal', '3146031352'),
(30, 'Martín E. Sánchez', '3207117556'),
(31, 'Daira Tapasco', NULL),
(32, 'Luis Alberto Olaya', '3163269117'),
(33, 'Viviana', '3167442587'),
(34, 'Angelo Miranda', NULL),
(35, 'Cesar', '318 5102088'),
(36, 'James', NULL),
(37, NULL, NULL),
(38, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `condicion`) VALUES
(1, 'Administrador', 'Administradores de área', 1),
(2, 'Vendedor', 'Vendedor área venta', 1),
(3, 'Almacenero', 'Almacenero área compras', 1),
(4, 'Diseñador', 'Área de diseño', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT '1',
  `idrol` int(10) UNSIGNED NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `usuario`, `password`, `condicion`, `idrol`, `remember_token`) VALUES
(7, 'admin', '$2y$10$dl7GtFi4rBFZ.KrmvS8JFuYGbzjXDxUtmTYvfWkm.UXIDNuGuMgca', 1, 1, NULL),
(2, 'julian', '$2y$10$2yoQ63PFFAFPgcuJ2eCYIOYIJ3Snhj068IOEld/nPcDAs7miVYsie', 1, 1, 'vrC3Fy1wzL5SkLC5WMJuHWeaJrw9B1h4GIA0lrbFnJOOmSRrEwhsCkudbjsZ');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `articulos_nombre_unique` (`nombre`),
  ADD KEY `articulos_idcategoria_foreign` (`idcategoria`);

--
-- Indices de la tabla `atributos`
--
ALTER TABLE `atributos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atributos_id_articulo_foreign` (`id_articulo`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD KEY `clientes_id_foraing` (`id`) USING BTREE;

--
-- Indices de la tabla `costois`
--
ALTER TABLE `costois`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costois_idproveedor_foreign` (`idproveedor`),
  ADD KEY `costois_idpersona_foreign` (`idpersona`);

--
-- Indices de la tabla `costos`
--
ALTER TABLE `costos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costos_idcostois_foreign` (`idcostois`),
  ADD KEY `costos_idorden_foreign` (`idorden`);

--
-- Indices de la tabla `detalletrabajos`
--
ALTER TABLE `detalletrabajos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalletrabajos_idorden_foreign` (`idorden`);

--
-- Indices de la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_ingresos_idingreso_foreign` (`idingreso`),
  ADD KEY `detalle_ingresos_idarticulo_foreign` (`idarticulo`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingresos_idproveedor_foreign` (`idproveedor`),
  ADD KEY `ingresos_idusuario_foreign` (`idusuario`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `opcion_atributos`
--
ALTER TABLE `opcion_atributos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opcion_atributos_id_atributo_foreign` (`id_atributo`);

--
-- Indices de la tabla `ordentrabajos`
--
ALTER TABLE `ordentrabajos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcliente` (`idcliente`) USING BTREE,
  ADD KEY `ordentrabajos_ibfk_2` (`idarticulo`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personas_nombre_unique` (`nombre`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD KEY `proveedores_id_foreign` (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_nombre_unique` (`nombre`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `users_usuario_unique` (`usuario`),
  ADD KEY `users_id_foreign` (`id`),
  ADD KEY `users_idrol_foreign` (`idrol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `articulos`
--
ALTER TABLE `articulos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `atributos`
--
ALTER TABLE `atributos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `costois`
--
ALTER TABLE `costois`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT de la tabla `costos`
--
ALTER TABLE `costos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalletrabajos`
--
ALTER TABLE `detalletrabajos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `opcion_atributos`
--
ALTER TABLE `opcion_atributos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ordentrabajos`
--
ALTER TABLE `ordentrabajos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `articulos_idcategoria_foreign` FOREIGN KEY (`idcategoria`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_id_foreign` FOREIGN KEY (`id`) REFERENCES `personas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `costois`
--
ALTER TABLE `costois`
  ADD CONSTRAINT `costois_idpersona_foreign` FOREIGN KEY (`idpersona`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `costois_idproveedor_foreign` FOREIGN KEY (`idproveedor`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `costos`
--
ALTER TABLE `costos`
  ADD CONSTRAINT `costos_idcostois_foreign` FOREIGN KEY (`idcostois`) REFERENCES `costois` (`id`),
  ADD CONSTRAINT `costos_idorden_foreign` FOREIGN KEY (`idorden`) REFERENCES `ordentrabajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalletrabajos`
--
ALTER TABLE `detalletrabajos`
  ADD CONSTRAINT `detalletrabajos_idorden_foreign` FOREIGN KEY (`idorden`) REFERENCES `ordentrabajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD CONSTRAINT `detalle_ingresos_idarticulo_foreign` FOREIGN KEY (`idarticulo`) REFERENCES `articulos` (`id`),
  ADD CONSTRAINT `detalle_ingresos_idingreso_foreign` FOREIGN KEY (`idingreso`) REFERENCES `ingresos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `ingresos_idproveedor_foreign` FOREIGN KEY (`idproveedor`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `ingresos_idusuario_foreign` FOREIGN KEY (`idusuario`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `opcion_atributos`
--
ALTER TABLE `opcion_atributos`
  ADD CONSTRAINT `opcion_atributos_id_atributo_foreign` FOREIGN KEY (`id_atributo`) REFERENCES `atributos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ordentrabajos`
--
ALTER TABLE `ordentrabajos`
  ADD CONSTRAINT `ordentrabajos_ibfk_2` FOREIGN KEY (`idarticulo`) REFERENCES `articulos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_id_foreign` FOREIGN KEY (`id`) REFERENCES `personas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_id_foreign` FOREIGN KEY (`id`) REFERENCES `personas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_idrol_foreign` FOREIGN KEY (`idrol`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
