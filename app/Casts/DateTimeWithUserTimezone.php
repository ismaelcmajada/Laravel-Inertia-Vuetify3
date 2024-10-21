<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DateTimeWithUserTimezone implements CastsAttributes
{

    protected $format;

    public function __construct($format = 'Y-m-d H:i:s')
    {
        $this->format = $format;
    }


    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        // Obtener la zona horaria del usuario logueado
        $timezone = auth()->user()->timezone ?? config('usertimezone.default');

        // Convertir la fecha a la zona horaria del usuario
        return Carbon::parse($value)->setTimezone($timezone)->format($this->format);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        // Convertir la fecha a UTC antes de almacenarla
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->toDateTimeString();
    }
}
