<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$costois = DB::table('costois')->get();
foreach ($costois as $c) {
    echo "ID: {$c->id}, Nombre: {$c->nombre}, Estado: {$c->estado}\n";
}
