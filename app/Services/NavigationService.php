<?php
namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

use App\Models\Suscriptor;
use App\Models\Pais;

class NavigationService
{

    private $routes;

    public function __construct()
    {
        $this->routes = [
            [
                'name' => Auth::user()->name ?? null,
                'icon' => 'mdi-account-circle',
            ],
            [
                'name' => 'Suscriptores',
                'icon' => 'mdi-account-group',
                'model' => Suscriptor::class,
            ],
            [
                'name' => 'hola',
                'path' => '/dashboard/hola',
                'icon' => 'mdi-account-group',
                'childs' => [
                    [
                        'name' => 'hola',
                        'path' => '/dashboard/hola/perro',
                        'icon' => 'mdi-account-group',
                    ],
                    [
                        'name' => 'Países',
                        'icon' => 'mdi-earth',
                        'model' => Pais::class,
                    ],
                ],
            ],
            [
                'name' => 'Países',
                'icon' => 'mdi-earth',
                'model' => Pais::class,
            ],
            [
                'name' => 'Cerrar sesión',
                'icon' => 'mdi-logout-variant',
                'path' => '/logout',
            ],
        ];
    }

    private function setPaths(&$routes)
    {
        foreach ($routes as &$route) {
            $modelClass = $route['model'] ?? null;
  
            if (class_exists($modelClass)) {
                $model = Str::lower(Str::afterLast($modelClass, '\\'));
                $route['path'] = '/dashboard/' . Str::lower($model);
            }

            if(isset($route['childs'])) {
                $childs = &$route['childs'];
                $this->setPaths($childs);
            }
        }
    }

    private function filterNavigation(&$routes) {
        return array_filter($routes, function (&$route) {
            $modelClass = $route['model'] ?? null;

            if (isset($route['childs'])) {
                $childs = &$route['childs'];
                $route['childs'] = $this->filterNavigation($childs);
            }

            if (class_exists($modelClass)) {
                $forbiddenActions = $modelClass::getForbiddenActions();
                $forbiddenActions = $forbiddenActions[Auth::user()->role ?? null] ?? [];

                if ($forbiddenActions) {
                    return !in_array('index', $forbiddenActions);
                }
                return true;
            }
            return true;
        });
    }

    public function getNavigation()
    {
        $this->setPaths($this->routes);
        return $this->filterNavigation($this->routes);
    }
}