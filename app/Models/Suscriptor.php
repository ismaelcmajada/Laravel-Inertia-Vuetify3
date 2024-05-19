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
                'name' => 'Nombre', 
                'field' => 'nombre', 
                'type' => 'string', 
                'table' => true, 
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'Apellidos', 
                'field' => 'apellidos', 
                'type' => 'string', 
                'table' => true, 
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'Email', 
                'field' => 'email', 
                'type' => 'email', 
                'table' => true, 
                'form' => true,
                'rules' => [
                    'required' => true,
                    'unique' => true
                ]
            ],
            [
                'name' => 'Teléfono', 
                'field' => 'telefono', 
                'type' => 'telephone', 
                'table' => true, 
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'DNI', 
                'field' => 'dni', 
                'type' => 'dni', 
                'table' => true, 
                'form' => true,
                'rules' => [
                    'required' => true,
                    'unique' => true
                ]
            ],
            [
                'name' => 'Sexo', 
                'field' => 'sexo', 
                'type' => 'select', 
                'table' => true, 
                'form' => true,
                'options' => ['Masculino', 'Femenino'],
                'rules' => [
                    'required' => true
                ]
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
                ],
                'rules' => [
                    'required' => true
                ]
            ],
        ];
    }
}