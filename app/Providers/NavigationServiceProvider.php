<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

use App\Models\Suscriptor;
use App\Models\Pais;
use App\Models\User;

class NavigationServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton('navigation', function ($app) {
            if (App::environment('production') || App::environment('prod')) {
                return Cache::rememberForever('navigation_routes', function () {
                    return $this->generateNavigation();
                });
            } else {
                return $this->generateNavigation();
            }
        });
    }

    private function generateNavigation()
    {
        $routes = [
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
                'name' => 'Países',
                'icon' => 'mdi-earth',
                'model' => Pais::class,
            ],
            [
                'name' => 'Usuarios',
                'icon' => 'mdi-account-group',
                'model' => User::class,
            ],
            [
                'name' => 'Cerrar sesión',
                'icon' => 'mdi-logout-variant',
                'path' => '/logout',
            ],
        ];

        $this->setPaths($routes);
        return $this->filterNavigation($routes);
    }

    private function setPaths(&$routes)
    {
        foreach ($routes as &$route) {
            $modelClass = $route['model'] ?? null;
            if (class_exists($modelClass)) {
                $route['path'] = $modelClass::getEndpoint();
            }

            if (isset($route['childs'])) {
                $this->setPaths($route['childs']);
            }
        }
    }

    private function filterNavigation(&$routes)
    {
        return array_filter($routes, function (&$route) {
            $modelClass = $route['model'] ?? null;

            if (isset($route['childs'])) {
                $route['childs'] = $this->filterNavigation($route['childs']);

                if (count($route['childs']) === 0) {
                    return false;
                }
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
}

