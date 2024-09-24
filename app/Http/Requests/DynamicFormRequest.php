<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class DynamicFormRequest extends BaseFormRequest
{
    public function __construct()
    {
        parent::__construct();

        // Obtener el modelo de la ruta
        $model = Request::route('model'); // Asumiendo que el nombre del modelo viene en la ruta

        $this->modelClass = 'App\\Models\\' . Str::studly($model);
    }
}
