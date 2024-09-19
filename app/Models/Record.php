<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Suscriptor;

class Pais extends BaseModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'records';

    protected static $endPoint = '/dashboard/record';

    protected static $includes = [];

    protected static $fields = [
        [
            'name' => 'Usuario',
            'field' => 'user_id',
            'type' => 'number',
            'relation' => [
                'model' => User::class,
                'relation' => 'user',
                'tableKey' => 'user_id',
                'formKey' => 'user_id',
            ],
            'table' => true,
            'form' => true,
            'rules' => [
                'required' => true
            ]
        ],
        [
            'name' => 'Elemento',
            'field' => 'element_id',
            'type' => 'number',
            'table' => true,
            'form' => true,
            'rules' => [
                'required' => true
            ]
        ],
        [
            'name' => 'Modelo',
            'field' => 'model',
            'type' => 'string',
            'table' => true,
            'form' => true,
            'rules' => [
                'required' => true
            ]
        ],
        [
            'name' => 'Acción',
            'field' => 'action',
            'type' => 'string',
            'table' => true,
            'form' => true,
            'rules' => [
                'required' => true
            ]
        ],
    ];

    protected static $externalRelations = [];

    protected static $forbiddenActions = [
        'user' => [
            'index',
        ],
    ];
}
