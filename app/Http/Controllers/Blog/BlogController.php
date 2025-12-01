<?php

namespace App\Http\Controllers\Blog;


use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogMetadata;
use App\Models\BlogImages;
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
        \Log::info('Datos recibidos:', $request->all());
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No tienes permisos para crear un blog')
                    ->send();
            }

            $data = $request->all();
            $data['idPsicologo'] = $psicologo->idPsicologo;
            $data['fecha_publicado'] = now();

            $blog = Blog::create($data);

            // ✅ Guardar metadata si viene en el request
            if ($request->has(['metaTitle', 'metaDescription', 'keywords'])) {
                BlogMetadata::create([
                    'blog_id' => $blog->idBlog,
                    'metaTitle' => $request->input('metaTitle'),
                    'metaDescription' => $request->input('metaDescription'),
                    'keywords' => $request->input('keywords'),
                ]);
            }


            // ✅ Guardar las imágenes asociadas (si hay)
            if ($request->has('imagenesMeta')) {
                foreach ($request->imagenesMeta as $img) {
                    BlogImages::create([
                        'blog_id' => $blog->idBlog,
                        'src' => $img['url'],          // ⚡ Usar 'url' en vez de 'src'
                        'title' => $img['title'] ?? null,
                        'alt' => $img['altText'] ?? null,
                    ]);
                }
            }



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
            $blogs = Blog::with(['psicologo.users', 'categoria'])->orderBy('fecha_publicado', 'desc')->get()->map(function ($blog) {
                return [
                    'id' => $blog->idBlog,
                    'tema' => $blog->tema,
                    'slug' => $blog->slug,
                    'contenido' => Str::limit($blog->contenido, 150),
                    'imagenes' => $blog->imagenes, // Array de imágenes
                    'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                    'nombrePsicologo' => $blog->psicologo->users->name . ' ' . $blog->psicologo->users->apellido,
                    'psicologoImagenId' => $blog->psicologo->users->imagen,
                    'categoria' => $blog->categoria?->nombre,
                    'fecha_publicado' => $blog->fecha_publicado,
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
                'categoria:idCategoria,nombre'
            ])->orderBy('fecha_publicado', 'desc')->get();

            $blogs = $blogs->map(fn($blog) => [
                'idBlog' => $blog->idBlog,
                'tema' => $blog->tema,
                'slug' => $blog->slug,
                'contenido' => $blog->contenido,
                'imagenes' => $blog->imagenes, // Array de imágenes
                'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'categoria' => $blog->categoria?->nombre,
                'fecha' => $blog->fecha_publicado,
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
            // Si el identificador es numérico, buscar por ID
            if (is_numeric($identifier)) {
                $blog = Blog::with(['categoria', 'psicologo.users'])
                    ->find($identifier);
            } else {
                $decoded = urldecode($identifier);
                $searchTerm = str_replace('-', ' ', $decoded);


                // Si no es numérico, buscar por tema/slug
                $blog = Blog::with(['categoria', 'psicologo.users', 'metadata'])
                    ->where('slug', $decoded) // ← buscar primero por slug exacto
                    ->orWhere('tema', $searchTerm) // ← o tema igual
                    ->orWhere('tema', 'LIKE', '%' . $searchTerm . '%') // ← o tema parecido
                    ->first();
            }

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
                'imagenes' => $blog->imagenes,
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'idCategoria' => $blog->idCategoria,
                'idPsicologo' => $blog->idPsicologo,
                'categoria' => $blog->categoria?->nombre,
                'fecha' => $blog->fecha_publicado,

            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Blog obtenido correctamente', $responseData)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener el blog')
                ->send();
        }
    }

    /**
     * Método optimizado para obtener blog solo por ID (para la nueva estructura de URL)
     */
    public function showByIdOnly(int $id): JsonResponse
    {
        try {
            $blog = Blog::with(['categoria', 'psicologo.users', 'metadata', 'images'])->findOrFail($id);

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
                'idCategoria' => $blog->categoria->idCategoria,
                'idPsicologo' => $blog->idPsicologo,
                'categoria' => $blog->categoria->nombre,
                'fecha' => $blog->fecha_publicado,

                'metaTitle' => $blog->metadata?->metaTitle,
                'metaDescription' => $blog->metadata?->metaDescription,
                'keywords' => $blog->metadata?->keywords,

                // (Opcional) si quieres devolver también las imágenes con title/alt:
                'imagenesMeta' => $blog->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => $img->src,
                        'title' => $img->title,
                        'altText' => $img->alt,
                    ];
                }),

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

            $blog = Blog::with(['metadata', 'images'])->findOrFail($id);

            // Verificar que el blog pertenece al psicólogo actual
            if ($blog->idPsicologo !== $currentPsicologo->idPsicologo) {
                return HttpResponseHelper::make()
                    ->forbiddenResponse('No puedes editar blogs que no te pertenecen')
                    ->send();
            }

            \Log::info('Payload actualización de blog:', $request->all());

            // ===============================
            // 1) ACTUALIZAR SOLO CAMPOS DEL BLOG
            // ===============================
            $blog->update([
                'idCategoria' => $request->input('idCategoria', $blog->idCategoria),
                'tema' => $request->input('tema', $blog->tema),
                'contenido' => $request->input('contenido', $blog->contenido),
                'imagenes' => $request->input('imagenes', $blog->imagenes),
                // 'idPsicologo' NO se toca aquí, se mantiene
            ]);

            // ===============================
            // 2) ACTUALIZAR METADATA SEO
            // ===============================
            if ($request->hasAny(['metaTitle', 'metaDescription', 'keywords'])) {
                $metadata = $blog->metadata;

                if (!$metadata) {
                    $metadata = new BlogMetadata();
                    $metadata->blog_id = $blog->idBlog;
                }

                $metadata->metaTitle = $request->input('metaTitle');
                $metadata->metaDescription = $request->input('metaDescription');
                $metadata->keywords = $request->input('keywords');
                $metadata->save();
            }

            // ===============================
            // 3) ACTUALIZAR IMÁGENES RELACIONADAS
            // ===============================
            if ($request->has('imagenesMeta')) {
                $imagenesMeta = $request->input('imagenesMeta', []);

                // Borrar imágenes anteriores asociadas a este blog
                BlogImages::where('blog_id', $blog->idBlog)->delete();

                // Crear las nuevas
                foreach ($imagenesMeta as $img) {
                    BlogImages::create([
                        'blog_id' => $blog->idBlog,
                        'src' => $img['url'] ?? null,
                        'title' => $img['title'] ?? null,
                        'alt' => $img['altText'] ?? null,
                    ]);
                }
            }

            // Volvemos a cargar las relaciones actualizadas
            $blog->load(['metadata', 'images']);

            return HttpResponseHelper::make()
                ->successfulResponse('Blog actualizado correctamente')
                ->send();

        } catch (\Exception $e) {
            \Log::error('Error al actualizar blog:', ['error' => $e->getMessage()]);
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
