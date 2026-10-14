<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración general del sitio en formato clave/valor, editable desde el panel:
 * nombre del diario, logo, ciudad del clima, redes sociales, WhatsApp,
 * propietario, registro DNDA, director, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 80)->unique();
            $table->text('valor')->nullable();
            $table->string('grupo', 40)->default('general');     // general | redes | footer | clima | seo
            $table->string('descripcion')->nullable();           // ayuda que se muestra en el panel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
