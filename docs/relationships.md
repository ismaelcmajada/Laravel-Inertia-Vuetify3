---
title: Relationships
description: Handle model relationships with the AutoCrud trait
---

# Relationships

The AutoCrud trait provides comprehensive support for Eloquent relationships, including BelongsTo, MorphTo, HasMany, and BelongsToMany relationships.

## BelongsTo Relationships

Define belongsTo relationships using the `relation` property in field definitions:

```php
[
    'name' => 'User',
    'field' => 'user_id',
    'type' => 'number',
    'relation' => [
        'model' => User::class,
        'relation' => 'user',
        'tableKey' => '{name}',
        'formKey' => '{name}',
    ],
    'table' => true,
    'form' => true,
    'rules' => ['required' => true],
]
```

### Relation Properties

| Property        | Type   | Required | Description                                       |
| --------------- | ------ | -------- | ------------------------------------------------- |
| `model`         | string | Yes      | Full class path to the related model              |
| `relation`      | string | Yes      | Method name (resolved dynamically via `__call`)   |
| `tableKey`      | string | No       | Template for displaying in tables                 |
| `formKey`       | string | No       | Template for displaying in forms                  |
| `polymorphic`   | bool   | No       | Whether this is a morphTo relationship            |
| `morphType`     | string | No       | Type column name (required if polymorphic = true) |
| `serverSide`    | bool   | No       | Whether formKey/tableKey is resolved on backend   |
| `storeShortcut` | bool   | No       | Useful for pivot tables - allows quick creation   |

### Template Keys

Use curly braces to reference related model attributes:

```php
'tableKey' => '{name} - {email}',
'formKey' => '{name}',
```

## MorphTo Relationships

For polymorphic relationships, set `polymorphic` to true and specify the `morphType` column:

```php
[
    'name' => 'Commentable',
    'field' => 'commentable_id',
    'type' => 'number',
    'relation' => [
        'model' => null, // Not needed for morphTo
        'relation' => 'commentable',
        'polymorphic' => true,
        'morphType' => 'commentable_type',
        'tableKey' => '{name}',
        'formKey' => '{name}',
    ],
    'table' => true,
    'form' => true,
]
```

## HasMany Relationships

For one-to-many relationships where the parent manages child records, use `$externalRelations` with `type: 'hasMany'`:

```php
protected static $externalRelations = [
    [
        'type' => 'hasMany',
        'relation' => 'roomtypes',
        'name' => 'Room Types',
        'model' => RoomType::class,
        'foreignKey' => 'hotel_id',
        'tableKey' => '{name}',
        'formKey' => '{name}',
        'table' => true,
    ],
];
```

### HasMany Properties

| Property        | Type   | Required | Description                           |
| --------------- | ------ | -------- | ------------------------------------- |
| `type`          | string | Yes      | Must be `'hasMany'`                   |
| `relation`      | string | Yes      | Relationship method name              |
| `name`          | string | Yes      | Display name                          |
| `model`         | string | Yes      | Related model class                   |
| `foreignKey`    | string | Yes      | Foreign key on the child model        |
| `tableKey`      | string | No       | Template for table display            |
| `formKey`       | string | No       | Template for form display             |
| `table`         | bool   | No       | Show in tables                        |
| `storeShortcut` | bool   | No       | Allow quick creation of child records |

