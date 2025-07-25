<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Turno extends Model
{
   use HasFactory;

    protected $table='turnos';

    protected $fillable=[
        'nutricionista_id','fecha','hora','paciente_id','estado'];

    public function nutricionista()
    {
        return $this->belongsTo(User::class, 'nutricionista_id');
    }
    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }
    public function scopeReservados($query)
    {
        return $query->where('estado', 'reservado');
    }
    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }
    public function scopePorNutricionista($query, $nutricionistaId)
    {
        return $query->where('nutricionista_id', $nutricionistaId);
    }
}
