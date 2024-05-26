<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                'forbiddenActions' => $this->getForbiddenActions($request),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => [
                'success' => session('success'),
                'data' => session('data'),
             ]
        ]);
    }

    private function getModelFromRoute(Request $request)
    {
        $model = $request->route('model');
        $modelClass = "App\\Models\\" . ucfirst($model);

        if (class_exists($modelClass)) {
            return new $modelClass;
        } else {
            return null;
        }
    }

    private function getForbiddenActions(Request $request)
    {
        $user = $request->user();
        $model = $this->getModelFromRoute($request);

        if ($model) {
            $forbiddenActions = $model->getForbiddenActions() ?? [];
            return $forbiddenActions[$user->role] ?? [];
        } else {
            return [];
        }
    }
}
