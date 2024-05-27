<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ReflectionClass;

class ModelScanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton('modelscanner', function ($app) {
            return new class {
                public function scanModels()
                {
                    return Cache::remember('scanned_models', 1440, function () {
                        $modelsPath = app_path('Models');
                        $modelFiles = File::allFiles($modelsPath);

                        foreach ($modelFiles as $modelFile) {
                            $className = '\\App\\Models\\' . str_replace(
                                ['/', '.php'],
                                ['\\', ''],
                                $modelFile->getRelativePathname()
                            );

                            if (class_exists($className)) {
                                $reflection = new ReflectionClass($className);
                                if ($reflection->isSubclassOf('App\Models\BaseModel') && !$reflection->isAbstract()) {
                                    $modelInstance = new $className;
                                    $modelName = Str::lower(Str::afterLast($className, '\\'));
                                    $models[$modelName] = $modelInstance->getModel();
                                }
                            }
                        }

                        return $models;
                    });
                }
            };
        });
    }
}
