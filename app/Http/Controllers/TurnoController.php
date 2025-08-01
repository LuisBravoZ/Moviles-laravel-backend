<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{

    //atender turno y el estado pasa a completado
    public function atenderTurno($id)
    {
        $turno = Turno::findOrFail($id);
        if (Auth::id() !== $turno->nutricionista_id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }   
        if ($turno->estado !== 'reservado') {
            return response()->json(['success' => false, 'message' => 'Turno no reservado'], 400);
        }
        $turno->update(['estado' => 'completado']);
        return response()->json(['success' => true, 'message' => 'Turno atendido y marcado como completado']);
    }

    // Nutricionista crea turno
    public function crearTurno(Request $request)
    {
        $nutricionistaId = Auth::id();
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'descanso_inicio' => 'required|date_format:H:i',
            'descanso_fin' => 'required|date_format:H:i|after:descanso_inicio',
        ]);



        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $hoy = Carbon::today();
        $horaInicio = Carbon::createFromTimeString($request->hora_inicio);
        $horaFin = Carbon::createFromTimeString($request->hora_fin);
        $descansoInicio = Carbon::createFromTimeString($request->descanso_inicio);
        $descansoFin = Carbon::createFromTimeString($request->descanso_fin);

        $turnosCreados = [];

        // Iterar sobre cada día entre las fechas
        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            // Excluir domingos
            if ($fecha->lt($hoy) || $fecha->isSunday()) {
                continue;
            }

            $horaActual = $horaInicio->copy();
            while ($horaActual < $horaFin) {
                // Omitir si está en la hora de descanso
                if ($horaActual->between($descansoInicio, $descansoFin->copy()->subMinute())) {
                    $horaActual->addHour();
                    continue;
                }

                // Verificar si ya existe un turno con esa hora y fecha para el nutricionista
                $existe = DB::table('turnos')
                    ->where('nutricionista_id', $nutricionistaId)
                    ->whereDate('fecha', $fecha->toDateString())
                    ->whereTime('hora', $horaActual->format('H:i:s'))
                    ->exists();

                if (!$existe) {
                    $turno = Turno::create([
                        'nutricionista_id' => $nutricionistaId,
                        'fecha' => $fecha->toDateString(),
                        'hora' => $horaActual->format('H:i:s'),
                    ]);
                    $turnosCreados[] = $turno;
                }

                $horaActual->addHour();
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($turnosCreados) > 0
                ? 'Turnos creados correctamente'
                : 'No se crearon nuevos turnos (posiblemente ya existían o fecha incorrecta)',
            'turnos' => $turnosCreados,
        ]);
    }

    // Paciente reserva turno
    public function reservarTurno($id)
    {
        $turno = Turno::findOrFail($id);
        if ($turno->estado !== 'disponible') {
            return response()->json(['success' => false, 'message' => 'Turno no disponible'], 400);
        }
        $turno->update([
            'paciente_id' => Auth::id(),
            'estado' => 'reservado',
        ]);
        return response()->json(['success' => true, 'message' => 'Turno reservado']);
    }

    // Nutricionista o paciente cancela turno
    public function cancelarTurno($id)
    {
        $turno = Turno::findOrFail($id);

        // Solo el nutricionista o el paciente asociado pueden cancelar
        if (
            Auth::id() === $turno->nutricionista_id ||
            Auth::id() === $turno->paciente_id
        ) {
            $turno->update([
                'estado' => 'disponible',
                'paciente_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Turno cancelado y puesto como disponible'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No autorizado'
        ], 403);
    }

    //eliminar turno
    public function eliminarTurno($id)
    {
        $turno = Turno::findOrFail($id);
        if (Auth::id() !== $turno->nutricionista_id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        $turno->delete();
        return response()->json(['success' => true, 'message' => 'Turno eliminado correctamente']);
    }

    // Nutricionista asigna turno a paciente
    public function asignarTurno(Request $request, $id)
    {
        $turno = Turno::findOrFail($id);
        if (Auth::id() !== $turno->nutricionista_id) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        $request->validate(['paciente_id' => 'required|exists:users,id']);
        $turno->update([
            'paciente_id' => $request->paciente_id,
            'estado' => 'reservado',
        ]);
        return response()->json(['success' => true, 'message' => 'Turno asignado']);
    }

    //crear turnos F.I-F.F
    public function crearTurnosMasivos(Request $request)
    {
        $nutricionistaId = Auth::id(); // Obtener el ID del nutricionista autenticado

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'descanso_inicio' => 'required|date_format:H:i',
            'descanso_fin' => 'required|date_format:H:i|after:descanso_inicio',
            //'nutricionista_id' => 'required|exists:users,id', // Asegúrate de que venga el ID
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);

        $horaInicio = Carbon::createFromTimeString($request->hora_inicio);
        $horaFin = Carbon::createFromTimeString($request->hora_fin);
        $descansoInicio = Carbon::createFromTimeString($request->descanso_inicio);
        $descansoFin = Carbon::createFromTimeString($request->descanso_fin);
        $hoy = Carbon::today();
        //$nutricionistaId = $request->nutricionista_id;
        $turnosCreados = [];

        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            if ($fecha->lt($hoy) || $fecha->isSunday()) {
                continue;
            }

            $horaActual = $horaInicio->copy();

            while ($horaActual < $horaFin) {
                if ($horaActual->between($descansoInicio, $descansoFin->copy()->subMinute())) {
                    $horaActual->addHour();
                    continue;
                }

                $existe = DB::table('turnos')
                    ->where('nutricionista_id', $nutricionistaId)
                    ->whereDate('fecha', $fecha->toDateString())
                    ->whereTime('hora', $horaActual->format('H:i:s'))
                    ->exists();

                if (!$existe) {
                    $turno = Turno::create([
                        'nutricionista_id' => $nutricionistaId,
                        'fecha' => $fecha->toDateString(),
                        'hora' => $horaActual->format('H:i:s'),
                    ]);
                    $turnosCreados[] = $turno;
                }

                $horaActual->addHour();
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($turnosCreados) > 0
                ? 'Turnos creados correctamente'
                : 'No se crearon nuevos turnos (ya existían o estaban en descanso/domingo)',
            'turnos' => $turnosCreados,
        ]);
    }

    // Mis turnos reservados 
    public function misTurnos()
    {
        $userId = Auth::id();

        $turnos = Turno::where('paciente_id', $userId)
            ->where('estado', 'reservado')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de mis turnos reservados',
            'turnos' => $turnos
        ]);
    }

    // Turnos reservados por paciente
    public function turnosReservadosPorPaciente()
    {
        $pacienteId = Auth::id();

        $turnosReservados = Turno::with('nutricionista') // si tienes la relación
            ->where('paciente_id', $pacienteId)
            ->orderBy('fecha')
            ->get();

        if ($turnosReservados->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turnos reservados todavía',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Turnos reservados del paciente obtenidos correctamente',
            'turnos' => $turnosReservados,
        ]);
    }

    // Turnos que tiene reservados por nutricionista
    public function turnosReservadosNutricionista()
    {
        $nutricionistaId = Auth::id();

        $turnos = Turno::with('paciente')
            ->where('nutricionista_id', $nutricionistaId)
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

    //tunos que estan completados por nutricionista
    public function turnosCompletadosNutricionista()
    {
        $nutricionistaId = Auth::id();
        $turnos = Turno::with('paciente')
            ->where('nutricionista_id', $nutricionistaId)
            ->where('estado', 'completado')
            ->get();
        if ($turnos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turnos completados para este nutricionista',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Lista de turnos completados obtenida correctamente',
            'turnos' => $turnos
        ]);
    }

    //turnos completados por paciente 
    public function turnosCompletadosPorPaciente()
    {
        $pacienteId = Auth::id();
        $turnos = Turno::with('nutricionista')
            ->where('paciente_id', $pacienteId)
            ->where('estado', 'completado')
            ->get();
        if ($turnos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turnos completados para este paciente',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Lista de turnos completados obtenida correctamente',
            'turnos' => $turnos
        ]);
    }
}
