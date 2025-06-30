<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PostBlogs\PostBlogs;
use App\Models\Psicologo;
use Illuminate\Support\Str;
use App\Traits\HttpResponseHelper;

class BlogController extends Controller
{
    public function createBlog(PostBlogs $request)
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
            $data['idPsicologo'] = $psicologo->idPsicologo;

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

    public function showAllBlogs()
    {
        try {
            $blogs = Blog::with('categoria', 'psicologo.users')->get()->map(function ($blog) {
                return [
                    'id' => $blog->idBlog,
                    'tema' => $blog->tema,
                    'contenido' => Str::limit($blog->contenido, 150),
                    'imagenes' => $blog->imagenes, // Array de imágenes
                    'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                    'nombrePsicologo' => $blog->psicologo->users->name . ' ' . $blog->psicologo->users->apellido,
                    'psicologoImagenId' => $blog->psicologo->users->imagen,
                    'categoria' =>  $blog->categoria->nombre,
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


    public function BlogAllPreviews()
    {
        try {
            $blogs = Blog::with([
                'categoria:idCategoria,nombre',
                'psicologo:idPsicologo,user_id',
                'psicologo.users:user_id,name,apellido,imagen',
            ])->get();

            $blogs = $blogs->map(fn($blog) => [
                'idBlog' => $blog->idBlog,
                'tema' => $blog->tema,
                'contenido' => $blog->contenido,
                'imagenes' => $blog->imagenes, // Array de imágenes
                'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'categoria' =>  $blog->categoria->nombre,
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

    public function showbyIdBlog($id)
    {
        try {
            $blog = Blog::with(['categoria', 'psicologo.users'])->find($id); // Eliminamos el `get()`

            if (!$blog) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('El blog no fue encontrado')
                    ->send();
            }
            $responseData = [
                'id' => $blog->idBlog,
                'tema' => $blog->tema,
                'contenido' => $blog->contenido,
                'imagenes' => $blog->imagenes, // Array de imágenes
                'imagen' => $blog->imagenes[0] ?? null, // Primera imagen para compatibilidad
                'psicologo' => $blog->psicologo?->users?->name,
                'psicologApellido' => $blog->psicologo?->users?->apellido,
                'psicologoImagenId' => $blog->psicologo?->users->imagen,
                'idCategoria'=> $blog->categoria->idCategoria,
                'idPsicologo' => $blog->idPsicologo, // Agregamos el ID del psicólogo
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

    public function showAllAuthors()
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

    public function updateBlog(PostBlogs $request, int $id)
    {
        try {
            $blog = Blog::findOrFail($id);
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

    public function destroyBlog(int $id)
    {
        try {
            $blog = Blog::findOrFail($id);
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
}
