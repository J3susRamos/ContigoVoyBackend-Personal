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
        Schema::create('etiquetas', function (Blueprint $table) {
            $table->increments('idEtiqueta');
            $table->string('nombre', 100);
        });

        Schema::create('canales', function (Blueprint $table) {
            $table->increments('idCanal'); 
            $table->string('nombre', 100);
        });

        Schema::create('tipo_citas', function (Blueprint $table) {
            $table->increments('idTipoCita'); 
            $table->string('nombre', 100); 
        });

        Schema::create('citas', function (Blueprint $table) {
            $table->unsignedBigInteger('idCita')->autoIncrement();
            $table->unsignedInteger('idPaciente')->nullable();
            $table->unsignedInteger('idPrePaciente')->nullable(); 
            $table->unsignedInteger('idPsicologo'); 
            $table->unsignedInteger('idTipoCita'); 
            $table->unsignedInteger('idCanal'); 
            $table->unsignedInteger('idEtiqueta'); 
            $table->text('motivo_Consulta'); 
            $table->string('estado_Cita', 100); 
            $table->string('colores',100);
            $table->integer('duracion'); 
            $table->date('fecha_cita');
            $table->time('hora_cita');

            $table->foreign('idPaciente')->references('idPaciente')->on('pacientes')->nullOnDelete();
            $table->foreign('idPrePaciente')->references('idPrePaciente')->on('pre_pacientes')->nullOnDelete();
            $table->foreign('idTipoCita')->references('idTipoCita')->on('tipo_citas');
            $table->foreign('idCanal')->references('idCanal')->on('canales');
            $table->foreign('idEtiqueta')->references('idEtiqueta')->on('etiquetas');
            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos');
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
        Schema::dropIfExists('etiquetas');
        Schema::dropIfExists('tipo_citas');
        Schema::dropIfExists('canales');
    }
};
