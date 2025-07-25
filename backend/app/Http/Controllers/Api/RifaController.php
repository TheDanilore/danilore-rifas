<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\Premio;
use App\Models\Nivel;
use App\Models\ProgresoPremio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RifaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios.niveles']);

        // Filtros
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('destacadas')) {
            $query->where('es_destacada', true);
        }

        // Ordenamiento
        if ($request->tipo === 'futura') {
            $query->orderBy('orden');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $rifas = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $codigo): JsonResponse
    {
        $rifa = Rifa::with([
            'categoria',
            'premios' => function($query) {
                $query->orderBy('orden');
            },
            'premios.niveles' => function($query) {
                $query->orderBy('orden');
            },
            'premios.progreso'
        ])->where('codigo_unico', $codigo)->firstOrFail();

        // Calcular boletos disponibles
        $boletosOcupados = $rifa->boletos()
            ->where(function($query) {
                $query->where('estado', 'pagado')
                      ->orWhere(function($q) {
                          $q->where('estado', 'reservado')
                            ->where('fecha_expiracion_reserva', '>', now());
                      });
            })
            ->pluck('numero')
            ->toArray();

        // Generar números disponibles (1 al boletos_minimos)
        $numerosDisponibles = [];
        for ($i = 1; $i <= $rifa->boletos_minimos; $i++) {
            $numerosDisponibles[] = sprintf('%04d', $i);
        }

        $numerosLibres = array_diff($numerosDisponibles, $boletosOcupados);

        return response()->json([
            'success' => true,
            'data' => [
                'rifa' => $rifa,
                'numeros_disponibles' => array_values($numerosLibres),
                'numeros_ocupados' => $boletosOcupados,
                'total_disponibles' => count($numerosLibres)
            ]
        ]);
    }

    /**
     * Get rifas actuales (en venta)
     */
    public function actuales(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios.niveles'])
            ->actuales()
            ->enVenta();

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $rifas = $query->orderBy('es_destacada', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Get rifas futuras
     */
    public function futuras(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios.niveles', 'rifaRequerida'])
            ->futuras();

        $rifas = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Get rifas destacadas
     */
    public function destacadas(): JsonResponse
    {
        $rifas = Rifa::with(['categoria', 'premios.niveles'])
            ->destacadas()
            ->enVenta()
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Get progreso de premios de una rifa
     */
    public function progreso(string $codigo): JsonResponse
    {
        $rifa = Rifa::where('codigo_unico', $codigo)->firstOrFail();
        
        $premios = Premio::with(['niveles', 'progreso'])
            ->where('rifa_id', $rifa->id)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'rifa' => $rifa,
                'premios' => $premios
            ]
        ]);
    }
}
