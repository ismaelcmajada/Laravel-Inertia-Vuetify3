---
title: AutoFormDialog
description: Dialog wrapper for create/edit forms
---

# AutoFormDialog

A dialog wrapper for `AutoForm` that handles create/edit operations with unsaved changes detection.

## Props

| Prop | Type | Description |
|------|------|-------------|
| `show` | boolean | v-model for dialog visibility |
| `item` | object | v-model for the current item being edited |
| `type` | string | v-model: `'create'` or `'edit'` |
| `model` | object | Model configuration |
| `modelName` | string | Alternative: model class name (e.g., `'App\\Models\\Product'`) |
| `customFilters` | object | Custom filter functions |
| `filteredItems` | object | Functions to filter relation items |
| `customItemProps` | object | Custom item props for autocompletes |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:show` | boolean | Dialog visibility changed |
| `update:type` | string | Form type changed |
| `update:item` | object | Item changed |
| `formChange` | object | Form data changed |
| `isDirty` | boolean | Form has unsaved changes |
| `success` | object | Form submitted successfully |

## Slots

```vue
<!-- Content before the form -->
<template #prepend="{ model, type, item }">
  <v-alert v-if="type === 'edit'" type="info">
    Editing: {{ item.name }}
  </v-alert>
</template>

<!-- Replace entire form -->
<template #auto-form="{ model, type, item, handleIsFormDirty }">
  <custom-form :item="item" @dirty="handleIsFormDirty" />
</template>

<!-- Content after the form -->
<template #append="{ model, type, item }">
  <v-btn @click="customAction(item)">Extra Action</v-btn>
</template>
```

### Nested Slots (passed to AutoForm)

```vue
<!-- Before form fields -->
<template #auto-form.prepend="{ formData, type }">
  <v-alert type="info">Instructions</v-alert>
</template>

<!-- Override specific field -->
<template #auto-form.field.name="{ formData, field }">
  <v-col cols="12">
    <custom-input v-model="formData.name" />
  </v-col>
</template>

<!-- After form fields -->
<template #auto-form.append="{ formData }">
  <v-checkbox v-model="formData.terms" label="Accept terms" />
</template>

<!-- After save button -->
<template #auto-form.after-save="{ item }">
  <related-data v-if="item?.id" :item-id="item.id" />
</template>
```

## Basic Usage

```vue
<script setup>
import AutoFormDialog from "@/Components/LaravelAutoCrud/AutoFormDialog.vue"
import { usePage } from "@inertiajs/vue3"
import { ref } from "vue"

const page = usePage()
const model = page.props.models.product

const showDialog = ref(false)
const currentItem = ref(null)
const formType = ref('create')

const openCreate = () => {
  currentItem.value = null
  formType.value = 'create'
  showDialog.value = true
}

const openEdit = (item) => {
  currentItem.value = item
  formType.value = 'edit'
  showDialog.value = true
}
</script>

<template>
  <v-btn @click="openCreate">Create New</v-btn>
  
  <auto-form-dialog
    v-model:show="showDialog"
    v-model:item="currentItem"
    v-model:type="formType"
    :model="model"
    @success="handleSuccess"
  />
</template>
```

## Using modelName (alternative)

Instead of passing the full model object, you can use `modelName` to reference models from `page.props.models`:

```vue
<template>
  <auto-form-dialog
    v-model:show="showDialog"
    v-model:item="currentItem"
    v-model:type="formType"
    model-name="App\Models\Product"
    @success="handleSuccess"
  />
</template>
```

## Handling Unsaved Changes

The dialog automatically detects unsaved changes and shows a confirmation dialog:

```vue
<script setup>
const handleDirty = (isDirty) => {
  console.log('Form has unsaved changes:', isDirty)
}
</script>

<template>
  <auto-form-dialog
    v-model:show="showDialog"
    v-model:item="currentItem"
    v-model:type="formType"
    :model="model"
    @isDirty="handleDirty"
  />
</template>
```

## With Custom Content

```vue
<template>
  <auto-form-dialog
    v-model:show="showDialog"
    v-model:item="currentItem"
    v-model:type="formType"
    :model="model"
  >
    <template #prepend="{ type }">
      <v-alert v-if="type === 'create'" type="info" class="mb-4">
        Fill in all required fields to create a new product.
      </v-alert>
    </template>

    <template #auto-form.field.price="{ formData }">
      <v-col cols="12" md="6">
        <v-text-field
          v-model.number="formData.price"
          label="Price"
          prefix="$"
          type="number"
          :rules="[v => v > 0 || 'Price must be positive']"
        />
      </v-col>
    </template>

    <template #append="{ item }">
      <v-btn 
        v-if="item?.id" 
        color="warning" 
        @click="duplicateProduct(item)"
      >
        Duplicate
      </v-btn>
    </template>
  </auto-form-dialog>
</template>
```
