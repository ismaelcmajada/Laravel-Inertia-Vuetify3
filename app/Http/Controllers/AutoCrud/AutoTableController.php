<?php

namespace App\Http\Controllers\AutoCrud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;


class AutoTableController extends Controller
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

    public function loadItems($model)
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $modelInstance = $this->getModel($model);
        $mainTable = $modelInstance->getTable();
        $query = $modelInstance::query();

        $query->with($modelInstance::getIncludes());

        if ($deleted && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($modelInstance))) {
            $query->onlyTrashed();
        }

        // Búsquedas
        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    $fieldSearchInfo = $this->getFieldSearchInfo($modelInstance, $key);

                    if ($fieldSearchInfo !== null) {
                        $this->applyDynamicSearch($query, $fieldSearchInfo['relationInfo'], $fieldSearchInfo['searchKey'], $value);
                    }
                }
            }
        }

        // Ordenamiento
        if (!empty($sortBy)) {
            foreach ($sortBy as $sort) {
                if (isset($sort['key']) && isset($sort['order'])) {
                    $key = $sort['key'];
                    $order = $sort['order'];

                    $fieldOrderInfo = $this->getFieldOrderInfo($modelInstance, $key);

                    if ($fieldOrderInfo !== null) {
                        $this->applyDynamicOrder($query, $fieldOrderInfo['relationInfo'], $fieldOrderInfo['orderKey'], $order);
                    } else {
                        // Ordenar por campo directo
                        $query->orderBy($mainTable . '.' . $key, $order);
                    }
                }
            }
        } else {
            $query->orderBy($mainTable . '.id', 'desc');
        }

        // Paginación
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

    private function applyDynamicOrder($query, $relationInfo, $orderKey, $order)
    {
        if (strpos($orderKey, '{') === false) {
            // Es un campo directo
            $fieldParts = explode('.', $orderKey);
            if (count($fieldParts) == 2) {
                // Campo de una relación directa
                $relationName = $fieldParts[0];
                $fieldName = $fieldParts[1];
                $query->leftJoin($relationName, $query->getModel()->getTable() . '.' . $relationName . '_id', '=', $relationName . '.id');
                $query->orderBy($relationName . '.' . $fieldName, $order);
            } else {
                // Campo de la tabla principal
                $query->orderBy($query->getModel()->getTable() . '.' . $orderKey, $order);
            }
        } else {
            preg_match_all('/\{([\w\.]+)\}/', $orderKey, $matches);
            $fields = $matches[1];
            $literals = preg_split('/\{[\w\.]+\}/', $orderKey);

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
                    // Campo dentro de una relación
                    $relationName = $relationInfo['relation'];
                    if (count($fieldParts) == 1) {
                        $fieldAlias = $relationName;
                        $fieldName = $fieldParts[0];
                        $relationPath = $relationName;
                    } else {
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
                        // Campo directo en la tabla principal
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

            // Aplicar la ordenación
            $query->orderByRaw("CONCAT_WS('', $concatString) $order");
        }
    }

    private function getFieldSearchInfo($modelInstance, $key)
    {
        foreach ($modelInstance::getTableFields() as $field) {
            if ($field['field'] === $key) {
                $relationInfo = isset($field['relation']) ? $field['relation'] : null;
                $searchKey = isset($relationInfo['tableKey']) ? $relationInfo['tableKey'] : '{' . $key . '}';
                return [
                    'relationInfo' => $relationInfo,
                    'searchKey' => $searchKey,
                ];
            }
        }

        // Si no se encuentra el campo, devolver null
        return null;
    }

    private function getFieldOrderInfo($modelInstance, $key)
    {
        foreach ($modelInstance::getTableFields() as $field) {
            if ($field['field'] === $key) {
                $relationInfo = isset($field['relation']) ? $field['relation'] : null;
                $orderKey = isset($relationInfo['tableKey']) ? $relationInfo['tableKey'] : '{' . $key . '}';
                return [
                    'relationInfo' => $relationInfo,
                    'orderKey' => $orderKey,
                ];
            }
        }

        // Si no se encuentra el campo, devolver null
        return null;
    }
}
