<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Presidente extends Model
{
    use AutoCrud;
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'presidentes';

    protected static $includes = [];

    protected static function getFields()
    {
        return [
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
    }

    protected static $externalRelations = [];

    protected static $forbiddenActions = [];

    protected static $calendarFields = [];
}
