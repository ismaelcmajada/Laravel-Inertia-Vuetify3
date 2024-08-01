<?php

namespace App\Http\Controllers\AutoCrud;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    /**
     * Servir una imagen pública.
     *
     * @param  string  $model
     * @param  string  $filename
     * @return \Illuminate\Http\Response
     */
    public function publicFile($model, $field, $id)
    {
        $path = "public/files/{$model}/{$field}/{$id}";
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
    public function privateFile($model, $field, $id)
    {
        $path = "private/files/{$model}/{$field}/{$id}";
        
        if (!Storage::exists($path) || !Auth::check()) {
            abort(404);
        }

        // Obtener el contenido encriptado del archivo
        $encryptedContent = Storage::get($path);

        // Desencriptar el contenido del archivo
        $decryptedContent = Crypt::decryptString($encryptedContent);

        // Crear un archivo temporal con el contenido desencriptado
        $tempFilePath = tempnam(sys_get_temp_dir(), 'decrypted_file');
        file_put_contents($tempFilePath, $decryptedContent);

        // Devolver el archivo temporal como respuesta
        return response()->file($tempFilePath)->deleteFileAfterSend(true);
    }
}