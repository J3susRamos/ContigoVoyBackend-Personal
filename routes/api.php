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
use App\Http\Controllers\Estadisticas\EstadisticasController;
use App\Http\Controllers\Marketing\MarketingController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\Boucher\BoucherController;
use App\Http\Controllers\Personal\PersonalController;
use App\Http\Controllers\Disponibilidad\DisponibilidadController;

Route::controller(AuthController::class)
    ->prefix("auth")
    ->group(function () {
        Route::post("/login", "login")->name("login");
        Route::post("/logout", "logout")->middleware("auth:sanctum");
    });

Route::controller(ContactosController::class)
    ->prefix("contactos")
    ->group(function () {
        Route::post("/create", "createContact")->middleware("throttle:100,1");

        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::get("/show", "showAllContact");
            },
        );
    });

Route::controller(PersonalController::class)
    ->prefix("personal")
    ->group(function () {
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::post("/", "createPersonal");
                Route::get("/permisos/{user_id}", "getPersonalWithPermissions");
            },
        );
    });

Route::controller(PacienteController::class)
    ->prefix("pacientes")
    ->group(function () {
        Route::post("/enviar-codigo", "resetPassword");
        Route::post("/verificar-codigo", "verificarCodigo");

    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN']], function () {
        Route::put('/activar/{id}', 'enablePatient'); // Nueva ruta para activar paciente y vincular con psicologo
        Route::get('/deshabilitados', 'showEnablePaciente'); // Listar pacientes inactivos para el ADMIN
        Route::get('/habilitados', 'showPacientesHabilitados'); // NUEVA RUTA para pacientes habilitados
    });
    //Endpoint para que sandro se consuma
    Route::get('/todos', 'getAllPacientes');
    Route::group(['middleware' => ['auth:sanctum', 'role:PSICOLOGO']], function () {
        Route::post('/{idCita?}', 'createPaciente');
        Route::get('/{id}', 'showPacienteById');
        Route::get('/', 'showPacientesByPsicologo'); // Listar pacientes activos por psicólogo
        Route::put('/{id}', 'updatePaciente');
        Route::put('/desactivar/{id}', 'disablePatient'); // Nueva ruta para desactivar paciente y desvincular paciente con psicologo
        Route::delete('/{id}', 'destroyPaciente');
        Route::get('/citas/{id}', 'getCitasPaciente');
        Route::get('/estadisticas/genero', 'getPacientesGenero');
        Route::get('/estadisticas/edad', 'getPacientesEdad');
        Route::get('/estadisticas/lugar', 'getPacientesLugar');
    });
});

Route::controller(PsicologosController::class)
    ->prefix("psicologos")
    ->group(function () {
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::get("/dashboard", "psicologoDashboard");
                Route::post("/", "createPsicologo");
                Route::put("/{id}", "updatePsicologo");
                Route::delete("/{id}", "DeletePsicologo");
                Route::put("/estado/{id}", "cambiarEstadoPsicologo"); // Cambiar estado del psicólogo A = Activo, I = Inactivo
                Route::get("/inactivo", "showInactivePsicologos"); // Nueva ruta para listar psicólogos inactivos
                Route::get("/nombre", "listarNombre"); // listar nombre, apellido y idPsicologo de psicologos activos
            },
        );
        Route::put("/update/{id}", "actualizarPsicologo");
        Route::get("/especialidades/{id}", "obtenerEspecialidades");
        Route::get("/", "showAllPsicologos"); // listar psicologos activos
        Route::get("/{id}", "showById");
    });

Route::controller(BlogController::class)
    ->prefix("blogs")
    ->group(function () {
        // Rutas específicas primero
        Route::get("/authors", "showAllAuthors");
        Route::get("/all", "showAllBlogs");
        Route::get("/", "BlogAllPreviews");
        Route::get("/id/{id}", "showByIdOnly")->where("id", "[0-9]+"); // Nueva ruta específica para IDs
        Route::get("/tema/{tema}", "showbyIdBlog"); // Nueva ruta específica para búsqueda por tema
        // Ruta general al final
        Route::get("/{identifier}", "showbyIdBlog"); // Acepta tanto ID como slug

        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PSICOLOGO|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::get("/psicologo/{idPsicologo}", "showBlogsByPsicologo"); // Nueva ruta
                Route::post("/", "createBlog");
                Route::put("/{id}", "updateBlog");
                Route::delete("/{id}", "destroyBlog");
            },
        );
    });

