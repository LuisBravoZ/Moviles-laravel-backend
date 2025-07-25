<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
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
}
