<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Turno;

class NutricionistaController extends Controller
{

    //funcion para obtener todos los nutricionistas
    public function indexNutricionista()
    {
        $nutricionistas = User::where('roles_id', 2)->get(); //rol de nutricionista es 2
        return response()->json([
            'success' => true,
            'message' => 'Lista de nutricionistas obtenida correctamente',
            'nutricionista' => $nutricionistas
        ]);
    }

    //funcion para obtener los turnos del nutricionista autenticado
  public function turnosReservadosPorNutricionista($nutricionistaId)
{
    
    $turnos = Turno::with('paciente') // Carga la relación con el paciente
        ->where('nutricionista_id', $nutricionistaId)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Turnos encontrados',
        'turnos' => $turnos
    ]);
}

    //funcion para obtener los turnos de un nutricionista cuando estan reservados por un paciente
    public function turnosNutricionistaEspecifico($id)
    {
        $turnos = Turno::with('paciente') 
        ->where('nutricionista_id', $id)
        ->where('estado', 'reservado')
        ->get();
        if ($turnos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turnos reservados para este nutricionista',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Lista de turnos reservados obtenida correctamente',
            'turnos' => $turnos
        ]);
    }

    //funcion para listar solo los nutricionistas
    public function listarNutricionistas()
    {
        $nutricionistas = User::where('roles_id', 2)->get(); //rol de nutricionista es 2
        return response()->json([
            'success' => true,
            'message' => 'Lista de nutricionistas obtenida correctamente',
            'nutricionistas' => $nutricionistas
        ]);
    }
//
    public function turnosPorNutricionista($id)
{
    $turnos = Turno::with('paciente')
        ->where('nutricionista_id', $id)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Turnos del nutricionista obtenidos correctamente',
        'turnos' => $turnos
    ]);
}

public function turnosNutricionista()
{
    $nutricionistaId = Auth::id(); // nutricionista autenticado

    $turnos = Turno::with('paciente')
        ->where('nutricionista_id', $nutricionistaId)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Turnos del nutricionista autenticado obtenidos correctamente',
        'turnos' => $turnos
    ]);
}



}
