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
        Schema::table('boucher', function (Blueprint $table) {
            $table->dropForeign(['idCita']);
            $table->dropUnique('boucher_idcita_unique');
            $table->foreign('idCita')->references('idCita')->on('citas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boucher', function (Blueprint $table) {
            $table->dropForeign(['idCita']);
            $table->unique('idCita');
            $table->foreign('idCita')->references('idCita')->on('citas')->onDelete('cascade');
        });
    }
};
