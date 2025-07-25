<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table='roles';

    protected $fillable=[
        'roles_id','nombre_rol','descripcion'];

    public function user(){
        return $this->HasMany(User::class,'user_id');
    }
}
