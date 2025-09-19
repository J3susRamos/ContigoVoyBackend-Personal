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
        Schema::create('pre_pacientes', function (Blueprint $table) {
            $table->increments('idPrePaciente');
            $table->string('nombre', 150);
            $table->string('correo')->unique();
            $table->string('celular', 30);
            $table->string('estado')->default('pendiente');
            $table->unsignedInteger('idPsicologo');
            $table->timestamps();

            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_pacientes');
    }
};
