-- ==============================================================================
-- SCRIPT DE ACTUALIZACIÓN DE BASE DE DATOS PARA EL SERVIDOR (empaque1_sistema)
-- Generado tras comparar empaque.sql (Servidor) vs dbsistemalaravel (Local)
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ------------------------------------------------------------------------------
-- 1. TABLAS NUEVAS (MÓDULOS DE CRM Y DESPIECE)
-- ------------------------------------------------------------------------------

-- Tabla: crm_bases_datos
CREATE TABLE IF NOT EXISTS `crm_bases_datos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `sector` varchar(191) DEFAULT NULL,
  `origen` varchar(191) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` varchar(191) NOT NULL DEFAULT 'Activa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_bases_datos_user_id_foreign` (`user_id`),
  CONSTRAINT `crm_bases_datos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_prospectos
CREATE TABLE IF NOT EXISTS `crm_prospectos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) NOT NULL,
  `empresa` varchar(191) DEFAULT NULL,
  `cargo` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `telefono` varchar(191) DEFAULT NULL,
  `celular` varchar(191) DEFAULT NULL,
  `direccion` varchar(191) DEFAULT NULL,
  `ciudad` varchar(191) DEFAULT NULL,
  `origen` varchar(191) NOT NULL DEFAULT 'Web',
  `estado` varchar(191) NOT NULL DEFAULT 'Nuevo',
  `user_id` int(10) unsigned NOT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_prospectos_user_id_foreign` (`user_id`),
  CONSTRAINT `crm_prospectos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_contactos_base
CREATE TABLE IF NOT EXISTS `crm_contactos_base` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `base_datos_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `empresa` varchar(191) NOT NULL,
  `contacto_nombre` varchar(191) DEFAULT NULL,
  `cargo` varchar(191) DEFAULT NULL,
  `sector` varchar(191) DEFAULT NULL,
  `telefono` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `ciudad` varchar(191) DEFAULT NULL,
  `direccion` varchar(191) DEFAULT NULL,
  `origen_detalle` varchar(191) DEFAULT NULL,
  `estado_gestion` varchar(191) NOT NULL DEFAULT 'Sin Contactar',
  `portafolio_enviado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio_portafolio` datetime DEFAULT NULL,
  `metodo_envio` varchar(191) DEFAULT NULL,
  `resultado_gestion` text DEFAULT NULL,
  `fecha_ultimo_contacto` datetime DEFAULT NULL,
  `prospecto_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_contactos_base_base_datos_id_foreign` (`base_datos_id`),
  KEY `crm_contactos_base_user_id_foreign` (`user_id`),
  KEY `crm_contactos_base_prospecto_id_foreign` (`prospecto_id`),
  CONSTRAINT `crm_contactos_base_base_datos_id_foreign` FOREIGN KEY (`base_datos_id`) REFERENCES `crm_bases_datos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_contactos_base_prospecto_id_foreign` FOREIGN KEY (`prospecto_id`) REFERENCES `crm_prospectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `crm_contactos_base_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_gestion_contactos_log
CREATE TABLE IF NOT EXISTS `crm_gestion_contactos_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contacto_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `tipo_accion` varchar(191) NOT NULL,
  `detalle` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_gestion_contactos_log_contacto_id_foreign` (`contacto_id`),
  KEY `crm_gestion_contactos_log_user_id_foreign` (`user_id`),
  CONSTRAINT `crm_gestion_contactos_log_contacto_id_foreign` FOREIGN KEY (`contacto_id`) REFERENCES `crm_contactos_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_gestion_contactos_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_etapas
