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
        $this->fillable =  array_column($this->formFields(), 'field');


        foreach ($this->fields as $field) {
            if ($field['type'] === 'number') {
                $this->casts[$field['field']] = 'integer';
            } elseif ($field['type'] === 'boolean') {
                $this->casts[$field['field']] = 'boolean';
            }
        }
    }

    abstract protected function setEndPoint();
    abstract protected function setFields();

    public function formFields()
    {
        $filteredFields = array_filter($this->fields, function ($field) {
            return $field['form'];
        });

        return array_values($filteredFields);
    }

    public function tableHeaders()
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
        }, array_filter($this->fields, function ($field) {
            return $field['table'];
        }));

        $headers[] = [
            'title' => 'Acciones',
            'key' => 'actions',
            'sortable' => false,
            'align' => 'center',
        ];

        return $headers;
    }

    //Return has json
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
}
