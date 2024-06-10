<?php
namespace App\Http\Controllers\AutoCrud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function setSession()
    {
        $key = Request::input('key');
        $value = Request::input('value');

        Session::put($key, $value);
    }

    public function getSession($key)
    {
        return Session::get($key);
    }
}