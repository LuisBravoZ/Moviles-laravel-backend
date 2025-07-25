<?php

namespace App\Http\Controllers;

use App\Models\Datos_Personales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class DatosPersonalesController extends Controller
{

    public function index()
    {
        $usuario = Auth::user(); // Obtener el usuario autenticado
        if ($usuario) {
            $datoPersonal = $usuario->dato_personal; // Relación con DatoPersonal
            return response()->json($datoPersonal);
        }
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }


    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:10',
            'cedula' => 'required|string|max:10',
            'fecha_nacimiento' => 'required|date',
            'direccion' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
        ]);

        // Comprobar si la validación falla
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Crear nuevo DatoPersonal
        $datoPersonal = new Datos_Personales([
            'telefono' => $request->telefono,
            'cedula' => $request->cedula,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
        ]);

        // Guardar el dato personal relacionado con el usuario
        $usuario->dato_personal()->save($datoPersonal);

        return response()->json([
            'message' => 'Datos personales guardados con éxito',
            'datoPersonal' => $datoPersonal,
        ], 201);
    }

    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        $datoPersonal = Datos_Personales::find($id);
        if (!$datoPersonal) {
            return response()->json(['message' => 'Dato personal no encontrado'], 404);
        }
        return response()->json($datoPersonal);
    }


    /**
     * Update the specified resource in storage.
     */
public function update(Request $request)
{
    $usuario = Auth::user();

    if (!$usuario) {
        return response()->json(['message' => 'Usuario no autenticado'], 401);
    }

    // Validar los datos entrantes
    $validator = Validator::make($request->all(), [
        'telefono' => 'sometimes|string|max:20',
        'cedula' => 'sometimes|string|max:15',
        'fecha_nacimiento' => 'sometimes|date',
        'direccion' => 'sometimes|string|max:100',
        'ciudad' => 'sometimes|string|max:100',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Obtener los datos personales del usuario (tiene que coincidir con la relación en User.php)
    $datoPersonal = $usuario->dato_personal()->first();

    // Si no tiene datos personales, llamamos al store
    if (!$datoPersonal) {
        return $this->store($request);
    }

    // Actualizamos solo los campos enviados
    $datoPersonal->update($request->only([
        'telefono',
        'cedula',
        'fecha_nacimiento',
        'direccion',
        'ciudad'
    ]));

    return response()->json([
        'message' => 'Datos personales actualizados con éxito',
        'Datos nuevos' => $datoPersonal,
    ]);
}





    /**
     * Remove the specified resource from storage.
     */
     public function destroy()
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->dato_personal) {
            return response()->json(['message' => 'Datos personales no encontrados'], 404);
        }

        $usuario->dato_personal->delete(); // Eliminar los datos personales
        return response()->json(['message' => 'Datos personales eliminados con éxito']);
    }
}