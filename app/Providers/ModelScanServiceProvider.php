<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class ModelScanServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('models', function ($app) {
            return $this->getModels();
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
                    $modelName = Str::lower(Str::afterLast($className, '\\'));
                    $models[$modelName] = $className::getModel();
                }
            }
        }

        return $models;
    }
}