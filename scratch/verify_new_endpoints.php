<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ProduccionSeguimientoController;
use Illuminate\Http\Request;

$controller = new ProduccionSeguimientoController();

echo "=== TESTING ENDPOINT: getDeliveryStats ===\n";
$req1 = new Request();
$resp1 = $controller->getDeliveryStats($req1);
$data1 = json_decode($resp1->getContent(), true);

if ($resp1->getStatusCode() === 200 && isset($data1['products']) && isset($data1['distribution'])) {
    echo "✅ Success! getDeliveryStats returned HTTP 200 and valid JSON.\n";
    echo "   Total Products Stats: " . count($data1['products']) . "\n";
    echo "   Average General Delay: " . ($data1['distribution']['promedio_general'] ?? 'N/A') . " days\n";
} else {
    echo "❌ Fail! getDeliveryStats response invalid. Code: " . $resp1->getStatusCode() . "\n";
    print_r($data1);
}

echo "\n=== TESTING ENDPOINT: getActiveProgress ===\n";
$req2 = new Request();
$resp2 = $controller->getActiveProgress($req2);
$data2 = json_decode($resp2->getContent(), true);

if ($resp2->getStatusCode() === 200 && is_array($data2)) {
    echo "✅ Success! getActiveProgress returned HTTP 200.\n";
    foreach ($data2 as $stage => $orders) {
        echo "   Stage: '$stage' has " . count($orders) . " orders.\n";
    }
} else {
    echo "❌ Fail! getActiveProgress response invalid. Code: " . $resp2->getStatusCode() . "\n";
    print_r($data2);
}

echo "\n=== TESTING ENDPOINT: getPrintOptimization ===\n";
$req3 = new Request();
$resp3 = $controller->getPrintOptimization($req3);
$data3 = json_decode($resp3->getContent(), true);

if ($resp3->getStatusCode() === 200 && isset($data3['suggestions']) && isset($data3['summary'])) {
    echo "✅ Success! getPrintOptimization returned HTTP 200.\n";
    echo "   Total Suggestions: " . $data3['summary']['total_suggestions'] . "\n";
    echo "   Total Setup Hours Saved: " . $data3['summary']['total_setup_hours_saved'] . " hours\n";
    echo "   Total Sheets Saved: " . $data3['summary']['total_sheets_saved'] . "\n";
} else {
    echo "❌ Fail! getPrintOptimization response invalid. Code: " . $resp3->getStatusCode() . "\n";
    print_r($data3);
}
echo "\n=== ALL TESTS DONE ===\n";
