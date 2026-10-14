<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cotizaciones de monedas para el widget lateral.
 * Se actualizan automáticamente desde una API (Módulo 8); se guardan acá
 * para no consultar la API en cada visita y para poder ocultar las que no interesan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();              // oficial, blue, bolsa, contadoconliqui, tarjeta, mayorista, cripto, eur, brl...
            $table->string('nombre', 60);                        // "Dólar Blue"
            $table->decimal('compra', 14, 2)->nullable();
            $table->decimal('venta', 14, 2)->nullable();
            $table->dateTime('actualizada_at')->nullable();      // fecha que informa la API
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
