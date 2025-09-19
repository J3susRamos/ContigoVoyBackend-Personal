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
        Schema::create('contactos', function (Blueprint $table) {
            $table->increments('idContacto'); 
            $table->string('nombre', 100); 
            $table->string('apellido', 100); 
            $table->integer('celular')->unique();
            $table->string('email', 100)->unique(); 
            $table->text('comentario'); 
            $table->timestamp('fecha_envio')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
