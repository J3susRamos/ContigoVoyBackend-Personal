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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->increments('idPaciente'); 
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100); 
            $table->string('apellido', 100); 
            $table->string('email', 100)->unique(); 
            $table->date('fecha_nacimiento');
            $table->mediumText('imagen')->nullable();
            $table->string('genero', 20); 
            $table->string('ocupacion', 100);
            $table->string('estadoCivil', 100);
            $table->string('DNI', 8)->unique();
            $table->string('celular', 30); 
            $table->string('direccion',150); 
            $table->string('departamento', 100);
            $table->string('pais', 100);  
            $table->unsignedInteger('idPsicologo');
            
            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos')->onDelete('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
