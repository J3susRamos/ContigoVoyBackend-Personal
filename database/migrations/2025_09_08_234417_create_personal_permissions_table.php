<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name_permission');
            $table->unsignedInteger('id_user');
            $table->foreign('id_user')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('personal_permissions');
    }
};
