<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('idiomas', function (Blueprint $table) {
            $table->id('idIdioma');            
            $table->string('nombre', 100)->unique();
        });
        Schema::create('idioma_detalle', function (Blueprint $table) {
            $table->unsignedInteger('idPsicologo');     // <-- INT UNSIGNED
            $table->unsignedBigInteger('idIdioma');     // idiomas.idIdioma sí es BIGINT (correcto)
            $table->primary(['idPsicologo','idIdioma']);

            $table->foreign('idPsicologo')
            ->references('idPsicologo')->on('psicologos')
            ->onDelete('cascade');

            $table->foreign('idIdioma')
            ->references('idIdioma')->on('idiomas')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idioma_detalle');
        Schema::dropIfExists('idiomas');
    }
};
