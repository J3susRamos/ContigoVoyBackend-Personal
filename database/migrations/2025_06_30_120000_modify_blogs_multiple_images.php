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
        Schema::table('blogs', function (Blueprint $table) {
            // Renombrar la columna imagen a imagenes y mantener como longText
            // pero ahora almacenará un JSON array de imágenes
            $table->renameColumn('imagen', 'imagenes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Revertir el cambio
            $table->renameColumn('imagenes', 'imagen');
        });
    }
};
