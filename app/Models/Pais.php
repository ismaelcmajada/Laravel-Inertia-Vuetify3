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
    protected $table = 'paises';

    protected static $endPoint = '/dashboard/pais';

    protected static $includes = [];

    protected static $fields = [
        [
            'name' => 'País',
            'field' => 'pais',
            'type' => 'string',
            'table' => true,
            'form' => true,
            'rules' => [
                'required' => true
            ]
        ],
        [
            'name' => 'Presidente',
            'field' => 'presidente_id',
            'type' => 'number',
            'relation' => [
                'model' => Presidente::class,
                'relation' => 'presidente',
                'tableKey' => '{name} ({id})',
                'formKey' => '{name} ({id})',
                'storeShortcut' => true,
                'serverSide' => false,
            ],
            'table' => true,
            'form' => true,
            'rules' => []
        ],
    ];

    protected static $externalRelations = [
        [
            'name' => 'Suscriptores',
            'storeShortcut' => true,
            'relation' => 'suscriptores',
            'formKey' => '{email}',
            'pivotTable' => 'suscriptores_paises',
            'foreignKey' => 'pais_id',
            'relatedKey' => 'suscriptor_id',
            'model' => Suscriptor::class
        ],
    ];

    protected static $forbiddenActions = [
        'user' => [
            'store',
        ],

        'super-admin' => [
            'exportExcel',
            'restore',
            'custom' => ['unauthorized'],
        ],
    ];

    public static function getCustomForbiddenActions()
    {
        return [
            'unauthorized' => function ($user, $action, $request) {
                if ($action === 'store') {
                    return $user->name !== "Default";
                }
            },
        ];
    }
}
