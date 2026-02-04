---
title: Custom Search Scopes
description: Implement custom search functionality using recognized scopes in AutoCrud
---

# Custom Search Scopes

The AutoCrud trait supports custom search functionality through specially named scopes that the package automatically recognizes and integrates with your models.

## Naming Convention

**Important**: The scope name must follow exactly the pattern `scopeSearchXxx` (starting with `scopeSearch`) for the package to recognize it. This is not just a suggested convention - it's a requirement.

### Pattern Requirements

- Method name must start with `scopeSearch`
- Standard signature: `public function scopeSearchX(Builder $query, $value): Builder`
- Must return the Builder instance for method chaining
- Should handle empty/null values gracefully

## Basic Implementation

```php
use Illuminate\Database\Eloquent\Builder;

public function scopeSearchFoo(Builder $query, $value): Builder
{
    if (blank($value)) {
        return $query; // Don't apply filter if no value
    }
    
    return $query->where('table.column', 'LIKE', "%{$value}%");
}
```

## Real-World Examples

### Search by Related Vehicles

```php
public function scopeSearchVehicles($query, $value)
{
    return $query
        ->select('reservations.*')
        ->join('reservations_vehicles', 'reservations.id', '=', 'reservations_vehicles.reservation_id')
        ->join('vehicles', 'reservations_vehicles.vehicle_id', '=', 'vehicles.id')
        ->where('vehicles.code', 'LIKE', "%{$value}%")
        ->groupBy('reservations.id');
}
```

### Search by Related Complements

```php
public function scopeSearchComplements($query, $value)
{
    return $query
        ->select('reservations.*')
        ->join('reservations_complements', 'reservations.id', '=', 'reservations_complements.reservation_id')
        ->join('complements', 'reservations_complements.complement_id', '=', 'complements.id')
        ->where('complements.name', 'LIKE', "%{$value}%")
        ->groupBy('reservations.id');
}
```

## Usage Examples

### Method Chaining

You can chain multiple search scopes together:

```php
$reservations = Reservation::query()
    ->searchVehicles($request->input('vehicles'))
    ->searchComplements($request->input('complements'))
    ->get();
```

### Controller Implementation

```php
public function index(Request $request)
{
    $query = Reservation::query();
    
    // Apply vehicle search if provided
    if ($vehicles = $request->get('vehicles')) {
        $query->searchVehicles($vehicles);
    }
    
    // Apply complements search if provided
    if ($complements = $request->get('complements')) {
        $query->searchComplements($complements);
    }
    
    return $query->paginate();
}
```

### Service Layer Implementation

```php
class ReservationService
{
    public function search(array $filters)
    {
        $query = Reservation::query();
        
        foreach ($filters as $key => $value) {
            if (blank($value)) continue;
            
            $method = 'search' . ucfirst($key);
            if (method_exists(Reservation::class, 'scope' . ucfirst($method))) {
                $query->$method($value);
            }
        }
        
        return $query->paginate();
    }
}
```

## Advanced Examples

### Search with Multiple Conditions

```php
public function scopeSearchUserDetails($query, $value)
{
    if (blank($value)) {
        return $query;
    }
    
    return $query->whereHas('user', function ($userQuery) use ($value) {
        $userQuery->where('name', 'LIKE', "%{$value}%")
                  ->orWhere('email', 'LIKE', "%{$value}%")
                  ->orWhere('phone', 'LIKE', "%{$value}%");
    });
}
```

### Search with Date Ranges

```php
public function scopeSearchDateRange($query, $value)
{
    if (blank($value) || !is_array($value)) {
        return $query;
    }
    
    if (isset($value['start'])) {
        $query->where('created_at', '>=', $value['start']);
    }
    
    if (isset($value['end'])) {
        $query->where('created_at', '<=', $value['end']);
    }
    
    return $query;
}
```

### Search with JSON Fields

