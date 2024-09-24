<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Suscriptor;

class Presidente extends BaseModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'presidentes';

    protected static $endPoint = '/dashboard/presidente';

    protected static $includes = [];

    protected static $fields = [
        [
            'name' => 'Nombre',
            'field' => 'name',
            'type' => 'string',
            'table' => true,
            'form' => true,
            'rules' => [
                'required' => true
            ]
        ],
    ];

    protected static $externalRelations = [];

    protected static $forbiddenActions = [];
}
