<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class User extends BaseModel implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens, Notifiable;

    protected static $endPoint = '/dashboard/user';

    protected function setFields()
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
                'name' => 'Contraseña', 
                'field' => 'password', 
                'type' => 'password', 
                'table' => false, 
                'form' => true, 
                'rules' => [
                    'required' => true
                ]
            ],
            [
                'name' => 'Rol', 
                'field' => 'role', 
                'type' => 'select', 
                'table' => true, 
                'form' => true, 
                'options' => ['user', 'admin'], 
                'rules' => [
                    'required' => true
                ]
            ],
        ];
    }

    protected function setIncludes()
    {
        return [];
    }

    protected function setExternalRelations()
    {
        return [];
    }

   protected static $forbiddenActions = [
        'admin' => [
            'destroy',
        ]
    ];
}