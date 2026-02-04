---
title: AutoForm
description: Dynamic form component based on model configuration
---

# AutoForm

The form component that renders fields based on model configuration and handles create/edit submissions.

## Props

| Prop | Type | Description |
|------|------|-------------|
| `item` | object | v-model for the item being edited |
| `type` | string | v-model: `'create'` or `'edit'` |
| `model` | object | Model configuration with `formFields` |
| `customFilters` | object | Custom filter functions for autocompletes |
| `filteredItems` | object | Functions to filter relation items |
| `customItemProps` | object | Custom item props for autocompletes |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:item` | object | Item changed |
| `update:type` | string | Form type changed |
| `formChange` | object | Form data changed |
| `isDirty` | boolean | Form has unsaved changes |
| `success` | object | Form submitted successfully |

## Slots

### Field Override

Override a specific field's rendering:

```vue
<template #field.description="{ formData, field, type }">
  <v-col cols="12">
    <rich-text-editor
      v-model="formData.description"
      :label="field.name"
    />
  </v-col>
</template>
```

### Content Slots

```vue
<!-- Before all fields -->
<template #prepend="{ formData, type, item, submit }">
  <v-alert type="info">Form instructions here</v-alert>
</template>

<!-- After all fields, before save button -->
<template #append="{ formData, type, item, submit }">
  <v-checkbox v-model="formData.terms" label="Accept terms" />
</template>

<!-- After save button (for related data) -->
<template #after-save="{ item, formData }">
  <template v-if="item?.id">
    <v-divider class="my-4" />
    <related-items-manager :parent-id="item.id" />
  </template>
</template>
```

### External Relations Actions

Add custom actions to external relation items:

```vue
<template #auto-external-relation.tags.actions="{ item }">
  <v-btn icon size="small" @click="editTag(item)">
    <v-icon>mdi-pencil</v-icon>
  </v-btn>
</template>
```

## Field Slot Props

When overriding a field, you receive:

| Prop | Type | Description |
|------|------|-------------|
| `formData` | object | Reactive form data (v-model target) |
| `field` | object | Field configuration from model |
| `item` | object | Current item being edited |
| `type` | string | `'create'` or `'edit'` |
| `submit` | function | Form submit function |
| `getFieldRules` | function | Get validation rules for a field |

## Basic Usage

```vue
<script setup>
import AutoForm from "@/Components/LaravelAutoCrud/AutoForm.vue"
import { usePage } from "@inertiajs/vue3"
import { ref } from "vue"

const page = usePage()
const model = page.props.models.product
const item = ref(null)
const formType = ref('create')

const onSuccess = (flash) => {
  console.log('Saved:', flash.data)
}
</script>

<template>
  <v-card>
    <v-card-text>
      <auto-form
        :model="model"
        v-model:item="item"
        v-model:type="formType"
        @success="onSuccess"
      />
    </v-card-text>
  </v-card>
</template>
```

## With Field Customization

```vue
<template>
  <auto-form
    :model="model"
    v-model:item="item"
    v-model:type="formType"
  >
    <!-- Custom price field with prefix -->
    <template #field.price="{ formData }">
      <v-col cols="12" md="6">
        <v-text-field
          v-model.number="formData.price"
          label="Price"
          prefix="$"
          type="number"
        />
      </v-col>
    </template>

    <!-- Custom description with rich editor -->
    <template #field.description="{ formData, field }">
      <v-col cols="12">
        <label>{{ field.name }}</label>
        <rich-text-editor v-model="formData.description" />
      </v-col>
    </template>
  </auto-form>
</template>
```

## Hidden Fields with Default Values

Set a field value automatically without showing it:

```vue
<template>
  <auto-form :model="model" v-model:item="item" v-model:type="formType">
    <template #field.category_id="{ formData, type }">
      <v-col cols="12" v-show="false">
        <span v-if="type === 'create'">
          {{ formData.category_id = selectedCategoryId }}
        </span>
      </v-col>
    </template>
  </auto-form>
</template>
```

## With Related Data (after-save)

Show related data management after the item is saved:

```vue
<template>
  <auto-form :model="model" v-model:item="item" v-model:type="formType">
    <template #after-save="{ item }">
      <template v-if="item?.id">
        <v-divider class="my-4" />
        
        <h4>Product Images</h4>
        <image-uploader :product-id="item.id" />
        
        <v-divider class="my-4" />
        
        <h4>Product Variants</h4>
        <variants-manager :product-id="item.id" />
      </template>
    </template>
  </auto-form>
</template>
```

## Filtering Relation Items

Filter the options shown in autocomplete fields:

```vue
<script setup>
const filteredItems = {
  // Filter categories based on current type
  category: (items, formData) => {
    if (!formData.type) return items
    return items.filter(item => item.type === formData.type)
  }
}

const customFilters = {
  // Custom search filter for category autocomplete
  category: (value, search, item) => {
    return item.name.toLowerCase().includes(search.toLowerCase()) ||
           item.code.toLowerCase().includes(search.toLowerCase())
  }
}
</script>

<template>
  <auto-form
    :model="model"
    v-model:item="item"
    v-model:type="formType"
    :filteredItems="filteredItems"
    :customFilters="customFilters"
  />
</template>
```

## Watching Form Changes

React to form data changes:

```vue
<script setup>
const handleFormChange = (formData) => {
  console.log('Form data changed:', formData)
  
  // Calculate derived values
  if (formData.quantity && formData.price) {
    calculatedTotal.value = formData.quantity * formData.price
  }
}
</script>

<template>
  <auto-form
    :model="model"
    v-model:item="item"
    v-model:type="formType"
    @formChange="handleFormChange"
  />
</template>
```
