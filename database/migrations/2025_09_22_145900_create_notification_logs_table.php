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
        Schema::create("notification_logs", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("idCita");
            $table->enum("tipo_notificacion", [
                "recordatorio_1_hora",
                "recordatorio_pago_3_horas",
                "recordatorio_30_minutos",
                "recordatorio_24_horas",
            ]);
            $table->string("telefono");
            $table->text("mensaje");
            $table
                ->enum("estado", ["enviado", "error", "pendiente"])
                ->default("pendiente");
            $table->string("whatsapp_message_id")->nullable();
            $table->text("error_mensaje")->nullable();
            $table->timestamp("fecha_programada");
            $table->timestamp("fecha_enviado")->nullable();
            $table->timestamps();

            // Índices
            $table
                ->foreign("idCita")
                ->references("idCita")
                ->on("citas")
                ->onDelete("cascade");
            $table->index(["idCita", "tipo_notificacion"]);
            $table->index("fecha_programada");
            $table->index("estado");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("notification_logs");
    }
};
