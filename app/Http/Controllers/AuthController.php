<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    //funcion de login - inicio de sesion
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }
        $user = Auth::user();
        $token = $user->createToken('AppToken')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    //funionde logout-cerrar  sesion
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    //funcion para registrar un usuario
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = bcrypt($data['password']);
            $user = \App\Models\User::create($data);
            $token = $user->createToken('AppToken')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'token' => $token,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //editar perfil de usuario
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }
    //ver datos de usuario autenticado, esta funcion se usa para ver el perfil del usuario autenticado
    public function perfil(Request $request)
    {
        return response()->json($request->user());
    }

    //ver todos los usuarios
    public function index()
    {
        $users = \App\Models\User::all();
        return response()->json($users);
    }
}