<?php

namespace App\Http\Controllers\AutoCrud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;


class CalendarController extends Controller
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

    public function loadEvents($model)
    {
        $modelInstance = $this->getModel($model);
        $eventFields = $modelInstance::getCalendarFields();
        $query = $modelInstance::query();

        $query->with($modelInstance::getIncludes());

        $items= $query->whereNotNull($eventFields['start'])->whereNotNull($eventFields['end'])->get();

        $events = $items->map(function ($item) use ($eventFields) {
            $event = [
                'start' => $item->{$eventFields['start']},
                'end' => $item->{$eventFields['end']},
                'title' => $item->{$eventFields['title']},
                'item' => $item,
                'class' => 'cell',
                'drag' => true,
            ];
            
            return $event;
        }); 

        return [
            'eventsData' => [
                'items' => $events,
                
            ]
        ];
    }   
}