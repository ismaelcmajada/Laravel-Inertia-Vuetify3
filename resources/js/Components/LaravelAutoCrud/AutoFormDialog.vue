<script setup>
import AutoForm from "./AutoForm.vue"
import { computed, ref } from "vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const props = defineProps([
  "show",
  "item",
  "type",
  "model",
  "customFilters",
  "filteredItems",
  "customItemProps",
  "modelName",
])

const emit = defineEmits([
  "update:show",
  "update:type",
  "update:item",
  "formChange",
  "isDirty",
])

const model = computed(() => {
  if (props.modelName) {
    const parts = props.modelName.split("\\")
    const modelName = parts[parts.length - 1].toLowerCase()

    return page.props.models[modelName]
  } else {
    return props.model
  }
})

const show = computed({
  get: () => props.show,
  set: (value) => {
    emit("update:show", value)
    if (!value) {
      item.value = null
    }
  },
})

const type = computed({
  get: () => props.type,
  set: (value) => {
    emit("update:type", value)
  },
})

const item = computed({
  get: () => props.item,
  set: (value) => {
    emit("update:item", value)
  },
})

const isFormDirty = ref(false)

const handleIsFormDirty = (value) => {
  isFormDirty.value = value
  emit("isDirty", value)
}
</script>

<template>
  <v-dialog scrollable v-model="show" width="1024">
    <v-card>
      <v-card-title class="mt-2 position-relative d-flex align-middle">
        <div class="text-center" style="flex: 1">
          <span v-if="type == 'create'"> Crear elemento </span>
          <span v-else> Editar elemento </span>
        </div>
        <v-chip
          v-if="isFormDirty"
          color="warning"
          size="small"
          class="position-absolute right-0 mr-10"
        >
          Sin guardar
        </v-chip>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <slot
            name="prepend"
            :model="model"
            :type="type"
            :item="item"
            :show="show"
          >
          </slot>
          <slot
            name="auto-form"
            :model="model"
            :type="type"
            :item="item"
            :isFormDirty="isFormDirty"
            :handleIsFormDirty="handleIsFormDirty"
          >
            <auto-form
              :model="model"
              v-model:type="type"
              v-model:item="item"
              :customFilters="props.customFilters"
              :filteredItems="props.filteredItems"
              :customItemProps="props.customItemProps"
              @formChange="emit('formChange', $event)"
              @isDirty="handleIsFormDirty($event)"
            >
              <template #prepend="slotProps">
                <slot name="auto-form.prepend" v-bind="slotProps"> </slot>
              </template>
              <template #append="slotProps">
                <slot name="auto-form.append" v-bind="slotProps"> </slot>
              </template>
            </auto-form>
          </slot>
          <slot
            name="append"
            :model="model"
            :type="type"
            :item="item"
            :show="show"
          >
          </slot>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="show = false">
          Cerrar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
