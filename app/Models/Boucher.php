<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boucher extends Model
{
    use HasFactory;

    protected $table = 'boucher';
    protected $primaryKey = 'idBoucher';
    public $timestamps = true;

    protected $fillable = [
        'codigo',
        'idCita',
        'fecha',
        'estado',
        'imagen',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'idCita');
    }

    public static function generateBoucherCode(): string
    {
        $lastCode = self::selectRaw(
            "MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as max_code"
        )->value("max_code");

        $newNumber = $lastCode ? $lastCode + 1 : 1;

        return "BOU" . str_pad($newNumber, 4, "0", STR_PAD_LEFT);
    }
}
