---
title: Examples
description: Real-world implementation examples of the AutoCrud trait
---

# Examples

This section provides complete, real-world examples of implementing the AutoCrud trait in various scenarios.

## Basic Model Example

A simple product model with basic fields and validation:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Product extends Model
{
    use AutoCrud, SoftDeletes;

    protected $table = 'products';

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
                    'unique' => true,
                ],
            ],
            [
                'name' => 'Description',
                'field' => 'description',
                'type' => 'text',
                'form' => true,
            ],
            [
                'name' => 'Price',
                'field' => 'price',
                'type' => 'decimal',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['positive_number'],
                ],
            ],
            [
                'name' => 'Stock',
                'field' => 'stock',
                'type' => 'number',
                'table' => true,
                'form' => true,
                'default' => 0,
            ],
            [
                'name' => 'Active',
                'field' => 'is_active',
                'type' => 'boolean',
                'table' => true,
                'form' => true,
                'default' => true,
            ],
        ];
    }

    public static function getCustomRules(): array
    {
        return [
            'positive_number' => function ($attribute, $value, $fail, $request) {
                if ($value <= 0) {
                    $fail('The ' . $attribute . ' must be a positive number.');
                }
            },
        ];
    }

    protected function creatingEvent(): void
    {
        $this->sku = $this->generateSKU();
    }

    private function generateSKU(): string
    {
        return 'PRD-' . strtoupper(substr($this->name, 0, 3)) . '-' . time();
    }
}
```

## Complex Model with Relationships

A reservation model with multiple relationships and external relations:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\ReservationVehicle;

class Reservation extends Model
{
    use AutoCrud, SoftDeletes;

    protected $table = 'reservations';

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
                'name' => 'Reservation Place',
                'field' => 'reservation_place',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => ['required' => true],
            ],
            [
                'name' => 'Return Place',
                'field' => 'return_place',
                'type' => 'string',
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
                'rules' => [
                    'required' => true,
                    'custom' => ['future_date', 'date_range'],
                ],
            ],
            [
                'name' => 'End Date',
                'field' => 'end_date',
                'type' => 'datetime',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['date_range'],
                ],
            ],
            [
                'name' => 'Status',
                'field' => 'status',
                'type' => 'select',
                'options' => [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'active' => 'Active',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ],
                'table' => true,
                'form' => true,
                'default' => 'pending',
            ],
            [
                'name' => 'Total Amount',
                'field' => 'total_amount',
                'type' => 'decimal',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['positive_number'],
                ],
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
                    'rules' => [
                        'required' => true,
                        'custom' => ['positive_integer'],
                    ],
                ],
                [
                    'name' => 'Daily Rate',
                    'field' => 'daily_rate',
                    'type' => 'decimal',
                    'form' => true,
                    'rules' => [
                        'required' => true,
                        'custom' => ['positive_number'],
                    ],
                ],
            ],
            'table' => false,
            'formKey' => '{vehicletype.name} ({code})',
        ],
    ];

    protected static $calendarFields = [
        'title' => '({user.name}) - {reservation_place} {return_place}',
        'start' => 'start_date',
        'end' => 'end_date',
        'separateEvents' => true,
        'startClass' => 'start-event-class',
        'endClass' => 'end-event-class',
        'class' => 'default-event-class',
    ];

    protected static $forbiddenActions = [
        'user' => ['destroyPermanent', 'restore'],
    ];

    public static function getCustomRules(): array
    {
        return [
            'future_date' => function ($attribute, $value, $fail, $request) {
                if (strtotime($value) <= time()) {
                    $fail('The ' . $attribute . ' must be a future date.');
                }
            },
            
            'date_range' => function ($attribute, $value, $fail, $request) {
                $data = $request->getData();
                $startDate = $data['start_date'] ?? null;
                $endDate = $data['end_date'] ?? null;
                
                if ($startDate && $endDate && strtotime($startDate) >= strtotime($endDate)) {
                    $fail('The end date must be after the start date.');
                }
            },
            
            'positive_number' => function ($attribute, $value, $fail, $request) {
                if ($value <= 0) {
                    $fail('The ' . $attribute . ' must be a positive number.');
                }
            },
            
            'positive_integer' => function ($attribute, $value, $fail, $request) {
                if (!is_int($value) || $value <= 0) {
                    $fail('The ' . $attribute . ' must be a positive integer.');
                }
            },
        ];
    }

    // Event Hooks
    protected function creatingEvent(): void
    {
        $this->reservation_number = $this->generateReservationNumber();
        $this->created_by = auth()->id();
    }

    protected function createdEvent(): void
    {
        // Send confirmation email
        Mail::to($this->user->email)->send(new ReservationConfirmationMail($this));
        
        // Update user statistics
        $this->user->increment('total_reservations');
    }

    protected function updatingEvent(): void
    {
        if ($this->isDirty('status')) {
            $this->status_changed_at = now();
            $this->status_changed_by = auth()->id();
        }
    }

    protected function updatedEvent(): void
    {
        if ($this->wasChanged('status')) {
            $this->handleStatusChange();
        }
    }

    private function generateReservationNumber(): string
    {
        return 'RES-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    private function handleStatusChange(): void
    {
        switch ($this->status) {
            case 'confirmed':
                Mail::to($this->user->email)->send(new ReservationConfirmedMail($this));
                break;
            case 'cancelled':
                Mail::to($this->user->email)->send(new ReservationCancelledMail($this));
                break;
        }
    }
}
```

## User Management Model

