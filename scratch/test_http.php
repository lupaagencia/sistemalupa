<?php
$urls = [
    'http://127.0.0.1:8000/orden?page=1&criterio=ordentrabajos.id&operador=like&buscar=%256642%25&per_page=10',
    'http://localhost:8000/orden?page=1&criterio=ordentrabajos.id&operador=like&buscar=%256642%25&per_page=10'
];

foreach ($urls as $url) {
    echo "Testing URL: $url\n";
    try {
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Accept: application/json\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
            echo "SUCCESS!\n";
            $data = json_decode($result, true);
            if (isset($data['ordenes']['data'])) {
                echo "Count: " . count($data['ordenes']['data']) . "\n";
                foreach ($data['ordenes']['data'] as $o) {
                    echo "ID: " . ($o['idorden'] ?? $o['id']) . "\n";
                }
            } else {
                echo "Response structure unexpected:\n";
                print_r(array_keys($data));
            }
            break;
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
