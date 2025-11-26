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
        Schema::create('blog_images', function (Blueprint $table) {
            $table->id();

            // ✅ MISMO tipo que blogs.idBlog
            $table->unsignedInteger('blog_id');

            $table->foreign('blog_id')
                ->references('idBlog')
                ->on('blogs')
                ->onDelete('cascade');

            // Imagen + metadatos
            $table->text('src');            // base64 o URL
            $table->string('title')->nullable();
            $table->string('alt')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_images');
    }
};