CREATE TABLE IF NOT EXISTS `crm_etapas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `probabilidad` int(11) NOT NULL DEFAULT 0,
  `color` varchar(191) NOT NULL DEFAULT '#3b82f6',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales para crm_etapas
INSERT INTO `crm_etapas` (`id`, `nombre`, `orden`, `probabilidad`, `color`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Prospecto / Calificación', 1, 10, '#64748b', 1, NOW(), NOW()),
(2, 'Contactado / Diagnóstico', 2, 25, '#0284c7', 1, NOW(), NOW()),
(3, 'Propuesta / Cotización Enviada', 3, 50, '#eab308', 1, NOW(), NOW()),
(4, 'Negociación', 4, 75, '#f97316', 1, NOW(), NOW()),
(5, 'Ganado (Cierre)', 5, 100, '#22c55e', 1, NOW(), NOW()),
(6, 'Perdido', 6, 0, '#ef4444', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `probabilidad` = VALUES(`probabilidad`), `color` = VALUES(`color`);

-- Tabla: crm_oportunidades
CREATE TABLE IF NOT EXISTS `crm_oportunidades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(191) NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `prospecto_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `etapa_id` bigint(20) unsigned NOT NULL,
  `monto_estimado` decimal(15,2) NOT NULL DEFAULT 0.00,
  `probabilidad` int(11) NOT NULL DEFAULT 50,
  `fecha_cierre_estimada` date DEFAULT NULL,
  `estado` varchar(191) NOT NULL DEFAULT 'Abierta',
  `motivo_perdida` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crm_oportunidades_codigo_unique` (`codigo`),
  KEY `crm_oportunidades_user_id_foreign` (`user_id`),
  KEY `crm_oportunidades_prospecto_id_foreign` (`prospecto_id`),
  KEY `crm_oportunidades_etapa_id_foreign` (`etapa_id`),
  CONSTRAINT `crm_oportunidades_etapa_id_foreign` FOREIGN KEY (`etapa_id`) REFERENCES `crm_etapas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_oportunidades_prospecto_id_foreign` FOREIGN KEY (`prospecto_id`) REFERENCES `crm_prospectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `crm_oportunidades_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_cotizaciones
