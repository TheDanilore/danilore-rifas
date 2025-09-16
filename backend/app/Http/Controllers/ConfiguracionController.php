<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ConfiguracionController extends Controller
{
    /**
     * Obtener todas las configuraciones públicas
     */
    public function index(): JsonResponse
    {
        $configuraciones = Configuracion::publicas()
                                       ->ordenadas()
                                       ->get()
                                       ->keyBy('clave');

        return response()->json([
            'success' => true,
            'data' => $configuraciones
        ]);
    }

    /**
     * Obtener configuraciones por categoría
     */
    public function porCategoria(string $categoria): JsonResponse
    {
        $configuraciones = Configuracion::porCategoria($categoria)
                                       ->publicas()
                                       ->ordenadas()
                                       ->get();

        return response()->json([
            'success' => true,
            'data' => $configuraciones
        ]);
    }

    /**
     * Obtener una configuración específica
     */
    public function show(string $clave): JsonResponse
    {
        $configuracion = Configuracion::where('clave', $clave)
                                     ->where('publica', true)
                                     ->first();

        if (!$configuracion) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $configuracion
        ]);
    }

    /**
     * Obtener valor específico de una configuración
     */
    public function valor(string $clave): JsonResponse
    {
        $valor = Configuracion::obtener($clave);

        if ($valor === null) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'clave' => $clave,
                'valor' => $valor
            ]
        ]);
    }

    /**
     * Obtener todas las configuraciones (solo admin)
     */
    public function admin(): JsonResponse
    {
        $configuraciones = Configuracion::orderBy('categoria')
                                       ->orderBy('orden')
                                       ->get()
                                       ->groupBy('categoria');

        return response()->json([
            'success' => true,
            'data' => $configuraciones
        ]);
    }

    /**
     * Actualizar una configuración (solo admin)
     */
    public function update(Request $request, string $clave): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'valor' => 'required',
            'descripcion' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $configuracion = Configuracion::where('clave', $clave)->first();

            if (!$configuracion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración no encontrada'
                ], 404);
            }

            if (!$configuracion->editable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta configuración no es editable'
                ], 403);
            }

            // Validar valor según tipo
            if (!$configuracion->validarValor($request->valor)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valor inválido para el tipo de configuración'
                ], 422);
            }

            $configuracion->actualizar($request->valor, Auth::id());

            if ($request->has('descripcion')) {
                $configuracion->update(['descripcion' => $request->descripcion]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'data' => $configuracion->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateBatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'configuraciones' => 'required|array',
            'configuraciones.*.clave' => 'required|string',
            'configuraciones.*.valor' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $actualizadas = [];
            $errores = [];

            foreach ($request->configuraciones as $config) {
                try {
                    $configuracion = Configuracion::where('clave', $config['clave'])->first();

                    if (!$configuracion) {
                        $errores[] = "Configuración '{$config['clave']}' no encontrada";
                        continue;
                    }

                    if (!$configuracion->editable) {
                        $errores[] = "Configuración '{$config['clave']}' no es editable";
                        continue;
                    }

                    if (!$configuracion->validarValor($config['valor'])) {
                        $errores[] = "Valor inválido para '{$config['clave']}'";
                        continue;
                    }

                    $configuracion->actualizar($config['valor'], Auth::id());
                    $actualizadas[] = $configuracion->clave;

                } catch (\Exception $e) {
                    $errores[] = "Error al actualizar '{$config['clave']}': " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones procesadas',
                'data' => [
                    'actualizadas' => $actualizadas,
                    'errores' => $errores
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar configuraciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar configuración al valor por defecto
     */
    public function restore(string $clave): JsonResponse
    {
        try {
            $configuracion = Configuracion::where('clave', $clave)->first();

            if (!$configuracion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración no encontrada'
                ], 404);
            }

            if (!$configuracion->editable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta configuración no puede ser restaurada'
                ], 403);
            }

            $configuracion->restaurarDefecto(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Configuración restaurada al valor por defecto',
                'data' => $configuracion->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar configuraciones
     */
    public function export(): JsonResponse
    {
        try {
            $configuraciones = Configuracion::exportarConfiguraciones();

            return response()->json([
                'success' => true,
                'data' => $configuraciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar configuraciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar configuraciones
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'configuraciones' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Configuracion::importarConfiguraciones($request->configuraciones, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Configuraciones importadas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar configuraciones: ' . $e->getMessage()
            ], 500);
        }
    }
}