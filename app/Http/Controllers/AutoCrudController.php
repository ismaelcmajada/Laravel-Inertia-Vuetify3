<?php
namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class AutoCrudController extends Controller
{

    private function getValidationRules($model, $id = null)
    {
        $modelInstance = $this->getModel($model);
        $rules = [];
    
        foreach ($modelInstance->formFields() as $field) {
            $fieldRules = [];
    
            if (isset($field['rules']['required']) && $field['rules']['required']) {
                $fieldRules[] = 'required';
            }
    
            switch ($field['type']) {
                case 'string':
                    $fieldRules[] = 'max:191';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:191';
                    break;
                case 'number':
                    $fieldRules[] = 'integer';
                    break;
                case 'select':
                    if (isset($field['options'])) {
                        $fieldRules[] = 'in:' . implode(',', $field['options']);
                    }
                    break;
                case 'telephone':
                    $fieldRules[] = 'digits_between:8,15';
                    break;
            }
    
            if (isset($field['rules']['unique']) && $field['rules']['unique']) {
                $uniqueRule = Rule::unique($modelInstance->getTable(), $field['field']);
                if ($id !== null) {
                    $uniqueRule = $uniqueRule->ignore($id);
                }
                $fieldRules[] = $uniqueRule;
            }
    
            $rules[$field['field']] = $fieldRules;
        }
    
        return $rules;
    }
    
    private function getModel($model)
    {
        $modelClass = 'App\\Models\\' . ucfirst($model);

        if (class_exists($modelClass)) {
            return new $modelClass;
        } else {
            abort(404, 'Model not found');
        }
    }

    public function index($model)
    {
        return Inertia::render('Dashboard/'.ucfirst($model), [
           'model' => $this->getModel($model)->getModel(),
        ]);
    }

    public function getAll($model)
    {
        return $this->getModel($model)::all();
    }

    public function loadItems($model)
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $model = $this->getModel($model);

        $query = $model::query(); 

        $query->with($model->getModel()['includes']);

        if ($deleted && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model))) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    $parts = explode('.', $key);
                    if (count($parts) == 2) {
                        $query->whereHas($parts[0], function ($q) use ($parts, $value) {
                            $q->where($parts[1], 'LIKE', '%' . $value . '%');
                        });
                    } else {
                        $query->where($key, 'LIKE', '%' . $value . '%');
                    }
                }
            }
        }
    
        if (!empty($sortBy)) {
            foreach ($sortBy as $sort) {
                if (isset($sort['key']) && isset($sort['order'])) {
                    $parts = explode('.', $sort['key']);
                    if (count($parts) == 2) {
                        
                        $relatedMethod = $parts[0];
                        $relatedField = $parts[1];

                        $relation = $model->$relatedMethod();
                        
                        $foreignKey = $relation->getForeignKeyName();
                        $relatedTable = $relation->getRelated()->getTable();
    
                        $query->join($relatedTable, $model->getTable() . '.' . $foreignKey, '=', $relatedTable . '.id')
                            ->orderBy($relatedTable . '.' . $relatedField, $sort['order']);
                        
                    } else {
                        $query->orderBy($sort['key'], $sort['order']);
                    }
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

    public function store($model)
    {
        $rules = $this->getValidationRules($model);
        $validatedData = Request::validate($rules);

        $this->getModel($model)::create($validatedData);

        return Redirect::back()->with('success', $model . ' creado.');
    }

    public function update($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $rules = $this->getValidationRules($model, $id);
        $validatedData = Request::validate($rules);

        $instance->update($validatedData);

        return Redirect::back()->with('success', $model . ' editado.');
    }

    public function destroy($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $instance->delete();

        return Redirect::back()->with('success', $model . ' movido a la papelera.');
    }

    public function destroyPermanent($model, $id)
    {
        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);
        $instance->forceDelete();

        return Redirect::back()->with('success', $model . ' eliminado de forma permanente.');
    }

    public function restore($model, $id)
    {
        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);
        $instance->restore();

        return Redirect::back()->with('success', $model . ' restaurado.');
    }

    public function exportExcel($model)
    {
        $items = $this->getModel($model)::all();

        return  [ 'itemsExcel' => $items ];
    }
}