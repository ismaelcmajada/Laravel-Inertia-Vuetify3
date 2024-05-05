<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Suscriptor extends BaseModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'suscriptores';

    protected function setEndPoint()
    {
        return '/dashboard/suscriptores';
    }

    protected function setFields()
    {
        return [
            [
                'name' => 'ID', 
                'field' => 'id', 
                'type' => 'number', 
                'unique' => true, 
                'table' => true, 
                'form' => false
            ],
            [
                'name' => 'Nombre', 
                'field' => 'nombre', 
                'type' => 'string', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Apellidos', 
                'field' => 'apellidos', 
                'type' => 'string', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Email', 
                'field' => 'email', 
                'type' => 'email', 
                'unique' => true, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Teléfono', 
                'field' => 'telefono', 
                'type' => 'number', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Nivel formativo', 
                'field' => 'nivel_formativo', 
                'type' => 'string', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Situación', 
                'field' => 'situacion', 
                'type' => 'string', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Acciones formativas', 
                'field' => 'acciones_formativas', 
                'type' => 'string', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'DNI', 
                'field' => 'dni', 
                'type' => 'string', 
                'unique' => true, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Sexo', 
                'field' => 'sexo', 
                'type' => 'select', 
                'unique' => false, 
                'table' => true, 
                'form' => true,
                'options' => [
                    ['value' => 'Hombre', 'string' => 'Hombre'],
                    ['value' => 'Mujer', 'string' => 'Mujer'],
                ]
            ],
        ];
    }
}