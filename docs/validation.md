---
title: Validation
description: Custom validation rules and logic in AutoCrud
---

# Validation

The AutoCrud trait provides flexible validation through field rules and custom validation methods.

## Basic Validation Rules

Define validation rules in the `rules` array of each field:

```php
[
    'name' => 'Email',
    'field' => 'email',
    'type' => 'string',
    'rules' => [
        'required' => true,
        'unique' => true,
    ],
    'table' => true,
    'form' => true,
]
```

## Available Rule Types

### Required Validation
```php
'rules' => [
    'required' => true,
]
```

### Unique Validation
```php
'rules' => [
    'unique' => true,
]
```

### Custom Validation
Reference custom rules defined in `getCustomRules()`:

```php
'rules' => [
    'custom' => ['email_validation', 'phone_format'],
]
```

## Custom Validation Rules

Implement the `getCustomRules()` method to define custom validation logic:

```php
public static function getCustomRules(): array
{
    return [
        'email_validation' => function ($attribute, $value, $fail, $request) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $fail('The ' . $attribute . ' must be a valid email address.');
            }
        },
        
        'phone_format' => function ($attribute, $value, $fail, $request) {
            if (!preg_match('/^\+?[1-9]\d{1,14}$/', $value)) {
                $fail('The ' . $attribute . ' must be a valid phone number.');
            }
        },
        
        'password_strength' => function ($attribute, $value, $fail, $request) {
            if (strlen($value) < 8) {
                $fail('The ' . $attribute . ' must be at least 8 characters long.');
            }
            
            if (!preg_match('/[A-Z]/', $value)) {
                $fail('The ' . $attribute . ' must contain at least one uppercase letter.');
            }
            
            if (!preg_match('/[a-z]/', $value)) {
                $fail('The ' . $attribute . ' must contain at least one lowercase letter.');
            }
            
            if (!preg_match('/[0-9]/', $value)) {
                $fail('The ' . $attribute . ' must contain at least one number.');
            }
        },
        
        'date_range' => function ($attribute, $value, $fail, $request) {
            $startDate = $request->getData()['start_date'] ?? null;
            $endDate = $request->getData()['end_date'] ?? null;
            
            if ($startDate && $endDate && $startDate >= $endDate) {
                $fail('The end date must be after the start date.');
            }
        },
    ];
}
```

## Request Data Access

Custom validation functions have access to the full request data through `$request->getData()`:

```php
'business_logic_validation' => function ($attribute, $value, $fail, $request) {
    $data = $request->getData();
    $userId = $data['user_id'] ?? null;
    $amount = $data['amount'] ?? 0;
    
    // Access user's balance or other related data
    $user = User::find($userId);
    if ($user && $user->balance < $amount) {
        $fail('Insufficient balance for this transaction.');
    }
},
```

## Complex Validation Examples

### Cross-Field Validation
```php
'date_consistency' => function ($attribute, $value, $fail, $request) {
    $data = $request->getData();
    $startDate = $data['start_date'] ?? null;
    $endDate = $data['end_date'] ?? null;
    
    if ($startDate && $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        if ($start >= $end) {
            $fail('End date must be after start date.');
        }
        
        $diff = $start->diff($end);
        if ($diff->days > 365) {
            $fail('Date range cannot exceed one year.');
        }
    }
},
```

### Database Validation
```php
'unique_combination' => function ($attribute, $value, $fail, $request) {
    $data = $request->getData();
    $userId = $data['user_id'] ?? null;
    $productId = $data['product_id'] ?? null;
    
    $exists = static::where('user_id', $userId)
                   ->where('product_id', $productId)
                   ->exists();
    
    if ($exists) {
        $fail('This combination already exists.');
    }
},
```

### Conditional Validation
```php
'conditional_required' => function ($attribute, $value, $fail, $request) {
    $data = $request->getData();
    $type = $data['type'] ?? null;
    
    if ($type === 'premium' && empty($value)) {
        $fail('This field is required for premium accounts.');
    }
},
```

## Complete Validation Example

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class User extends Model
{
    use AutoCrud;

    protected static function getFields(): array
    {
        return [
            [
                'name' => 'Name',
                'field' => 'name',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['name_format'],
                ],
            ],
            [
                'name' => 'Email',
                'field' => 'email',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'unique' => true,
                    'custom' => ['email_validation'],
                ],
            ],
            [
                'name' => 'Password',
                'field' => 'password',
                'type' => 'password',
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['password_strength'],
                ],
            ],
            [
                'name' => 'Phone',
                'field' => 'phone',
                'type' => 'telephone',
                'table' => true,
                'form' => true,
                'rules' => [
                    'custom' => ['phone_format'],
                ],
            ],
        ];
    }

    public static function getCustomRules(): array
    {
        return [
            'name_format' => function ($attribute, $value, $fail, $request) {
                if (strlen($value) < 2) {
                    $fail('Name must be at least 2 characters long.');
                }
                
                if (!preg_match('/^[a-zA-Z\s]+$/', $value)) {
                    $fail('Name can only contain letters and spaces.');
                }
            },
            
            'email_validation' => function ($attribute, $value, $fail, $request) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('Please enter a valid email address.');
                }
                
                // Check for disposable email domains
                $disposableDomains = ['tempmail.com', '10minutemail.com'];
                $domain = substr(strrchr($value, "@"), 1);
                
                if (in_array($domain, $disposableDomains)) {
                    $fail('Disposable email addresses are not allowed.');
                }
            },
            
            'password_strength' => function ($attribute, $value, $fail, $request) {
                if (strlen($value) < 8) {
                    $fail('Password must be at least 8 characters long.');
                }
                
                if (!preg_match('/[A-Z]/', $value)) {
                    $fail('Password must contain at least one uppercase letter.');
                }
                
                if (!preg_match('/[a-z]/', $value)) {
                    $fail('Password must contain at least one lowercase letter.');
                }
                
                if (!preg_match('/[0-9]/', $value)) {
                    $fail('Password must contain at least one number.');
                }
                
                if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                    $fail('Password must contain at least one special character.');
                }
            },
            
            'phone_format' => function ($attribute, $value, $fail, $request) {
                if (!empty($value) && !preg_match('/^\+?[1-9]\d{1,14}$/', $value)) {
                    $fail('Please enter a valid phone number.');
                }
            },
        ];
    }
}
```

## Best Practices

1. **Keep validation logic focused**: Each custom rule should validate one specific aspect
2. **Provide clear error messages**: Make error messages user-friendly and specific
3. **Use request data wisely**: Access `$request->getData()` for cross-field validation
4. **Handle edge cases**: Consider null values, empty strings, and type conversions
5. **Performance considerations**: Avoid heavy database queries in validation rules
6. **Reusable rules**: Create generic validation rules that can be used across models
