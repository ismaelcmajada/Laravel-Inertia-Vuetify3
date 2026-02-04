---
title: Event Hooks
description: Lifecycle event handling in AutoCrud
---

# Event Hooks

The AutoCrud trait provides lifecycle event hooks that allow you to execute custom logic during model operations.

## Available Event Hooks

The trait supports all standard Eloquent events:

- `creatingEvent()` - Before creating a new record
- `createdEvent()` - After creating a new record
- `updatingEvent()` - Before updating a record
- `updatedEvent()` - After updating a record
- `deletingEvent()` - Before deleting a record
- `deletedEvent()` - After deleting a record
- `savingEvent()` - Before saving (create or update)
- `savedEvent()` - After saving (create or update)

## Hook Method Signature

All event hooks have the same signature:
- **No parameters** - Use `$this` to access the model instance
- **Return void** - No return value expected

```php
protected function creatingEvent(): void
{
    // Custom logic here
    // Access model data via $this
}
```

## Event Hook Examples

### Creating Event
Execute logic before a record is created:

```php
protected function creatingEvent(): void
{
    // Set default values
    $this->status = $this->status ?? 'pending';
    
    // Generate unique codes
    $this->code = $this->generateUniqueCode();
    
    // Set user context
    if (auth()->check()) {
        $this->created_by = auth()->id();
    }
    
    // Log creation attempt
    Log::info('Creating new record', [
        'model' => static::class,
        'data' => $this->toArray(),
    ]);
}
```

### Created Event
Execute logic after a record is successfully created:

```php
protected function createdEvent(): void
{
    // Send notifications
    if ($this->notify_admin) {
        Mail::to('admin@example.com')->send(new NewRecordNotification($this));
    }
    
    // Create related records
    $this->createDefaultSettings();
    
    // Update counters
    $this->updateRelatedCounters();
    
    // Log successful creation
    Log::info('Record created successfully', [
        'id' => $this->id,
        'model' => static::class,
    ]);
}
```

### Updating Event
Execute logic before a record is updated:

```php
protected function updatingEvent(): void
{
    // Track changes
    $this->previous_values = $this->getOriginal();
    
    // Update timestamps
    $this->updated_by = auth()->id();
    $this->last_modified = now();
    
    // Validate business rules
    if ($this->isDirty('status') && $this->status === 'published') {
        if (!$this->isReadyForPublication()) {
            throw new \Exception('Record is not ready for publication');
        }
    }
    
    // Log update attempt
    Log::info('Updating record', [
        'id' => $this->id,
        'changes' => $this->getDirty(),
    ]);
}
```

### Updated Event
Execute logic after a record is successfully updated:

```php
protected function updatedEvent(): void
{
    // Send change notifications
    if ($this->wasChanged('status')) {
        $this->notifyStatusChange();
    }
    
    // Update search index
    $this->updateSearchIndex();
    
    // Clear related caches
    Cache::tags(['model_' . $this->id])->flush();
    
    // Log successful update
    Log::info('Record updated successfully', [
        'id' => $this->id,
        'changes' => $this->getChanges(),
    ]);
}
```

### Deleting Event
Execute logic before a record is deleted:

```php
protected function deletingEvent(): void
{
    // Check dependencies
    if ($this->hasActiveRelations()) {
        throw new \Exception('Cannot delete record with active relations');
    }
    
    // Backup data
    $this->createBackup();
    
    // Update related records
    $this->updateRelatedRecordsBeforeDelete();
    
    // Log deletion attempt
    Log::warning('Deleting record', [
        'id' => $this->id,
        'model' => static::class,
        'user' => auth()->id(),
    ]);
}
```

### Deleted Event
Execute logic after a record is successfully deleted:

```php
protected function deletedEvent(): void
{
    // Clean up related files
    $this->deleteAssociatedFiles();
    
    // Update counters
    $this->decrementRelatedCounters();
    
    // Send notifications
    Mail::to('admin@example.com')->send(new RecordDeletedNotification($this));
    
    // Log successful deletion
    Log::warning('Record deleted successfully', [
        'id' => $this->id,
        'model' => static::class,
    ]);
}
```

### Saving Event
Execute logic before any save operation (create or update):

```php
protected function savingEvent(): void
{
    // Normalize data
    $this->email = strtolower(trim($this->email));
    $this->name = ucwords(strtolower($this->name));
    
    // Validate business rules
    $this->validateBusinessRules();
    
    // Set computed fields
    $this->full_name = $this->first_name . ' ' . $this->last_name;
    
    // Update search keywords
    $this->search_keywords = $this->generateSearchKeywords();
}
```