A comprehensive user model with authentication and profile management:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class User extends Authenticatable
{
    use HasFactory, Notifiable, AutoCrud, SoftDeletes;

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
                'onlyUpdate' => false,
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
            [
                'name' => 'Birth Date',
                'field' => 'birth_date',
                'type' => 'date',
                'form' => true,
                'rules' => [
                    'custom' => ['valid_age'],
                ],
            ],
            [
                'name' => 'Role',
                'field' => 'role',
                'type' => 'select',
                'options' => [
                    'user' => 'User',
                    'admin' => 'Administrator',
                    'moderator' => 'Moderator',
                ],
                'table' => true,
                'form' => true,
                'default' => 'user',
            ],
            [
                'name' => 'Active',
                'field' => 'is_active',
                'type' => 'boolean',
                'table' => true,
                'form' => true,
                'default' => true,
            ],
            [
                'name' => 'Email Verified',
                'field' => 'email_verified_at',
                'type' => 'datetime',
                'table' => true,
                'form' => false,
            ],
        ];
    }

    protected static $forbiddenActions = [
        'user' => ['destroy', 'destroyPermanent', 'restore'],
        'moderator' => ['destroyPermanent'],
    ];

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
            
            'valid_age' => function ($attribute, $value, $fail, $request) {
                if (!empty($value)) {
                    $birthDate = new \DateTime($value);
                    $today = new \DateTime();
                    $age = $today->diff($birthDate)->y;
                    
                    if ($age < 13) {
                        $fail('User must be at least 13 years old.');
                    }
                    
                    if ($age > 120) {
                        $fail('Please enter a valid birth date.');
                    }
                }
            },
        ];
    }

    // Event Hooks
    protected function creatingEvent(): void
    {
        // Generate unique username if not provided
        if (empty($this->username)) {
            $this->username = $this->generateUsername();
        }
        
        // Set default avatar
        $this->avatar = $this->generateDefaultAvatar();
    }

    protected function createdEvent(): void
    {
        // Send welcome email
        Mail::to($this->email)->send(new WelcomeUserMail($this));
        
        // Create user profile
        $this->profile()->create([
            'bio' => '',
            'preferences' => json_encode([]),
        ]);
    }

    protected function updatingEvent(): void
    {
        // Hash password if changed
        if ($this->isDirty('password')) {
            $this->password = bcrypt($this->password);
        }
        
        // Update email verification if email changed
        if ($this->isDirty('email')) {
            $this->email_verified_at = null;
        }
    }

    protected function updatedEvent(): void
    {
        // Send email verification if email changed
        if ($this->wasChanged('email')) {
            $this->sendEmailVerificationNotification();
        }
        
        // Log role changes
        if ($this->wasChanged('role')) {
            Log::info('User role changed', [
                'user_id' => $this->id,
                'old_role' => $this->getOriginal('role'),
                'new_role' => $this->role,
                'changed_by' => auth()->id(),
            ]);
        }
    }

    // Helper methods
    private function generateUsername(): string
    {
        $base = strtolower(str_replace(' ', '', $this->name));
        $username = $base;
        $counter = 1;
        
        while (static::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }

    private function generateDefaultAvatar(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    // Relationships
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
```

## Polymorphic Model Example

A comment model that can be attached to multiple types of content:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;

class Comment extends Model
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
                'name' => 'Content',
                'field' => 'content',
                'type' => 'text',
                'table' => true,
                'form' => true,
                'rules' => [
                    'required' => true,
                    'custom' => ['content_length'],
                ],
            ],
            [
                'name' => 'Commentable',
                'field' => 'commentable_id',
                'type' => 'number',
                'relation' => [
                    'relation' => 'commentable',
                    'polymorphic' => true,
                    'morphType' => 'commentable_type',
                    'tableKey' => '{title}',
                    'formKey' => '{title}',
                ],
                'table' => true,
                'form' => true,
                'rules' => ['required' => true],
            ],
            [
                'name' => 'Status',
                'field' => 'status',
                'type' => 'select',
                'options' => [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ],
                'table' => true,
                'form' => true,
                'default' => 'pending',
            ],
        ];
    }

    protected static $includes = [
        'user',
        'commentable',
    ];

    public static function getCustomRules(): array
    {
        return [
            'content_length' => function ($attribute, $value, $fail, $request) {
                if (strlen($value) < 10) {
                    $fail('Comment must be at least 10 characters long.');
                }
                
                if (strlen($value) > 1000) {
                    $fail('Comment cannot exceed 1000 characters.');
                }
            },
        ];
    }

    protected function creatingEvent(): void
    {
        $this->created_by = auth()->id();
        
        // Auto-approve comments from trusted users
        if ($this->user && $this->user->is_trusted) {
            $this->status = 'approved';
        }
    }

    protected function createdEvent(): void
    {
        // Notify content owner
        if ($this->commentable && $this->commentable->user) {
            Mail::to($this->commentable->user->email)
                ->send(new NewCommentNotification($this));
        }
    }

    // Polymorphic relationship
    public function commentable()
    {
        return $this->morphTo();
    }
}
```

These examples demonstrate various aspects of the AutoCrud trait:

- **Basic CRUD operations** with validation
- **Complex relationships** including polymorphic and many-to-many
- **Custom validation rules** with business logic
- **Event hooks** for lifecycle management
- **Role-based permissions** with forbidden actions
- **Calendar integration** for date-based models
- **Real-world scenarios** like user management, reservations, and comments

Each example can be adapted to your specific use case by modifying the field definitions, validation rules, and event hooks according to your business requirements.
