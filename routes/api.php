<?php

use App\Http\Controllers\Atencion\AtencionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Contactos\ContactosController;
use App\Http\Controllers\Psicologos\PsicologosController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Citas\CitaController;
use App\Http\Controllers\Comentarios\ComentarioController;
use App\Http\Controllers\Especialidad\EspecialidadController;
use App\Http\Controllers\Categoria\CategoriaController;
use App\Http\Controllers\Enfermedad\EnfermedadController;
use App\Http\Controllers\Pacientes\PacienteController;
use App\Http\Controllers\Prepaciente\PrePacienteController;
use App\Http\Controllers\RespuestasBlog\RespuestaComentarioController;
use App\Http\Controllers\RegistroFamiliar\RegistroFamiliarController;
use App\Http\Controllers\DashboardController;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

Route::controller(ContactosController::class)->prefix('contactos')->group(function () {
    Route::post('/create', 'createContact')->middleware('throttle:100,1');

    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN']], function () {
        Route::get('/show', 'showAllContact');
    });
});

Route::controller(PacienteController::class)->prefix('pacientes')->group(function () {
    Route::group(['middleware' => ['auth:sanctum', 'role:PSICOLOGO']], function () {
        Route::post('/', 'createPaciente');
        Route::get('/{id}', 'showPacienteById');
        Route::get('/', 'showPacientesByPsicologo');
        Route::put('/{id}', 'updatePaciente');
        Route::delete('/{id}', 'destroyPaciente');
        Route::get('/citas/{id}', 'getCitasPaciente');
        Route::get('/estadisticas/genero', 'getPacientesGenero');
        Route::get('/estadisticas/edad', 'getPacientesEdad');
        Route::get('/estadisticas/lugar', 'getPacientesLugar');
    });
});

Route::controller(PsicologosController::class)->prefix('psicologos')->group(function () {
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN']], function () {
        Route::get('/dashboard', 'psicologoDashboard');
        Route::post('/', 'createPsicologo');
        Route::put('/{id}', 'updatePsicologo');
        Route::delete('/{id}', 'DeletePsicologo');
    });
    Route::get('/', 'showAllPsicologos');
    Route::get('/{id}', 'showById');
});

Route::controller(BlogController::class)->prefix('blogs')->group(function () {
    Route::get('/authors', 'showAllAuthors');
    Route::get('/{id}', 'showbyIdBlog');
    Route::get('/all', 'showAllBlogs');
    Route::get('/', 'BlogAllPreviews');
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN|PSICOLOGO']], function () {
        Route::post('/', 'createBlog');
        Route::put('/{id}', 'updateBlog');
        Route::delete('/{id}', 'destroyBlog');
    });
});

Route::controller(ComentarioController::class)->prefix('comentarios')->group(function () {
    Route::post('/{id}', 'createComentario');
    Route::get('/{id}', 'showComentariosByBlog');
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN|PSICOLOGO']], function () {
        Route::delete('/{id}', 'destroyComentario');
    });
});

Route::controller(EspecialidadController::class)->prefix('especialidades')->group(function () {
    Route::get('/', 'showAll');
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN']], function () {
        Route::post('/', 'createEspecialidad');
        Route::put('/{id}', 'updateEspecialidad');
        Route::delete('/{id}', 'destroyEspecialidad');
    });
});

Route::controller(CategoriaController::class)->prefix('categorias')->group(function () {
    Route::get('/', 'showAll');
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN|PSICOLOGO']], function () {
        Route::post('/', 'createCategoria');
    });
});

Route::controller(CitaController::class)->prefix('citas')->group(function () {
    Route::get('/pendientes/{id}', 'showCitasPendientes');
    Route::get('/estadisticas', 'getCitasPorEstado');
    Route::get('/periodo', 'getCitasPorPeriodo');
    Route::group(['middleware' => ['auth:sanctum', 'role:PSICOLOGO']], function () {
        Route::get('/', 'showAllCitasByPsicologo');
        Route::get('/dashboard/psicologo', 'psicologoDashboard');
        Route::post('/', 'createCita');
        Route::get('/{id}', 'showCitaById');
        Route::put('/{id}', 'updateCita');
        Route::delete('/{id}', 'destroyCita');
    });
});

Route::controller(RespuestaComentarioController::class)->prefix('respuestas')->group(function () {
    Route::post('/', 'createRespuesta');
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN|PSICOLOGO']], function () {
        Route::get('/{id}', 'showRespuestasByComentario');
        Route::delete('/{id}', 'destroyRespuesta');
    });
});

Route::controller(AtencionController::class)->prefix('atenciones')->group(function () {
    Route::group(['middleware' => ['auth:sanctum', 'role:PSICOLOGO']], function () {
        Route::get('/ultima/paciente/{id}', 'showAtencionByPaciente');
        Route::get('/paciente/{id}', 'showAllAtencionesPaciente');
        Route::get('/', 'showAllAtenciones');
        Route::post('/{idCita}', 'createAtencion');
        Route::put('/{id}', 'updateAtencion');
        Route::delete('/{id}', 'destroyAtencion');
        Route::get('/{id}', 'showAtencion');
    });
});

Route::controller(RegistroFamiliarController::class)->prefix('registros')->group(function () {
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN|PSICOLOGO']], function () {
        Route::post('/{id}', 'createRegistro');
        Route::get('/{id}', 'showRegistro');
        Route::put('/{id}', 'updateRegistro');
        Route::delete('/{id}', 'destroyRegistro');
    });
});

Route::controller(EnfermedadController::class)->prefix('enfermedades')->group(function () {
    Route::get('/', 'showAll');
});

Route::controller(PrePacienteController::class)->prefix('pre-pacientes')->group(function () {
    Route::post('/', 'createPrePaciente');
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN|PSICOLOGO']], function () {
        Route::get('/{id}', 'showPrePaciente');
        Route::put('/{id}', 'updatePrePaciente');
        Route::delete('/{id}', 'destroyPrePaciente');
    });
});




