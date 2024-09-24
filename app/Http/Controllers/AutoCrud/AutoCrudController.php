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
use App\Http\Requests\DynamicFormRequest;

class AutoCrudController extends Controller
{
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

    public function store(DynamicFormRequest $request, $model)
    {
        $validatedData = $request->validated();
        $modelInstance = $this->getModel($model);

        foreach ($modelInstance::getFormFields() as $field) {
            if ($field['type'] === 'select' && isset($field['multiple']) && $field['multiple']) {
                $validatedData[$field['field']] = implode(', ', $validatedData[$field['field']]);
            }
        }

        $instance = $modelInstance::create($validatedData);

        // Manejo de archivos
        foreach ($modelInstance::getFormFields() as $field) {
            if (($field['type'] === 'image' || $field['type'] === 'file') && $request->hasFile($field['field'])) {
                $storagePath = $field['public'] ? 'public/' : 'private/';
                $storagePath .= $field['type'] === 'image' ? 'images/' : 'files/';
                $storagePath .= $model;
                $filePath = $request->file($field['field'])->storeAs($storagePath,  $field['field'] . '/' . $instance['id']);

                if (!$field['public'] && $field['type'] === 'file') {
                    $fileContent = Storage::get($filePath);
                    $encryptedContent = Crypt::encryptString($fileContent);
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

    public function update(DynamicFormRequest $request, $model, $id)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $validatedData = $request->validated();

        foreach ($instance::getFormFields() as $field) {
            if ($field['type'] === 'image' || $field['type'] === 'file') {
                if ($request->input($field['field'] . '_edited')) {
                    Storage::delete($field['public'] ? 'public/' : 'private/' . $field['type'] . '/' . $model . '/' . $field['field'] . '/' . $id);
                    $validatedData[$field['field']] = null;
                }
                if ($request->hasFile($field['field'])) {
                    $storagePath = $field['public'] ? 'public/' : 'private/';
                    $storagePath .= $field['type'] === 'image' ? 'images/' : 'files/';
                    $storagePath .= $model;
                    $filePath = $request->file($field['field'])->storeAs($storagePath, $field['field'] . '/' . $id);

                    if (!$field['public'] && $field['type'] === 'file') {
                        $fileContent = Storage::get($filePath);
                        $encryptedContent = Crypt::encryptString($fileContent);
                        Storage::put($filePath, $encryptedContent);
                    }

                    $validatedData[$field['field']] = $filePath;
                }
            }

            if ($field['type'] === 'select' && isset($field['multiple']) && $field['multiple']) {
                $validatedData[$field['field']] = implode(', ', $validatedData[$field['field']]);
            }

            if ($field['type'] === 'password' && !$request->input($field['field'])) {
                unset($validatedData[$field['field']]);
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

    public function bind(DynamicFormRequest $request, $model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $validatedData = $request->validated();

        $instance->{$externalRelation}()->attach($item, $validatedData);

        $instance->load($instance::getIncludes());

        $this->setRecord($model, $instance->id, 'update');

        return Redirect::back()->with(['success' => 'Elemento vinculado', 'data' => $instance]);
    }

    public function updatePivot(DynamicFormRequest $request, $model, $id, $externalRelation, $item)
    {
        $instance = $this->getModel($model)::findOrFail($id);
        $validatedData = $request->validated();

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
