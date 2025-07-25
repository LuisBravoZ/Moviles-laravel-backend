<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Datos_Personales extends Model
{
    protected $table = 'datos_personales';

    protected $fillable = [
        'user_id',
        'telefono',
        'cedula',
        'fecha_nacimiento',
        'direccion',
        'ciudad',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
