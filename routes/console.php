<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})->purpose("Display an inspiring quote");

// 🚀 CONFIGURACIÓN DE CRON JOBS PARA NOTIFICACIONES AUTOMÁTICAS
Schedule::command("notifications:process")
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/notifications.log"));

// Programar notificaciones para los próximos 7 días, ejecutar una vez al día
Schedule::command("notifications:schedule --days=7")
    ->dailyAt("06:00")
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/schedule-notifications.log"));

// Comando para cancelar citas sin pagar (ya existente, pero ahora con logs)
Schedule::command("app:cancelar-citas-sin-pagar")
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path("logs/cancel-unpaid-appointments.log"));