```php
public function scopeSearchMetadata($query, $value)
{
    if (blank($value)) {
        return $query;
    }
    
    return $query->whereRaw("JSON_SEARCH(metadata, 'one', ?) IS NOT NULL", ["%{$value}%"]);
}
```

### Search with Full-Text Search

```php
public function scopeSearchFullText($query, $value)
{
    if (blank($value)) {
        return $query;
    }
    
    return $query->whereRaw(
        "MATCH(title, description, content) AGAINST(? IN BOOLEAN MODE)",
        [$value]
    );
}
```

## Complete Model Example

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
                    'tableKey' => '{name}',
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

    // Custom Search Scopes
    public function scopeSearchVehicles(Builder $query, $value): Builder
    {
        if (blank($value)) {
            return $query;
        }
        
        return $query
            ->select('reservations.*')
            ->join('reservations_vehicles', 'reservations.id', '=', 'reservations_vehicles.reservation_id')
            ->join('vehicles', 'reservations_vehicles.vehicle_id', '=', 'vehicles.id')
            ->where('vehicles.code', 'LIKE', "%{$value}%")
            ->groupBy('reservations.id');
    }

    public function scopeSearchComplements(Builder $query, $value): Builder
    {
        if (blank($value)) {
            return $query;
        }
        
        return $query
            ->select('reservations.*')
            ->join('reservations_complements', 'reservations.id', '=', 'reservations_complements.reservation_id')
            ->join('complements', 'reservations_complements.complement_id', '=', 'complements.id')
            ->where('complements.name', 'LIKE', "%{$value}%")
            ->groupBy('reservations.id');
    }

    public function scopeSearchUserDetails(Builder $query, $value): Builder
    {
        if (blank($value)) {
            return $query;
        }
        
        return $query->whereHas('user', function ($userQuery) use ($value) {
            $userQuery->where('name', 'LIKE', "%{$value}%")
                      ->orWhere('email', 'LIKE', "%{$value}%");
        });
    }

    public function scopeSearchStatus(Builder $query, $value): Builder
    {
        if (blank($value)) {
            return $query;
        }
        
        return $query->where('status', $value);
    }

    public function scopeSearchDateRange(Builder $query, $value): Builder
    {
        if (blank($value) || !is_array($value)) {
            return $query;
        }
        
        if (isset($value['start'])) {
            $query->where('start_date', '>=', $value['start']);
        }
        
        if (isset($value['end'])) {
            $query->where('end_date', '<=', $value['end']);
        }
        
        return $query;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'reservations_vehicles')
                    ->withPivot(['quantity', 'daily_rate']);
    }

    public function complements()
    {
        return $this->belongsToMany(Complement::class, 'reservations_complements')
                    ->withPivot(['quantity']);
    }
}
```

## API Integration

When using these custom search scopes, they integrate seamlessly with the AutoCrud API endpoints:

```javascript
// Frontend usage
const searchReservations = async (filters) => {
    const params = new URLSearchParams();
    
    if (filters.vehicles) params.append('vehicles', filters.vehicles);
    if (filters.complements) params.append('complements', filters.complements);
    if (filters.userDetails) params.append('userDetails', filters.userDetails);
    
    const response = await fetch(`/laravel-auto-crud/reservations?${params}`);
    return response.json();
};
```

## Best Practices

1. **Always check for empty values**: Use `blank($value)` to avoid unnecessary queries
2. **Return the Builder**: Always return the query builder for method chaining
3. **Use proper joins**: When joining tables, select specific columns to avoid conflicts
4. **Group results**: Use `groupBy()` when joining multiple related tables
5. **Handle arrays**: Check if the value is an array when expecting complex search parameters
6. **Optimize queries**: Consider adding database indexes for frequently searched columns
7. **Validate input**: Sanitize and validate search parameters in your controllers

## Important Notes

- The AutoCrud trait doesn't automatically register these scopes
- These scopes complement the automatic filtering provided by the package
- Scopes can be used independently or combined with other AutoCrud features
- Always test your custom scopes with various input scenarios
- Consider performance implications when joining multiple tables
