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
        Schema::create('enfermedades', function (Blueprint $table) {
            $table->increments('idEnfermedad'); 
            $table->string('nombreEnfermedad', 500); 
            $table->string('DSM5', 50); 
            $table->string('CEA10',50);
        });

        Schema::create('atenciones', function (Blueprint $table) {
            $table->id('idAtencion'); 
            $table->unsignedBigInteger('idCita');
            $table->text('motivoConsulta');
            $table->string('formaContacto', 100);
            $table->text('diagnostico');
            $table->text('tratamiento');
            $table->text('observacion');
            $table->text('ultimosObjetivos');
            $table->unsignedInteger('idEnfermedad'); 
            $table->text('documentosAdicionales')->nullable();
            $table->text('comentario');
            $table->date('fechaAtencion');
            $table->text('descripcion')->nullable();

            $table->foreign('idCita')->references('idCita')->on('citas')->onDelete('cascade');
            $table->foreign('idEnfermedad')->references('idEnfermedad')->on('enfermedades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atenciones');
        Schema::dropIfExists('enfermedades');
    }
};
