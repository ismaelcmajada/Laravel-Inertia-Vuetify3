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
        return 'suscriptores';
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
                'type' => 'text', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Apellidos', 
                'field' => 'apellidos', 
                'type' => 'text', 
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
                'type' => 'text', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Situación', 
                'field' => 'situacion', 
                'type' => 'text', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'Acciones formativas', 
                'field' => 'acciones_formativas', 
                'type' => 'text', 
                'unique' => false, 
                'table' => true, 
                'form' => true
            ],
            [
                'name' => 'DNI', 
                'field' => 'dni', 
                'type' => 'text', 
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
                    ['value' => 'Hombre', 'text' => 'Hombre'],
                    ['value' => 'Mujer', 'text' => 'Mujer'],
                ]
            ],
        ];
    }
}