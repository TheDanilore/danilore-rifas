<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\Premio;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Subir imagen para una rifa
     */
    public function uploadRifaImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
            $file = $request->file('image');
            $alt = $request->input('alt', 'Imagen de rifa');
            
            // Generar nombre único para el archivo
            $filename = 'rifa_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Guardar archivo
            $path = $file->storeAs('rifas', $filename, 'public');
            $url = '/storage/' . $path;
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen subida correctamente',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $filename,
                    'alt' => $alt,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir imagen para un premio
     */
    public function uploadPremioImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
            $file = $request->file('image');
            $alt = $request->input('alt', 'Imagen de premio');
            
            // Generar nombre único para el archivo
            $filename = 'premio_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Guardar archivo
            $path = $file->storeAs('premios', $filename, 'public');
            $url = '/storage/' . $path;
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen subida correctamente',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $filename,
                    'alt' => $alt,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir imagen para un nivel
     */
    public function uploadNivelImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
            $file = $request->file('image');
            $alt = $request->input('alt', 'Imagen de nivel');
            
            // Generar nombre único para el archivo
            $filename = 'nivel_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Guardar archivo
            $path = $file->storeAs('niveles', $filename, 'public');
            $url = '/storage/' . $path;
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen subida correctamente',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $filename,
                    'alt' => $alt,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar imagen subida
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta de archivo requerida'
            ], 422);
        }

        try {
            $path = $request->input('path');
            
            // Verificar que el archivo existe y eliminarlo
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagen eliminada correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}
