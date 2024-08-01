<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; 

abstract class BaseModel extends Model
{
    protected $fields = [];
    protected $externalRelations = [];
    protected $includes = [];
    protected $fillable = [];
    protected $casts = [];
    protected $hidden = [];

    protected static $endPoint;
    protected static $forbiddenActions;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->fields = $this->setFields();
        $this->includes = $this->setIncludes();
        $this->fillable = array_column($this->formFields(), 'field');
        $this->externalRelations = $this->setExternalRelations();

        foreach ($this->fields as &$field) {
            if ($field['type'] === 'number') {
                $this->casts[$field['field']] = 'integer';
            } elseif ($field['type'] === 'boolean') {
                $this->casts[$field['field']] = 'boolean';
            } elseif ($field['type'] === 'password') {
                $this->casts[$field['field']] = 'hashed';
                $this->hidden[] = $field['field'];
            } elseif ($field['type'] === 'date') {
                $this->casts[$field['field']] = 'date:d-m-Y';
            }
            if (isset($field['relation'])) {
                $this->includes[] = $field['relation']['relation'];

                $field['relation']['endPoint'] = $field['relation']['model']::getEndpoint();
            }
        }

        foreach ($this->externalRelations as &$relation) {
            $this->includes[] = $relation['relation'];
            $relation['endPoint'] = $relation['model']::getEndpoint();
        }
    }

    abstract protected function setFields();
    abstract protected function setIncludes();
    abstract protected function setExternalRelations();

    public static function getEndpoint()
    {
        return static::$endPoint;
    }

    public static function getForbiddenActions()
    {
        return static::$forbiddenActions;
    }

    public function externalRelations() 
    {
        return $this->externalRelations;
    }

    public function formFields()
    {
        $formFields = array_filter($this->fields, function ($field) {
            return $field['form'];
        });

        foreach ($this->fields as $field) {
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
        }

        return array_values($formFields);
    }

    public function tableFields()
    {
        $tableFields = array_filter($this->fields, function ($field) {
            return isset($field['table']) && $field['table'];
        });

        return array_values($tableFields);
    }

    public function __call($method, $parameters)
    {
        foreach ($this->fields as $field) {
            if (isset($field['relation']) && $field['relation']['relation'] === $method) {
                return $this->handleRelation($field);
            }
        }

        foreach ($this->externalRelations as $relation) {
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

    public function getModel() {
        return [
            'endPoint' => static::getEndpoint(),
            'formFields' => $this->formFields(),
            'tableHeaders' => $this->tableHeaders(),
            'externalRelations' => $this->externalRelations,
            'includes' => $this->includes,
            'fillable' => $this->fillable,
            'forbiddenActions' => static::getForbiddenActions(),
        ];
    }

    protected function tableHeaders()
    {
        $headers = array_map(function ($field) {
                if (isset($field['relation'])) {
                    if (isset($field['comboField'])) {
                        return [
                            'title' => $field['name'],
                            'sortable' => true,
                            'key' => $field['comboField'],
                            'align' => 'center',
                        ];
                    } else {
                        return [
                            'title' => $field['name'],
                            'sortable' => true,
                            'key' => $field['relation']['relation'].'.'.$field['relation']['tableKey'],
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
        }, $this->tableFields());

        $headers[] = [
            'title' => 'Acciones',
            'key' => 'actions',
            'sortable' => false,
            'align' => 'center',
        ];

        return $headers;
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

        static::restored(function ($model) {
            $model->handleEvent('restored');
        });
    }

    protected function handleEvent($event)
    {
        if (method_exists($this, $event . 'Event')) {
            call_user_func([$this, $event . 'Event']);
        }
    }
}

