<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class AutoCrudController extends Controller
{
    private $model;
    private $modelName;

    private function getValidationRules()
    {
        $rules = [];
        foreach ($this->model->formFields() as $field) {
            $fieldRules = [];
            if ($field['required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            if ($field['type'] === 'string') {
                $fieldRules[] = 'max:191';
            } elseif ($field['type'] === 'email') {
                $fieldRules[] = 'email|max:191';
            } elseif ($field['type'] === 'number') {
                $fieldRules[] = 'integer';
            } elseif ($field['type'] === 'select') {
                $fieldRules[] = 'in:' . implode(',', $field['options']);
            }

            if ($field['unique']) {
                $fieldRules[] = 'unique:' . $this->model->getTable() . ',' . $field['field'];
            }

            $rules[$field['field']] = $fieldRules;
        }
        return $rules;
    }

    public function __construct()
    {
        $this->modelName = request()->route('model');

        $modelClass = 'App\\Models\\' . ucfirst($this->modelName);

        if (class_exists($modelClass)) {
            $this->model = new $modelClass;
        } else {
            abort(404, 'Model not found');
        }
    }

    public function index()
    {
        return Inertia::render('Dashboard/'.ucfirst($this->modelName), [
           'model' => $this->model->getModel(),
        ]);
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = $this->model::query();  // Usando el modelo dinámico

        if ($deleted && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
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
        $rules = $this->getValidationRules();
        $validatedData = Request::validate($rules);

        $this->model::create($validatedData);

        return Redirect::back()->with('success', $this->modelName . ' creado.');
    }

    public function update($id)
    {
        $instance = $this->model::findOrFail($id);
        $rules = $this->getValidationRules();
        $validatedData = Request::validate($rules);

        $instance->update($validatedData);

        return Redirect::back()->with('success', $this->modelName . ' editado.');
    }

    public function destroy($id)
    {
        $instance = $this->model::findOrFail($id);
        $instance->delete();

        return Redirect::back()->with('success', $this->modelName . ' movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $instance = $this->model::onlyTrashed()->findOrFail($id);
        $instance->forceDelete();

        return Redirect::back()->with('success', $this->modelName . ' eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $instance = $this->model::onlyTrashed()->findOrFail($id);
        $instance->restore();

        return Redirect::back()->with('success', $this->modelName . ' restaurado.');
    }

    public function exportExcel()
    {
        $items = $this->model::all();

        return  [ 'itemsExcel' => $items ];
    }
}