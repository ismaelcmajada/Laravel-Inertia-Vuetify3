<?php

namespace App\Http\Controllers;

use App\Models\Suscriptor;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class SuscriptorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        return Inertia::render('Dashboard/Suscriptor');
    }

    public function loadItems() 
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Suscriptor::query();

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }

        if (!empty($sortBy)) {
            foreach ($sortBy as $sort) {
                if (isset($sort['key']) && isset($sort['order'])) {
                    $query->orderBy($sort['key'], $sort['order']);
                }
            }
        } else {
            $query->orderBy("id", "desc");
        }

        if ($itemsPerPage == -1) {
            $itemsPerPage = $query->count();
        }    

        $items = $query->paginate($itemsPerPage);

        return [
            'tableData' => [
                'items' => $items->items(),
                'itemsLength' => $items->total(),
                'itemsPerPage' => $items->perPage(),
                'page' => $items->currentPage(),
                'sortBy' => $sortBy,
                'search' => $search, 
                'deleted' => $deleted,
            ]
        ];
    }

    public function store()
    {
        Suscriptor::create(
            Request::validate([
                'nombre' => ['required', 'max:191'],
                'apellidos' => ['required', 'max:191'],
                'dni' => ['required', 'max:191'],
                'email' => ['required', 'email', 'max:191'],
                'telefono' => ['nullable', 'integer'],
                'sexo' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with('success', 'Suscriptor creado.');
    }

    public function update(Suscriptor $subscriber)
    {
        $subscriber->update(
            Request::validate([
                'nombre' => ['required', 'max:191'],
                'apellidos' => ['required', 'max:191'],
                'dni' => ['required', 'max:191'],
                'email' => ['required', 'email', 'max:191'],
                'telefono' => ['nullable', 'integer'],
                'sexo' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with('success', 'Suscriptor editado.');
    }

    public function destroy(Suscriptor $subscriber)
    {
        $subscriber->delete();

        return Redirect::back()->with('success', 'Suscriptor movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $subscriber = Suscriptor::onlyTrashed()->findOrFail($id);
        $subscriber->forceDelete();

        return Redirect::back()->with('success', 'Suscriptor eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $subscriber = Suscriptor::onlyTrashed()->findOrFail($id);
        $subscriber->restore();

        return Redirect::back()->with('success', 'Suscriptor restaurado.');
    }

    public function exportExcel()
    {
        $items = Suscriptor::all();

        return  [ 'itemsExcel' => $items ];
    }
    
}
