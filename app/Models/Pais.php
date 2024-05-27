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

    protected function setIncludes()
    {
        return [];
    }

    protected function setFields()
    {
        return [
            [
                'name' => 'País', 
                'field' => 'pais', 
                'type' => 'string', 
                'table' => true, 
                'form' => true,
                'rules' => [
                    'required' => true,
                    'unique' => true
                ]

            ],
        ];
    }

    protected function setExternalRelations()
    {
        return [
            [
                'name' => 'Suscriptores',
                'relation' => 'suscriptores',
                'formKey' => 'email',
                'pivotTable' => 'suscriptores_paises',
                'foreignKey' => 'pais_id',
                'relatedKey' => 'suscriptor_id',
                'model' => Suscriptor::class
            ],
        ];
    }

    protected static $forbiddenActions = [
        'user' => [
            'store',
        ],

        'super-admin' => [
            'destroy',
            'destroyPermanent',
            'exportExcel',
            'restore',
            'store',
        ],
    ];
    
}