<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/comprobante/remisiones', 'GET', [
    'page' => 1,
    'per_page' => 100,
    'buscar' => '',
    'criterio' => 'cliente_id'
]);
$res = app('App\Http\Controllers\ComprobanteController')->remisiones($req);
$jsonStr = json_encode($res);
$parsed = json_decode($jsonStr, true);

echo "JSON Root Keys: " . implode(', ', array_keys($parsed)) . "\n";
echo "comprobantes key type: " . gettype($parsed['comprobantes']) . "\n";
if (is_array($parsed['comprobantes'])) {
    echo "comprobantes subkeys: " . implode(', ', array_keys($parsed['comprobantes'])) . "\n";
    if (isset($parsed['comprobantes']['data'])) {
        echo "comprobantes.data count: " . count($parsed['comprobantes']['data']) . "\n";
    }
}
