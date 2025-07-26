<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Paciente extends Model
{
    use HasFactory;
    protected $primaryKey = "idPaciente";
    public $incrementing = true;
    protected $keyType = "int";
    protected $table = "pacientes";
    public $timestamps = false;

    protected $casts = [
        "fecha_nacimiento" => "date",
    ];

    protected $fillable = [
        "codigo",
        "nombre",
        "apellido",
        "email",
        "fecha_nacimiento",
        "ocupacion",
        "estadoCivil",
        "genero",
        "DNI",
        "imagen",
        "celular",
        "direccion",
        "idPsicologo",
        "pais",
        "departamento",
        "user_id",
    ];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, "idPaciente");
    }

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, "idPsicologo");
    }

    public function registroFamiliar(): HasOne
    {
        return $this->hasOne(RegistroFamiliar::class, "idPaciente");
    }

    public function getEdadAttribute(): int
    {
        return Carbon::parse($this->fecha_nacimiento)->age;
    }

    public static function generatePacienteCode(): string
    {
        $lastCode = self::selectRaw(
            "MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as max_code"
        )->value("max_code");

        $newNumber = $lastCode ? $lastCode + 1 : 1;

        return "PAC" . str_pad($newNumber, 4, "0", STR_PAD_LEFT);
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
