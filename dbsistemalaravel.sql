-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-05-2026 a las 11:24:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `id` int(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `actividad` varchar(1000) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos`
--

CREATE TABLE `activos` (
  `id` int(10) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `activo` varchar(50) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `ubicacion` varchar(50) DEFAULT NULL,
  `responsable` int(10) DEFAULT NULL,
  `clasificacion` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `grupo` varchar(50) DEFAULT NULL,
  `datos_activo` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ajustes`
--

CREATE TABLE `ajustes` (
  `id` int(10) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `detalle` varchar(50) NOT NULL,
  `valor` varchar(200) NOT NULL,
  `categoria` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_item_padre` int(11) DEFAULT NULL,
  `idcategoria` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `tipo_cantidad` varchar(400) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `imagen` varchar(100) NOT NULL DEFAULT 'noimagen',
  `rangos` varchar(400) DEFAULT NULL,
  `precio_venta` decimal(11,2) DEFAULT NULL,
  `iva` tinyint(4) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `tamano` varchar(20) DEFAULT NULL,
  `medida_final` varchar(191) DEFAULT NULL,
  `ancho_final` decimal(10,2) DEFAULT NULL,
  `largo_final` decimal(10,2) DEFAULT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `condicion` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `etiquetas` text DEFAULT NULL,
  `ancho` decimal(12,2) DEFAULT NULL,
  `largo` decimal(12,2) DEFAULT NULL,
  `alto` decimal(12,2) DEFAULT NULL,
  `volumen` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos2`
--

CREATE TABLE `articulos2` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_item_padre` int(11) DEFAULT NULL,
  `idcategoria` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `tipo_cantidad` varchar(400) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `imagen` varchar(100) NOT NULL DEFAULT 'noimagen',
  `rangos` varchar(400) DEFAULT NULL,
  `precio_venta` decimal(11,2) DEFAULT NULL,
  `iva` tinyint(4) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `tamano` varchar(20) DEFAULT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `condicion` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `etiquetas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulo_troquels`
--

CREATE TABLE `articulo_troquels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `articulo_id` int(10) UNSIGNED NOT NULL,
  `cabida` int(11) NOT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `ancho_impresion` decimal(10,2) DEFAULT NULL,
  `largo_impresion` decimal(10,2) DEFAULT NULL,
  `tamano` varchar(191) DEFAULT NULL,
  `mostrar` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributos`
--

CREATE TABLE `atributos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_articulo` int(10) UNSIGNED DEFAULT NULL,
  `valor` decimal(30,0) DEFAULT NULL,
  `tipo_atributo` varchar(10) DEFAULT NULL,
  `tipo_campo` int(11) DEFAULT NULL,
  `tipo_valor` int(11) DEFAULT NULL,
  `nombre` varchar(20) DEFAULT NULL,
  `tipo_impresion` varchar(20) DEFAULT NULL,
  `nota` varchar(50) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `alerta` varchar(100) DEFAULT NULL,
  `unidad_medida` varchar(50) DEFAULT NULL,
  `operacion` varchar(10) DEFAULT NULL,
  `minimo` int(11) DEFAULT NULL,
  `maximo` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributos_tienda`
--

CREATE TABLE `atributos_tienda` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(191) NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `etiquetas` text DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `valor_extra` decimal(11,2) NOT NULL DEFAULT 0.00,
  `formula` text DEFAULT NULL,
  `dependencia` varchar(255) DEFAULT NULL,
  `condicion` text DEFAULT NULL,
  `es_buscable` tinyint(1) NOT NULL DEFAULT 1,
  `mostrar_en_producto` tinyint(1) NOT NULL DEFAULT 1,
  `seleccion_multiple` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `padre_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `banner` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias2`
--

CREATE TABLE `categorias2` (
  `id` int(10) UNSIGNED NOT NULL,
  `padre_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(256) DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `banner` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_cliente` varchar(20) DEFAULT NULL,
  `razonsocial` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccionf` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `pais` varchar(50) DEFAULT NULL,
  `contacto` varchar(50) DEFAULT NULL,
  `sitio_web` varchar(50) DEFAULT NULL,
  `redes_sociales` varchar(50) DEFAULT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `num_documento` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_contacto`
--

CREATE TABLE `cliente_contacto` (
  `id` int(10) NOT NULL,
  `cliente_id` int(10) DEFAULT NULL,
  `contacto_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_envio`
--

CREATE TABLE `cliente_envio` (
  `id` int(10) NOT NULL,
  `cliente_id` int(10) DEFAULT NULL,
  `datosenvio_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_factura`
--

CREATE TABLE `cliente_factura` (
  `id` int(10) NOT NULL,
  `cliente_id` int(10) NOT NULL,
  `facturacion_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes`
--

CREATE TABLE `comprobantes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `num_comprobante` int(11) DEFAULT NULL,
  `fuente_id` int(10) DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `datos_factura_id` int(10) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fecha` date DEFAULT NULL,
  `forma_pago` varchar(50) DEFAULT NULL,
  `subtotal` decimal(20,2) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(20,2) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT NULL,
  `impuestos` decimal(20,2) DEFAULT NULL,
  `abono` decimal(20,2) DEFAULT NULL,
  `saldo` decimal(20,2) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `transportadora` varchar(50) DEFAULT NULL,
  `monto_aplicado_anticipo` decimal(20,2) NOT NULL DEFAULT 0.00,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes2`
--

CREATE TABLE `comprobantes2` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `num_comprobante` int(11) DEFAULT NULL,
  `fuente_id` int(10) DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `datos_factura_id` int(10) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fecha` date DEFAULT NULL,
  `forma_pago` varchar(50) DEFAULT NULL,
  `subtotal` decimal(20,2) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(20,2) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT NULL,
  `impuestos` decimal(20,2) DEFAULT NULL,
  `abono` decimal(20,2) DEFAULT NULL,
  `saldo` decimal(20,2) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `transportadora` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes3`
--

CREATE TABLE `comprobantes3` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `num_comprobante` int(11) DEFAULT NULL,
  `fuente_id` int(10) DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `datos_factura_id` int(10) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fecha` date DEFAULT NULL,
  `forma_pago` varchar(50) DEFAULT NULL,
  `subtotal` decimal(20,2) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(20,2) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT NULL,
  `impuestos` decimal(20,2) DEFAULT NULL,
  `abono` decimal(20,2) DEFAULT NULL,
  `saldo` decimal(20,2) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `transportadora` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobates`
--

CREATE TABLE `comprobates` (
  `id` int(10) UNSIGNED NOT NULL,
  `num_comprobante` int(11) NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `forma_pago` varchar(50) NOT NULL,
  `subtotal` decimal(20,2) NOT NULL,
  `descuento` decimal(20,2) NOT NULL,
  `total` decimal(20,2) NOT NULL,
  `impuestos` decimal(20,2) NOT NULL,
  `fuente` varchar(50) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(10) NOT NULL,
  `favorito` int(1) NOT NULL,
  `nombre` varchar(400) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `telefono_particular` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `tipo_contacto` varchar(100) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `nombre_asistente` varchar(100) DEFAULT NULL,
  `telefono_asistente` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costois`
--

CREATE TABLE `costois` (
  `id` int(10) UNSIGNED NOT NULL,
  `idproveedor` int(10) UNSIGNED NOT NULL,
  `idpersona` int(10) UNSIGNED NOT NULL,
  `tipo_costo` varchar(20) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_medida` varchar(50) NOT NULL,
  `valor` decimal(11,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos`
--

CREATE TABLE `costos` (
  `id` int(10) UNSIGNED NOT NULL,
  `ordentrabajo_id` int(10) UNSIGNED NOT NULL,
  `costois_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(20) DEFAULT NULL,
  `descripcion` decimal(20,2) DEFAULT NULL,
  `cantidad` decimal(20,2) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `completado` int(11) NOT NULL DEFAULT 0,
  `pago` int(11) NOT NULL DEFAULT 0,
  `fecha_termina` date DEFAULT NULL,
  `terminado` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costosproduccion`
--

CREATE TABLE `costosproduccion` (
  `id` int(10) UNSIGNED NOT NULL,
  `idproveedor` int(10) UNSIGNED NOT NULL,
  `idpersona` int(10) UNSIGNED NOT NULL,
  `tipo_costo` varchar(20) NOT NULL,
  `nombre` varchar(7) DEFAULT NULL,
  `unidad_medida` varchar(10) NOT NULL,
  `valor` decimal(4,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costo_articulos`
--

CREATE TABLE `costo_articulos` (
  `id` int(10) UNSIGNED NOT NULL,
  `idarticulo` int(10) UNSIGNED NOT NULL,
  `idopcion` int(10) UNSIGNED DEFAULT 0,
  `idcostois` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(20) NOT NULL,
  `descripcion` varchar(400) NOT NULL,
  `orden_produccion` int(11) NOT NULL,
  `fraccion` decimal(10,2) NOT NULL,
  `rentabilidad` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `valorfull` decimal(20,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cruce_cartera`
--

CREATE TABLE `cruce_cartera` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `recibo_pago_id` bigint(20) UNSIGNED NOT NULL,
  `comprobante_id` bigint(20) UNSIGNED NOT NULL,
  `monto` decimal(20,2) NOT NULL,
  `fecha_cruce` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datosenvio`
--

CREATE TABLE `datosenvio` (
  `id` int(11) UNSIGNED NOT NULL,
  `favorito` int(1) NOT NULL,
  `idcliente` int(11) DEFAULT NULL,
  `contacto` varchar(50) DEFAULT NULL,
  `empresa` varchar(50) DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `pais` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_factura`
--

CREATE TABLE `datos_factura` (
  `id` int(10) NOT NULL,
  `favorito` int(1) NOT NULL,
  `razonsocial` varchar(400) NOT NULL,
  `tipo_persona` varchar(50) DEFAULT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `digito` varchar(10) DEFAULT NULL,
  `direccion` varchar(400) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `ciudad` varchar(50) NOT NULL,
  `departamento` varchar(50) NOT NULL,
  `pais` varchar(50) NOT NULL,
  `actividad` varchar(10) DEFAULT NULL,
  `responsable` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalletrabajos`
--

CREATE TABLE `detalletrabajos` (
  `id` int(10) UNSIGNED NOT NULL,
  `ordentrabajo_id` int(10) UNSIGNED NOT NULL,
  `costos_id` int(11) DEFAULT NULL,
  `titulo` varchar(20) NOT NULL,
  `descripcion` varchar(400) DEFAULT NULL,
  `valor` varchar(1910) NOT NULL,
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
-- Estructura de tabla para la tabla `egresos`
--

CREATE TABLE `egresos` (
  `id` int(10) UNSIGNED NOT NULL,
  `cuenta_contable` varchar(100) NOT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `tipo_egreso` varchar(20) NOT NULL,
  `forma_pago` varchar(20) NOT NULL,
  `subtotal` decimal(20,2) NOT NULL,
  `total` decimal(4,2) NOT NULL,
  `iva` decimal(4,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `tipo_doc` varchar(50) DEFAULT NULL,
  `num_doc` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `lugar_nacimiento` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `estado_civil` varchar(50) DEFAULT NULL,
  `num_hijos` varchar(11) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `tipo_contrato` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `tipo_jornada` varchar(50) DEFAULT NULL,
  `turno` varchar(50) DEFAULT NULL,
  `horas_semanales` varchar(11) DEFAULT NULL,
  `num_cuenta_banco` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `tipo_cuenta` varchar(50) DEFAULT NULL,
  `num_afiliacion_social` varchar(50) DEFAULT NULL,
  `pension` varchar(100) DEFAULT NULL,
  `eps` varchar(100) DEFAULT NULL,
  `arl` varchar(100) DEFAULT NULL,
  `nivel_estudio` varchar(100) DEFAULT NULL,
  `titulos` text DEFAULT NULL,
  `certificaciones` text DEFAULT NULL,
  `idiomas` text DEFAULT NULL,
  `habilidades` text DEFAULT NULL,
  `experiencia` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `talla_dotacion` varchar(10) DEFAULT NULL,
  `img_doc` varchar(255) DEFAULT NULL,
  `pdf_hoja` varchar(255) DEFAULT NULL,
  `pdf_contrato` varchar(255) DEFAULT NULL,
  `contacto_esposa` text DEFAULT NULL,
  `contacto_padres` text DEFAULT NULL,
  `contacto_emergencia` text DEFAULT NULL,
  `info_medica` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas`
--

CREATE TABLE `entregas` (
  `id` int(10) NOT NULL,
  `ordentrabajo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pedido_id` int(10) NOT NULL,
  `fecha` date NOT NULL,
  `numero_remision` int(10) NOT NULL,
  `observaciones` varchar(500) NOT NULL,
  `user_id` int(10) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `saldo_anterior` int(11) NOT NULL DEFAULT 0,
  `saldo_restante` int(11) NOT NULL DEFAULT 0,
  `tipo_documento` varchar(191) DEFAULT NULL,
  `comprobante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cuentacobro_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrega_items`
--

CREATE TABLE `entrega_items` (
  `id` int(10) NOT NULL,
  `entrega_id` int(10) NOT NULL,
  `linea_comprobante_id` int(10) NOT NULL,
  `cantidad` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `flujo_produccion`
--

CREATE TABLE `flujo_produccion` (
  `id` int(11) NOT NULL,
  `orden_trabajo_id` int(11) NOT NULL,
  `proceso` varchar(50) NOT NULL,
  `fecha_inicia` date DEFAULT NULL,
  `fecha_termina` date DEFAULT NULL,
  `hora_inicia` time DEFAULT NULL,
  `hora_termina` time DEFAULT NULL,
  `cantidad` int(50) DEFAULT NULL,
  `siguiente_proceso` varchar(50) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `observaciones` varchar(400) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes`
--

CREATE TABLE `imagenes` (
  `id` int(10) NOT NULL,
  `id_tabla` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `orden` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `tipo_comprobante` varchar(20) NOT NULL,
  `serie_comprobante` varchar(7) DEFAULT NULL,
  `num_comprobante` varchar(10) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `impuesto` decimal(4,2) NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventarios_materia_primas`
--

CREATE TABLE `inventarios_materia_primas` (
  `id` int(10) NOT NULL,
  `asignado_id` int(10) DEFAULT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `costois_id` varchar(50) DEFAULT NULL,
  `detalles` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `uso` varchar(50) DEFAULT NULL,
  `cantidad` bigint(10) DEFAULT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `cambio` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `linea_comprobantes`
--

CREATE TABLE `linea_comprobantes` (
  `id` int(10) UNSIGNED NOT NULL,
  `comprobante_id` int(10) UNSIGNED NOT NULL,
  `articulo_id` int(10) UNSIGNED NOT NULL,
  `ordentrabajo_id` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `valor_unitario` decimal(20,0) DEFAULT NULL,
  `subtotal` decimal(20,2) DEFAULT NULL,
  `descuento` decimal(20,0) DEFAULT NULL,
  `impuesto` decimal(20,0) DEFAULT NULL,
  `valor_total` decimal(20,0) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario` varchar(191) DEFAULT NULL,
  `contenido` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_materia_primas`
--

CREATE TABLE `movimiento_materia_primas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventarios_materia_prima_id` bigint(20) UNSIGNED NOT NULL,
  `proveedores_id` bigint(20) UNSIGNED NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `costo_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opcion_atributos`
--

CREATE TABLE `opcion_atributos` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_atributo` int(10) UNSIGNED DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `valor` decimal(20,2) DEFAULT NULL,
  `opciones` varchar(50) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `alerta` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordentrabajos`
--

CREATE TABLE `ordentrabajos` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `articulo_id` int(10) UNSIGNED NOT NULL,
  `medida_final` varchar(50) DEFAULT NULL,
  `cantidad` decimal(20,0) DEFAULT NULL,
  `cantidad_entregada` int(11) NOT NULL DEFAULT 0,
  `tamano` decimal(20,2) DEFAULT NULL,
  `medida_material` varchar(50) DEFAULT NULL,
  `valor_unitario` decimal(20,0) DEFAULT NULL,
  `impuesto` decimal(30,2) DEFAULT NULL,
  `valor_impuesto` decimal(20,2) DEFAULT NULL,
  `produccion` varchar(20) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `cabida` decimal(20,2) DEFAULT NULL,
  `detalles_diseno` varchar(3000) DEFAULT NULL,
  `carpeta_cliente` varchar(50) DEFAULT NULL,
  `observaciones` varchar(3000) DEFAULT NULL,
  `totalParcial` decimal(30,0) DEFAULT NULL,
  `descuento` decimal(20,0) DEFAULT NULL,
  `abono` decimal(20,0) DEFAULT NULL,
  `saldo` decimal(20,0) DEFAULT NULL,
  `total` decimal(20,0) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `prioridad` varchar(50) DEFAULT NULL,
  `impresa` int(1) DEFAULT NULL,
  `plancha` int(11) DEFAULT NULL,
  `pago` int(11) DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cantidad_original` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_remision`
--

CREATE TABLE `pedidos_remision` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `remision_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cuentacobro_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `num_documento` varchar(20) DEFAULT NULL,
  `direccion` varchar(70) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procesos`
--

CREATE TABLE `procesos` (
  `id` int(10) NOT NULL,
  `posicion` int(10) NOT NULL,
  `proceso` varchar(50) NOT NULL,
  `cantidad` int(10) DEFAULT NULL,
  `organizacion` varchar(50) DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(10) UNSIGNED NOT NULL,
  `contacto` varchar(50) DEFAULT NULL,
  `telefono_contacto` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `num_documento` varchar(20) DEFAULT NULL,
  `direccion` varchar(70) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibo_pagos`
--

CREATE TABLE `recibo_pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comprobante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(20,2) NOT NULL,
  `saldo_recibo` decimal(20,2) DEFAULT 0.00,
  `forma_pago` varchar(191) NOT NULL,
  `num_recibo` varchar(191) NOT NULL,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_produccion`
--

CREATE TABLE `registros_produccion` (
  `id` int(10) UNSIGNED NOT NULL,
  `orden_trabajo_id` int(10) DEFAULT NULL,
  `empleado_id` int(10) UNSIGNED NOT NULL,
  `actividad` varchar(50) DEFAULT NULL,
  `elemento` varchar(100) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `minutos` int(10) UNSIGNED DEFAULT NULL,
  `cantidad` int(10) UNSIGNED DEFAULT NULL,
  `unidad` varchar(20) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `estado` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT 1,
  `produccion` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `statusproduccion`
--

CREATE TABLE `statusproduccion` (
  `id` int(10) NOT NULL,
  `idorden` int(10) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `prioridad` int(11) NOT NULL,
  `fecha_termina` timestamp NULL DEFAULT NULL,
  `hora` time NOT NULL,
  `observaciones` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `valor` decimal(30,2) NOT NULL,
  `tamano` int(10) NOT NULL,
  `area` decimal(30,3) NOT NULL,
  `impresiones` int(10) NOT NULL,
  `sobrante` int(11) NOT NULL,
  `cabida` int(10) NOT NULL,
  `tipo_cantidad` varchar(400) DEFAULT NULL,
  `rangos` varchar(400) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `formula_ancho` varchar(191) DEFAULT NULL,
  `formula_largo` varchar(191) DEFAULT NULL,
  `piezas_por_pliego` int(11) NOT NULL DEFAULT 0,
  `gastos_fijos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rentabilidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `permite_troquelado` tinyint(1) NOT NULL DEFAULT 0,
  `imagen` varchar(100) DEFAULT NULL,
  `orden` int(11) NOT NULL,
  `estado` int(2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto_atributo_tienda`
--

CREATE TABLE `tipo_producto_atributo_tienda` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_producto_id` int(11) NOT NULL,
  `atributo_tienda_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto_procesos`
--

CREATE TABLE `tipo_producto_procesos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `costo_unitario` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `empleado_id` int(10) NOT NULL,
  `usuario` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT 1,
  `idrol` varchar(100) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users2`
--

CREATE TABLE `users2` (
  `id` int(10) UNSIGNED NOT NULL,
  `empleado_id` int(10) NOT NULL,
  `usuario` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT 1,
  `idrol` int(10) UNSIGNED NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ajustes`
--
ALTER TABLE `ajustes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `articulos_idcategoria_foreign` (`idcategoria`);

--
-- Indices de la tabla `articulos2`
--
ALTER TABLE `articulos2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `articulos_idcategoria_foreign` (`idcategoria`);

--
-- Indices de la tabla `articulo_troquels`
--
ALTER TABLE `articulo_troquels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `articulo_troquels_articulo_id_foreign` (`articulo_id`);

--
-- Indices de la tabla `atributos`
--
ALTER TABLE `atributos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atributos_id_articulo_foreign` (`id_articulo`);

--
-- Indices de la tabla `atributos_tienda`
--
ALTER TABLE `atributos_tienda`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias2`
--
ALTER TABLE `categorias2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorias_padre_id_foreign` (`padre_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clientes_id_foraing` (`id`) USING BTREE;

--
-- Indices de la tabla `cliente_contacto`
--
ALTER TABLE `cliente_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `contacto_id` (`contacto_id`);

--
-- Indices de la tabla `cliente_envio`
--
ALTER TABLE `cliente_envio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idcliente` (`cliente_id`,`datosenvio_id`);

--
-- Indices de la tabla `cliente_factura`
--
ALTER TABLE `cliente_factura`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comprobantes_tipo_num_unique` (`tipo`,`num_comprobante`) USING BTREE,
  ADD KEY `comprobates_id_cliente_foreign` (`cliente_id`),
  ADD KEY `comprobates_id_user_foreign` (`user_id`);

--
-- Indices de la tabla `comprobantes2`
--
ALTER TABLE `comprobantes2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comprobantes_tipo_num_unique` (`tipo`,`num_comprobante`) USING BTREE,
  ADD KEY `comprobates_id_cliente_foreign` (`cliente_id`),
  ADD KEY `comprobates_id_user_foreign` (`user_id`);

--
-- Indices de la tabla `comprobantes3`
--
ALTER TABLE `comprobantes3`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comprobantes_tipo_num_unique` (`tipo`,`num_comprobante`) USING BTREE,
  ADD KEY `comprobates_id_cliente_foreign` (`cliente_id`),
  ADD KEY `comprobates_id_user_foreign` (`user_id`);

--
-- Indices de la tabla `comprobates`
--
ALTER TABLE `comprobates`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `costos_idcostois_foreign` (`costois_id`),
  ADD KEY `costos_idorden_foreign` (`ordentrabajo_id`);

--
-- Indices de la tabla `costosproduccion`
--
ALTER TABLE `costosproduccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costosproduccion_idproveedor_foreign` (`idproveedor`),
  ADD KEY `costosproduccion_idpersona_foreign` (`idpersona`);

--
-- Indices de la tabla `costo_articulos`
--
ALTER TABLE `costo_articulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `costo_articulos_idopcion_foreign` (`idopcion`),
  ADD KEY `costo_articulos_idcostois_foreign` (`idcostois`),
  ADD KEY `costo_articulos_idarticulo_foreign` (`idarticulo`);

--
-- Indices de la tabla `cruce_cartera`
--
ALTER TABLE `cruce_cartera`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `datosenvio`
--
ALTER TABLE `datosenvio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `datosenvio_idcliente_foreign` (`idcliente`);

--
-- Indices de la tabla `datos_factura`
--
ALTER TABLE `datos_factura`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalletrabajos`
--
ALTER TABLE `detalletrabajos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalletrabajos_idorden_foreign` (`ordentrabajo_id`);

--
-- Indices de la tabla `detalle_ingresos`
--
ALTER TABLE `detalle_ingresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_ingresos_idingreso_foreign` (`idingreso`),
  ADD KEY `detalle_ingresos_idarticulo_foreign` (`idarticulo`);

--
-- Indices de la tabla `egresos`
--
ALTER TABLE `egresos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entrega_items`
--
ALTER TABLE `entrega_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `flujo_produccion`
--
ALTER TABLE `flujo_produccion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `imagenes`
--
ALTER TABLE `imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imagenes_idtabla_foranea` (`id_tabla`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingresos_idproveedor_foreign` (`cliente_id`),
  ADD KEY `ingresos_idusuario_foreign` (`usuario_id`);

--
-- Indices de la tabla `inventarios_materia_primas`
--
ALTER TABLE `inventarios_materia_primas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `linea_comprobantes`
--
ALTER TABLE `linea_comprobantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comprobantes_foreign` (`comprobante_id`),
  ADD KEY `articulos_foreign` (`articulo_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimiento_materia_primas`
--
ALTER TABLE `movimiento_materia_primas`
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
  ADD KEY `idcliente` (`cliente_id`) USING BTREE,
  ADD KEY `ordentrabajos_ibfk_2` (`articulo_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `pedidos_remision`
--
ALTER TABLE `pedidos_remision`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personas_nombre_unique` (`nombre`);

--
-- Indices de la tabla `procesos`
--
ALTER TABLE `procesos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `statusproduccion_idorden_foreign` (`posicion`) USING BTREE;

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedores_id_foreign` (`id`);

--
-- Indices de la tabla `recibo_pagos`
--
ALTER TABLE `recibo_pagos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `registros_produccion`
--
ALTER TABLE `registros_produccion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iduser` (`iduser`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_nombre_unique` (`nombre`);

--
-- Indices de la tabla `statusproduccion`
--
ALTER TABLE `statusproduccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `statusproduccion_idorden_foreign` (`idorden`) USING BTREE;

--
-- Indices de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_producto_atributo_tienda`
--
ALTER TABLE `tipo_producto_atributo_tienda`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_producto_procesos`
--
ALTER TABLE `tipo_producto_procesos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_usuario_unique` (`usuario`),
  ADD KEY `users_id_foreign` (`id`),
  ADD KEY `users_idrol_foreign` (`idrol`);

--
-- Indices de la tabla `users2`
--
ALTER TABLE `users2`
  ADD UNIQUE KEY `users_usuario_unique` (`usuario`),
  ADD KEY `users_id_foreign` (`id`),
  ADD KEY `users_idrol_foreign` (`idrol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ajustes`
--
ALTER TABLE `ajustes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `articulos`
--
ALTER TABLE `articulos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `articulos2`
--
ALTER TABLE `articulos2`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `articulo_troquels`
--
ALTER TABLE `articulo_troquels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atributos`
--
ALTER TABLE `atributos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atributos_tienda`
--
ALTER TABLE `atributos_tienda`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias2`
--
ALTER TABLE `categorias2`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_contacto`
--
ALTER TABLE `cliente_contacto`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_envio`
--
ALTER TABLE `cliente_envio`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_factura`
--
ALTER TABLE `cliente_factura`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobantes2`
--
ALTER TABLE `comprobantes2`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobantes3`
--
ALTER TABLE `comprobantes3`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobates`
--
ALTER TABLE `comprobates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `costois`
--
ALTER TABLE `costois`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `costos`
--
ALTER TABLE `costos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `costosproduccion`
--
ALTER TABLE `costosproduccion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `costo_articulos`
--
ALTER TABLE `costo_articulos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cruce_cartera`
--
ALTER TABLE `cruce_cartera`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `datosenvio`
--
ALTER TABLE `datosenvio`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `datos_factura`
--
ALTER TABLE `datos_factura`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `egresos`
--
ALTER TABLE `egresos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `flujo_produccion`
--
ALTER TABLE `flujo_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `imagenes`
--
ALTER TABLE `imagenes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventarios_materia_primas`
--
ALTER TABLE `inventarios_materia_primas`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `linea_comprobantes`
--
ALTER TABLE `linea_comprobantes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento_materia_primas`
--
ALTER TABLE `movimiento_materia_primas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `opcion_atributos`
--
ALTER TABLE `opcion_atributos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordentrabajos`
--
ALTER TABLE `ordentrabajos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos_remision`
--
ALTER TABLE `pedidos_remision`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `procesos`
--
ALTER TABLE `procesos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recibo_pagos`
--
ALTER TABLE `recibo_pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registros_produccion`
--
ALTER TABLE `registros_produccion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `statusproduccion`
--
ALTER TABLE `statusproduccion`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_atributo_tienda`
--
ALTER TABLE `tipo_producto_atributo_tienda`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_procesos`
--
ALTER TABLE `tipo_producto_procesos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos2`
--
ALTER TABLE `articulos2`
  ADD CONSTRAINT `articulos_idcategoria_foreign` FOREIGN KEY (`idcategoria`) REFERENCES `categorias2` (`id`);

--
-- Filtros para la tabla `articulo_troquels`
--
ALTER TABLE `articulo_troquels`
  ADD CONSTRAINT `articulo_troquels_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categorias2`
--
ALTER TABLE `categorias2`
  ADD CONSTRAINT `categorias_padre_id_foreign` FOREIGN KEY (`padre_id`) REFERENCES `categorias2` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `comprobantes2`
--
ALTER TABLE `comprobantes2`
  ADD CONSTRAINT `comprobates_id_cliente_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `comprobates_id_user_foreign` FOREIGN KEY (`user_id`) REFERENCES `users2` (`id`);

--
-- Filtros para la tabla `costois`
--
ALTER TABLE `costois`
  ADD CONSTRAINT `costois_idpersona_foreign` FOREIGN KEY (`idpersona`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `costois_idproveedor_foreign` FOREIGN KEY (`idproveedor`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `costosproduccion`
--
ALTER TABLE `costosproduccion`
  ADD CONSTRAINT `costosproduccion_idpersona_foreign` FOREIGN KEY (`idpersona`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `costosproduccion_idproveedor_foreign` FOREIGN KEY (`idproveedor`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `detalletrabajos`
--
ALTER TABLE `detalletrabajos`
  ADD CONSTRAINT `detalletrabajos_idorden_foreign` FOREIGN KEY (`ordentrabajo_id`) REFERENCES `ordentrabajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
