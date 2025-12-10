<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('psicologos', function (Blueprint $table) {
            $table->string('celular', 20)->nullable()->after('genero');
        });
    }

    public function down()
    {
        Schema::table('psicologos', function (Blueprint $table) {
            $table->dropColumn('celular');
        });
    }

};
