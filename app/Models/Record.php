<?php

namespace App\Models;

use App\Models\User;

class Record extends BaseModel
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'records';

    protected static $endPoint = '/dashboard/record';

    protected static $includes = ['user', 'recordable'];

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
            'relation' => [
                'relation' => 'recordable',
                'polymorphic' => true,
                'morphType' => 'model',
            ],
            'table' => false,
            'form' => false,
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
