<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Pais extends BaseModel
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'paises';

    protected function setIncludes()
    {
        return [];
    }

    protected function setEndPoint()
    {
        return '/dashboard/pais';
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
}