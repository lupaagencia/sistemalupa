-- ==========================================================
-- SCRIPT DE ACTUALIZACION PARA EL SERVIDOR (empaque1_sistema)
-- Generado comparando dbsistemalaravel (local) vs empaque.sql (servidor)
-- Fecha: 2026-08-14 06:30:18
-- ==========================================================

-- 1. NUEVAS TABLAS EN LOCAL
-- Tabla nueva: crm_actividades
CREATE TABLE `crm_actividades` (
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

-- Tabla nueva: crm_bases_datos
CREATE TABLE `crm_bases_datos` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_contactos_base
CREATE TABLE `crm_contactos_base` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_cotizaciones
CREATE TABLE `crm_cotizaciones` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_cotizacion_detalles
CREATE TABLE `crm_cotizacion_detalles` (
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_etapas
CREATE TABLE `crm_etapas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `probabilidad` int(11) NOT NULL DEFAULT 0,
  `color` varchar(191) NOT NULL DEFAULT '#3b82f6',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_gestion_contactos_log
CREATE TABLE `crm_gestion_contactos_log` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_metas_ventas
CREATE TABLE `crm_metas_ventas` (
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

-- Tabla nueva: crm_oportunidades
CREATE TABLE `crm_oportunidades` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: crm_prospectos
CREATE TABLE `crm_prospectos` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla nueva: despiece_divisiones
CREATE TABLE `despiece_divisiones` (
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

-- Tabla nueva: despiece_muebles
CREATE TABLE `despiece_muebles` (
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

-- Tabla nueva: despiece_piezas
CREATE TABLE `despiece_piezas` (
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

-- Tabla nueva: despiece_proyectos
CREATE TABLE `despiece_proyectos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente` varchar(191) DEFAULT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ESTRUCTURA: MODIFICACIONES Y COLUMNAS NUEVAS EN TABLAS EXISTENTES
ALTER TABLE `abonos_cuentas_por_pagar` 
  MODIFY COLUMN `metodo_pago` varchar(50) NULL DEFAULT 'NULL'  AFTER `soporte`;

ALTER TABLE `articulos` 
  ADD COLUMN `cabidas_materiales` text NULL DEFAULT 'NULL'  AFTER `medida_final`;

ALTER TABLE `costos` 
  ADD COLUMN `medida_material` varchar(50) NULL DEFAULT 'NULL'  AFTER `descripcion`,
  ADD COLUMN `tamano` varchar(50) NULL DEFAULT 'NULL'  AFTER `medida_material`,
  ADD COLUMN `medida_final` varchar(50) NULL DEFAULT 'NULL'  AFTER `tamano`,
  ADD COLUMN `cabida` varchar(50) NULL DEFAULT 'NULL'  AFTER `medida_final`,
  ADD COLUMN `sobrante` varchar(50) NULL DEFAULT 'NULL'  AFTER `cabida`,
  ADD COLUMN `componente` varchar(50) NULL DEFAULT 'NULL'  AFTER `sobrante`;

ALTER TABLE `egresos` 
  MODIFY COLUMN `beneficiario` varchar(255) NULL DEFAULT 'NULL'  AFTER `metodo_pago`,
  MODIFY COLUMN `soporte` varchar(255) NULL DEFAULT 'NULL'  AFTER `beneficiario`;

