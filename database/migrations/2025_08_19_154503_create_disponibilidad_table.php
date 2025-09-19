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
        Schema::create('disponibilidad', function (Blueprint $table) {
            $table->id('idDisponibilidad');
            $table->unsignedInteger('idPsicologo');

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('turno')->nullable();
            $table->timestamps();

            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidad');
    }
};