### Complete HasMany Example

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Hotel extends Model
{
    use AutoCrud, SoftDeletes;

    protected $table = 'hotels';

    protected static function getFields(): array
    {
        return [
            [
                'name' => 'Name',
                'field' => 'name',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => ['required' => true, 'unique' => true],
            ],
            [
                'name' => 'Logo',
                'field' => 'logo',
                'type' => 'image',
                'table' => true,
                'form' => true,
            ],
        ];
    }

    protected static $externalRelations = [
        [
            'type' => 'hasMany',
            'relation' => 'roomtypes',
            'name' => 'Room Types',
            'model' => RoomType::class,
            'foreignKey' => 'hotel_id',
            'tableKey' => '{name}',
            'formKey' => '{name}',
            'table' => true,
        ],
        [
            'type' => 'hasMany',
            'relation' => 'extrafields',
            'name' => 'Extra Fields',
            'model' => ExtraField::class,
            'foreignKey' => 'hotel_id',
            'tableKey' => '{name}',
            'formKey' => '{name}',
            'table' => true,
        ],
    ];
}
```

The child records (RoomType, ExtraField) are managed inline within the parent form. The `foreignKey` is automatically set when creating child records.

---

## BelongsToMany Relationships

For complex many-to-many relationships, use the `$externalRelations` property:

```php
protected static $externalRelations = [
    [
        'relation' => 'vehicles',
        'name' => 'Vehículos',
        'model' => Vehicle::class,
        'pivotTable' => 'reservations_vehicles',
        'foreignKey' => 'reservation_id',
        'relatedKey' => 'vehicle_id',
        'pivotModel' => ReservationVehicle::class, // optional
        'pivotFields' => [
            [
                'name' => 'Quantity',
                'field' => 'quantity',
                'type' => 'number',
                'form' => true,
                'rules' => ['required' => true],
            ],
        ],
        'table' => false,
        'formKey' => '{vehicletype.name} ({code})',
    ],
];
```

### External Relation Properties

| Property      | Type   | Required | Description                       |
| ------------- | ------ | -------- | --------------------------------- |
| `relation`    | string | Yes      | Relationship method name          |
| `name`        | string | Yes      | Display name for the relationship |
| `model`       | string | Yes      | Related model class               |
| `pivotTable`  | string | Yes      | Pivot table name                  |
| `foreignKey`  | string | Yes      | Foreign key in pivot table        |
| `relatedKey`  | string | Yes      | Related key in pivot table        |
| `pivotModel`  | string | No       | Pivot model class                 |
| `pivotFields` | array  | No       | Fields for the pivot table        |
| `table`       | bool   | No       | Show in tables                    |
| `formKey`     | string | No       | Display template                  |

## Automatic Relationship Handling

The trait automatically:

1. **Adds endpoints** to each relation and pivot fields
2. **Uses `withPivot()`** with all pivot columns if `pivotFields` are defined
3. **Applies `withTrashed()`** if the related model uses SoftDeletes
4. **Resolves relationships dynamically** via the `__call` method

## Includes Configuration

Control which relationships are always loaded using the `$includes` property:

```php
protected static $includes = [
    'user',
    'category',
    'tags',
];
```

The `getIncludes()` method automatically adds:

- `'records.user'`
- Relationships defined in `getFields()`
- Relationships from `$externalRelations`

## Dynamic Relationship Resolution

The trait uses `__call()` to dynamically resolve relationships:

```php
// This automatically creates the relationship method
public function user()
{
    return $this->belongsTo(User::class);
}

// For external relations
public function vehicles()
{
    return $this->belongsToMany(Vehicle::class, 'reservations_vehicles')
                ->withPivot(['quantity'])
                ->withTrashed();
}
```

## Soft Deletes Support

The trait automatically detects and handles soft deletes:

```php
// If User model uses SoftDeletes, this is automatically applied
public function user()
{
    return $this->belongsTo(User::class)->withTrashed();
}
```

## Complete Example

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Reservation extends Model
{
    use AutoCrud, SoftDeletes;

    protected static function getFields(): array
    {
        return [
            [
                'name' => 'User',
                'field' => 'user_id',
                'type' => 'number',
                'relation' => [
                    'model' => User::class,
                    'relation' => 'user',
                    'tableKey' => '{name} ({email})',
                    'formKey' => '{name}',
                ],
                'table' => true,
                'form' => true,
                'rules' => ['required' => true],
            ],
            [
                'name' => 'Start Date',
                'field' => 'start_date',
                'type' => 'datetime',
                'table' => true,
                'form' => true,
                'rules' => ['required' => true],
            ],
            [
                'name' => 'End Date',
                'field' => 'end_date',
                'type' => 'datetime',
                'table' => true,
                'form' => true,
                'rules' => ['required' => true],
            ],
        ];
    }

    protected static $includes = [
        'user',
    ];

    protected static $externalRelations = [
        [
            'relation' => 'vehicles',
            'name' => 'Vehicles',
            'model' => Vehicle::class,
            'pivotTable' => 'reservations_vehicles',
            'foreignKey' => 'reservation_id',
            'relatedKey' => 'vehicle_id',
            'pivotModel' => ReservationVehicle::class,
            'pivotFields' => [
                [
                    'name' => 'Quantity',
                    'field' => 'quantity',
                    'type' => 'number',
                    'form' => true,
                    'rules' => ['required' => true],
                ],
            ],
            'formKey' => '{name} ({code})',
        ],
    ];
}
```