CREATE TABLE IF NOT EXISTS `crm_cotizaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero_cotizacion` varchar(191) NOT NULL,
  `prospecto_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `oportunidad_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(15,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(191) NOT NULL DEFAULT 'Borrador',
  `condiciones_pago` varchar(191) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `pedido_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crm_cotizaciones_numero_cotizacion_unique` (`numero_cotizacion`),
  KEY `crm_cotizaciones_user_id_foreign` (`user_id`),
  KEY `crm_cotizaciones_prospecto_id_foreign` (`prospecto_id`),
  KEY `crm_cotizaciones_oportunidad_id_foreign` (`oportunidad_id`),
  CONSTRAINT `crm_cotizaciones_oportunidad_id_foreign` FOREIGN KEY (`oportunidad_id`) REFERENCES `crm_oportunidades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `crm_cotizaciones_prospecto_id_foreign` FOREIGN KEY (`prospecto_id`) REFERENCES `crm_prospectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `crm_cotizaciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_cotizacion_detalles
CREATE TABLE IF NOT EXISTS `crm_cotizacion_detalles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cotizacion_id` bigint(20) unsigned NOT NULL,
  `articulo_id` int(10) unsigned DEFAULT NULL,
  `concepto` varchar(191) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT 1.00,
  `precio_unitario` decimal(15,2) NOT NULL DEFAULT 0.00,
  `descuento_porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_cotizacion_detalles_cotizacion_id_foreign` (`cotizacion_id`),
  CONSTRAINT `crm_cotizacion_detalles_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `crm_cotizaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_actividades
CREATE TABLE IF NOT EXISTS `crm_actividades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prospecto_id` bigint(20) unsigned DEFAULT NULL,
  `oportunidad_id` bigint(20) unsigned DEFAULT NULL,
  `cotizacion_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `tipo` varchar(191) NOT NULL,
  `asunto` varchar(191) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `completada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_completada` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_actividades_user_id_foreign` (`user_id`),
  KEY `crm_actividades_prospecto_id_foreign` (`prospecto_id`),
  KEY `crm_actividades_oportunidad_id_foreign` (`oportunidad_id`),
  KEY `crm_actividades_cotizacion_id_foreign` (`cotizacion_id`),
  CONSTRAINT `crm_actividades_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `crm_cotizaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_actividades_oportunidad_id_foreign` FOREIGN KEY (`oportunidad_id`) REFERENCES `crm_oportunidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_actividades_prospecto_id_foreign` FOREIGN KEY (`prospecto_id`) REFERENCES `crm_prospectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crm_actividades_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: crm_metas_ventas
CREATE TABLE IF NOT EXISTS `crm_metas_ventas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `monto_meta` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crm_metas_ventas_user_id_mes_anio_unique` (`user_id`,`mes`,`anio`),
  CONSTRAINT `crm_metas_ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: despiece_proyectos
CREATE TABLE IF NOT EXISTS `despiece_proyectos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente` varchar(191) DEFAULT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: despiece_muebles
CREATE TABLE IF NOT EXISTS `despiece_muebles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` int(10) unsigned NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `tipo_mueble` varchar(191) NOT NULL,
  `ancho` int(11) NOT NULL,
  `ancho_derecho` int(11) DEFAULT NULL,
  `hueco_alto` int(11) DEFAULT NULL,
  `hueco_ancho` int(11) DEFAULT NULL,
  `espacio_ciego` int(11) DEFAULT NULL,
  `alto` int(11) NOT NULL,
  `profundidad` int(11) NOT NULL,
  `espesor_material` int(11) NOT NULL DEFAULT 15,
  `material` varchar(191) DEFAULT NULL,
  `material_interno` varchar(191) DEFAULT NULL,
  `material_externo` varchar(191) DEFAULT NULL,
  `tipo_meson` varchar(191) DEFAULT NULL,
  `alto_meson` int(11) NOT NULL DEFAULT 0,
  `costados_vistos` varchar(191) DEFAULT NULL,
  `sistema_apertura` varchar(191) DEFAULT NULL,
  `tipo_tirador` varchar(191) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `tiene_fondo` tinyint(1) NOT NULL DEFAULT 1,
  `material_fondo` varchar(191) NOT NULL DEFAULT 'Durolac Blanco / MDF 3mm',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `despiece_muebles_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `despiece_muebles_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `despiece_proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: despiece_divisiones
CREATE TABLE IF NOT EXISTS `despiece_divisiones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mueble_id` int(10) unsigned NOT NULL,
  `posicion` int(11) NOT NULL DEFAULT 1,
  `tipo` varchar(191) NOT NULL DEFAULT 'Cajones',
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `despiece_divisiones_mueble_id_foreign` (`mueble_id`),
  CONSTRAINT `despiece_divisiones_mueble_id_foreign` FOREIGN KEY (`mueble_id`) REFERENCES `despiece_muebles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: despiece_piezas
CREATE TABLE IF NOT EXISTS `despiece_piezas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mueble_id` int(10) unsigned NOT NULL,
  `nombre_pieza` varchar(191) NOT NULL,
  `material` varchar(191) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `largo` decimal(8,2) NOT NULL,
  `ancho` decimal(8,2) NOT NULL,
  `canto_l1` tinyint(1) NOT NULL DEFAULT 0,
  `canto_l2` tinyint(1) NOT NULL DEFAULT 0,
  `canto_a1` tinyint(1) NOT NULL DEFAULT 0,
  `canto_a2` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `despiece_piezas_mueble_id_foreign` (`mueble_id`),
  CONSTRAINT `despiece_piezas_mueble_id_foreign` FOREIGN KEY (`mueble_id`) REFERENCES `despiece_muebles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------------------
-- 2. MODIFICACIONES A TABLAS EXISTENTES (COLUMNAS NUEVAS)
-- ------------------------------------------------------------------------------

-- Nuevas columnas en tabla `articulos`
ALTER TABLE `articulos` 
  ADD COLUMN IF NOT EXISTS `cabidas_materiales` text DEFAULT NULL AFTER `medida_final`;

-- Nuevas columnas en tabla `costos`
ALTER TABLE `costos` 
  ADD COLUMN IF NOT EXISTS `medida_material` varchar(50) DEFAULT NULL AFTER `descripcion`,
  ADD COLUMN IF NOT EXISTS `tamano` varchar(50) DEFAULT NULL AFTER `medida_material`,
  ADD COLUMN IF NOT EXISTS `medida_final` varchar(50) DEFAULT NULL AFTER `tamano`,
  ADD COLUMN IF NOT EXISTS `cabida` varchar(50) DEFAULT NULL AFTER `medida_final`,
  ADD COLUMN IF NOT EXISTS `sobrante` varchar(50) DEFAULT NULL AFTER `cabida`,
  ADD COLUMN IF NOT EXISTS `componente` varchar(50) DEFAULT NULL AFTER `sobrante`;


-- ------------------------------------------------------------------------------
-- 3. INVALIDAR TODAS LAS CUENTAS DE COBRO EN EL SERVIDOR
-- ------------------------------------------------------------------------------

-- Cambiar el estado de todas las Cuentas de Cobro a 'Invalida' para que no computen en Cartera
UPDATE `comprobantes` SET `estado` = 'Invalida' WHERE `tipo` = 'cuentacobro';

-- ------------------------------------------------------------------------------
-- 4. CONFIGURACIÓN GENERAL DE AUXILIO DE TRANSPORTE Y NÓMINA Y DÍAS FRACCIONARIOS
-- ------------------------------------------------------------------------------

ALTER TABLE `liquidacion_quincena` MODIFY COLUMN `dias_trabajados` decimal(8,2) NOT NULL DEFAULT 15.00;
ALTER TABLE `liquidacion_primas` MODIFY COLUMN `dias_trabajados` decimal(8,2) NOT NULL DEFAULT 180.00;

INSERT INTO `ajustes` (`tipo`, `detalle`, `valor`, `categoria`)
SELECT 'nomina', 'auxilio_transporte', '200000', 'nomina'
WHERE NOT EXISTS (SELECT 1 FROM `ajustes` WHERE `tipo` = 'nomina' AND `detalle` = 'auxilio_transporte');

-- ------------------------------------------------------------------------------
-- 5. HORARIOS Y TURNOS POR DÍA DE LA SEMANA (turno_dias)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `turno_dias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `turno_id` bigint(20) unsigned NOT NULL,
  `dia_num` tinyint(3) unsigned NOT NULL COMMENT '1=Lunes, 7=Domingo',
  `dia_nombre` varchar(191) NOT NULL,
  `laborable` tinyint(1) NOT NULL DEFAULT 1,
  `hora_entrada` time NOT NULL DEFAULT '08:00:00',
  `tolerancia_minutos` int(11) NOT NULL DEFAULT 10,
  `hora_salida_receso` time DEFAULT NULL,
  `hora_entrada_receso` time DEFAULT NULL,
  `hora_salida_almuerzo` time DEFAULT NULL,
  `hora_entrada_almuerzo` time DEFAULT NULL,
  `hora_salida` time NOT NULL DEFAULT '17:00:00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `turno_dias_turno_id_dia_num_unique` (`turno_id`,`dia_num`),
  CONSTRAINT `turno_dias_turno_id_foreign` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. COLUMNA BENEFICIARIO EN CUENTAS POR PAGAR Y PAGOS PROGRAMADOS
-- ------------------------------------------------------------------------------

ALTER TABLE `cuentas_por_pagar` ADD COLUMN `beneficiario` varchar(191) DEFAULT NULL AFTER `proveedor_id`;

CREATE TABLE IF NOT EXISTS `pagos_programados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `concepto` varchar(191) NOT NULL,
  `categoria` varchar(191) NOT NULL DEFAULT 'Préstamo / Crédito',
  `proveedor_id` int(10) unsigned DEFAULT NULL,
  `beneficiario` varchar(191) DEFAULT NULL,
  `monto_estimado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `frecuencia` varchar(191) NOT NULL DEFAULT 'Mensual',
  `proxima_fecha_pago` date NOT NULL,
  `recordatorio_dias` int(11) NOT NULL DEFAULT '5',
  `cuenta_id` int(10) unsigned DEFAULT NULL,
  `estado` varchar(191) NOT NULL DEFAULT 'Activo',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_programados_proveedor_id_foreign` (`proveedor_id`),
  KEY `pagos_programados_cuenta_id_foreign` (`cuenta_id`),
  CONSTRAINT `pagos_programados_cuenta_id_foreign` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_programados_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `pagos_programados` ADD COLUMN `beneficiario` varchar(191) DEFAULT NULL AFTER `proveedor_id`;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

