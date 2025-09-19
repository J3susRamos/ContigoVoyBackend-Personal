<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnfermedadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enfermedades = [
            [
                'nombreEnfermedad' => 'Discapacidad intelectual',
                'DSM5' => '317.00',
                'CEA10' => 'F70'
            ],
            [
                'nombreEnfermedad' => 'Discapacidad intelectual',
                'DSM5' => '318.00',
                'CEA10' => 'F71'
            ],
            [
                'nombreEnfermedad' => 'Discapacidad intelectual',
                'DSM5' => '318.10',
                'CEA10' => 'F72'
            ],
            [
                'nombreEnfermedad' => 'Discapacidad intelectual',
                'DSM5' => '318.20',
                'CEA10' => 'F73'
            ],
            [
                'nombreEnfermedad' => 'Retraso general del desarrollo',
                'DSM5' => '315.00',
                'CEA10' => 'F88'
            ],
            [
                'nombreEnfermedad' => 'Discapacidad intelectual no especificada',
                'DSM5' => '319.00',
                'CEA10' => 'F79'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del lenguaje',
                'DSM5' => '315.32',
                'CEA10' => 'F80.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno fonológico',
                'DSM5' => '315.39',
                'CEA10' => 'F80.0'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de fluidez de inicio en la infancia',
                'DSM5' => '315.35',
                'CEA10' => 'F80.81'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de fluidez de inicio en la infancia',
                'DSM5' => '315.35',
                'CEA10' => 'F80.81'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de fluidez de inicio en el adulto',
                'DSM5' => '307.00',
                'CEA10' => 'F98.5'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la comunicación social',
                'DSM5' => '315.39',
                'CEA10' => 'F80.89'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la comunicación no especificado',
                'DSM5' => '315.39',
                'CEA10' => 'F80.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del espectro del autismo',
                'DSM5' => '299.00',
                'CEA10' => 'F84.0'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del espectro del autismo con especificación',
                'DSM5' => '293.89',
                'CEA10' => 'F06.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por déficit de atención por hiperactividad',
                'DSM5' => '314.01',
                'CEA10' => 'F90.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por déficit de atención por hiperactividad',
                'DSM5' => '314.00',
                'CEA10' => 'F90.0'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por déficit de atención por hiperactividad',
                'DSM5' => '314.01',
                'CEA10' => 'F90.1'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno por déficit de atención por hiperactividad',
                'DSM5' => '314.01',
                'CEA10' => 'F90.08'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por déficit de atención por hiperactividad no especificado',
                'DSM5' => '314.01',
                'CEA10' => 'F90.09'
            ],
            [
                'nombreEnfermedad' => 'Trastorno específico del aprendizaje',
                'DSM5' => '315.00',
                'CEA10' => 'F81.0'
            ],
            [
                'nombreEnfermedad' => 'Trastorno específico del aprendizaje',
                'DSM5' => '315.20',
                'CEA10' => 'F81.81'
            ],
            [
                'nombreEnfermedad' => 'Trastorno específico del aprendizaje',
                'DSM5' => '315.10',
                'CEA10' => 'F81.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del desarrollo de la coordinación',
                'DSM5' => '315.20',
                'CEA10' => 'F82'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de movimientos estereotipados',
                'DSM5' => '307.30',
                'CEA10' => 'F98.4'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la Tourette',
                'DSM5' => '307.23',
                'CEA10' => 'F95.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de tics motores o vocales persistente',
                'DSM5' => '307.22',
                'CEA10' => 'F95.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de tics transitorio',
                'DSM5' => '307.21',
                'CEA10' => 'F95.0'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de tics especificado',
                'DSM5' => '307.20',
                'CEA10' => 'F95.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de tics no especificado',
                'DSM5' => '307.20',
                'CEA10' => 'F95.9'
            ],
            [
                'nombreEnfermedad' => 'Catatonía asociada a otro trastorno mental',
                'DSM5' => '293.89',
                'CEA10' => 'F06.1'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno del espectro de la esquizofrenia',
                'DSM5' => '298.80',
                'CEA10' => 'F28'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del espectro de la esquizofrenia no especificado',
                'DSM5' => '298.90',
                'CEA10' => 'F29'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco actual o más leve',
                'DSM5' => '296.41',
                'CEA10' => 'F31.11'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco actual moderado',
                'DSM5' => '296.42',
                'CEA10' => 'F31.12'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco actual grave',
                'DSM5' => '296.43',
                'CEA10' => 'F31.13'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco con características psicóticas',
                'DSM5' => '296.44',
                'CEA10' => 'F31.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco en remisión parcial',
                'DSM5' => '296.45',
                'CEA10' => 'F31.73'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco en remisión total',
                'DSM5' => '296.46',
                'CEA10' => 'F31.74'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio maníaco no especificado',
                'DSM5' => '296.40',
                'CEA10' => 'F31.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio hipomaníaco actual',
                'DSM5' => '296.40',
                'CEA10' => 'F31.71'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio hipomaníaco en remisión parcial',
                'DSM5' => '296.45',
                'CEA10' => 'F31.72'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio hipomaníaco en remisión total',
                'DSM5' => '296.46',
                'CEA10' => 'F31.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio hipomaníaco no especificado',
                'DSM5' => '296.40',
                'CEA10' => 'F31.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo actual leve',
                'DSM5' => '296.51',
                'CEA10' => 'F31.31'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo actual moderado',
                'DSM5' => '296.52',
                'CEA10' => 'F31.32'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo actual grave',
                'DSM5' => '296.53',
                'CEA10' => 'F31.5'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo con características psicóticas',
                'DSM5' => '296.54',
                'CEA10' => 'F31.75'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo en remisión parcial',
                'DSM5' => '296.55',
                'CEA10' => 'F31.76'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo en remisión total',
                'DSM5' => '296.56',
                'CEA10' => 'F31.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar - Episodio depresivo no especificado',
                'DSM5' => '296.50',
                'CEA10' => 'F31.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar 2',
                'DSM5' => '296.89',
                'CEA10' => 'F31.81'
            ],
            [
                'nombreEnfermedad' => 'Trastorno ciclotímico',
                'DSM5' => '301.13',
                'CEA10' => 'F34.0',
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar y trastorno relacionado debido a otra afección médica - Con características psicóticas',
                'DSM5' => '293.83',
                'CEA10' => 'F06.33',
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar y trastorno relacionado debido a otra afección médica - Con síntomas maníacos',
                'DSM5' => '293.83',
                'CEA10' => 'F06.34',
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno bipolar y trastorno relacionado especificado',
                'DSM5' => '296.89',
                'CEA10' => 'F31.89',
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar y trastorno relacionado no especificado',
                'DSM5' => '296.80',
                'CEA10' => 'F31.9',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de desregulación perturbador del estado de ánimo',
                'DSM5' => '296.99',
                'CEA10' => 'F34.8',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (leve)',
                'DSM5' => '296.21',
                'CEA10' => 'F32.0',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (moderado)',
                'DSM5' => '296.22',
                'CEA10' => 'F32.1',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (grave)',
                'DSM5' => '296.23',
                'CEA10' => 'F32.2',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (con características psicóticas)',
                'DSM5' => '296.24',
                'CEA10' => 'F32.3',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (en remisión parcial)',
                'DSM5' => '296.25',
                'CEA10' => 'F32.4',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (en remisión total)',
                'DSM5' => '296.26',
                'CEA10' => 'F32.5',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio único (no especificado)',
                'DSM5' => '296.20',
                'CEA10' => 'F32.9',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (leve)',
                'DSM5' => '296.31',
                'CEA10' => 'F33.0',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (moderado)',
                'DSM5' => '296.32',
                'CEA10' => 'F33.1',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (grave)',
                'DSM5' => '296.33',
                'CEA10' => 'F33.2',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (con características psicóticas)',
                'DSM5' => '296.34',
                'CEA10' => 'F33.3',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (en remisión parcial)',
                'DSM5' => '296.35',
                'CEA10' => 'F33.41',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (en remisión total)',
                'DSM5' => '296.36',
                'CEA10' => 'F33.42',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de depresión mayor - Episodio recurrente (no especificado)',
                'DSM5' => '296.30',
                'CEA10' => 'F33.9',
            ],
            [
                'nombreEnfermedad' => 'Trastorno depresivo persistente',
                'DSM5' => '300.40',
                'CEA10' => 'F34.1',
            ],
            [
                'nombreEnfermedad' => 'Trastorno disfórico premenstrual',
                'DSM5' => '625.40',
                'CEA10' => 'N94.3',
            ],
            [
                'nombreEnfermedad' => 'Trastorno depresivo debido a otra afección médica',
                'DSM5' => '293.83',
                'CEA10' => 'F06.31',
            ],
            [
                'nombreEnfermedad' => 'Trastorno depresivo debido a otra afección médica - Con síntomas depresivos',
                'DSM5' => '293.83',
                'CEA10' => 'F06.32',
            ],
            [
                'nombreEnfermedad' => 'Trastorno depresivo debido a otra afección médica - Con episodio depresivo mayor',
                'DSM5' => '293.83',
                'CEA10' => 'F06.34',
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno depresivo especificado',
                'DSM5' => '311.00',
                'CEA10' => 'F32.8',
            ],
            [
                'nombreEnfermedad' => 'Trastorno depresivo no especificado',
                'DSM5' => '311.00',
                'CEA10' => 'F32.9',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad por separación',
                'DSM5' => '309.21',
                'CEA10' => 'F93.0',
            ],
            [
                'nombreEnfermedad' => 'Mutismo selectivo',
                'DSM5' => '313.23',
                'CEA10' => 'F94.0',
            ],
            [
                'nombreEnfermedad' => 'Fobia específica - Animal',
                'DSM5' => '300.29',
                'CEA10' => 'F40.218',
            ],
            [
                'nombreEnfermedad' => 'Fobia específica - Entorno natural',
                'DSM5' => '300.29',
                'CEA10' => 'F40.228',
            ],
            [
                'nombreEnfermedad' => 'Sangre-inyección-lesión - Miedo a la sangre',
                'DSM5' => '300.29',
                'CEA10' => 'F40.230',
            ],
            [
                'nombreEnfermedad' => 'Sangre-inyección-lesión - Miedo a las inyecciones u otras perforaciones',
                'DSM5' => '300.29',
                'CEA10' => 'F40.231',
            ],
            [
                'nombreEnfermedad' => 'Sangre-inyección-lesión - Miedo a otra atención médica',
                'DSM5' => '300.29',
                'CEA10' => 'F40.232',
            ],
            [
                'nombreEnfermedad' => 'Sangre-inyección-lesión - Miedo a una lesión',
                'DSM5' => '300.29',
                'CEA10' => 'F40.233',
            ],
            [
                'nombreEnfermedad' => 'Sangre-inyección-lesión - Situacional',
                'DSM5' => '300.29',
                'CEA10' => 'F40.248',
            ],
            [
                'nombreEnfermedad' => 'Sangre-inyección-lesión - Otra',
                'DSM5' => '300.29',
                'CEA10' => 'F40.298',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad social',
                'DSM5' => '300.23',
                'CEA10' => 'F40.10',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de pánico',
                'DSM5' => '300.01',
                'CEA10' => 'F41.0',
            ],
            [
                'nombreEnfermedad' => 'Agorafobia',
                'DSM5' => '300.22',
                'CEA10' => 'F40.0',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad generalizada',
                'DSM5' => '300.02',
                'CEA10' => 'F41.1',
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad debido a otra afección médica',
                'DSM5' => '293.84',
                'CEA10' => 'F06.4',
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de ansiedad especificado',
                'DSM5' => '300.09',
                'CEA10' => 'F41.8',
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de ansiedad especificado', 
                'DSM5' => '300.09', 
                'CEA10' => 'F41.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad no especificado', 
                'DSM5' => '300.00', 
                'CEA10' => 'F41.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno obsesivo-compulsivo', 
                'DSM5' => '300.30', 
                'CEA10' => 'F42'
            ],
            [
                'nombreEnfermedad' => 'Trastorno dismórfico corporal', 
                'DSM5' => '300.70', 
                'CEA10' => 'F45.22'
            ],
            [
                'nombreEnfermedad' => 'Tricotilomanía', 
                'DSM5' => '312.39', 
                'CEA10' => 'F63.3'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de excoriación', 
                'DSM5' => '698.40', 
                'CEA10' => 'L98.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno obsesivo-compulsivo y trastorno relacionado inducido por sustancias/medicamentos', 
                'DSM5' => '294.80', 
                'CEA10' => 'F06.8'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno obsesivo-compulsivo y trastorno relacionado especificado', 
                'DSM5' => '300.30', 
                'CEA10' => 'F42'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de apego reactivo', 
                'DSM5' => '313.89', 
                'CEA10' => 'F94.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de relación social desinhibida', 
                'DSM5' => '313.89', 
                'CEA10' => 'F94.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de estrés postraumático', 
                'DSM5' => '309.81', 
                'CEA10' => 'F43.10'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de estrés agudo', 
                'DSM5' => '308.30', 
                'CEA10' => 'F43.0'
            ],
            [
                'nombreEnfermedad' => 'Trastornos de adaptación - Con estado de ánimo depresivo', 
                'DSM5' => '309.0', 
                'CEA10' => 'F43.21'
            ],
            [
                'nombreEnfermedad' => 'Trastornos de adaptación - Con ansiedad', 
                'DSM5' => '309.24', 
                'CEA10' => 'F43.22'
            ],
            [
                'nombreEnfermedad' => 'Trastornos de adaptación - Con ansiedad mixta y estado de ánimo depresivo mixto', 
                'DSM5' => '309.28', 
                'CEA10' => 'F43.23'
            ],
            [
                'nombreEnfermedad' => 'Trastornos de adaptación - Con alteración de la conducta', 
                'DSM5' => '309.3', 
                'CEA10' => 'F43.24'
            ],
            [
                'nombreEnfermedad' => 'Trastornos de adaptación - Con alteración mixta de emociones y conducta', 
                'DSM5' => '309.4', 
                'CEA10' => 'F43.25'
            ],
            [
                'nombreEnfermedad' => 'Trastornos de adaptación - Sin especificar', 
                'DSM5' => '309.9', 
                'CEA10' => 'F43.20'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno relacionado con traumas y factores de estrés especificado', 
                'DSM5' => '309.89', 
                'CEA10' => 'F43.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno relacionado con traumas y factores de estrés no especificado', 
                'DSM5' => '309.9', 
                'CEA10' => 'F43.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de identidad disociativo', 
                'DSM5' => '300.14', 
                'CEA10' => 'F44.81'
            ],
            [
                'nombreEnfermedad' => 'Amnesia disociativa', 
                'DSM5' => '300.12', 
                'CEA10' => 'F44.0'
            ],
            [
                'nombreEnfermedad' => 'Amnesia disociativa - Con fuga disociativa', 
                'DSM5' => '300.13', 
                'CEA10' => 'F44.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de despersonalización/desrealización', 
                'DSM5' => '300.60', 
                'CEA10' => 'F48.1'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno disociativo especificado', 
                'DSM5' => '300.15', 
                'CEA10' => 'F44.89'
            ],
            [
                'nombreEnfermedad' => 'Trastorno disociativo no especificado', 
                'DSM5' => '300.15', 
                'CEA10' => 'F44.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de síntomas somáticos', 
                'DSM5' => '300.82', 
                'CEA10' => 'F45.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad por enfermedad', 
                'DSM5' => '300.70', 
                'CEA10' => 'F45.21'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de conversión - Con debilidad o parálisis', 
                'DSM5' => '300.11', 
                'CEA10' => 'F44.4'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de conversión - Con ataques o convulsiones', 
                'DSM5' => '300.11', 
                'CEA10' => 'F44.5'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de conversión - Con anestesia o pérdida sensorial', 
                'DSM5' => '300.11', 
                'CEA10' => 'F44.6'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de conversión - Con síntomas mixtos', 
                'DSM5' => '300.11', 
                'CEA10' => 'F44.7'
            ],
            [
                'nombreEnfermedad' => 'Factores psicológicos que afectan a otras afecciones médicas', 
                'DSM5' => '316.00', 
                'CEA10' => 'F54'
            ],
            [
                'nombreEnfermedad' => 'Trastorno facticio', 
                'DSM5' => '300.19', 
                'CEA10' => 'F68.10'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de síntomas somáticos y trastorno relacionado especificado', 
                'DSM5' => '300.89', 
                'CEA10' => 'F45.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de síntomas somáticos y trastorno relacionado no especificado', 
                'DSM5' => '300.82', 
                'CEA10' => 'F45.9'
            ],
            [
                'nombreEnfermedad' => 'Pica - En niños', 
                'DSM5' => '307.52', 
                'CEA10' => 'F98.3'
            ],
            [
                'nombreEnfermedad' => 'Pica - En adultos', 
                'DSM5' => '307.52', 
                'CEA10' => 'F50.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de rumiación', 
                'DSM5' => '307.53', 
                'CEA10' => 'F98.21'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de evitación/restricción de la ingestión de alimentos', 
                'DSM5' => '307.59', 
                'CEA10' => 'F50.8'
            ],
            [
                'nombreEnfermedad' => 'Anorexia nerviosa - Tipo restrictivo', 
                'DSM5' => '307.10', 
                'CEA10' => 'F50.01'
            ],
            [
                'nombreEnfermedad' => 'Anorexia nerviosa - Tipo por atracón/purgas', 
                'DSM5' => '307.10', 
                'CEA10' => 'F50.02'
            ],
            [
                'nombreEnfermedad' => 'Bulimia nerviosa', 
                'DSM5' => '307.51', 
                'CEA10' => 'F50.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por atracón',
                'DSM5' => '307.51',
                'CEA10' => 'F50.8'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno alimentario o de la ingestión de alimentos especificado',
                'DSM5' => '307.59',
                'CEA10' => 'F50.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno alimentario o de la ingestión de alimentos no especificado',
                'DSM5' => '307.50',
                'CEA10' => 'F50.9'
            ],
            [
                'nombreEnfermedad' => 'Enuresis',
                'DSM5' => '307.60',
                'CEA10' => 'F98.0'
            ],
            [
                'nombreEnfermedad' => 'Encopresis',
                'DSM5' => '307.70',
                'CEA10' => 'F98.1'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de la excreción especificado - Con síntomas predominantes urinarios',
                'DSM5' => '788.39',
                'CEA10' => 'N39.498'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de la excreción especificado - Con síntomas predominantes fecales',
                'DSM5' => '787.60',
                'CEA10' => 'R15.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la excreción no especificado - Con síntomas urinarios',
                'DSM5' => '788.30',
                'CEA10' => 'R32'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la excreción no especificado - Con síntomas fecales',
                'DSM5' => '787.60',
                'CEA10' => 'R15.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de insomnio',
                'DSM5' => '307.42',
                'CEA10' => 'F51.01'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por hipersomnia',
                'DSM5' => '307.44',
                'CEA10' => 'F51.11'
            ],
            [
                'nombreEnfermedad' => 'Narcolepsia sin cataplejía pero con deficiencia de hipocretina',
                'DSM5' => '347.00',
                'CEA10' => 'G47.419'
            ],
            [
                'nombreEnfermedad' => 'Narcolepsia con cataplejía pero sin deficiencia de hipocretina',
                'DSM5' => '347.01',
                'CEA10' => 'G47.411'
            ],
            [
                'nombreEnfermedad' => 'Narcolepsia secundaria a otra afección médica',
                'DSM5' => '347.10',
                'CEA10' => 'G47.429'
            ],
            [
                'nombreEnfermedad' => 'Apnea e hipopnea obstructiva del sueño',
                'DSM5' => '327.23',
                'CEA10' => 'G47.33'
            ],
            [
                'nombreEnfermedad' => 'Apnea central del sueño idiopática',
                'DSM5' => '327.21',
                'CEA10' => 'G47.31'
            ],
            [
                'nombreEnfermedad' => 'Respiración de Cheyne-Stokes',
                'DSM5' => '786.04',
                'CEA10' => 'R06.3'
            ],
            [
                'nombreEnfermedad' => 'Apnea central del sueño con consumo concurrente de opioides',
                'DSM5' => '780.57',
                'CEA10' => 'G47.37'
            ],
            [
                'nombreEnfermedad' => 'Hipoventilación idiopática',
                'DSM5' => '327.24',
                'CEA10' => 'G47.34'
            ],
            [
                'nombreEnfermedad' => 'Hipoventilación alveolar central congénita',
                'DSM5' => '327.25',
                'CEA10' => 'G47.35'
            ],
            [
                'nombreEnfermedad' => 'Hipoventilación concurrente relacionada con el sueño debido a una afección médica',
                'DSM5' => '327.26',
                'CEA10' => 'G47.36'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del ritmo circadiano de sueño-vigilia - Tipo fase de sueño retrasada',
                'DSM5' => '307.45',
                'CEA10' => 'G47.21'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del ritmo circadiano de sueño-vigilia - Tipo fase de sueño avanzada',
                'DSM5' => '307.45',
                'CEA10' => 'G47.22'
            ],
            [
                'nombreEnfermedad' => 'Tipo de sueño-vigilia irregular',
                'DSM5' => '307.45',
                'CEA10' => 'G47.23'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del ritmo circadiano de sueño-vigilia - Tipo sueño-vigilia no sincronizado con las 24 h',
                'DSM5' => '307.45',
                'CEA10' => 'G47.24'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del ritmo circadiano de sueño-vigilia - Tipo inducido por trabajo por turnos',
                'DSM5' => '307.45',
                'CEA10' => 'G47.26'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del ritmo circadiano de sueño-vigilia - No especificado',
                'DSM5' => '307.45',
                'CEA10' => 'G47.20'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del despertar del sueño no REM - Tipo confusional',
                'DSM5' => '307.46',
                'CEA10' => 'F51.3'
            ],
            [
                'nombreEnfermedad' => 'Trastornos del despertar del sueño no REM - Tipo sonambulismo',
                'DSM5' => '307.46',
                'CEA10' => 'F51.4'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de pesadillas',
                'DSM5' => '307.47',
                'CEA10' => 'F51.5'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del comportamiento del sueño REM',
                'DSM5' => '327.42',
                'CEA10' => 'G47.52'
            ],
            [
                'nombreEnfermedad' => 'Síndrome de las piernas inquietas',
                'DSM5' => '333.94',
                'CEA10' => 'G25.81'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de insomnio especificado',
                'DSM5' => '780.52',
                'CEA10' => 'G47.09'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de insomnio no especificado',
                'DSM5' => '780.52',
                'CEA10' => 'G47.00'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno de hipersomnia especificado',
                'DSM5' => '780.54',
                'CEA10' => 'G47.19'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de hipersomnia no especificado',
                'DSM5' => '780.54',
                'CEA10' => 'G47.10'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno del sueño-vigilia especificado',
                'DSM5' => '780.59',
                'CEA10' => 'G47.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del sueño-vigilia no especificado',
                'DSM5' => '780.59',
                'CEA10' => 'G47.9'
            ],
            [
                'nombreEnfermedad' => 'Eyaculación retardada',
                'DSM5' => '302.74',
                'CEA10' => 'F52.32'
            ],
            [
                'nombreEnfermedad' => 'Trastorno eréctil',
                'DSM5' => '302.72',
                'CEA10' => 'F52.21'
            ],
            [
                'nombreEnfermedad' => 'Trastorno orgásmico femenino',
                'DSM5' => '302.73',
                'CEA10' => 'F52.21'
            ],
            [
                'nombreEnfermedad' => 'Trastorno del interés/excitación sexual femenino',
                'DSM5' => '302.72',
                'CEA10' => 'F52.22'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de dolor genito-pélvico/penetración',
                'DSM5' => '302.76',
                'CEA10' => 'F52.6'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de deseo sexual hipoactivo en el varón',
                'DSM5' => '302.71',
                'CEA10' => 'F52.0'
            ],
            [
                'nombreEnfermedad' => 'Eyaculación prematura',
                'DSM5' => '302.75',
                'CEA10' => 'F52.4'
            ],
            [
                'nombreEnfermedad' => 'Otra disfunción sexual especificada',
                'DSM5' => '302.79',
                'CEA10' => 'F52.8'
            ],
            [
                'nombreEnfermedad' => 'Disfunción sexual no especificada',
                'DSM5' => '302.70',
                'CEA10' => 'F52.9'
            ],
            [
                'nombreEnfermedad' => 'Disforia de género en niños',
                'DSM5' => '302.60',
                'CEA10' => 'F64.2'
            ],
            [
                'nombreEnfermedad' => 'Disforia de género en adolescentes y adultos',
                'DSM5' => '302.85',
                'CEA10' => 'F64.1'
            ],
            [
                'nombreEnfermedad' => 'Otra disforia de género especificada',
                'DSM5' => '302.60',
                'CEA10' => 'F64.8'
            ],
            [
                'nombreEnfermedad' => 'Disforia de género no especificada',
                'DSM5' => '302.60',
                'CEA10' => 'F64.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno negativista desafiante',
                'DSM5' => '313.81',
                'CEA10' => 'F91.3'
            ],
            [
                'nombreEnfermedad' => 'Trastorno explosivo intermitente',
                'DSM5' => '312.34',
                'CEA10' => 'F63.81'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la conducta - Tipo de inicio infantil',
                'DSM5' => '312.81',
                'CEA10' => 'F91.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la conducta - Tipo de inicio adolescente',
                'DSM5' => '312.82',
                'CEA10' => 'F91.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la conducta - Tipo de inicio no especificado',
                'DSM5' => '312.89',
                'CEA10' => 'F91.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de la personalidad antisocial',
                'DSM5' => '301.70',
                'CEA10' => 'F60.2'
            ],
            [
                'nombreEnfermedad' => 'Piromanía',
                'DSM5' => '312.33',
                'CEA10' => 'F63.1'
            ],
            [
                'nombreEnfermedad' => 'Cleptomanía',
                'DSM5' => '312.32',
                'CEA10' => 'F63.2'
            ],
            [
                'nombreEnfermedad' => 'Otro trastorno destructivo, del control de los impulsos',
                'DSM5' => '312.89',
                'CEA10' => 'F91.8'
            ],
            [
                'nombreEnfermedad' => 'Trastorno destructivo, del control de los impulsos',
                'DSM5' => '312.90',
                'CEA10' => 'F91.9'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de alcohol',
                'DSM5' => '305.00',
                'CEA10' => 'F10.10'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de alcohol',
                'DSM5' => '303.90',
                'CEA10' => 'F10.20'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de alcohol',
                'DSM5' => '303.90',
                'CEA10' => 'F10.20.1'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por alcohol - Con trastorno por consumo',
                'DSM5' => '303.00',
                'CEA10' => 'F10.129'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por alcohol - Con trastorno por consumo',
                'DSM5' => '303.00',
                'CEA10' => 'F10.229'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por alcohol - Sin trastorno por consumo',
                'DSM5' => '303.00',
                'CEA10' => 'F10.929'
            ],
            [
                'nombreEnfermedad' => 'Abstinencia de alcohol - Sin alteraciones de la percepción',
                'DSM5' => '291.81',
                'CEA10' => 'F10.239'
            ],
            [
                'nombreEnfermedad' => 'Abstinencia de alcohol - Con alteraciones de la percepción',
                'DSM5' => '291.81',
                'CEA10' => 'F10.232'
            ],
            [
                'nombreEnfermedad' => 'Trastorno relacionado con el alcohol no especificado',
                'DSM5' => '291.90',
                'CEA10' => 'F10.99'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cafeína',
                'DSM5' => '305.90',
                'CEA10' => 'F15.929'
            ],
            [
                'nombreEnfermedad' => 'Abstinencia de cafeína',
                'DSM5' => '292.00',
                'CEA10' => 'F15.93'
            ],
            [
                'nombreEnfermedad' => 'Trastorno relacionado con la cafeína no especificado',
                'DSM5' => '292.90',
                'CEA10' => 'F15.99'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de cannabis',
                'DSM5' => '305.20',
                'CEA10' => 'F12.10'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de cannabis',
                'DSM5' => '304.30',
                'CEA10' => 'F12.20'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de cannabis',
                'DSM5' => '304.30',
                'CEA10' => 'F12.20.1'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cannabis - Sin alteraciones de la percepción',
                'DSM5' => '292.89',
                'CEA10' => 'F12.129'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cannabis - Sin alteraciones de la percepción',
                'DSM5' => '292.89',
                'CEA10' => 'F12.229'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cannabis - Sin alteraciones de la percepción',
                'DSM5' => '292.89',
                'CEA10' => 'F12.929'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cannabis - Con alteraciones de la percepción',
                'DSM5' => '292.89',
                'CEA10' => 'F12.122'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cannabis - Con alteraciones de la percepción',
                'DSM5' => '292.89',
                'CEA10' => 'F12.222'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por cannabis - Con alteraciones de la percepción',
                'DSM5' => '292.89',
                'CEA10' => 'F12.922'
            ],
            [
                'nombreEnfermedad' => 'Abstinencia de cannabis',
                'DSM5' => '292.00',
                'CEA10' => 'F12.288'
            ],
            [
                'nombreEnfermedad' => 'Trastorno relacionado con el cannabis no especificado',
                'DSM5' => '292.90',
                'CEA10' => 'F12.99'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de fenciclidina',
                'DSM5' => '305.90',
                'CEA10' => 'F16.10'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de fenciclidina',
                'DSM5' => '304.60',
                'CEA10' => 'F16.20'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de fenciclidina',
                'DSM5' => '304.60',
                'CEA10' => 'F16.20.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de otros alucinógenos',
                'DSM5' => '305.30',
                'CEA10' => 'F16.10'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de otros alucinógenos',
                'DSM5' => '304.50',
                'CEA10' => 'F16.20'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por consumo de otros alucinógenos',
                'DSM5' => '304.50',
                'CEA10' => 'F16.20.2'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por fenciclidina - Con trastorno por consumo',
                'DSM5' => '292.89',
                'CEA10' => 'F16.129'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por fenciclidina - Con trastorno por consumo',
                'DSM5' => '292.89',
                'CEA10' => 'F16.229'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por fenciclidina - Sin trastorno por consumo',
                'DSM5' => '292.89',
                'CEA10' => 'F16.929'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por otro alucinógeno - Con trastorno por consumo',
                'DSM5' => '292.89',
                'CEA10' => 'F16.129'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por otro alucinógeno - Con trastorno por consumo',
                'DSM5' => '292.89',
                'CEA10' => 'F16.229'
            ],
            [
                'nombreEnfermedad' => 'Intoxicación por otro alucinógeno - Sin trastorno por consumo',
                'DSM5' => '292.89',
                'CEA10' => 'F16.929'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de percepción persistente por alucinógenos',
                'DSM5' => '292.89',
                'CEA10' => 'F16.983'
            ],
            [
                'nombreEnfermedad' => 'Trastorno relacionado con la fenciclidina no especificado',
                'DSM5' => '292.90',
                'CEA10' => 'F16.99'
            ],
            [
                'nombreEnfermedad' => 'Trastorno relacionado con los alucinógenos no especificado',
                'DSM5' => '292.90',
                'CEA10' => 'F16.99.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de ansiedad generalizada',
                'DSM5' => '300.02',
                'CEA10' => 'F41.1'
            ],
            [
                'nombreEnfermedad' => 'Trastorno obsesivo-compulsivo',
                'DSM5' => '300.3',
                'CEA10' => 'F42'
            ],
            [
                'nombreEnfermedad' => 'Trastorno de estrés postraumático',
                'DSM5' => '309.81',
                'CEA10' => 'F43.1'
            ],
            [
                'nombreEnfermedad' => 'Anorexia nerviosa',
                'DSM5' => '307.1',
                'CEA10' => 'F50.0'
            ],
            [
                'nombreEnfermedad' => 'Bulimia nerviosa',
                'DSM5' => '307.51',
                'CEA10' => 'F50.2'
            ],
            [
                'nombreEnfermedad' => 'Trastorno bipolar',
                'DSM5' => '296.89',
                'CEA10' => 'F31'
            ],
            [
                'nombreEnfermedad' => 'Esquizofrenia',
                'DSM5' => '295.90',
                'CEA10' => 'F20'
            ],
            [
                'nombreEnfermedad' => 'Trastorno límite de la personalidad',
                'DSM5' => '301.83',
                'CEA10' => 'F60.3'
            ],
            [
                'nombreEnfermedad' => 'Trastorno por déficit de atención/hiperactividad',
                'DSM5' => '314.01',
                'CEA10' => 'F90'
            ],
            [
                'nombreEnfermedad' => 'Trastorno disocial de la conducta',
                'DSM5' => '312.81',
                'CEA10' => 'F91'
            ]
            
        ];


        DB::table('enfermedades')->insert($enfermedades);

    }
    
}
