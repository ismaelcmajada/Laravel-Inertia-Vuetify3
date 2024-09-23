<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class BaseModel extends Model
{


    protected $fillable = [];
    protected $casts = [];
    protected $hidden = [];

    protected static $includes;
    protected static $fields;
    protected static $externalRelations;
    protected static $endPoint;
    protected static $forbiddenActions;


    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_column(static::getFormFields(), 'field');

        foreach (static::$fields as $field) {
            if ($field['type'] === 'number' && isset($field['relation'])) {
                $this->casts[$field['field']] = 'integer';
            } elseif ($field['type'] === 'number') {
                $this->casts[$field['field']] = 'string';
            } elseif ($field['type'] === 'boolean') {
                $this->casts[$field['field']] = 'boolean';
            } elseif ($field['type'] === 'password') {
                $this->casts[$field['field']] = 'hashed';
                $this->hidden[] = $field['field'];
            } elseif ($field['type'] === 'date') {
                $this->casts[$field['field']] = 'date:d-m-Y';
            } else if ($field['type'] === 'telephone') {
                $this->casts[$field['field']] = 'string';
            }
        }
    }

    public static function getIncludes()
    {

        static::$includes[] = 'records.user';

        foreach (static::$fields as $field) {
            if (isset($field['relation'])) {
                static::$includes[] = $field['relation']['relation'];
            }
        }

        foreach (static::$externalRelations as $relation) {
            static::$includes[] = $relation['relation'];
        }

        return static::$includes;
    }

    public static function getEndpoint()
    {
        return static::$endPoint;
    }

    public static function getForbiddenActions()
    {
        return static::$forbiddenActions;
    }

    public static function getExternalRelations()
    {

        foreach (static::$externalRelations as &$relation) {

            $relation['endPoint'] = $relation['model']::getEndpoint();
        }

        return static::$externalRelations;
    }

    public static function getFormFields()
    {


        $formFields = array_filter(static::$fields, function ($field) {
            return $field['form'];
        });


        foreach (static::$fields as $key => $field) {
            if (isset($field['comboField'])) {
                $formFields[$field['comboField']] = [
                    'field' => $field['comboField'],
                    'type' => 'string',
                    'table' => false,
                    'form' => true,
                    'hidden' => true,
                    'rules' => [
                        'required' => true
                    ]
                ];
            }

            if (isset($field['relation']) && (!isset($field['relation']['polymorphic']) || !$field['relation']['polymorphic'])) {
                $formFields[$key]['relation']['endPoint'] = $field['relation']['model']::getEndpoint();
            }
        }

        return array_values($formFields);
    }

    public static function getTableFields()
    {
        $tableFields = array_filter(static::$fields, function ($field) {
            return isset($field['table']) && $field['table'];
        });

        return array_values($tableFields);
    }

    public function __call($method, $parameters)
    {
        foreach (static::$fields as $field) {
            if (isset($field['relation']) && isset($field['relation']['relation']) && $field['relation']['relation'] === $method) {
                if (isset($field['relation']['polymorphic']) && $field['relation']['polymorphic'] && $field['relation']['relation'] === $method) {
                    return $this->morphTo($field['relation']['relation'], $field['relation']['morphType'], $field['field'])->withTrashed();
                } else {
                    return $this->handleRelation($field);
                }
            }
        }

        foreach (static::getExternalRelations() as $relation) {
            if (isset($relation['relation']) && $relation['relation'] === $method) {
                return $this->handleExternalRelation($relation);
            }
        }

        return parent::__call($method, $parameters);
    }

    protected function handleRelation($field)
    {
        $relatedModelClass = $field['relation']['model'];
        if (!class_exists($relatedModelClass)) {
            throw new \Exception("Modelo relacionado {$relatedModelClass} no existe");
        }

        return $this->belongsTo($relatedModelClass, $field['field'])->withTrashed();
    }

    protected function handleExternalRelation($relation)
    {
        $relatedModelClass = $relation['model'];
        if (!class_exists($relatedModelClass)) {
            throw new \Exception("Modelo relacionado {$relatedModelClass} no existe");
        }

        $relatedPivotModelClass = $relation['pivotModel'] ?? null;
        if (class_exists($relatedPivotModelClass)) {
            $relationMethod = $this->belongsToMany($relatedModelClass, $relation['pivotTable'], $relation['foreignKey'], $relation['relatedKey'])->using($relatedPivotModelClass)->withTrashed();
        } else {
            $relationMethod = $this->belongsToMany($relatedModelClass, $relation['pivotTable'], $relation['foreignKey'], $relation['relatedKey'])->withTrashed();
        }

        if (isset($relation['pivotFields'])) {
            $tableFields = Schema::getColumnListing($relation['pivotTable']);

            $relationMethod->withPivot($tableFields);
        }

        return $relationMethod;
    }

    public static function getModel($processedModels = [])
    {
        return [
            'endPoint' => static::getEndpoint(),
            'formFields' => static::getFormFields($processedModels),
            'tableHeaders' => static::getTableHeaders(),
            'externalRelations' => static::getExternalRelations($processedModels),
            'forbiddenActions' => static::getForbiddenActions(),
        ];
    }

    protected static function getTableHeaders()
    {
        $headers = array_map(function ($field) {

            if (isset($field['relation'])) {
                if (!isset($field['relation']['polymorphic']) || !$field['relation']['polymorphic']) {
                    return [
                        'title' => $field['name'],
                        'sortable' => true,
                        'key' => $field['field'],
                        'relation' => $field['relation'],
                        'align' => 'center',
                    ];
                }
            }
            return [
                'title' => $field['name'],
                'sortable' => true,
                'key' => $field['field'],
                'align' => 'center',
            ];
        }, static::getTableFields());

        $headers[] = [
            'title' => 'Acciones',
            'key' => 'actions',
            'sortable' => false,
            'align' => 'center',
        ];

        return $headers;
    }

    public static function getTableKeyFields()
    {
        $tableKeyFields = [];

        foreach (static::$fields as $field) {
            if (isset($field['relation']) && isset($field['relation']['tableKey'])) {
                $tableKey = $field['relation']['tableKey'];
                preg_match_all('/\{([\w\.]+)\}/', $tableKey, $matches);
                $fields = $matches[1];
                $tableKeyFields[$field['field']] = [
                    'relation' => $field['relation']['relation'],
                    'fields' => $fields,
                    'literals' => preg_split('/\{[\w\.]+\}/', $tableKey),
                    'tableKey' => $tableKey
                ];
            }
        }

        return $tableKeyFields;
    }

    public static function getFormKeyFields()
    {
        $formKeyFields = [];

        foreach (static::$fields as $field) {
            if (isset($field['relation']) && isset($field['relation']['formKey'])) {
                $formKey = $field['relation']['formKey'];
                preg_match_all('/\{(\w+)\}/', $formKey, $matches);
                $fields = $matches[1];
                $formKeyFields[$field['field']] = [
                    'relation' => $field['relation']['relation'],
                    'fields' => $fields,
                    'literals' => preg_split('/\{\w+\}/', $formKey),
                    'formKey' => $formKey
                ];
            }
        }

        return $formKeyFields;
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->handleEvent('creating');
        });

        static::created(function ($model) {
            $model->handleEvent('created');
        });

        static::updating(function ($model) {
            $model->handleEvent('updating');
        });

        static::updated(function ($model) {
            $model->handleEvent('updated');
        });

        static::deleting(function ($model) {
            $model->handleEvent('deleting');
        });

        static::deleted(function ($model) {
            $model->handleEvent('deleted');
        });

        static::saving(function ($model) {
            $model->handleEvent('saving');
        });

        static::saved(function ($model) {
            $model->handleEvent('saved');
        });
    }

    protected function handleEvent($event)
    {
        if (method_exists($this, $event . 'Event')) {
            call_user_func([$this, $event . 'Event']);
        }
    }

    public function records()
    {
        return $this->morphMany(Record::class, 'recordable', 'model', 'element_id');
    }
}
