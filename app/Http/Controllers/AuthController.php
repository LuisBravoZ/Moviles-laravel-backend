<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Turno;

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
            'user' => $user,
            'message' => 'Inicio de sesión exitoso'
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
            // Validar roles_id solo si se envía (opcional)
            'roles_id' => 'nullable|integer|exists:roles,id',
        ]);

        // Encriptar la contraseña
        $data['password'] = bcrypt($data['password']);

        // Asignar el rol enviado o el rol por defecto (Paciente = ID 3)
        $data['roles_id'] = $request->input('roles_id', 3);

        // Crear el usuario
        $user = \App\Models\User::create($data);

        // Crear el token
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
            'roles_id' => 'sometimes|required|exists:roles_id',
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
        $users = User::with('roles')->get(); // Cargar los roles relacionados
        return response()->json($users);
    }

    //eliminar usuario
     public function destroy($id)
    {
        $usuario = User::find($id);
        $admin = Auth::user(); // Obtiene el administrador autenticado

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // No permitir que el admin se elimine a sí mismo
        if ($usuario->id === $admin->id) {
            return response()->json(['message' => 'No puedes eliminarte a ti mismo'], 403);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

    //Funcion para editar los usuarios desde el usuario adminitrador
    public function editAdmin(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'roles_id' => 'sometimes|required|exists:roles,id',
        ]);
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);

        }
        $user->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => $user
        ]);
    }

    // funcion de turnos reservados por paciente
    public function turnosReservados()
    {
        $pacienteId = Auth::id();
        $turnos = Turno::where('paciente_id', $pacienteId)->get();
        if ($turnos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes turnos reservados'
            ], 404);

        }
        return response()->json([

            'success' => true,
            'message' => 'Turnos reservados obtenidos correctamente',
            'turnos' => $turnos
        ]);
    }

    public function misTurnosPacienteReservado()
    {
        $pacienteId = Auth::id();
        $turnos = Turno::where('paciente_id', $pacienteId)->get();
        if ($turnos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes turnos reservados'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Turnos reservados obtenidos correctamente',
            'turnos' => $turnos
        ]);
    }
}