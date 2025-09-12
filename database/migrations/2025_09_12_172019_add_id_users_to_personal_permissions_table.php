<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id_urls')->nullable()->after('id');
            $table->foreign('id_urls')
                ->references('idUrls')
                ->on('urls')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('personal_permissions', function (Blueprint $table) {
            $table->dropColumn('id_urls');
        });
    }
};