### Saved Event
Execute logic after any save operation (create or update):

```php
protected function savedEvent(): void
{
    // Update related aggregates
    $this->updateAggregates();
    
    // Sync with external systems
    $this->syncWithExternalAPI();
    
    // Clear caches
    $this->clearRelatedCaches();
    
    // Update full-text search
    $this->updateFullTextSearch();
}
```

## Complete Example

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ismaelcmajada\LaravelAutoCrud\Models\Traits\AutoCrud;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    use AutoCrud, SoftDeletes;

    protected static function getFields(): array
    {
        return [
            [
                'name' => 'Order Number',
                'field' => 'order_number',
                'type' => 'string',
                'table' => true,
                'form' => true,
                'rules' => ['required' => true, 'unique' => true],
            ],
            [
                'name' => 'Customer',
                'field' => 'customer_id',
                'type' => 'number',
                'relation' => [
                    'model' => Customer::class,
                    'relation' => 'customer',
                    'tableKey' => '{name}',
                    'formKey' => '{name}',
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
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
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
                'rules' => ['required' => true],
            ],
        ];
    }

    // Event Hooks
    protected function creatingEvent(): void
    {
        // Generate unique order number
        $this->order_number = $this->generateOrderNumber();
        
        // Set default status
        $this->status = $this->status ?? 'pending';
        
        // Set creation context
        if (auth()->check()) {
            $this->created_by = auth()->id();
        }
        
        Log::info('Creating new order', [
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
        ]);
    }

    protected function createdEvent(): void
    {
        // Send order confirmation email
        if ($this->customer && $this->customer->email) {
            Mail::to($this->customer->email)->send(new OrderConfirmationMail($this));
        }
        
        // Update customer statistics
        $this->customer->increment('total_orders');
        $this->customer->increment('total_spent', $this->total_amount);
        
        Log::info('Order created successfully', ['id' => $this->id]);
    }

    protected function updatingEvent(): void
    {
        // Track status changes
        if ($this->isDirty('status')) {
            $this->previous_status = $this->getOriginal('status');
            $this->status_changed_at = now();
            $this->status_changed_by = auth()->id();
        }
        
        // Update modification tracking
        $this->updated_by = auth()->id();
        
        Log::info('Updating order', [
            'id' => $this->id,
            'changes' => $this->getDirty(),
        ]);
    }

    protected function updatedEvent(): void
    {
        // Handle status change notifications
        if ($this->wasChanged('status')) {
            $this->handleStatusChange();
        }
        
        // Update search index
        $this->updateSearchIndex();
        
        // Clear related caches
        Cache::tags(['order_' . $this->id])->flush();
        
        Log::info('Order updated successfully', ['id' => $this->id]);
    }

    protected function deletingEvent(): void
    {
        // Check if order can be deleted
        if (in_array($this->status, ['shipped', 'delivered'])) {
            throw new \Exception('Cannot delete shipped or delivered orders');
        }
        
        // Create audit trail
        $this->createDeletionAuditTrail();
        
        Log::warning('Deleting order', [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user' => auth()->id(),
        ]);
    }

    protected function deletedEvent(): void
    {
        // Update customer statistics
        if ($this->customer) {
            $this->customer->decrement('total_orders');
            $this->customer->decrement('total_spent', $this->total_amount);
        }
        
        // Send cancellation notification
        if ($this->customer && $this->customer->email) {
            Mail::to($this->customer->email)->send(new OrderCancelledMail($this));
        }
        
        Log::warning('Order deleted successfully', ['id' => $this->id]);
    }

    // Helper methods
    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    private function handleStatusChange(): void
    {
        switch ($this->status) {
            case 'shipped':
                Mail::to($this->customer->email)->send(new OrderShippedMail($this));
                break;
            case 'delivered':
                Mail::to($this->customer->email)->send(new OrderDeliveredMail($this));
                break;
            case 'cancelled':
                $this->handleCancellation();
                break;
        }
    }

    private function handleCancellation(): void
    {
        // Restore inventory
        foreach ($this->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }
        
        // Process refund if needed
        if ($this->payment_status === 'paid') {
            $this->processRefund();
        }
    }
}
```

## Best Practices

1. **Keep hooks focused**: Each hook should handle one specific aspect of the lifecycle
2. **Handle exceptions**: Use try-catch blocks for operations that might fail
3. **Log important events**: Always log significant operations for debugging
4. **Avoid heavy operations**: Keep hooks lightweight to maintain performance
5. **Use transactions**: Wrap complex operations in database transactions
6. **Test thoroughly**: Event hooks can have side effects, so test all scenarios
