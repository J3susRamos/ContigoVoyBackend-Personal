<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Paciente extends Model
{
    use HasFactory;
    protected $primaryKey = 'idPaciente';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $table = 'pacientes';
    public $timestamps = false; 

    protected $casts = [
        'fecha_nacimiento' => 'date',
     ];

    protected $fillable = [
        'codigo',
        'nombre',
        'apellido',
        'email',
        'fecha_nacimiento',
        'ocupacion',
        'estadoCivil',
        'genero',
        'DNI',
        'imagen',
        'celular',
        'direccion',
        'idPsicologo'
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class, 'idPaciente');
    }
    
    public function psicologo()
    {
        return $this->belongsTo(Psicologo::class, 'idPsicologo');
    }

    public function registroFamiliar()
    {
        return $this->hasOne(RegistroFamiliar::class, 'idPaciente');
    }

    public function getEdadAttribute()
    {
        return Carbon::parse($this->fecha_nacimiento)->age;
    }

    public static function generatePacienteCode()
    {
        $lastCode = self::selectRaw("MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as max_code")
            ->value('max_code');
    
        $newNumber = $lastCode ? $lastCode + 1 : 1;
    
        return 'PAC' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
}
