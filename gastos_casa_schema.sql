-- ==============================================================================
-- SCRIPT SQL: ESTRUCTURA Y SEEDS PARA MÓDULO GASTOS CASA DE LA CASA (gh_*)
-- ==============================================================================

-- 1. Tabla gh_personas
CREATE TABLE IF NOT EXISTS `gh_personas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) NOT NULL,
  `telefono` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `porcentaje_participacion` decimal(5,2) NOT NULL DEFAULT 33.33,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seeds gh_personas
INSERT INTO `gh_personas` (`id`, `nombre`, `porcentaje_participacion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Julián', 33.34, 1, NOW(), NOW()),
(2, 'Óscar', 33.33, 1, NOW(), NOW()),
(3, 'Diego', 33.33, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 2. Tabla gh_categorias
CREATE TABLE IF NOT EXISTS `gh_categorias` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) NOT NULL,
  `tipo` enum('gasto','ingreso','reserva') NOT NULL DEFAULT 'gasto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seeds gh_categorias
INSERT INTO `gh_categorias` (`id`, `nombre`, `tipo`, `created_at`, `updated_at`) VALUES
(1, 'Arreglos y Remodelación', 'gasto', NOW(), NOW()),
(2, 'Servicios Públicos', 'gasto', NOW(), NOW()),
(3, 'Impuesto Predial', 'gasto', NOW(), NOW()),
(4, 'Mantenimiento General', 'gasto', NOW(), NOW()),
(5, 'Materiales de Construcción', 'gasto', NOW(), NOW()),
(6, 'Mano de Obra', 'gasto', NOW(), NOW()),
(7, 'Otros Gastos', 'gasto', NOW(), NOW())
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 3. Tabla gh_gastos
CREATE TABLE IF NOT EXISTS `gh_gastos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `persona_id` bigint(20) UNSIGNED DEFAULT NULL,
  `origen_pago` varchar(50) NOT NULL DEFAULT 'persona',
  `categoria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `soporte_path` varchar(191) DEFAULT NULL,
  `soporte_nombre_orig` varchar(191) DEFAULT NULL,
  `comprobante_path` varchar(191) DEFAULT NULL,
  `comprobante_nombre_orig` varchar(191) DEFAULT NULL,
  `estado` enum('pendiente','reembolsado_parcial','reembolsado_total','anulado') NOT NULL DEFAULT 'pendiente',
  `saldo_pendiente` decimal(12,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gh_gastos_persona_id_foreign` (`persona_id`),
  KEY `gh_gastos_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `gh_gastos_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `gh_personas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gh_gastos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `gh_categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla gh_ingresos
CREATE TABLE IF NOT EXISTS `gh_ingresos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo_ingreso` varchar(50) NOT NULL DEFAULT 'arriendo',
  `fecha` date NOT NULL,
  `periodo_mes` varchar(191) DEFAULT NULL,
  `inquilino_nombre` varchar(191) DEFAULT NULL,
  `descripcion` varchar(191) NOT NULL,
  `valor_total` decimal(12,2) NOT NULL,
  `comprobante_path` varchar(191) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla gh_distribuciones
CREATE TABLE IF NOT EXISTS `gh_distribuciones` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ingreso_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_destino` enum('reembolso_persona','reserva_predial','reserva_arreglos','reserva_otra') NOT NULL,
  `persona_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gasto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL,
  `observaciones` varchar(191) DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gh_distribuciones_ingreso_id_foreign` (`ingreso_id`),
  KEY `gh_distribuciones_persona_id_foreign` (`persona_id`),
  KEY `gh_distribuciones_gasto_id_foreign` (`gasto_id`),
  CONSTRAINT `gh_distribuciones_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `gh_ingresos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gh_distribuciones_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `gh_personas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gh_distribuciones_gasto_id_foreign` FOREIGN KEY (`gasto_id`) REFERENCES `gh_gastos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla gh_reservas
CREATE TABLE IF NOT EXISTS `gh_reservas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo_reserva` enum('predial','arreglos','otro') NOT NULL DEFAULT 'predial',
  `tipo_movimiento` enum('ingreso','egreso') NOT NULL DEFAULT 'ingreso',
  `distribucion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `concepto` varchar(191) NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `soporte_path` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gh_reservas_distribucion_id_foreign` (`distribucion_id`),
  CONSTRAINT `gh_reservas_distribucion_id_foreign` FOREIGN KEY (`distribucion_id`) REFERENCES `gh_distribuciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
