<?php

/**
 * Script de verificación para la Fase 6 (Simulador Contable Interactivo)
 * Valida la existencia de archivos, su registro en el empaquetador JS
 * y que contenido.blade.php contenga la referencia al simulador.
 */

echo "=========================================================\n";
echo " INICIANDO VERIFICACIÓN DE RECURSOS DEL SIMULADOR CONTABLE \n";
echo "=========================================================\n\n";

$baseDir = __DIR__ . '/..';

// 1. Verificar existencia de archivos Vue
$vueFile = "$baseDir/resources/assets/js/components/SimuladorContable.vue";
if (file_exists($vueFile)) {
    echo "✔ Componente Vue encontrado: resources/assets/js/components/SimuladorContable.vue\n";
} else {
    echo "❌ ERROR: No se encontró el componente SimuladorContable.vue\n";
    exit(1);
}

// 2. Verificar registro en app.js
$appJs = file_get_contents("$baseDir/resources/assets/js/app.js");
if (strpos($appJs, 'simulador-contable') !== false) {
    echo "✔ Registro del componente en app.js confirmado.\n";
} else {
    echo "❌ ERROR: El componente simulador-contable no está registrado en app.js\n";
    exit(1);
}

// 3. Verificar inyección en contenido.blade.php
$contenidoBlade = file_get_contents("$baseDir/resources/views/backend/contenido.blade.php");
if (strpos($contenidoBlade, 'simulador-contable') !== false) {
    echo "✔ Inyección del componente en contenido.blade.php confirmada.\n";
} else {
    echo "❌ ERROR: No se detectó la referencia a simulador-contable en contenido.blade.php\n";
    exit(1);
}

// 4. Verificar sidebar administrador y contador
$sidebarAdmin = file_get_contents("$baseDir/resources/views/plantilla/sidebaradministrador.blade.php");
$sidebarCont = file_get_contents("$baseDir/resources/views/plantilla/sidebarcontador.blade.php");

if (strpos($sidebarAdmin, 'menu=49') !== false && strpos($sidebarCont, 'menu=49') !== false) {
    echo "✔ Enlaces de navegación del menú sidebar configurados en ambos roles (Administrador y Contador).\n";
} else {
    echo "❌ ERROR: Enlaces de menú 'menu=49' faltantes en los sidebars.\n";
    exit(1);
}

echo "\n=========================================================\n";
echo " VERIFICACIÓN EXITOSA - EL SIMULADOR CONTABLE ESTÁ LISTO \n";
echo "=========================================================\n";
exit(0);
