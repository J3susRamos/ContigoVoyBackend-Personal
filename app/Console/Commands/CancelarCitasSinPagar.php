<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;
use Carbon\Carbon;

class CancelarCitasSinPagar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancelar-citas-sin-pagar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancela citas sin pagar cuya fecha límite ya pasó';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $citas = Cita::where('estado_Cita', 'Sin pagar')
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<', $now)
            ->get();

        foreach ($citas as $cita) {
            $cita->estado_Cita = 'Cancelada';
            $cita->save();
        }

        $this->info(count($citas) . ' citas fueron canceladas.');
    }
}
