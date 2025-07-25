<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Premio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Subir imagen para un premio
     */
    public function uploadPremioImage(Request $request, $premioId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tipo' => 'sometimes|in:principal,galeria',
            'alt' => 'sometimes|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $premio = Premio::findOrFail($premioId);
            
            $file = $request->file('image');
            $tipo = $request->input('tipo', 'galeria');
            $alt = $request->input('alt', $premio->titulo);
            
            // Generar nombre único para el archivo
            $filename = 'premio_' . $premio->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Guardar archivo
            $path = $file->storeAs('premios', $filename, 'public');
            $url = '/storage/' . $path;
            
            if ($tipo === 'principal') {
                // Actualizar imagen principal
                $premio->imagen_principal = $url;
                $premio->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen principal actualizada correctamente',
                    'data' => [
                        'url' => $url,
                        'tipo' => 'principal',
                        'alt' => $alt
                    ]
                ]);
            } else {
                // Agregar a galería
                $premio->agregarImagenGaleria($url, 'imagen', $alt);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen agregada a la galería',
                    'data' => [
                        'url' => $url,
                        'tipo' => 'galeria',
                        'alt' => $alt,
                        'galeria_completa' => $premio->fresh()->media_gallery
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar imagen de la galería de un premio
     */
    public function deletePremioImage(Request $request, $premioId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'indice' => 'required|integer|min:0',
            'tipo' => 'sometimes|in:principal,galeria'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $premio = Premio::findOrFail($premioId);
            $indice = $request->input('indice');
            $tipo = $request->input('tipo', 'galeria');
            
            if ($tipo === 'principal') {
                $premio->imagen_principal = null;
                $premio->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen principal eliminada'
                ]);
            } else {
                $premio->eliminarImagenGaleria($indice);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen eliminada de la galería',
                    'data' => [
                        'galeria_completa' => $premio->fresh()->media_gallery
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las imágenes de un premio
     */
    public function getPremioImages($premioId): JsonResponse
    {
        try {
            $premio = Premio::findOrFail($premioId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'imagen_principal' => $premio->imagen_principal,
                    'galeria' => $premio->media_gallery,
                    'todas_las_imagenes' => $premio->todas_las_imagenes
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las imágenes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordenar imágenes de la galería
     */
    public function reorderPremioImages(Request $request, $premioId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orden' => 'required|array',
            'orden.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $premio = Premio::findOrFail($premioId);
            $nuevoOrden = $request->input('orden');
            $gallery = $premio->media_gallery ?? [];
            
            $nuevaGaleria = [];
            foreach ($nuevoOrden as $indice) {
                if (isset($gallery[$indice])) {
                    $nuevaGaleria[] = $gallery[$indice];
                }
            }
            
            $premio->media_gallery = $nuevaGaleria;
            $premio->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Orden de imágenes actualizado',
                'data' => [
                    'galeria_completa' => $premio->fresh()->media_gallery
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar las imágenes: ' . $e->getMessage()
            ], 500);
        }
    }
}
