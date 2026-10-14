<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega a la tabla users los datos que se muestran como autor/firma de las notas
 * y un flag para desactivar usuarios del panel sin borrarlos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('firma', 120)->nullable()->after('name');      // nombre a mostrar en la nota (ej: "Redacción")
            $table->string('avatar')->nullable()->after('email');
            $table->text('bio')->nullable()->after('avatar');
            $table->boolean('activo')->default(true)->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['firma', 'avatar', 'bio', 'activo']);
        });
    }
};
