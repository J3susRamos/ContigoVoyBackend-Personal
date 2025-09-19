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
        Schema::create('comentarios', function (Blueprint $table) {
            $table->increments('idComentario');
            $table->string('nombre', 50);
            $table->text('comentario');
            $table->unsignedInteger('idBlog');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('idBlog')->references('idBlog')->on('blogs')->onDelete('cascade');
        });

        Schema::create('respuestas', function (Blueprint $table) {
            $table->increments('idRespuesta'); 
            $table->string('nombre', 50);
            $table->text('respuesta');
            $table->unsignedInteger('idComentario');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('idComentario')->references('idComentario')->on('comentarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('comentarios');
    }
};
