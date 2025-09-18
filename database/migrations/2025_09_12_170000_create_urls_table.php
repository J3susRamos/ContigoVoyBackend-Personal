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
        Schema::create('urls', function (Blueprint $table) {
            $table->unsignedBigInteger('idUrls')->autoIncrement()->primary();
            $table->string('name');
            $table->string('enlace');
            $table->unsignedBigInteger('idPadre')->nullable();
            $table->string('iconoName');
            $table->timestamps();

            $table->foreign('idPadre')
                ->references('idUrls')
                ->on('urls')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
