<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixUsersTableIdPrimaryAndIncrementing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key if it exists
            try {
                $table->dropForeign('users_id_foreign');
            } catch (\Exception $e) {}

            // Drop existing primary key (likely on 'usuario')
            // Using DB statement because Blueprint dropPrimary needs to know the column or index name
            try {
                DB::statement('ALTER TABLE users DROP PRIMARY KEY');
            } catch (\Exception $e) {}
        });

        // Use raw SQL to make id primary and auto-increment
        DB::statement('ALTER TABLE users MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY');

        // Re-add unique constraint to usuario if it was dropped with the PK
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'usuario')) {
                // This shouldn't happen, but just in case
            } else {
                // Ensure usuario stays unique
                try {
                    $table->unique('usuario');
                } catch (\Exception $e) {}
            }
            
            // Re-add foreign key to personas
            try {
                $table->foreign('id')->references('id')->on('personas')->onDelete('cascade');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id']);
            DB::statement('ALTER TABLE users MODIFY COLUMN id INT UNSIGNED');
            DB::statement('ALTER TABLE users DROP PRIMARY KEY');
            $table->primary('usuario');
            $table->foreign('id')->references('id')->on('personas')->onDelete('cascade');
        });
    }
}
