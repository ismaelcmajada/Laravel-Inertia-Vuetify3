<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ReflectionClass;
use Illuminate\Support\Facades\App;

class ModelScanServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('models', function ($app) {
            if (App::environment('production') || App::environment('prod')) {
                return Cache::rememberForever('scanned_models', function () {
                    return $this->getModels();
                });
            } else {
                return $this->getModels();
            }
        });
    }

    private function getModels()
    {
        $modelsPath = app_path('Models');
        $modelFiles = File::allFiles($modelsPath);
        $models = [];

        foreach ($modelFiles as $modelFile) {
            $className = '\\App\\Models\\' . Str::replace(
                ['/', '.php'],
                ['\\', ''],
                $modelFile->getRelativePathname()
            );

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                if ($reflection->isSubclassOf('Illuminate\Database\Eloquent\Model') && !$reflection->isAbstract()) {
                    $modelInstance = new $className;
                    $modelName = Str::lower(Str::afterLast($className, '\\'));
                    $models[$modelName] = $modelInstance->getModel();
                }
            }
        }

        return $models;
    }
}