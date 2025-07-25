<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categorias = Categoria::activas()
                              ->ordenadas()
                              ->withCount(['rifas' => function($query) {
                                  $query->enVenta();
                              }])
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug): JsonResponse
    {
        $categoria = Categoria::where('slug', $slug)
                             ->where('activa', true)
                             ->with(['rifas' => function($query) {
                                 $query->enVenta()->with('premios.niveles');
                             }])
                             ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $categoria
        ]);
    }
}
