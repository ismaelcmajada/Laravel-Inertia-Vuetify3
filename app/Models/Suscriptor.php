<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Suscriptor extends Model
{
    use AutoCrud;
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'suscriptores';

    protected static $includes = ['pais.presidente', 'paises.presidente'];

    protected static function getFields()
    {
        return [
            [
                'name' => 'Nombre',
                'field' => 'nombre',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['dni', 'telephone']
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
                'options' => ['Masculino', 'Femenino', 'Otro'],
                'multiple' => true,
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
                    'tableKey' => 'Mi país es {pais} que tiene el presidente {presidente.name}',
                    'formKey' => 'Mi país es {pais} que tiene el presidente {presidente.name}',
                    'storeShortcut' => true,
                    'serverSide' => true,
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
    }

    protected static $externalRelations = [
        [
            'name' => 'País',
            'relation' => 'paises',
            'formKey' => 'Mi país es {pais} que tiene el presidente {presidente.name}',
            'pivotTable' => 'suscriptores_paises',
            'foreignKey' => 'suscriptor_id',
            'relatedKey' => 'pais_id',
            'storeShortcut' => true,
            'model' => Pais::class,
        ]
    ];

    protected static $forbiddenActions = [];

    protected static $calendarFields = [];

    public static function getCustomRules()
    {
        return [
            'dni' => function ($attribute, $value, $fail) {
                if (!preg_match('/^\d{8}[A-Z]$/', $value)) {
                    $fail('El DNI debe tener 8 números seguidos de una letra mayúscula.');
                }
            },
            'telephone' => function ($attribute, $value, $fail) {
                if (!preg_match('/^\d{8,15}$/', $value)) {
                    $fail('El teléfono debe tener entre 8 y 15 números.');
                }
            }
        ];
    }
}
