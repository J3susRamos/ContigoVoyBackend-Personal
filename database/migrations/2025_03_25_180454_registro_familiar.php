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
        Schema::create('registro_familiar', function (Blueprint $table) {
            $table->increments('idRegistro');
            $table->unsignedInteger('idPaciente');
            $table->string('nombre_madre', 150);
            $table->string('estado_madre', 100);
            $table->string('nombre_padre', 150);
            $table->string('estado_padre', 100);
            $table->string('nombre_apoderado', 150);
            $table->string('estado_apoderado', 100);
            $table->integer('cantidad_hijos');
            $table->integer('cantidad_hermanos');
            $table->text('integracion_familiar');
            $table->text('historial_familiar');
            
            $table->foreign('idPaciente')->references('idPaciente')->on('pacientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_familiar');
    }
};