Route::controller(ComentarioController::class)
    ->prefix("comentarios")
    ->group(function () {
        Route::post("/{id}", "createComentario");
        Route::get("/{id}", "showComentariosByBlog");
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PSICOLOGO|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::delete("/{id}", "destroyComentario");
            },
        );
    });

Route::controller(EspecialidadController::class)
    ->prefix("especialidades")
    ->group(function () {
        Route::get("/", "showAll");
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::post("/", "createEspecialidad");
                Route::put("/{id}", "updateEspecialidad");
                Route::delete("/{id}", "destroyEspecialidad");
            },
        );
    });

Route::controller(CategoriaController::class)
    ->prefix("categorias")
    ->group(function () {
        Route::get("/", "showAll");
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PSICOLOGO|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::post("/", "createCategoria");
            },
        );
    });

Route::controller(CitaController::class)->prefix('citas')->group(function () {


    Route::get('/pendientes/{id}', 'showCitasPendientes');
    Route::get('/estadisticas', 'getCitasPorEstado');
    Route::get('/periodo', 'getCitasPorPeriodo');
    Route::get('/listar-canceladas', 'listarCitasCanceladas');
    Route::post('/cancelar-citas', 'cancelarCitasVencidas');
    Route::group(['middleware' => ['auth:sanctum', 'role:PSICOLOGO|PACIENTE']], function () {
        Route::get('/enlaces','listarCitasPaciente');
        Route::get('/paciente/{id}','getCitaVouchers');
        Route::get('/contador','estadisticas');//contador de estados por citas
        Route::post('/reprogramar/{idCita}',"citaReprogramada");
    });
    Route::group(['middleware' => ['auth:sanctum', 'role:ADMIN']], function () {
        Route::post('/habilitar-boucher', 'aceptarBoucher');// ACEPTAR BOUCHER Y GENERAR VIDEOLLAMADA
        Route::post('/rechazar', 'rechazarBoucher');
        Route::get('/sin-pagar', 'listunpaid'); // Nueva ruta para listar citas sin pagar
        Route::get('/pagadas','listpaid');
    });
    Route::group(['middleware' => ['auth:sanctum', 'role:PSICOLOGO']], function () {
        Route::get('/periodosmensuales', 'getCitasPorPeriodoPsicologo');
        Route::get('/dashboard/psicologo', 'psicologoDashboard');
        Route::get('/lista', 'showAllCitasByPsicologo');
        Route::post('/', 'createCita');
        Route::get('/{id}', 'showCitaById');
        Route::put('/{id}', 'updateCita');
        Route::delete('/{id}', 'destroyCita');
        Route::post('/realizada', 'citaRealizada');
    });

});

Route::controller(RespuestaComentarioController::class)
    ->prefix("respuestas")
    ->group(function () {
        Route::post("/", "createRespuesta");
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PSICOLOGO|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::get("/{id}", "showRespuestasByComentario");
                Route::delete("/{id}", "destroyRespuesta");
            },
        );
    });

Route::controller(AtencionController::class)
    ->prefix("atenciones")
    ->group(function () {
        Route::group(
            ["middleware" => ["auth:sanctum", "role:PSICOLOGO"]],
            function () {
                Route::get("/ultima/paciente/{id}", "showAtencionByPaciente");
                Route::get("/paciente/{id}", "showAllAtencionesPaciente");
                Route::get("/", "showAllAtenciones");
                Route::post("/{idCita}", "createAtencion");
                Route::put("/{id}", "updateAtencion");
                Route::delete("/{id}", "destroyAtencion");
                Route::get("/{id}", "showAtencion");
            },
        );
    });

