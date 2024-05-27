<?php
namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AutoCrudController extends Controller
{

    private function getValidationRules($model, $id = null)
    {
        $modelInstance = $this->getModel($model);
        $rules = [];
    
        foreach ($modelInstance->formFields() as $field) {
            $fieldRules = [];
    
            if (isset($field['rules']['required']) && $field['rules']['required']) {
                if ($field['type'] !== 'image' && $field['type'] !== 'password') {
                    $fieldRules[] = 'required';
                }
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
    
        $modelInstance = $this->getModel($model);
        $modelTable = $modelInstance->getTable();
        $query = $modelInstance::query();
    
        $query->select($modelTable . '.*');
    
        $query->with($modelInstance->getModel()['includes']);
    
        if ($deleted && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($modelInstance))) {
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
                        $query->where($modelTable . '.' . $key, 'LIKE', '%' . $value . '%');
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
    
                        $relation = $modelInstance->$relatedMethod();
                        $foreignKey = $relation->getForeignKeyName();
                        $relatedTable = $relation->getRelated()->getTable();
                        $relatedModelKey = $relation->getRelated()->getKeyName();
    
                        $query->addSelect("$relatedTable.$relatedField as $relatedMethod" . "_" . "$relatedField");
                        $query->leftJoin($relatedTable, "$modelTable.$foreignKey", '=', "$relatedTable.$relatedModelKey")
                              ->orderBy("$relatedMethod" . "_" . "$relatedField", $sort['order']);
                    } else {
                        $query->orderBy($modelTable . '.' . $sort['key'], $sort['order']);
                    }
                }
            }
        } else {
            $query->orderBy($modelTable . ".id", "desc");
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
        $modelInstance = $this->getModel($model);
        $instance = $modelInstance::create($validatedData);

        foreach ($modelInstance->formFields() as $field) {
            if ($field['type'] === 'image' && Request::hasFile($field['field']) && $instance) {
                $storagePath = $field['public'] ? 'public/images/'.$model : 'private/images/'.$model;
                $imagePath = Request::file($field['field'])->storeAs($storagePath,  $field['field'].'/'.$instance['id']);
                $instance->{$field['field']} = Storage::url($imagePath);
            }
        }

        $instance->save();

        $instance->load($instance->getModel()['includes']);
    
        return Redirect::back()->with(['success' => 'Elemento creado.', 'data' => $instance]);
    }
    
    public function update($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $rules = $this->getValidationRules($model, $id);
        $validatedData = Request::validate($rules);

        foreach ($instance->formFields() as $field) {
            if ($field['type'] === 'image') {
                if(Request::input($field['field'].'_edited')) {
                    Storage::delete($field['public'] ? 'public/images/'.$model.'/'.$field['field'].'/'.$id : 'private/images/'.$model.'/'.$field['field'].'/'.$id );
                    $validatedData[$field['field']] = null;
                }
                if (Request::hasFile($field['field'])) {
                    $storagePath = $field['public'] ? 'public/images/'.$model : 'private/images/'.$model;
                    $imagePath = Request::file($field['field'])->storeAs($storagePath, $field['field'].'/'.$id);
                    $validatedData[$field['field']] = Storage::url($imagePath);
                }    
            }
        }

        $instance->update($validatedData);
        $instance->load($instance->getModel()['includes']);

        return Redirect::back()->with(['success' => 'Elemento editado.', 'data' => $instance]);
    }

    public function destroy($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $instance->delete();

        return Redirect::back()->with('success', 'Elemento movido a la papelera.');
    }

    public function destroyPermanent($model, $id)
    {


        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);
        foreach ($instance->formFields() as $field) {
            if ($field['type'] === 'image') {
                Storage::delete($field['public'] ? 'public/images/'.$model.'/'.$field['field'].'/'.$id : 'private/images/'.$model.'/'.$field['field'].'/'.$id );
            }
        }
        $instance->forceDelete();

        return Redirect::back()->with('success', 'Elemento eliminado de forma permanente.');
    }

    public function restore($model, $id)
    {
        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);
        $instance->restore();

        return Redirect::back()->with('success', 'Elemento restaurado.');
    }

    public function exportExcel($model)
    {
        $items = $this->getModel($model)::all();

        return  [ 'itemsExcel' => $items ];
    }

    public function bind($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $instance->{$externalRelation}()->attach($item);

        $instance->load($instance->getModel()['includes']);

        return Redirect::back()->with(['success' => 'Elemento vinculado', 'data' => $instance]);

    }

    public function unbind($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $instance->{$externalRelation}()->detach($item);

        $instance->load($instance->getModel()['includes']);

        return Redirect::back()->with(['success' => 'Elemento desvinculado', 'data' => $instance]);
    }
}