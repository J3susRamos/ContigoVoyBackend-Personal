<?php

namespace App\Http\Controllers\Boucher;

use App\Http\Controllers\Controller;
use App\Models\Boucher;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\Request;
use App\Models\Cita;
use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Models\Paciente;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class BoucherController extends Controller
{
    public function enviarBoucher(Request $request)
    {
        $request->validate([
            'idCita' => 'required|exists:citas,idCita',
            'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $userId = Auth::id();

            $paciente = Paciente::where('user_id', Auth::id())->first();

            if (!$paciente) {
                Log::error("No se encontró paciente con user_id: " . Auth::id());
                return response()->json(['message' => 'Paciente no encontrado.'], 404);
            }

            Log::info("Usuario autenticadoaaa: $userId, ID del paciente: {$paciente->idPaciente}");

            $cita = Cita::where('idCita', $request->idCita)
                ->where('idPaciente', $paciente->idPaciente)
                ->where('estado_Cita', 'Sin pagar')
                ->first();

            if (!$cita) {
                return response()->json(['message' => 'La cita no existe o no te pertenece.'], 404);
            }

            $boucherExistente = Boucher::where('idCita', $cita->idCita)->first();
            if ($boucherExistente) {
                return response()->json(['message' => 'Ya has enviado un boucher para esta cita.'], 409);
            }

            $imagen = $request->file('imagen');
            $nombreImagen = 'boucher_' . Str::uuid() . '.webp';
            $rutaCarpeta = 'bouchers';


            $imageManager = new ImageManager(Driver::class);
            $imageWebp = $imageManager
                ->read($imagen->getRealPath())
                ->encode(new WebpEncoder(quality: 90));
            Storage::disk('public')->put("$rutaCarpeta/$nombreImagen", $imageWebp);

            $ruta = "$rutaCarpeta/$nombreImagen";

            $boucher = Boucher::create([
                'codigo' => Boucher::generateBoucherCode(),
                'idCita' => $cita->idCita,
                'fecha' => Carbon::now()->toDateString(),
                'estado' => 'pendiente',
                'imagen' => $ruta,
            ]);

            return response()->json([
                'message' => 'Boucher enviado correctamente.',
                'boucher' => $boucher
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al enviar el boucher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getBouchers()
    {
        try {
            $userId = Auth::id();

            $paciente = Paciente::where('user_id', $userId)->first();

            if (!$paciente) {
                return response()->json(['message' => 'Paciente no encontrado.'], 404);
            }

            Log::info("Usuario autenticado: $userId, ID del paciente: {$paciente->idPaciente}");

            $bouchers = DB::table('boucher')
                ->join('citas', 'boucher.idCita', '=', 'citas.idCita')
                ->where('citas.idPaciente', $paciente->idPaciente)
                ->select('boucher.*')
                ->get();

            Log::info('Bouchers con join: ' . count($bouchers));

            return response()->json(['bouchers' => $bouchers], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener los bouchers.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function sinPagar(){
        try {
            $userId = Auth::id();

            $paciente = Paciente::where('user_id', $userId)->first();

            if (!$paciente) {
                return response()->json(['message' => 'Paciente no encontrado.'], 404);
            }

            Log::info("Usuario autenticado: $userId, ID del paciente: {$paciente->idPaciente}");

            $citasSinPagar = Cita::where('idPaciente', $paciente->idPaciente)
                ->where('estado_Cita', 'Sin pagar')
                ->get();

            return response()->json(['citasSinPagar' => $citasSinPagar], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener las citas sin pagar.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

}
