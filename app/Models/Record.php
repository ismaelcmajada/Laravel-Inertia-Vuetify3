<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Record extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    use AutoCrud;
    protected $table = 'records';

    protected static $includes = ['user', 'recordable'];

    protected static function getFields()
    {
        return [
            [
                'name' => 'Usuario',
                'field' => 'user_id',
                'type' => 'number',
                'relation' => [
                    'model' => User::class,
                    'relation' => 'user',
                    'tableKey' => 'user_id',
                    'formKey' => '{user_id}',
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
    }

    protected static $externalRelations = [];

    protected static $forbiddenActions = [
        'user' => [
            'index',
        ],
    ];

    protected static $calendarFields = [];
}
