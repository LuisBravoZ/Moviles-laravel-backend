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
        $nutricionistas = User::where('roles_id', 2)->get(); // Asumiendo que el rol de nutricionista es 2
        return response()->json([
            'success'=>true,
            'message'=>'Lista de nutricionistas obtenida correctamente',
            'nutricionista'=> $nutricionistas]);
    }

    //funcion para obtener los turnos del nutricionista autenticado
    public function turnosNutricionista()
    {
        $nutricionistaId = Auth::id();
        $turnos = Turno::where('nutricionista_id', $nutricionistaId)->get();
        return response()->json([
            'success'=>true,
            'message'=>'Lista de turnos obtenida correctamente',
            'turnos'=> $turnos]);
    }
   
}