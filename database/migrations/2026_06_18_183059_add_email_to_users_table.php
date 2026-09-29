<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->after('idrol');
        });

        // Copy emails from linked empleados
        \DB::statement('UPDATE users JOIN empleados ON users.empleado_id = empleados.id SET users.email = empleados.correo WHERE empleados.correo IS NOT NULL AND empleados.correo != ""');
    }

    /**
     * Reverse the migrations.
     *
     * @var void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
}
