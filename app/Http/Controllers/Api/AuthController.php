<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    /**
     * Registro de nuevo usuario.
     * POST /api/register
     */
    public function register(Request $request)
    {
        // 1. Validar datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Crear usuario (la contraseña se hashea automáticamente por el cast 'hashed')
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'cliente', // Por defecto todos son clientes
        ]);

        // 3. Generar token
        $token = $user->createToken('auth-token')->plainTextToken;

        // 4. Devolver JSON
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Usuario registrado exitosamente',
        ], 201);
    }

    /**
     * Login de usuario.
     * POST /api/login
     */
    public function login(Request $request)
    {
        // 1. Validar datos
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Buscar usuario
        $user = User::where('email', $request->email)->first();

        // 3. Verificar credenciales
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden.'],
            ]);
        }

        // 4. Generar token (opcional: borrar tokens viejos)
        // $user->tokens()->delete(); // Descomenta si quieres que solo haya una sesión activa Ventaja: Solo hay un token activo por usuario. Más limpio y seguro.Desventaja: Cerrar sesión en un dispositivo cierra la sesión en todos.
        $token = $user->createToken('auth-token')->plainTextToken;

        // 5. Devolver JSON
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login exitoso',
        ], 200);
    }

    /**
     * Logout: revoca el token actual.
     * POST /api/logout (protegida)
     */
    public function logout(Request $request)
    {
        // Elimina solo el token con el que se hizo esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ], 200);
    }

    /**
     * Obtener el usuario autenticado actual.
     * GET /api/me (protegida)
     */
    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }

}
