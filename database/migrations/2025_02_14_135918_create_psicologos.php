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
        Schema::create('especialidades', function (Blueprint $table) {
            $table->increments('idEspecialidad');
            $table->string('nombre',100)->unique();
        });

        // se modifico 'Titulo' a 'titulo'
        Schema::create('psicologos', function (Blueprint $table) {
            $table->increments('idPsicologo');
            $table->string('titulo', 100);
            $table->text('introduccion');
            $table->string('pais', 4);
            $table->string('genero', 50);
            $table->integer('experiencia');
            $table->json('horario')->nullable(); 
            $table->char('estado', 1);
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Tabla intermedia entre psicÃ³logos y especialidades (Muchos a Muchos)
        Schema::create('especialidad_detalle', function (Blueprint $table) {
            $table->unsignedInteger('idPsicologo');
            $table->unsignedInteger('idEspecialidad');

            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos')->onDelete('cascade');
            $table->foreign('idEspecialidad')->references('idEspecialidad')->on('especialidades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidad_detalle');
        Schema::dropIfExists('psicologos');
        Schema::dropIfExists('especialidades');
    }
};