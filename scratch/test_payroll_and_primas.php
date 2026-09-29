<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Empleado;
use App\LiquidacionQuincena;
use App\LiquidacionPrima;
use App\Http\Controllers\LiquidacionPrimaController;
use App\Http\Controllers\LiquidacionQuincenaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== START PAYROLL AND PRIMAS VERIFICATION ===\n\n";

// Authenticate as the first user in DB to satisfy FK constraints on egresos.user_id
$firstUser = DB::table('users')->first();
if ($firstUser) {
    Auth::loginUsingId($firstUser->id);
    echo "Authenticated in test session as user ID: {$firstUser->id}\n";
} else {
    echo "WARNING: No users found in database. Attempting to insert a test user...\n";
    DB::table('users')->insert([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('secret'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    Auth::loginUsingId(1);
    echo "Created and authenticated test user with ID: 1\n";
}

// 1. Check/create test contractor employee
$contractor = Empleado::where('tipo_contrato', 'Prestación de servicios')->first();
if (!$contractor) {
    echo "Creating a mock Contractor employee...\n";
    $contractor = new Empleado();
    $contractor->nombre = "Carlos";
    $contractor->apellido = "Contratista";
    $contractor->tipo_doc = "CC";
    $contractor->num_doc = "99999991";
    $contractor->cargo = "Consultor Externo";
    $contractor->salario = 50000; // Hourly rate for testing
    $contractor->auxilio_transporte = 0;
    $contractor->tipo_contrato = "Prestación de servicios";
    $contractor->fecha_ingreso = "2026-01-01";
    $contractor->save();
} else {
    echo "Using existing Contractor employee: {$contractor->nombre} {$contractor->apellido} (CC {$contractor->num_doc})\n";
}

// 2. Check/create test standard employee
$employee = Empleado::where('tipo_contrato', 'Término Indefinido')->first();
if (!$employee) {
    echo "Creating a mock standard Employee...\n";
    $employee = new Empleado();
    $employee->nombre = "Juan";
    $employee->apellido = "Trabajador";
    $employee->tipo_doc = "CC";
    $employee->num_doc = "99999992";
    $employee->cargo = "Auxiliar de Oficina";
    $employee->salario = 1500000; // Standard monthly salary
    $employee->auxilio_transporte = 175000;
    $employee->tipo_contrato = "Término Indefinido";
    $employee->fecha_ingreso = "2026-01-15";
    $employee->save();
} else {
    echo "Using existing standard Employee: {$employee->nombre} {$employee->apellido} (CC {$employee->num_doc})\n";
}

echo "\n--- Test A: Contractor Quincena Store ---\n";
// Create a fake HTTP request for Contractor hourly payment
// 80 hours worked * $50,000 / hour = $4,000,000 net, deductions should be 0.
$reqContractor = new Request([
    'empleado_id' => $contractor->id,
    'fecha_pago' => '2026-06-15',
    'fecha_inicio' => '2026-06-01',
    'fecha_fin' => '2026-06-15',
    'dias_trabajados' => 15,
    'salario_base' => $contractor->salario,
    'sueldo_neto' => 4000000.00,
    'auxilio_transporte' => 0.00,
    'monto_extras' => 0.00,
    'salud_deduccion' => 0.00,
    'pension_deduccion' => 0.00,
    'otras_deducciones' => 0.00,
    'neto_pagado' => 4000000.00,
    'metodo_pago' => 'Banco',
    'horas_extras_json' => json_encode([
        'horasHED' => 0, 'horasHEN' => 0, 'horasHEDD' => 0, 'horasHEND' => 0,
        'horasRNO' => 0, 'horasRDD' => 0, 'horasRND' => 0, 'turnos' => [],
        'tipoPagoContratista' => 'Horas',
        'horasTrabajadasContratista' => 80,
        'valorHoraContratista' => 50000
    ])
]);

$quincenaController = new LiquidacionQuincenaController();

// We run this inside a transaction that rolls back to avoid polluting DB permanently, but we'll print outputs.
try {
    DB::beginTransaction();

    $response = $quincenaController->store($reqContractor);
    $resData = json_decode($response->getContent(), true);

    if (isset($resData['error'])) {
        throw new \Exception("Error in Contractor store: " . $resData['error']);
    }

    echo "Contractor quincena stored successfully!\n";
    $quincenaId = $resData['quincena']['id'];
    $quincenaRec = LiquidacionQuincena::with('egreso.comprobante.detalles.cuenta')->find($quincenaId);

    echo "Quincena Record Details:\n";
    echo "  Neto Pagado: {$quincenaRec->neto_pagado}\n";
    echo "  Salud Deduccion: {$quincenaRec->salud_deduccion} (Expected: 0)\n";
    echo "  Pension Deduccion: {$quincenaRec->pension_deduccion} (Expected: 0)\n";
    echo "  Aux Transporte: {$quincenaRec->auxilio_transporte} (Expected: 0)\n";

    echo "Accounting Journal Entries (Comprobante):\n";
    if ($quincenaRec->egreso && $quincenaRec->egreso->comprobante) {
        $comp = $quincenaRec->egreso->comprobante;
        echo "  Comprobante #{$comp->numero}: {$comp->descripcion}\n";
        foreach ($comp->detalles as $det) {
            $cuentaCod = $det->cuenta ? $det->cuenta->codigo : 'N/A';
            $cuentaNom = $det->cuenta ? $det->cuenta->nombre : 'N/A';
            echo "    Cuenta: {$cuentaCod} ({$cuentaNom}) | Debe: {$det->debe} | Haber: {$det->haber} | Ref: {$det->referencia}\n";
        }
    } else {
        echo "  No accounting entries found.\n";
    }

    DB::rollBack();
    echo "Contractor test transactions rolled back successfully.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Exception in Test A: " . $e->getMessage() . "\n";
}


echo "\n--- Test B: Standard Employee Quincena Store ---\n";
// Monthly wage = $1,500,000 -> 15 days Sueldo = $750,000.
// Aux Transp = $175,000 -> 15 days Aux = $87,500.
// Health 4% = ($750k) * 0.04 = $30,000.
// Pension 4% = ($750k) * 0.04 = $30,000.
// Net = 750000 + 87500 - 30000 - 30000 = $777,500.
$reqEmployee = new Request([
    'empleado_id' => $employee->id,
    'fecha_pago' => '2026-06-15',
    'fecha_inicio' => '2026-06-01',
    'fecha_fin' => '2026-06-15',
    'dias_trabajados' => 15,
    'salario_base' => 1500000.00 - 175000.00,
    'sueldo_neto' => 750000.00,
    'auxilio_transporte' => 87500.00,
    'monto_extras' => 0.00,
    'salud_deduccion' => 30000.00,
    'pension_deduccion' => 30000.00,
    'otras_deducciones' => 0.00,
    'neto_pagado' => 777500.00,
    'metodo_pago' => 'Banco',
    'horas_extras_json' => json_encode([
        'horasHED' => 0, 'horasHEN' => 0, 'horasHEDD' => 0, 'horasHEND' => 0,
        'horasRNO' => 0, 'horasRDD' => 0, 'horasRND' => 0, 'turnos' => []
    ])
]);

try {
    DB::beginTransaction();

    $response = $quincenaController->store($reqEmployee);
    $resData = json_decode($response->getContent(), true);

    if (isset($resData['error'])) {
        throw new \Exception("Error in Employee store: " . $resData['error']);
    }

    echo "Employee quincena stored successfully!\n";
    $quincenaId = $resData['quincena']['id'];
    $quincenaRec = LiquidacionQuincena::with('egreso.comprobante.detalles.cuenta')->find($quincenaId);

    echo "Quincena Record Details:\n";
    echo "  Neto Pagado: {$quincenaRec->neto_pagado}\n";
    echo "  Salud Deduccion: {$quincenaRec->salud_deduccion} (Expected: 30000)\n";
    echo "  Pension Deduccion: {$quincenaRec->pension_deduccion} (Expected: 30000)\n";
    echo "  Aux Transporte: {$quincenaRec->auxilio_transporte} (Expected: 87500)\n";

    echo "Accounting Journal Entries (Comprobante):\n";
    if ($quincenaRec->egreso && $quincenaRec->egreso->comprobante) {
        $comp = $quincenaRec->egreso->comprobante;
        echo "  Comprobante #{$comp->numero}: {$comp->descripcion}\n";
        foreach ($comp->detalles as $det) {
            $cuentaCod = $det->cuenta ? $det->cuenta->codigo : 'N/A';
            $cuentaNom = $det->cuenta ? $det->cuenta->nombre : 'N/A';
            echo "    Cuenta: {$cuentaCod} ({$cuentaNom}) | Debe: {$det->debe} | Haber: {$det->haber} | Ref: {$det->referencia}\n";
        }
    } else {
        echo "  No accounting entries found.\n";
    }

    DB::rollBack();
    echo "Employee test transactions rolled back successfully.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Exception in Test B: " . $e->getMessage() . "\n";
}


echo "\n--- Test C: Primas Calculation & Registration ---\n";
// Test Primas calculation for 2026-I
$primaController = new LiquidacionPrimaController();

try {
    DB::beginTransaction();

    // 1. Calculate
    $reqCalc = new Request(['anio' => 2026, 'periodo' => 1]);
    $calcResponse = $primaController->calcularPrimas($reqCalc);
    $calcData = json_decode($calcResponse->getContent(), true);

    echo "Primas calculation returns " . count($calcData) . " employees.\n";
    
    // Find the first employee with positive calculated prima, or fallback to the first element
    $first = null;
    foreach ($calcData as $item) {
        if ($item['valor_prima'] > 0) {
            $first = $item;
            break;
        }
    }
    if (!$first && count($calcData) > 0) {
        $first = $calcData[0];
    }

    if ($first) {
        echo "Calculated prima details for: {$first['nombre']}\n";
        echo "  Dias: {$first['dias_trabajados']}\n";
        echo "  Salario Base: {$first['salario_base']}\n";
        echo "  Aux Transporte: {$first['auxilio_transporte']}\n";
        echo "  Base Calculo: {$first['base_calculo']}\n";
        echo "  Valor Prima: {$first['valor_prima']}\n";
        echo "  Ya Pagado: " . ($first['ya_pagado'] ? 'Si' : 'No') . "\n";

        // 2. Register Prima for this employee
        $reqStore = new Request([
            'fecha_pago' => '2026-06-20',
            'metodo_pago' => 'Banco',
            'anio' => 2026,
            'periodo' => 1,
            'pagos' => [
                [
                    'empleado_id' => $first['empleado_id'],
                    'dias_trabajados' => $first['dias_trabajados'],
                    'salario_base' => $first['salario_base'],
                    'promedio_extras' => $first['promedio_extras'],
                    'valor_prima' => $first['valor_prima']
                ]
            ]
        ]);

        $storeResponse = $primaController->store($reqStore);
        $storeData = json_decode($storeResponse->getContent(), true);

        if (isset($storeData['error'])) {
            throw new \Exception("Error storing prima: " . $storeData['error']);
        }

        echo "Prima recorded successfully!\n";
        $primaId = $storeData['registros'][0]['id'];
        $primaRec = LiquidacionPrima::with('egreso.comprobante.detalles.cuenta')->find($primaId);

        echo "Prima Record Details:\n";
        echo "  Valor Prima: {$primaRec->valor_prima}\n";
        echo "  Anio: {$primaRec->anio} | Periodo: {$primaRec->periodo}\n";

        echo "Accounting Journal Entries (Comprobante):\n";
        if ($primaRec->egreso && $primaRec->egreso->comprobante) {
            $comp = $primaRec->egreso->comprobante;
            echo "  Comprobante #{$comp->numero}: {$comp->descripcion}\n";
            foreach ($comp->detalles as $det) {
                $cuentaCod = $det->cuenta ? $det->cuenta->codigo : 'N/A';
                $cuentaNom = $det->cuenta ? $det->cuenta->nombre : 'N/A';
                echo "    Cuenta: {$cuentaCod} ({$cuentaNom}) | Debe: {$det->debe} | Haber: {$det->haber} | Ref: {$det->referencia}\n";
            }
        } else {
            echo "  No accounting entries found.\n";
        }
    }

    DB::rollBack();
    echo "Primas test transactions rolled back successfully.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Exception in Test C: " . $e->getMessage() . "\n";
}

echo "\n=== PAYROLL AND PRIMAS VERIFICATION COMPLETE ===\n";
