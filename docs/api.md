---
title: API Reference
description: Complete API reference for the AutoCrud trait
---

# API Reference

The AutoCrud trait provides a comprehensive set of public methods for interacting with your models programmatically.

## Core Methods

### getFields()
Returns the field definitions for the model.

```php
protected static function getFields(): array
```

**Returns:** Array of field definitions

**Example:**
```php
$fields = User::getFields();
```

### getIncludes()
Returns relationships to be loaded with the model.

```php
public static function getIncludes(): array
```

**Returns:** Array of relationship names

**Automatically includes:**
- `'records.user'`
- Relationships defined in `getFields()`
- Relationships from `$externalRelations`

### getEndpoint()
Returns the API endpoint for the model.

```php
public static function getEndpoint($model = null): string
```

**Parameters:**
- `$model` (optional): Model instance or class name

**Returns:** API endpoint string (e.g., `/laravel-auto-crud/users`)

### getModelName()
Returns the camelCase model name.

```php
public static function getModelName(): string
```

**Returns:** Model name in camelCase with initial lowercase

**Example:**
```php
// For UserProfile model
UserProfile::getModelName(); // returns "userProfile"
```

## Validation Methods

### getCustomRules()
Returns custom validation rules.

```php
public static function getCustomRules(): array
```

**Returns:** Array of custom validation rules

**Default:** Empty array `[]`

### getCustomForbiddenActions()
Returns custom forbidden actions.

```php
public static function getCustomForbiddenActions(): array
```

**Returns:** Array of forbidden actions

**Default:** Empty array `[]`

### getForbiddenActions()
Returns all forbidden actions (including custom ones).

```php
public static function getForbiddenActions(): array
```

**Returns:** Array of forbidden actions by role

## Relationship Methods

### getExternalRelations()
Returns enriched external relationships.

```php
public static function getExternalRelations(): array
```

**Returns:** Array of external relationship definitions with added endpoints

## Form and Table Methods

### getFormFields()
Returns fields that should appear in forms.

```php
public static function getFormFields(): array
```

**Returns:** Array of fields where `form === true`, plus auto-generated `comboField` entries

### getTableFields()
Returns fields that should appear in tables.

```php
public static function getTableFields(): array
```

**Returns:** Array of fields where `table === true`

### getTableHeaders()
Returns calculated table headers.

```php
protected static function getTableHeaders(): array
```

**Returns:** Array of table header definitions

### getModel()
Returns the complete payload for frontend consumption.

```php
public static function getModel($processedModels = []): array
```

**Parameters:**
- `$processedModels`: Array to prevent circular references

**Returns:** Complete model payload including:
- Field definitions
- Validation rules
- Relationships
- Endpoints
- Table headers
- Form fields

## Key Field Methods

### getTableKeyFields()
Returns field definitions for table key placeholders.

```php
public static function getTableKeyFields(): array
```

**Returns:** Array of field definitions used in `tableKey` templates

### getFormKeyFields()
Returns field definitions for form key placeholders.

```php
public static function getFormKeyFields(): array
```

**Returns:** Array of field definitions used in `formKey` templates

## Record Relationship

### records()
Returns the morphMany relationship to Record model.

```php
public function records()
```

**Returns:** MorphMany relationship instance

## Calendar Configuration

### $calendarFields
Static property for calendar field configuration.

```php
protected static $calendarFields = [
    'title' => '({user.name}) - {reservation_place} {return_place}',
    'start' => 'start_date',
    'end' => 'end_date',
    'separateEvents' => true,
    'startClass' => 'start-event-class',
    'endClass' => 'end-event-class',
    'class' => 'default-event-class',
];
```

## Static Properties

### $includes
Always-loaded relationships.

```php
protected static $includes = ['user', 'category'];
```

### $externalRelations
BelongsToMany external relationships.

```php
protected static $externalRelations = [
    [
        'relation' => 'vehicles',
        'name' => 'Vehicles',
        'model' => Vehicle::class,
        'pivotTable' => 'reservations_vehicles',
        'foreignKey' => 'reservation_id',
        'relatedKey' => 'vehicle_id',
        // ... more configuration
    ],
];
```

### $forbiddenActions
Actions forbidden by role.

```php
protected static $forbiddenActions = [
    'user' => ['destroyPermanent', 'restore', 'destroy'],
    'admin' => ['destroyPermanent'],
];
```

## Internal Methods

### __call()
Dynamically resolves relationship methods.

```php
public function __call($method, $parameters)
```

**Handles:**
- BelongsTo relationships from `getFields()`
- MorphTo relationships from `getFields()`
- BelongsToMany relationships from `$externalRelations`

### handleRelation()
Constructs Eloquent relationships from field definitions.

```php
protected function handleRelation($field)
```

### handleExternalRelation()
Constructs BelongsToMany relationships from external relation definitions.

```php
protected function handleExternalRelation($relation)
```

### usesSoftDeletes()
Detects if a model class uses SoftDeletes trait.

```php
protected static function usesSoftDeletes($modelClass): bool
```

## Usage Examples

### Getting Complete Model Data
```php
// Get complete model payload for frontend
$modelData = User::getModel();

// Structure includes:
// - fields: field definitions
// - rules: validation rules
// - relationships: relationship data
// - endpoints: API endpoints
// - tableHeaders: table configuration
// - formFields: form configuration
```

### Working with Relationships
```php
// Get user relationship dynamically
$user = $model->user; // Calls handleRelation() internally

// Get external relationships
$vehicles = $reservation->vehicles; // Calls handleExternalRelation()
```

### Form and Table Integration
```php
// Get fields for form rendering
$formFields = User::getFormFields();

// Get fields for table rendering
$tableFields = User::getTableFields();

// Get table headers
$headers = User::getTableHeaders();
```

### Validation Integration
```php
// Get custom validation rules
$customRules = User::getCustomRules();

// Get forbidden actions for current user role
$forbiddenActions = User::getForbiddenActions();
```

### API Endpoint Generation
```php
// Get model endpoint
$endpoint = User::getEndpoint(); // "/laravel-auto-crud/users"

// Get specific model endpoint
$userEndpoint = User::getEndpoint($userInstance);
```

## Automatic Initialization

The `initializeAutoCrud()` method automatically:

1. **Fills `$fillable`** with all field names from `getFields()`
2. **Builds `$casts`** based on field types
3. **Adds password fields** to `$hidden` array

This happens automatically when the trait is used, so you don't need to manually define these properties for fields covered by `getFields()`.

## Error Handling

The trait includes built-in error handling for:
- Circular relationship references
- Missing relationship models
- Invalid field configurations
- Validation failures

Always wrap API calls in try-catch blocks when using in production:

```php
try {
    $modelData = User::getModel();
} catch (\Exception $e) {
    // Handle error appropriately
    Log::error('Failed to get model data', ['error' => $e->getMessage()]);
}
```
