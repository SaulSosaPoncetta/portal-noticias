<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitas por nota y por día. Permite armar "Más leídas" de las últimas 24/48 hs
 * o de la semana, sin depender del contador total.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->date('fecha');
            $table->unsignedInteger('cantidad')->default(0);

            $table->unique(['noticia_id', 'fecha']);
            $table->index(['fecha', 'cantidad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_diarias');
    }
};
