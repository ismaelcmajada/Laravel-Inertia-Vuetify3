<script setup>
import AutoForm from "./AutoForm.vue"
import { computed } from "vue"

const props = defineProps([
  "show",
  "item",
  "type",
  "model",
  "customFilters",
  "filteredItems",
  "customItemProps",
])

const emit = defineEmits(["update:show", "update:type", "update:item"])

const model = computed(() => {
  return props.model
})

const show = computed({
  get: () => props.show,
  set: (value) => {
    emit("update:show", value)
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
</script>

<template>
  <v-dialog scrollable v-model="show" width="1024">
    <v-card>
      <v-card-title class="mt-2 text-center">
        <span v-if="type == 'create'"> Crear elemento </span>
        <span v-else> Editar elemento </span>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <slot name="prepend" :model="model" :type="type" :item="item"> </slot>
          <slot name="auto-form" :model="model" :type="type" :item="item">
            <auto-form
              :model="model"
              v-model:type="type"
              v-model:item="item"
              :customFilters="props.customFilters"
              :filteredItems="props.filteredItems"
              :customItemProps="props.customItemProps"
            />
          </slot>
          <slot name="append" :model="model" :type="type" :item="item"> </slot>
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
