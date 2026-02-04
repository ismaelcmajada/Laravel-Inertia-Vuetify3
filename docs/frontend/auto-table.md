---
title: AutoTable
description: Data table component with automatic CRUD operations
---

# AutoTable

The main component that displays a data table with automatic CRUD operations.

## Props

| Prop | Type | Description |
|------|------|-------------|
| `title` | string | Table title displayed in the toolbar |
| `model` | object | Model configuration from `page.props.models` |
| `search` | object | Initial search filters (uses model scopes) |
| `exactFilters` | object | Exact match filters for columns |
| `orderBy` | array | Initial sort order `[{ key: 'field', order: 'asc' }]` |
| `customHeaders` | array | Additional columns to add to the table |
| `itemsPerPage` | number | Items per page (default: 10) |
| `itemsPerPageOptions` | array | Available page size options |
| `listMode` | boolean | Display as list instead of table |
| `hideReset` | boolean | Hide the refresh button |
| `customFilters` | object | Custom filter functions for autocompletes |
| `filteredItems` | object | Functions to filter relation items |
| `customItemProps` | object | Custom item props for autocompletes |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:item` | object | Emitted when the current item changes |
| `openDialog` | - | Emitted when a dialog opens |
| `closeDialog` | - | Emitted when a dialog closes |
| `formChange` | object | Emitted when form data changes |

## Slots

### Table Actions
```vue
<!-- Add buttons before default actions -->
<template #table.actions.prepend="{ openDialog, loadItems, tableData }">
  <v-btn icon @click="exportData(tableData)">
    <v-icon>mdi-export</v-icon>
  </v-btn>
</template>

<!-- Replace default actions completely -->
<template #table.actions="{ openDialog, loadItems, tableData }">
  <v-btn @click="openDialog('create')">Custom Create</v-btn>
</template>

<!-- Add buttons after default actions -->
<template #table.actions.append="{ openDialog, loadItems, tableData }">
  <v-btn icon @click="customAction()">
    <v-icon>mdi-cog</v-icon>
  </v-btn>
</template>
```

### Row Actions
```vue
<!-- Add buttons before row actions -->
<template #item.actions.prepend="{ item, openDialog }">
  <v-btn icon @click="preview(item)">
    <v-icon>mdi-eye</v-icon>
  </v-btn>
</template>

<!-- Replace row actions completely -->
<template #item.actions="{ item, openDialog, forbiddenActions }">
  <v-btn @click="openDialog('edit', item)">Edit</v-btn>
</template>
```

### Custom Columns
```vue
<!-- Custom cell content for a column -->
<template #item.status="{ item }">
  <v-chip :color="item.status === 'active' ? 'success' : 'error'">
    {{ item.status }}
  </v-chip>
</template>
```

### Form Customization (nested slots)
```vue
<!-- Override a specific form field -->
<template #auto-form-dialog.auto-form.field.category_id="{ formData, field }">
  <v-col cols="12">
    <custom-category-picker v-model="formData.category_id" />
  </v-col>
</template>

<!-- Add content before form fields -->
<template #auto-form-dialog.auto-form.prepend="{ formData, type }">
  <v-alert v-if="type === 'create'" type="info">
    Fill all required fields
  </v-alert>
</template>

<!-- Add content after form fields -->
<template #auto-form-dialog.auto-form.append="{ formData }">
  <v-divider class="my-4" />
  <p>Additional info here</p>
</template>

<!-- Add content after save button (only visible when item exists) -->
<template #auto-form-dialog.auto-form.after-save="{ item }">
  <div v-if="item?.id">
    <v-divider class="my-4" />
    <custom-related-data :item-id="item.id" />
  </div>
</template>
```

## Basic Usage

```vue
<script setup>
import AutoTable from "@/Components/LaravelAutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()
const model = page.props.models.product
</script>

<template>
  <auto-table title="Products" :model="model" />
</template>
```

## With Filters and Custom Headers

```vue
<script setup>
import AutoTable from "@/Components/LaravelAutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"
import { computed, ref } from "vue"

const page = usePage()
const model = page.props.models.order

const showCompleted = ref(false)

const exactFilters = computed(() => ({
  status: showCompleted.value ? 'completed' : 'pending'
}))

const orderBy = [{ key: 'created_at', order: 'desc' }]

const customHeaders = [
  {
    title: 'Total Items',
    key: 'items_count',
    sortable: false,
    after: 'status'
  }
]
</script>

<template>
  <auto-table
    title="Orders"
    :model="model"
    :exactFilters="exactFilters"
    :orderBy="orderBy"
    :customHeaders="customHeaders"
  >
    <template #table.actions.prepend="{ tableData }">
      <v-btn icon @click="showCompleted = !showCompleted">
        <v-icon>mdi-filter</v-icon>
      </v-btn>
    </template>

    <template #item.items_count="{ item }">
      <v-chip size="small">{{ item.items?.length || 0 }}</v-chip>
    </template>
  </auto-table>
</template>
```

## Custom Header Positioning

The `customHeaders` array supports positioning options:

```js
const customHeaders = [
  { title: 'Col A', key: 'col_a', position: 'start' },     // First column
  { title: 'Col B', key: 'col_b', position: 'end' },       // Last column
  { title: 'Col C', key: 'col_c', before: 'status' },      // Before 'status'
  { title: 'Col D', key: 'col_d', after: 'name' },         // After 'name'
  { title: 'Col E', key: 'col_e' }                         // Before actions (default)
]
```

## Slot Hierarchy

Understanding how slots propagate through components:

```
AutoTable
├── #auto-form-dialog                    → Replace entire dialog
├── #auto-form-dialog.prepend            → Before form in dialog
├── #auto-form-dialog.auto-form          → Replace form
├── #auto-form-dialog.auto-form.prepend  → Before form fields
├── #auto-form-dialog.auto-form.field.{fieldName} → Override field
├── #auto-form-dialog.auto-form.append   → After form fields
├── #auto-form-dialog.auto-form.after-save → After save button
├── #auto-form-dialog.append             → After form in dialog
├── #table.actions.prepend               → Before toolbar actions
├── #table.actions                       → Replace toolbar actions
├── #table.actions.append                → After toolbar actions
├── #item.{columnKey}                    → Custom column content
├── #item.actions.prepend                → Before row actions
├── #item.actions                        → Replace row actions
└── #item.actions.append                 → After row actions
```
