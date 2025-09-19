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
        Schema::create('categorias', function (Blueprint $table) {
            $table->increments('idCategoria');
            $table->string('nombre',100);
        });

        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('idBlog');
            $table->unsignedInteger('idCategoria');
            $table->string('tema', 100);
            $table->text('contenido');
            $table->text('imagen');
            $table->unsignedInteger('idPsicologo');
            $table->timestamp('fecha_publicado')->useCurrent();

            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos')->onDelete('cascade');
            $table->foreign('idCategoria')->references('idCategoria')->on('categorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('categorias');
    }
};
