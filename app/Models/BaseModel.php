<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    protected $endPoint;
    protected $fields = [];
    protected $externalRelations = [];
    protected $includes = [];
    protected $fillable = [];
    protected $casts = [];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->endPoint = $this->setEndPoint();
        $this->fields = $this->setFields();
        $this->includes = $this->setIncludes();
        $this->fillable = array_column($this->formFields(), 'field');

        foreach ($this->fields as $field) {
            if ($field['type'] === 'number') {
                $this->casts[$field['field']] = 'integer';
            } elseif ($field['type'] === 'boolean') {
                $this->casts[$field['field']] = 'boolean';
            }
            if (isset($field['relation'])) {
                $this->includes[] = $field['relation']['relation'];
            }
        }
    }

    abstract protected function setEndPoint();
    abstract protected function setFields();
    abstract protected function setIncludes();

    public function formFields()
    {
        $formFields = array_filter($this->fields, function ($field) {
            return $field['form'];
        });

        foreach ($formFields as &$field) {
            if (isset($field['relation'])) {
                $relatedModelClass = 'App\\Models\\' . $field['relation']['model'];
                $relatedModel = new $relatedModelClass;
                $field['relation']['endPoint'] = $relatedModel->getModel()['endPoint'];
            }
        }

        return array_values($formFields);
    }

    public function __call($method, $parameters)
    {
        foreach ($this->fields as $field) {
            if (isset($field['relation']) && $field['relation']['relation'] === $method) {
                return $this->handleRelation($field);
            }
        }

        return parent::__call($method, $parameters);
    }

    protected function handleRelation($field)
    {
        $relatedModelClass = 'App\\Models\\' . $field['relation']['model'];
        if (!class_exists($relatedModelClass)) {
            throw new \Exception("Modelo relacionado {$relatedModelClass} no existe");
        }

        switch ($field['relation']['type']) {
            case 'belongsTo':
                return $this->belongsTo($relatedModelClass, $field['field']);
            case 'hasMany':
                return $this->hasMany($relatedModelClass, $field['field']);
            case 'belongsToMany':
                return $this->belongsToMany($relatedModelClass, $field['relation']['pivotTable'], $field['relation']['foreignKey'], $field['relation']['relatedKey']);
        }
    }

    public function getModel() {
        return [
            'endPoint' => $this->endPoint,
            'formFields' => $this->formFields(),
            'tableHeaders' => $this->tableHeaders(),
            'externalRelations' => $this->externalRelations,
            'includes' => $this->includes,
            'fillable' => $this->fillable,
        ];
    }

    protected function tableHeaders()
    {
        $headers = array_map(function ($field) {
            if (isset($field['relation'])) {
                return [
                    'title' => $field['name'],
                    'sortable' => true,
                    'key' => $field['relation']['relation'].'.'.$field['relation']['tableKey'],
                    'align' => 'center',
                ];
            }
            return [
                'title' => $field['name'],
                'sortable' => true,
                'key' => $field['field'],
                'align' => 'center',
            ];
        }, $this->fields);

        $headers[] = [
            'title' => 'Acciones',
            'key' => 'actions',
            'sortable' => false,
            'align' => 'center',
        ];

        return $headers;
    }
}
