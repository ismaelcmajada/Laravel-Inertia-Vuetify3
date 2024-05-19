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

    protected function setIncludes()
    {
        return [];
    }

    protected function setEndPoint()
    {
        return '/dashboard/suscriptor';
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
                'options' => ['Masculino', 'Femenino'],
            ],
            [
                'name' => 'País',
                'field' => 'pais_id',
                'type' => 'number',
                'form' => true,
                'table' => true,
                'relation' => [
                    'type' => 'belongsTo',
                    'model' => 'Pais',
                    'relation' => 'paises',
                    'tableKey' => 'pais',
                    'formKey' => 'pais'
                ]
            ],
        ];
    }
}