Route::controller(RegistroFamiliarController::class)
    ->prefix("registros")
    ->group(function () {
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PSICOLOGO|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::post("/{id}", "createRegistro");
                Route::get("/{id}", "showRegistro");
                Route::put("/{id}", "updateRegistro");
                Route::delete("/{id}", "destroyRegistro");
            },
        );
    });

Route::controller(EnfermedadController::class)
    ->prefix("enfermedades")
    ->group(function () {
        Route::get("/", "showAll");
    });

Route::controller(PrePacienteController::class)
    ->prefix("pre-pacientes")
    ->group(function () {
        Route::post("/", "createPrePaciente");
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PSICOLOGO"]],
            function () {
                Route::get("/{id}", "showPrePaciente");
                Route::put("/{id}", "updatePrePaciente");
                Route::delete("/{id}", "destroyPrePaciente");
            },
        );
    });

Route::controller(EstadisticasController::class)
    ->prefix("estadisticas")
    ->middleware(["auth:sanctum", "role:PSICOLOGO"])
    ->group(function () {
        Route::get("/", "statistics");
        Route::get("/porcentaje-genero", "porcentajePacientesPorGenero");
    });

Route::controller(MarketingController::class)
    ->prefix("marketing")
    ->middleware(["auth:sanctum", "role:PSICOLOGO|ADMIN|ADMINISTRADOR|MARKETING|COMUNICACION"])
    ->group(function () {
        Route::post("/", "crearPlantilla");
        Route::get("/", "listarPorPsicologo");
        Route::get("/{id}", "detallePlantilla");
        Route::put("/{id}", "actualizarPlantilla");
        Route::delete("/{id}", "eliminarPlantilla");
        Route::post("/enviar", "enviarEmail");
        Route::get("/pacientes-emails", "listarEmailsPacientes");
    });

// WhatsApp routes
Route::prefix("whatsapp")->group(function () {
    // Enviar mensajes
    Route::post("send-confirmation", [
        WhatsAppController::class,
        "sendAppointmentConfirmation",
    ]);
    Route::post("send-reminder", [
        WhatsAppController::class,
        "sendAppointmentReminder",
    ]);
    Route::post("send-interactive", [
        WhatsAppController::class,
        "sendInteractiveMessage",
    ]);

    // Webhook para recibir mensajes
    Route::match(["get", "post"], "webhook", [
        WhatsAppController::class,
        "webhook",
    ]);

    // Estado del servicio
    Route::get("status", [WhatsAppController::class, "status"]);
});

Route::controller(BoucherController::class)
    ->prefix("boucher")
    ->group(function () {
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|PACIENTE|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::post("/enviar", "enviarBoucher");
                Route::get("/pendientes-aceptadas", "getBouchers"); // lista citas pendientes y aceptadas del paciente filtrar por estado, por rango de fechas y por id de cita
                Route::get("/citas-sin-pagar", "sinPagar"); // El paciente autenticado que quiere ver sus propias citas sin pagar
                Route::get("/todas", "todasPendientes");
                Route::post("/rechazar", "cancelarBoucher");
            },
        );
    });

Route::controller(DisponibilidadController::class)
    ->prefix("disponibilidad")
    ->group(function () {
        Route::group(
            ["middleware" => ["auth:sanctum", "role:ADMIN|ADMINISTRADOR|MARKETING|COMUNICACION"]],
            function () {
                Route::get("/listar", "listar"); // filtrar fecha inicio y fecha fin
            },
        );
        Route::group(
            ["middleware" => ["auth:sanctum", "role:PSICOLOGO"]],
            function () {
                Route::post("/crear", "crearDisponibilidad");
                Route::get("/listar-psicologo", "listarPsicologo");
                Route::delete("/eliminar", "eliminarDisponibilidad");
                Route::put("/editar", "editarDisponibilidad");
                Route::get("/last-next-7-days", "lastAndNext7days"); // 👉nuevo ruta traer horarios ultima semana
            },
        );
    });
