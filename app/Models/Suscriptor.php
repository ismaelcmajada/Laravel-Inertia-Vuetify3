<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suscriptor extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'suscriptores';

    /**
     * @var array
     */
    protected $fillable = ['nombre', 'email', 'telefono', 'nivel_formativo', 'apellidos', 'situacion', 'acciones_formativas', 'dni', 'sexo'];

    protected $casts = [
        'telefono' => 'integer',
    ];
}