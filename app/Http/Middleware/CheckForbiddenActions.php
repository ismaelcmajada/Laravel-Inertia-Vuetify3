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
        
            $userRole = Auth::user()->role;
            $model = $request->route('model');
            $modelClass = "App\\Models\\" . ucfirst($model);

            if (class_exists($modelClass)) {
                $forbiddenActions = $modelClass::getForbiddenActions()[$userRole] ?? [];

                $action = explode('@', $request->route()->getActionName())[1];
                if (in_array('all', $forbiddenActions) || in_array($action, $forbiddenActions)) {
                    return Redirect::back()->withErrors(['error' => 'No tienes permiso para acceder realizar esta acción.']);
                }
            } else {
                abort(404, "El modelo especificado no existe.");
            }
        }

        return $next($request);
    }
}
