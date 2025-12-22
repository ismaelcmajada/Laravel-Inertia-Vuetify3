<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Pais extends Model
{
    use AutoCrud;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'paises';


    protected static $includes = [];

    protected static function getFields()
    {
        return [
            [
                'name' => 'Nombre',
                'field' => 'pais',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'Fecha',
                'field' => 'start_date',
                'type' => 'date',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'Fecha fin',
                'field' => 'end_date',
                'type' => 'date',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'Presi',
                'field' => 'presidente_id',
                'type' => 'number',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true
                ]
            ],
        ];
    }

    protected static $externalRelations = [

        [
            'type' => 'hasMany',              // NUEVO: tipo de relación
            'relation' => 'suscriptores',          // nombre del método de relación
            'name' => 'Suscriptores',
            'model' => Suscriptor::class,
            'foreignKey' => 'pais_id',         // FK en la tabla hija
            'formKey' => '{nombre}',             // PK local (opcional, default: 'id')
        ],

    ];

    protected static $forbiddenActions = [];

    protected static $calendarFields = [];
}
