<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Rol;
use App\User;

class AddSuperadministradorRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('roles')) {
            $rolSuper = Rol::where('nombre', 'Superadministrador')->first();
            if (!$rolSuper) {
                $rolSuper = new Rol();
                $rolSuper->nombre = 'Superadministrador';
                $rolSuper->descripcion = 'Super Administrador con control total del sistema';
                $rolSuper->condicion = 1;
                $rolSuper->produccion = 0;
                $rolSuper->save();
            }
        }

        if (Schema::hasTable('users')) {
            User::where('usuario', 'julian')->update(['idrol' => 'Superadministrador']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
