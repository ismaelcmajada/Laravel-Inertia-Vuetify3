<?php

namespace App\Http\Controllers\AutoCrud;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Models\Record;
use Illuminate\Support\Facades\Auth;

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

    public function store($model)
    {
        $rules = $this->getValidationRules($model);
        $validatedData = Request::validate($rules);
        $modelInstance = $this->getModel($model);

        foreach ($modelInstance::getFormFields() as $field) {
            if ($field['type'] === 'select') {
                if (isset($field['multiple']) && $field['multiple']) {
                    $validatedData[$field['field']] = implode(', ', $validatedData[$field['field']]);
                }
            }
        }

        $instance = $modelInstance::create($validatedData);

        foreach ($modelInstance::getFormFields() as $field) {
            if ($field['type'] === 'image' && Request::hasFile($field['field'])) {
                $storagePath = $field['public'] ? 'public/images/' . $model : 'private/images/' . $model;
                $imagePath = Request::file($field['field'])->storeAs($storagePath,  $field['field'] . '/' . $instance['id']);
                $instance->{$field['field']} = $imagePath;
            }

            if ($field['type'] === 'file' && Request::hasFile($field['field'])) {
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
        }

        $created = $instance->save();

        $instance->load($modelInstance::getIncludes());

        if ($created) {

            $this->setRecord($model, $instance->id, 'create');

            return Redirect::back()->with(['success' => 'Elemento creado.', 'data' => $instance]);
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

            $this->setRecord($model, $instance->id, 'update');

            return Redirect::back()->with(['success' => 'Elemento editado.', 'data' => $instance]);
        }
    }

    public function destroy($model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);

        if ($instance->delete()) {

            $this->setRecord($model, $instance->id, 'destroy');

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

            $this->setRecord($model, $instance->id, 'destroyPermanent');

            return Redirect::back()->with('success', 'Elemento eliminado de forma permanente.');
        }
    }

    public function restore($model, $id)
    {
        $instance = $this->getModel($model)::onlyTrashed()->findOrFail($id);

        if ($instance->restore()) {

            $this->setRecord($model, $instance->id, 'restore');

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

        $this->setRecord($model, $instance->id, 'update');

        return Redirect::back()->with(['success' => 'Elemento vinculado', 'data' => $instance]);
    }

    public function updatePivot($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);

        $rules = $this->getValidationRules($model, $id, $item);

        $validatedData = Request::validate($rules);

        $instance->{$externalRelation}()->updateExistingPivot($item, $validatedData);

        $instance->load($instance::getIncludes());

        $this->setRecord($model, $instance->id, 'update');
        return Redirect::back()->with(['success' => 'Elemento actualizado', 'data' => $instance]);
    }

    public function unbind($model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $instance->{$externalRelation}()->detach($item);

        $instance->load($instance::getIncludes());

        $this->setRecord($model, $instance->id, 'update');
        return Redirect::back()->with(['success' => 'Elemento desvinculado', 'data' => $instance]);
    }

    public function setRecord($model, $element_id, $action)
    {
        $record = new Record();
        $record->user_id = Auth::user()->id;
        $record->element_id = $element_id;
        $record->action = $action;
        $record->model = 'App\\Models\\' . ucfirst($model);

        $record->save();
    }
}
