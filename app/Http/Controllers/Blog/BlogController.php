<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PostBlogs\PostBlogs;
use App\Models\Psicologo;
use Illuminate\Support\Str;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\JsonResponse;

class BlogController extends Controller
{
    public function createBlog(PostBlogs $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No tienes permisos para crear un blog')
                    ->send();
            }

            $data = $request->all();
            $data['psicologo_id'] = $psicologo->idPsicologo;
            $data['fecha'] = now();

            Blog::create($data);

            return HttpResponseHelper::make()
                ->successfulResponse('Blog creado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function showAllBlogs(): JsonResponse
    {
        try {
            $blogs = Blog::with('psicologo.users')->orderBy('fecha','desc')->get()->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'tema' => $blog->tema,
                    'slug' => $blog->slug, // Generado dinámicamente
                    'contenido' => Str::limit($blog->descripcion, 150),
                    'imagenes' => [$blog->imagen], // Convertir a array para compatibilidad
                    'imagen' => $blog->imagen,
                    'nombrePsicologo' => $blog->psicologo->users->name . ' ' . $blog->psicologo->users->apellido,
                    'psicologoImagenId' => $blog->psicologo->users->imagen,
                    'categoria' => $blog->especialidad,
                    'fecha_publicado' => $blog->fecha,
                ];
            });

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de blogs obtenida correctamente', $blogs)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener los blogs: ' . $e->getMessage())
                ->send();
        }
    }

    public function BlogAllPreviews(): JsonResponse
    {
        try {
            $blogs = Blog::with([
                'psicologo:idPsicologo,user_id',
                'psicologo.users:user_id,name,apellido,imagen',
            ])->orderBy('fecha','desc')->get();

            $blogs = $blogs->map(fn($blog) => [
                'idBlog' => $blog->id,
                'tema' => $blog->tema,
                'slug' => $blog->slug, // Generado dinámicamente
                'contenido' => $blog->descripcion,
                'imagenes' => [$blog->imagen], // Convertir a array para compatibilidad
                'imagen' => $blog->imagen,
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'categoria' => $blog->especialidad,
                'fecha' => $blog->fecha,
            ]);

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de blogs obtenida correctamente', $blogs)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener los blogs: ' . $e->getMessage())
                ->send();
        }
    }

    public function showbyIdBlog($identifier): JsonResponse
    {
        try {
            // Log detallado para debugging en producción
            \Log::info("=== INICIO BÚSQUEDA BLOG ===");
            \Log::info("Identifier recibido: " . $identifier);
            \Log::info("Tipo de identifier: " . gettype($identifier));
            \Log::info("Es numérico: " . (is_numeric($identifier) ? 'Sí' : 'No'));
            \Log::info("Request URL: " . request()->fullUrl());

            // Intentar buscar por ID numérico primero, luego por tema
            $blog = null;

            if (is_numeric($identifier)) {
                \Log::info("Intentando búsqueda por ID numérico: " . $identifier);
                $blog = Blog::with(['psicologo.users'])->find($identifier);
                \Log::info("Resultado búsqueda por ID: " . ($blog ? "ENCONTRADO (ID: {$blog->id})" : "NO ENCONTRADO"));
                if ($blog) {
                    \Log::info("Tema del blog encontrado por ID: " . $blog->tema);
                }
            }

            // Si no se encuentra por ID, buscar por tema exacto
            if (!$blog) {
                \Log::info("Intentando búsqueda por tema exacto: " . $identifier);
                $blog = Blog::with(['psicologo.users'])->where('tema', $identifier)->first();
                \Log::info("Resultado búsqueda por tema exacto: " . ($blog ? "ENCONTRADO (ID: {$blog->id})" : "NO ENCONTRADO"));
                if ($blog) {
                    \Log::info("Tema del blog encontrado: " . $blog->tema);
                }
            }

            // Si no se encuentra por tema exacto, buscar por tema similar (búsqueda flexible)
            if (!$blog) {
                // Convertir guiones a espacios y decodificar URL para búsqueda más flexible
                $searchTerm = str_replace('-', ' ', urldecode($identifier));
                \Log::info("Intentando búsqueda flexible con término: " . $searchTerm);
                \Log::info("Identifier original: " . $identifier);

                // Verificar cuántos blogs hay en total
                $totalBlogs = Blog::count();
                \Log::info("Total de blogs en base de datos: " . $totalBlogs);

                // Listar algunos temas para debug
                $sampleBlogs = Blog::select('id', 'tema')->limit(5)->get();
                \Log::info("Muestra de temas en BD:");
                foreach ($sampleBlogs as $sample) {
                    \Log::info("  - ID {$sample->id}: {$sample->tema}");
                }

                // También intentar con búsqueda case-insensitive mejorada
                $blog = Blog::with(['psicologo.users'])
                    ->where(function ($query) use ($searchTerm, $identifier) {
                        $query->where('tema', 'LIKE', '%' . $searchTerm . '%')
                              ->orWhere('tema', 'LIKE', '%' . $identifier . '%');
                    })
                    ->first();

                \Log::info("Resultado búsqueda flexible: " . ($blog ? "ENCONTRADO (ID: {$blog->id})" : "NO ENCONTRADO"));
                if ($blog) {
                    \Log::info("Tema del blog encontrado con búsqueda flexible: " . $blog->tema);
                }

                // Si aún no se encuentra, intentar búsqueda más agresiva
                if (!$blog) {
                    \Log::info("Intentando búsqueda MUY flexible - case insensitive");
                    $blog = Blog::with(['psicologo.users'])
                        ->whereRaw('LOWER(tema) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                        ->first();
                    \Log::info("Resultado búsqueda MUY flexible: " . ($blog ? "ENCONTRADO (ID: {$blog->id})" : "NO ENCONTRADO"));
                }
            }

            if (!$blog) {
                \Log::error("=== BLOG NO ENCONTRADO ===");
                \Log::error("Identifier que falló: " . $identifier);
                \Log::error("Todos los intentos de búsqueda fallaron");

                // Intentar buscar algo similar para debug
                $similarBlogs = Blog::where('tema', 'LIKE', '%' . substr($identifier, 0, 10) . '%')->limit(3)->get(['id', 'tema']);
                if ($similarBlogs->count() > 0) {
                    \Log::info("Blogs similares encontrados:");
                    foreach ($similarBlogs as $similar) {
                        \Log::info("  - ID {$similar->id}: {$similar->tema}");
                    }
                } else {
                    \Log::info("No se encontraron blogs similares");
                }

                return HttpResponseHelper::make()
                    ->notFoundResponse('El blog no fue encontrado')
                    ->send();
            }

            \Log::info("=== BLOG ENCONTRADO EXITOSAMENTE ===");
            \Log::info("Blog ID: " . $blog->id);
            \Log::info("Blog tema: " . $blog->tema);
            \Log::info("Blog psicologo_id: " . $blog->psicologo_id);

            $responseData = [
                'id' => $blog->id,
                'tema' => $blog->tema,
                'slug' => $blog->slug,
                'contenido' => $blog->descripcion,
                'imagenes' => [$blog->imagen], // Convertir a array para compatibilidad
                'imagen' => $blog->imagen,
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'idCategoria'=> $blog->especialidad,
                'idPsicologo' => $blog->psicologo_id,
                'categoria' => $blog->especialidad,
                'fecha' => $blog->fecha,
            ];

            \Log::info("Datos de respuesta preparados correctamente");
            return HttpResponseHelper::make()
                ->successfulResponse('Blog obtenido correctamente', $responseData)
                ->send();
        } catch (\Exception $e) {
            \Log::error("=== ERROR EN SHOWBYIDBLOG ===");
            \Log::error("Identifier: " . $identifier);
            \Log::error("Error: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());

            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener el blog: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Método optimizado para obtener blog solo por ID (para la nueva estructura de URL)
     */
    public function showByIdOnly(int $id): JsonResponse
    {
        try {
            $blog = Blog::with(['categoria', 'psicologo.users'])->find($id);

            if (!$blog) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('El blog no fue encontrado')
                    ->send();
            }

            $responseData = [
                'idBlog' => $blog->idBlog,
                'tema' => $blog->tema,
                'slug' => $blog->slug,
                'contenido' => $blog->contenido,
                'imagenes' => $blog->imagenes, // Array de imágenes
                'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'idCategoria'=> $blog->categoria->idCategoria,
                'idPsicologo' => $blog->idPsicologo,
                'categoria' =>  $blog->categoria->nombre,
                'fecha' => $blog->fecha_publicado,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Blog obtenido correctamente', $responseData)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener el blog: ' . $e->getMessage())
                ->send();
        }
    }

    public function showAllAuthors(): JsonResponse
    {
        try {
            $authors = Blog::with('psicologo.users')
                ->get()
                ->map(function ($blog) {
                    return [
                        'id' => $blog->psicologo->idPsicologo,
                        'name' => $blog->psicologo?->users?->name,
                        'lastname' => $blog->psicologo?->users?->apellido,
                        'photo' => $blog->psicologo?->users?->imagen,
                    ];
                })

                ->unique('name')
                ->values();

            return HttpResponseHelper::make()
                ->successfulResponse('Autores Publicados blogs', $authors)
                ->send();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al obtener autores: ' . $th->getMessage()], 500);
        }
    }

    public function updateBlog(PostBlogs $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $currentPsicologo = Psicologo::where('user_id', $userId)->first();

            if (!$currentPsicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No tienes permisos para actualizar blogs')
                    ->send();
            }

            $blog = Blog::findOrFail($id);

            // Verificar que el blog pertenece al psicólogo actual
            if ($blog->idPsicologo !== $currentPsicologo->idPsicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No puedes editar blogs que no te pertenecen')
                    ->send();
            }

            $blog->update($request->all());

            return HttpResponseHelper::make()
                ->successfulResponse('Blog actualizado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al actualizar el blog: ' . $e->getMessage())
                ->send();
        }
    }

    public function destroyBlog(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $currentPsicologo = Psicologo::where('user_id', $userId)->first();

            if (!$currentPsicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No tienes permisos para eliminar blogs')
                    ->send();
            }

            $blog = Blog::findOrFail($id);

            // Verificar que el blog pertenece al psicólogo actual
            if ($blog->idPsicologo !== $currentPsicologo->idPsicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No puedes eliminar blogs que no te pertenecen')
                    ->send();
            }

            $blog->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Blog eliminado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar el blog: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Obtener blogs específicos de un psicólogo
     */
    public function showBlogsByPsicologo($idPsicologo)
    {
        try {
            $userId = Auth::id();
            $currentPsicologo = Psicologo::where('user_id', $userId)->first();

            // Verificar que el psicólogo autenticado solo pueda ver sus propios blogs
            if (!$currentPsicologo || $currentPsicologo->idPsicologo != $idPsicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No tienes permisos para ver estos blogs')
                    ->send();
            }

            $blogs = Blog::with('categoria', 'psicologo.users')
                ->where('idPsicologo', $idPsicologo)
                ->orderBy('fecha_publicado', 'desc')
                ->get()
                ->map(function ($blog) {
                    return [
                        'idBlog' => $blog->idBlog,
                        'tema' => $blog->tema,
                        'slug' => $blog->slug,
                        'contenido' => Str::limit($blog->contenido, 150),
                        'imagenes' => $blog->imagenes, // Array de imágenes
                        'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                        'categoria' => $blog->categoria->nombre,
                        'idPsicologo' => $blog->idPsicologo,
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Blogs del psicólogo obtenidos correctamente', $blogs)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener los blogs: ' . $e->getMessage())
                ->send();
        }
    }
}
