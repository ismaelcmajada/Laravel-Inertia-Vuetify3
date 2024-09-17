<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pais;

class Suscriptor extends BaseModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'suscriptores';

    protected static $endPoint = '/dashboard/suscriptor';

    protected static $includes = [];

    protected static $fields = [
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
                'model' => Pais::class,
                'relation' => 'pais',
                'tableKey' => 'pais',
                'formKey' => 'pais',
                'storeShortcut' => true,
                'serverSide' => true,
                'searchKeys' => ['name', 'code'],
            ],
            'rules' => [
                'required' => true
            ]
        ],
        [
            'name' => 'Foto',
            'field' => 'foto',
            'type' => 'image',
            'table' => false,
            'form' => true,
            'public' => true,
            'onlyUpdate' => true,
            'rules' => [
                'required' => false,
                'mimes' => 'jpeg,png,jpg,gif,svg',
                'max' => 2048,
            ]
        ],
    ];

    protected static $externalRelations = [
        [
            'name' => 'País',
            'relation' => 'paises',
            'formKey' => 'pais',
            'pivotTable' => 'suscriptores_paises',
            'foreignKey' => 'suscriptor_id',
            'relatedKey' => 'pais_id',
            'storeShortcut' => true,
            'model' => Pais::class,
        ]
    ];

    protected static $forbiddenActions = [];
}
