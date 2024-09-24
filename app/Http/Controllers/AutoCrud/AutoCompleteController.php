<?php

namespace App\Http\Controllers\AutoCrud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class AutoCompleteController extends Controller
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

    public function getAll($model)
    {
        $modelInstance = $this->getModel($model);
        $query = $modelInstance::query();

        $query->with($modelInstance::getIncludes());

        return $query->get();
    }

    public function loadAutocompleteItems($model)
    {
        $search = Request::get('search', '');
        $keyField = Request::get('key', null);

        $modelInstance = $this->getModel($model);
        $mainTable = $modelInstance->getTable();
        $query = $modelInstance::query();

        $query->select("{$mainTable}.*");

        $query->with($modelInstance::getIncludes());

        $formKeyInfo = $this->getFormKeyInfo($modelInstance, $keyField);

        $relationInfo = $formKeyInfo['relationInfo'];
        $formKey = $formKeyInfo['formKey'];

        if (!empty($search)) {
            $this->applyDynamicSearch($query, $relationInfo, $formKey, $search);
        }


        $items = $query->limit(6)->get();


        return ['autocompleteItems' => $items];
    }

    private function applyDynamicSearch($query, $relationInfo, $searchKey, $value)
    {
        if (strpos($searchKey, '{') === false) {
            // Es un campo directo
            $fieldParts = explode('.', $searchKey);
            if (count($fieldParts) == 2) {
                // Campo de una relación directa
                $relationName = $fieldParts[0];
                $fieldName = $fieldParts[1];
                $query->whereHas($relationName, function ($q) use ($fieldName, $value) {
                    $q->where($fieldName, 'LIKE', '%' . $value . '%');
                });
            } else {
                // Campo de la tabla principal
                $query->where($query->getModel()->getTable() . '.' . $searchKey, 'LIKE', '%' . $value . '%');
            }
        } else {

            preg_match_all('/\{([\w\.]+)\}/', $searchKey, $matches);
            $fields = $matches[1];
            $literals = preg_split('/\{[\w\.]+\}/', $searchKey);

            $concatString = "";
            $usedRelations = [];
            $modelInstance = $query->getModel();
            $mainTable = $modelInstance->getTable();

            foreach ($fields as $index => $field) {
                if (isset($literals[$index])) {
                    $concatString .= "'" . $literals[$index] . "', ";
                }

                $fieldParts = explode('.', $field);

                if ($relationInfo !== null) {
                    // Estamos en el contexto de una relación
                    $relationName = $relationInfo['relation'];
                    if (count($fieldParts) == 1) {
                        // Campo de la relación actual
                        $fieldAlias = $relationName;
                        $fieldName = $fieldParts[0];
                        $relationPath = $relationName;
                    } else {
                        // Campo de una relación anidada
                        $relationPath = $relationName . '.' . implode('.', array_slice($fieldParts, 0, -1));
                        $fieldName = end($fieldParts);
                        $fieldAlias = str_replace('.', '_', $relationPath);
                    }
                } else {
                    if (count($fieldParts) > 1) {
                        // Campo de una relación anidada
                        $relationPath = implode('.', array_slice($fieldParts, 0, -1));
                        $fieldName = end($fieldParts);
                        $fieldAlias = str_replace('.', '_', $relationPath);
                    } else {
                        // Campo de la tabla principal
                        $fieldName = $fieldParts[0];
                        $fieldAlias = $mainTable;
                        $relationPath = null;
                    }
                }

                $concatString .= "IFNULL(`{$fieldAlias}`.`{$fieldName}`, ''), ";

                if ($relationPath !== null) {
                    $usedRelations[$relationPath] = true;
                }
            }

            if (isset($literals[count($fields)])) {
                $concatString .= "'" . $literals[count($fields)] . "'";
            } else {
                $concatString = rtrim($concatString, ', ');
            }

            // Construir los JOINs necesarios sin duplicados
            $addedJoins = [];
            foreach (array_keys($usedRelations) as $relationPath) {
                $relations = explode('.', $relationPath);
                $previousAlias = $mainTable;
                $relationModel = $modelInstance;

                foreach ($relations as $index => $relation) {
                    $alias = implode('_', array_slice($relations, 0, $index + 1));

                    // Verificar si ya se ha agregado este alias
                    if (in_array($alias, $addedJoins)) {
                        $previousAlias = $alias;
                        $relationModel = $relationModel->$relation()->getRelated();
                        continue;
                    }

                    $relationMethod = $relationModel->$relation();

                    $relatedTable = $relationMethod->getRelated()->getTable();
                    $foreignKey = $relationMethod->getForeignKeyName();
                    $ownerKey = $relationMethod->getOwnerKeyName();

                    $query->leftJoin("{$relatedTable} as {$alias}", "{$previousAlias}.{$foreignKey}", '=', "{$alias}.{$ownerKey}");

                    $previousAlias = $alias;
                    $relationModel = $relationMethod->getRelated();

                    // Registrar que este alias ya ha sido agregado
                    $addedJoins[] = $alias;
                }
            }

            $searchWords = explode(' ', $value);
            foreach ($searchWords as $word) {
                $query->whereRaw("CONCAT_WS('', $concatString) LIKE ?", ["%{$word}%"]);
            }
        }
    }

    private function getFormKeyInfo($modelInstance, $keyField = null)
    {
        $formKeyFields = $modelInstance::getFormKeyFields();

        if ($keyField !== null) {
            // Buscar en los campos de formulario para encontrar el campo correspondiente
            if (isset($formKeyFields[$keyField])) {
                $relationInfo = $formKeyFields[$keyField];
                $formKey = $relationInfo['formKey'];
                return [
                    'relationInfo' => $relationInfo,
                    'formKey' => $formKey,
                ];
            } else {
                // Si no hay un formKey definido para este campo, usar el campo directo
                return [
                    'relationInfo' => null,
                    'formKey' => '{' . $keyField . '}',
                ];
            }
        } elseif (!empty($formKeyFields)) {
            // Obtener el primer formKey definido en el modelo
            $firstField = array_keys($formKeyFields)[0];
            $relationInfo = $formKeyFields[$firstField];
            $formKey = $relationInfo['formKey'];
            return [
                'relationInfo' => $relationInfo,
                'formKey' => $formKey,
            ];
        } else {
            // Si no hay formKey, usar un key por defecto
            return [
                'relationInfo' => null,
                'formKey' => '{id}',
            ];
        }
    }
}
