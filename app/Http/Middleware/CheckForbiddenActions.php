<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CheckForbiddenActions
{
    public function handle($request, Closure $next)
    {
        if ($request->route('model')) {
            $user = Auth::user();
            $userRole = $user->role;
            $model = $request->route('model');
            $modelClass = "App\\Models\\" . ucfirst($model);

            if (class_exists($modelClass)) {
                $forbiddenActions = $modelClass::getForbiddenActions()[$userRole] ?? [];
                $customForbiddenActions = $modelClass::getCustomForbiddenActions();
                $action = explode('@', $request->route()->getActionName())[1];
                $forbidden = false;

                if (in_array('all', $forbiddenActions)) {
                    $forbidden = true;
                } elseif (in_array($action, $forbiddenActions)) {
                    $forbidden = true;
                } elseif (isset($forbiddenActions['custom'])) {
                    foreach ($forbiddenActions['custom'] as $customAction) {
                        if (isset($customForbiddenActions[$customAction])) {
                            $forbidden = $customForbiddenActions[$customAction]($user, $action, $request);
                        }
                    }
                }

                if ($forbidden) {
                    return Redirect::back()->withErrors(['error' => 'No tienes permiso para realizar esta acción.']);
                }
            } else {
                abort(404, "El modelo especificado no existe.");
            }
        }

        return $next($request);
    }
}
