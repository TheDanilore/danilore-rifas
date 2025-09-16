<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NivelController extends Controller
{
    /**
     * Obtener todos los niveles (público)
     */
    public function index()
    {
        try {
            $niveles = Nivel::orderBy('orden')->get();

            return response()->json([
                'success' => true,
                'data' => $niveles,
                'message' => 'Niveles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver un nivel específico
     */
    public function show($id)
    {
        try {
            $nivel = Nivel::with('premios')->find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $nivel,
                'message' => 'Nivel obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Crear un nuevo nivel
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:niveles,nombre',
                'descripcion' => 'nullable|string|max:500',
                'orden' => 'required|integer|min:1',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'icono' => 'nullable|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nivel = Nivel::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $nivel,
                'message' => 'Nivel creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un nivel existente
     */
    public function update(Request $request, $id)
    {
        try {
            $nivel = Nivel::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:niveles,nombre,' . $id,
                'descripcion' => 'nullable|string|max:500',
                'orden' => 'required|integer|min:1',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'icono' => 'nullable|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nivel->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $nivel->fresh(),
                'message' => 'Nivel actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un nivel
     */
    public function destroy($id)
    {
        try {
            $nivel = Nivel::find($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            // Verificar si tiene premios asociados
            if ($nivel->premios()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un nivel que tiene premios asociados'
                ], 400);
            }

            $nivel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nivel eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordenar niveles
     */
    public function reordenar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'niveles' => 'required|array',
                'niveles.*.id' => 'required|exists:niveles,id',
                'niveles.*.orden' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->niveles as $nivelData) {
                Nivel::where('id', $nivelData['id'])
                    ->update(['orden' => $nivelData['orden']]);
            }

            $niveles = Nivel::orderBy('orden')->get();

            return response()->json([
                'success' => true,
                'data' => $niveles,
                'message' => 'Niveles reordenados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de niveles
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_niveles' => Nivel::count(),
                'niveles_con_premios' => Nivel::has('premios')->count(),
                'niveles_sin_premios' => Nivel::doesntHave('premios')->count(),
                'premios_por_nivel' => Nivel::withCount('premios')
                    ->orderBy('orden')
                    ->get(['id', 'nombre', 'premios_count']),
                'nivel_mas_usado' => Nivel::withCount('premios')
                    ->orderBy('premios_count', 'desc')
                    ->first()
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}