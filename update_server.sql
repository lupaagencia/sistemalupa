-- ==========================================================================
-- SQL MIGRATION SCRIPT FOR SERVER DATABASE (to match local database schema)
-- Generated on: 2026-07-01 04:02:15
-- Local database: dbsistemalaravel
-- Server SQL file used: empaque1_sistema.sql
-- ==========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- TABLES TO CREATE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comprobantes_contables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('Ingreso','Egreso','Diario','Traspaso') NOT NULL,
  `numero` int(10) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comprobantes_contables_user_id_foreign` (`user_id`),
  CONSTRAINT `comprobantes_contables_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cuentas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `tipo` enum('Activo','Pasivo','Patrimonio','Ingreso','Gasto','Costo') NOT NULL,
  `naturaleza` enum('Debito','Credito') NOT NULL,
  `es_detalle` tinyint(1) NOT NULL DEFAULT 1,
  `padre_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuentas_codigo_unique` (`codigo`),
  KEY `cuentas_padre_id_foreign` (`padre_id`),
  CONSTRAINT `cuentas_padre_id_foreign` FOREIGN KEY (`padre_id`) REFERENCES `cuentas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- DUMPING DATA FOR TABLE `cuentas` (Plan Único de Cuentas - PUC)
-- --------------------------------------------------------
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('1', '1', 'Activos', 'Activo', 'Debito', '0', NULL, '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('2', '2', 'Pasivos', 'Pasivo', 'Credito', '0', NULL, '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('3', '3', 'Patrimonio', 'Patrimonio', 'Credito', '0', NULL, '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('4', '4', 'Ingresos', 'Ingreso', 'Credito', '0', NULL, '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('5', '5', 'Gastos', 'Gasto', 'Debito', '0', NULL, '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('6', '6', 'Costos de Ventas', 'Costo', 'Debito', '0', NULL, '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('7', '11', 'Disponible', 'Activo', 'Debito', '0', '1', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('8', '13', 'Deudores', 'Activo', 'Debito', '0', '1', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('9', '22', 'Proveedores', 'Pasivo', 'Credito', '0', '2', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('10', '23', 'Cuentas por Pagar', 'Pasivo', 'Credito', '0', '2', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('11', '31', 'Capital Social', 'Patrimonio', 'Credito', '0', '3', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('12', '41', 'Operacionales', 'Ingreso', 'Credito', '0', '4', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('13', '51', 'Operacionales de Administración', 'Gasto', 'Debito', '0', '5', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('14', '61', 'Operacionales', 'Costo', 'Debito', '0', '6', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('15', '1105', 'Caja', 'Activo', 'Debito', '0', '7', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('16', '1110', 'Bancos', 'Activo', 'Debito', '0', '7', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('17', '1305', 'Clientes', 'Activo', 'Debito', '0', '8', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('18', '2205', 'Proveedores Nacionales', 'Pasivo', 'Credito', '0', '9', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('19', '2335', 'Costos y Gastos por Pagar', 'Pasivo', 'Credito', '0', '10', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('20', '3105', 'Capital Suscrito y Pagado', 'Patrimonio', 'Credito', '0', '11', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('21', '4135', 'Comercio al por mayor y al por menor', 'Ingreso', 'Credito', '0', '12', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('22', '5105', 'Gastos de Personal', 'Gasto', 'Debito', '0', '13', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('23', '5135', 'Servicios', 'Gasto', 'Debito', '0', '13', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('24', '5195', 'Diversos', 'Gasto', 'Debito', '0', '13', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('25', '6135', 'Comercio al por mayor y al por menor', 'Costo', 'Debito', '0', '14', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('26', '110505', 'Caja General', 'Activo', 'Debito', '1', '15', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('27', '110510', 'Caja Menor', 'Activo', 'Debito', '1', '15', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('28', '111005', 'Bancos Nacionales (Moneda Local)', 'Activo', 'Debito', '1', '16', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('29', '130505', 'Clientes Nacionales', 'Activo', 'Debito', '1', '17', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('30', '220505', 'Proveedores Nacionales Detalle', 'Pasivo', 'Credito', '1', '18', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('31', '233505', 'Gastos Financieros por Pagar', 'Pasivo', 'Credito', '1', '19', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('32', '233550', 'Servicios Públicos por Pagar', 'Pasivo', 'Credito', '1', '19', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('33', '310505', 'Capital Autorizado', 'Patrimonio', 'Credito', '1', '20', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('34', '413505', 'Ventas de Productos / Empaques', 'Ingreso', 'Credito', '1', '21', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('35', '510506', 'Sueldos', 'Gasto', 'Debito', '1', '22', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('36', '510527', 'Auxilio de Transporte', 'Gasto', 'Debito', '1', '22', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('37', '513505', 'Servicios de Acueducto, Energía y Teléfono', 'Gasto', 'Debito', '1', '23', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('38', '519595', 'Gastos Diversos / Insumos', 'Gasto', 'Debito', '1', '24', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('39', '613505', 'Costo de Materia Prima e Insumos Directos', 'Costo', 'Debito', '1', '25', '2026-06-22 15:01:25', '2026-06-22 15:01:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('52', '510515', 'Horas Extras y Recargos', 'Gasto', 'Debito', '1', '22', '2026-06-23 00:39:25', '2026-06-23 00:39:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('53', '237005', 'Aportes a Salud (Deducción Empleado)', 'Pasivo', 'Credito', '1', NULL, '2026-06-23 00:39:25', '2026-06-23 00:39:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('54', '238030', 'Aportes a Pensión (Deducción Empleado)', 'Pasivo', 'Credito', '1', NULL, '2026-06-23 00:39:25', '2026-06-23 00:39:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('55', '233595', 'Otras Deducciones de Nómina', 'Pasivo', 'Credito', '1', NULL, '2026-06-23 00:39:25', '2026-06-23 00:39:25') ON DUPLICATE KEY UPDATE `id`=`id`;
INSERT INTO `cuentas` (`id`, `codigo`, `nombre`, `tipo`, `naturaleza`, `es_detalle`, `padre_id`, `created_at`, `updated_at`) VALUES ('56', '240805', 'Impuesto sobre las Ventas por Pagar (IVA)', 'Pasivo', 'Credito', '1', NULL, '2026-06-23 00:42:14', '2026-06-23 00:42:14') ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `asientos_detalles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comprobante_id` int(10) unsigned NOT NULL,
  `cuenta_id` int(10) unsigned NOT NULL,
  `tercero_id` int(10) unsigned DEFAULT NULL,
  `debe` decimal(15,2) NOT NULL DEFAULT 0.00,
  `haber` decimal(15,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asientos_detalles_comprobante_id_foreign` (`comprobante_id`),
  KEY `asientos_detalles_cuenta_id_foreign` (`cuenta_id`),
  KEY `asientos_detalles_tercero_id_foreign` (`tercero_id`),
  CONSTRAINT `asientos_detalles_comprobante_id_foreign` FOREIGN KEY (`comprobante_id`) REFERENCES `comprobantes_contables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asientos_detalles_cuenta_id_foreign` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `extractos_bancarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `descripcion` varchar(191) NOT NULL,
  `monto` decimal(20,2) NOT NULL,
  `referencia` varchar(191) DEFAULT NULL,
  `estado` enum('Pendiente','Conciliado') NOT NULL DEFAULT 'Pendiente',
  `comprobante_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `extractos_bancarios_comprobante_id_foreign` (`comprobante_id`),
  CONSTRAINT `extractos_bancarios_comprobante_id_foreign` FOREIGN KEY (`comprobante_id`) REFERENCES `comprobantes_contables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `facturas_electronicas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comprobante_id` int(10) unsigned NOT NULL,
  `cufe` varchar(255) DEFAULT NULL,
  `uuid_proveedor` varchar(255) DEFAULT NULL,
  `estado_dian` enum('Pendiente','Aceptado','Rechazado') NOT NULL DEFAULT 'Pendiente',
  `xml_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `dian_response` text DEFAULT NULL,
  `fecha_transmision` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facturas_electronicas_comprobante_id_unique` (`comprobante_id`),
  CONSTRAINT `facturas_electronicas_comprobante_id_foreign` FOREIGN KEY (`comprobante_id`) REFERENCES `comprobantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `liquidacion_primas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `periodo` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `dias_trabajados` int(11) NOT NULL,
  `salario_base` decimal(12,2) NOT NULL,
  `promedio_extras` decimal(12,2) NOT NULL DEFAULT 0.00,
  `valor_prima` decimal(12,2) NOT NULL,
  `egreso_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `liquidacion_primas_empleado_id_foreign` (`empleado_id`),
  KEY `liquidacion_primas_egreso_id_foreign` (`egreso_id`),
  CONSTRAINT `liquidacion_primas_egreso_id_foreign` FOREIGN KEY (`egreso_id`) REFERENCES `egresos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `liquidacion_primas_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `periodos_contables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Abierto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodos_contables_mes_anio_unique` (`mes`,`anio`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- TABLE MODIFICATIONS (ALTER TABLE)
-- --------------------------------------------------------
ALTER TABLE `abonos_cuentas_por_pagar` 
  ADD COLUMN `comprobante_id` int(10) unsigned NULL DEFAULT NULL AFTER `metodo_pago`,
  ADD INDEX `abonos_cuentas_por_pagar_comprobante_id_foreign` (`comprobante_id`);

ALTER TABLE `articulos2` 
  ADD INDEX `articulos_idcategoria_foreign` (`idcategoria`);
-- ALTER TABLE `articulos2` DROP INDEX `articulos2_idcategoria_foreign`;

ALTER TABLE `categorias2` 
  ADD INDEX `categorias_padre_id_foreign` (`padre_id`);
-- ALTER TABLE `categorias2` DROP INDEX `categorias2_padre_id_foreign`;

ALTER TABLE `comprobantes` 
  ADD COLUMN `comprobante_contable_id` int(10) unsigned NULL DEFAULT NULL AFTER `updated_at`,
  ADD INDEX `comprobantes_comprobante_contable_id_foreign` (`comprobante_contable_id`);

ALTER TABLE `comprobantes2` 
  ADD INDEX `comprobates_id_cliente_foreign` (`cliente_id`),
  ADD INDEX `comprobates_id_user_foreign` (`user_id`);
-- ALTER TABLE `comprobantes2` DROP INDEX `comprobantes2_cliente_id_foreign`;
-- ALTER TABLE `comprobantes2` DROP INDEX `comprobantes2_user_id_foreign`;

ALTER TABLE `cuentas_por_pagar` 
  ADD COLUMN `comprobante_id` int(10) unsigned NULL DEFAULT NULL AFTER `fecha_vencimiento`,
  ADD COLUMN `cuenta_id` int(10) unsigned NULL DEFAULT NULL AFTER `comprobante_id`,
  ADD COLUMN `iva` decimal(15,2) NOT NULL DEFAULT '0.00' AFTER `updated_at`,
  ADD INDEX `cuentas_por_pagar_comprobante_id_foreign` (`comprobante_id`),
  ADD INDEX `cuentas_por_pagar_cuenta_id_foreign` (`cuenta_id`);

ALTER TABLE `egresos` 
  ADD COLUMN `comprobante_id` int(10) unsigned NULL DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `cuenta_id` int(10) unsigned NULL DEFAULT NULL AFTER `comprobante_id`,
  MODIFY COLUMN `iva` decimal(15,2) NOT NULL DEFAULT '0.00',
  ADD INDEX `egresos_comprobante_id_foreign` (`comprobante_id`),
  ADD INDEX `egresos_cuenta_id_foreign` (`cuenta_id`);
-- ALTER TABLE `egresos` DROP COLUMN `cuenta_contable`;
-- ALTER TABLE `egresos` DROP COLUMN `tipo_documento`;
-- ALTER TABLE `egresos` DROP COLUMN `forma_pago`;
-- ALTER TABLE `egresos` DROP COLUMN `subtotal`;
-- ALTER TABLE `egresos` DROP COLUMN `total`;
-- ALTER TABLE `egresos` DROP COLUMN `estado`;

ALTER TABLE `empleados` 
  MODIFY COLUMN `auxilio_transporte` decimal(12,2) NOT NULL DEFAULT '0.00';

ALTER TABLE `ingresos` 
  ADD COLUMN `comprobante_id` int(10) unsigned NULL DEFAULT NULL AFTER `dias_credito`,
  ADD INDEX `ingresos_comprobante_id_foreign` (`comprobante_id`);

ALTER TABLE `movimiento_materia_primas` 
  ADD COLUMN `comprobante_id` int(10) unsigned NULL DEFAULT NULL AFTER `costo_total`,
  ADD INDEX `movimiento_materia_primas_comprobante_id_foreign` (`comprobante_id`);

ALTER TABLE `recibo_pagos` 
  ADD COLUMN `comprobante_contable_id` int(10) unsigned NULL DEFAULT NULL AFTER `updated_at`,
  ADD INDEX `recibo_pagos_comprobante_contable_id_foreign` (`comprobante_contable_id`);

ALTER TABLE `users` 
  MODIFY COLUMN `email` varchar(191) NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;
