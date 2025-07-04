<?php
// filepath: database/migrations/xxxx_xx_xx_xxxxxx_create_plantilla_marketing_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_marketing', function (Blueprint $table) {
            $table->increments('id_plantilla');
            $table->unsignedInteger('idPsicologo');
            $table->string('nombre', 100);
            $table->string('asunto', 150);
            $table->string('remitente', 100)->nullable();
            $table->string('destinatarios', 255)->nullable();
            $table->json('bloques');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->boolean('estado')->default(true);

            $table->foreign('idPsicologo')->references('idPsicologo')->on('psicologos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_marketing');
    }
};
