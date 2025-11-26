<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ Nombre correcto de la tabla
        Schema::create('blog_metadata', function (Blueprint $table) {
            $table->id();

            // ✅ Tipo compatible con blogs.idBlog (increments → unsignedInteger)
            $table->unsignedInteger('blog_id');

            $table->foreign('blog_id')
                ->references('idBlog')
                ->on('blogs')
                ->onDelete('cascade');

            // Campos de SEO
            $table->string('metaTitle')->nullable();
            $table->text('metaDescription')->nullable();
            $table->string('keywords')->nullable();

            $table->timestamps(); // si no los quieres, puedes quitar esto y poner $timestamps=false en el modelo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_metadata');
    }
};
