<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = DB::select("SHOW CREATE TABLE tipo_producto");
print_r($r[0]->{'Create Table'});
