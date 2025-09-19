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
        Schema::create('boucher', function (Blueprint $table) {
            $table->unsignedBigInteger('idBoucher')->autoIncrement()->primary();
            $table->string('codigo')->unique();

            $table->unsignedBigInteger('idCita')->unique();
            $table->foreign('idCita')->references('idCita')->on('citas')->onDelete('cascade');

            $table->date('fecha');
            $table->enum('estado',['aceptado','rechazado','pendiente']);
            $table->string('imagen')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boucher');
    }
};
