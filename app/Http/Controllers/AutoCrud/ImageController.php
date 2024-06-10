<?php

namespace App\Http\Controllers\AutoCrud;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ImageController extends Controller
{
    /**
     * Servir una imagen pública.
     *
     * @param  string  $model
     * @param  string  $filename
     * @return \Illuminate\Http\Response
     */
    public function publicImage($model, $field, $id)
    {
        $path = "public/images/{$model}/{$field}/{$id}";
        if (!Storage::exists($path)) {
            abort(404);
        }

        return response()->file(Storage::path($path));
    }

    /**
     * Servir una imagen privada.
     *
     * @param  string  $model
     * @param  string  $filename
     * @return \Illuminate\Http\Response
     */
    public function privateImage($model, $field, $id)
    {
        $path = "private/images/{$model}/{$field}/{$id}";
        if (!Storage::exists($path) || !Auth::check()) {
            abort(404);
        }

        return response()->file(Storage::path($path));
    }
}