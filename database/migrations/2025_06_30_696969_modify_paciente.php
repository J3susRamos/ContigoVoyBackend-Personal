<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table("pacientes", function (Blueprint $table) {
            $table->string("pais")->nullable();
            $table->string("provincia")->nullable();
            $table->string("departamento")->nullable();
        });
    }

    public function down()
    {
        Schema::table("pacientes", function (Blueprint $table) {
            $table->dropColumn(["pais", "provincia", "departamento"]);
        });
    }
};
