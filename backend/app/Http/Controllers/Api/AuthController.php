<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Registro de usuario
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'required|string|max:15',
            'tipo_documento' => 'required|in:dni,ce,passport,ruc,otros',
            'numero_documento' => 'required|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:masculino,femenino,otro'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'rol' => 'usuario',
                'activo' => true
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login de usuario
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        
        // Verificar que el usuario esté activo
        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario desactivado'
            ], 403);
        }

        // Actualizar último acceso
        $user->update(['ultimo_acceso' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load(['ventas.rifa', 'boletos.rifa']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:15',
            'fecha_nacimiento' => 'sometimes|date',
            'genero' => 'sometimes|in:masculino,femenino,otro',
            'direccion' => 'sometimes|string|max:255',
            'ciudad' => 'sometimes|string|max:100',
            'departamento' => 'sometimes|string|max:100',
            'codigo_postal' => 'sometimes|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($request->only([
                'name', 'telefono', 'fecha_nacimiento', 'genero',
                'direccion', 'ciudad', 'departamento', 'codigo_postal'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}
