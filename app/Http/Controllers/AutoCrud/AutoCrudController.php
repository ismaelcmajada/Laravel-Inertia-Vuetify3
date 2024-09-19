<?php

namespace App\Http\Controllers\AutoCrud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class AutoCrudController extends Controller
{

    private function buildFieldRules($field, $modelInstance, $id, $relation = null, $itemId = null)
    {
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
                    if (isset($field['multiple']) && $field['multiple']) {

                        $fieldRules[] = function ($attribute, $value, $fail) use ($field) {

                            foreach ($value as $option) {
                                if (!in_array(trim($option), $field['options'])) {
                                    $fail("La opción seleccionada '{$option}' no es válida.");
                                }
                            }
                        };
                    } else {
                        $fieldRules[] = 'in:' . implode(',', $field['options']);
                    }
                }
                break;
            case 'telephone':
                $fieldRules[] = 'digits_between:8,15';
                break;
        }

        if (isset($field['rules']['unique']) && $field['rules']['unique']) {
            if ($relation) {
                $uniqueRule = Rule::unique($relation['pivotTable'], $field['field'])->where(function ($query) use ($field, $relation, $id, $itemId) {
                    if ($field['type'] === 'boolean') {
                        $query->where($field['field'], '=', true)
                            ->where($relation['foreignKey'], '=', $id)
                            ->where($relation['relatedKey'], '!=', $itemId);
                    }
                });
            } else {
                $uniqueRule = Rule::unique($modelInstance->getTable(), $field['field'])->where(function ($query) use ($field) {
                    if ($field['type'] === 'boolean') {
                        $query->where($field['field'], '=', true);
                    }
                });

                if ($id !== null) {
                    $uniqueRule = $uniqueRule->ignore($id);
                }
            }




            $fieldRules[] = $uniqueRule;
        }

        return $fieldRules;
    }

    public function getValidationRules($model, $id = null, $itemId = null)
    {
        $modelInstance = $this->getModel($model);
        $rules = [];

        if (!$itemId) {
            foreach ($modelInstance::getFormFields() as $field) {
                $fieldRules = $this->buildFieldRules($field, $modelInstance, $id);
                $rules[$field['field']] = $fieldRules;
            }

            return $rules;
        }

        foreach ($modelInstance::getExternalRelations() as $relation) {
            if (isset($relation['pivotFields'])) {
                foreach ($relation['pivotFields'] as $pivotField) {
                    $fieldRules = $this->buildFieldRules($pivotField, $modelInstance, $id, $relation, $itemId);
                    $rules[$pivotField['field']] = $fieldRules;
                }
            }
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
        return Inertia::render('Dashboard/' . ucfirst($model));
    }

    public function getAll($model)
    {
        return $this->getModel($model)::all();
    }

    public function loadAutocompleteItems($model)
    {
        $search = Request::get('search', '');
        $key = Request::get('key', 'name');
        $items = $this->getModel($model)::whereRaw("$key LIKE '%$search%'")->limit(6)->get();

        return ['autocompleteItems' => $items];
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

        $query->with($modelInstance::getIncludes());

        if ($deleted && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($modelInstance))) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    $parts = explode('.', $key);
                    if (count($parts) == 2) {
                        $query->whereHas($parts[0], function ($q) use ($parts, $value) {
                            $words = explode(' ', $value);
                            foreach ($words as $word) {
                                $q->where($parts[1], 'LIKE', '%' . $word . '%');
                            }
                        });
                    } else if ($key === 'created_at' || $key === 'updated_at' || $key === 'deleted_at') {
                        $query->where(DB::raw("DATE_FORMAT(" . $modelTable . "." . $key . ", '%d-%m-%Y%')"), 'LIKE', '%' . $value . '%');
                    } else {
                        $words = explode(' ', $value);
                        foreach ($words as $word) {
                            $query->where($modelTable . '.' . $key, 'LIKE', '%' . $word . '%');
                        }
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

        foreach ($modelInstance::getFormFields() as $field) {
            if ($field['type'] === 'image' && Request::hasFile($field['field']) && $instance) {
                $storagePath = $field['public'] ? 'public/images/' . $model : 'private/images/' . $model;
                $imagePath = Request::file($field['field'])->storeAs($storagePath,  $field['field'] . '/' . $instance['id']);
                $instance->{$field['field']} = $imagePath;
            }

            if ($field['type'] === 'file' && Request::hasFile($field['field']) && $instance) {
                $storagePath = $field['public'] ? 'public/files/' . $model : 'private/files/' . $model;
                $filePath = Request::file($field['field'])->storeAs($storagePath,  $field['field'] . '/' . $instance['id']);

                if (!$field['public']) {
                    $fileContent = Storage::get($filePath);

                    // Encriptar el contenido del archivo
                    $encryptedContent = Crypt::encryptString($fileContent);

                    // Guardar el contenido encriptado de nuevo en el archivo
                    Storage::put($filePath, $encryptedContent);
                }

                $instance->{$field['field']} = $filePath;
            }

            if ($field['type'] === 'select') {
                if (isset($field['multiple']) && $field['multiple']) {
                    $validatedData[$field['field']] = implode(', ', $validatedData[$field['field']]);
                }
            }
        }

        $created = $modelInstance::create($validatedData);

        $created->load($modelInstance::getIncludes());

        if ($created) {
            return Redirect::back()->with(['success' => 'Elemento creado.', 'data' => $created]);
        }
    }

    public function update($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $rules = $this->getValidationRules($model, $id);
        $validatedData = Request::validate($rules);

        foreach ($instance::getFormFields() as $field) {
            if ($field['type'] === 'image') {
                if (Request::input($field['field'] . '_edited')) {
                    Storage::delete($field['public'] ? 'public/images/' . $model . '/' . $field['field'] . '/' . $id : 'private/images/' . $model . '/' . $field['field'] . '/' . $id);
                    $validatedData[$field['field']] = null;
                }
                if (Request::hasFile($field['field'])) {
                    $storagePath = $field['public'] ? 'public/images/' . $model : 'private/images/' . $model;
                    $imagePath = Request::file($field['field'])->storeAs($storagePath, $field['field'] . '/' . $id);
                    $validatedData[$field['field']] = $imagePath;
                }
            }

            if ($field['type'] === 'file') {
                if (Request::input($field['field'] . '_edited')) {
                    Storage::delete($field['public'] ? 'public/files/' . $model . '/' . $field['field'] . '/' . $id : 'private/files/' . $model . '/' . $field['field'] . '/' . $id);
                    $validatedData[$field['field']] = null;
                }
                if (Request::hasFile($field['field'])) {
                    $storagePath = $field['public'] ? 'public/files/' . $model : 'private/files/' . $model;
                    $filePath = Request::file($field['field'])->storeAs($storagePath, $field['field'] . '/' . $id);
                    $validatedData[$field['field']] = $filePath;

                    if (!$field['public']) {
                        $fileContent = Storage::get($filePath);

                        // Encriptar el contenido del archivo
                        $encryptedContent = Crypt::encryptString($fileContent);

                        // Guardar el contenido encriptado de nuevo en el archivo
                        Storage::put($filePath, $encryptedContent);
                    }
                }
            }

            if ($field['type'] === 'select') {
                if (isset($field['multiple']) && $field['multiple']) {
                    $validatedData[$field['field']] = implode(', ', $validatedData[$field['field']]);
                }
            }

            if ($field['type'] === 'password') {
                if (!Request::input($field['field'])) {
                    $validatedData[$field['field']] = $instance->{$field['field']};
                }
            }
        }


        $updated = $instance->update($validatedData);
        $instance->load($instance::getIncludes());

        if ($updated) {
            return Redirect::back()->with(['success' => 'Elemento editado.', 'data' => $instance]);
        }
    }

    public function destroy($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);

        if ($instance->delete()) {
            return Redirect::back()->with('success', 'Elemento movido a la papelera.');
        }
    }

    public function destroyPermanent($model, $id)
    {
        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);
        foreach ($instance::getFormFields() as $field) {
            if ($field['type'] === 'image') {
                Storage::delete($field['public'] ? 'public/images/' . $model . '/' . $field['field'] . '/' . $id : 'private/images/' . $model . '/' . $field['field'] . '/' . $id);
            }

            if ($field['type'] === 'file') {
                Storage::delete($field['public'] ? 'public/files/' . $model . '/' . $field['field'] . '/' . $id : 'private/files/' . $model . '/' . $field['field'] . '/' . $id);
            }
        }

        if ($instance->forceDelete()) {
            return Redirect::back()->with('success', 'Elemento eliminado de forma permanente.');
        }
    }

    public function restore($model, $id)
    {
        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);

        if ($instance->restore()) {
            return Redirect::back()->with('success', 'Elemento restaurado.');
        }
    }

    public function exportExcel($model)
    {
        $items = $this->getModel($model)::all();

        return  ['itemsExcel' => $items];
    }

    public function bind($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);

        $rules = $this->getValidationRules($model, $id, $item);

        $validatedData = Request::validate($rules);

        $instance->{$externalRelation}()->attach($item, $validatedData);

        $instance->load($instance::getIncludes());

        return Redirect::back()->with(['success' => 'Elemento vinculado', 'data' => $instance]);
    }

    public function updatePivot($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);

        $rules = $this->getValidationRules($model, $id, $item);

        $validatedData = Request::validate($rules);

        $instance->{$externalRelation}()->updateExistingPivot($item, $validatedData);

        $instance->load($instance::getIncludes());

        return Redirect::back()->with(['success' => 'Elemento actualizado', 'data' => $instance]);
    }

    public function unbind($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $instance->{$externalRelation}()->detach($item);

        $instance->load($instance::getIncludes());

        return Redirect::back()->with(['success' => 'Elemento desvinculado', 'data' => $instance]);
    }
}
