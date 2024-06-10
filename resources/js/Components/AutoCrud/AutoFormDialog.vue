<script setup>
import AutoForm from "./AutoForm.vue"
import { computed, watch } from "vue"

const props = defineProps(["show", "item", "type", "model"])
const emit = defineEmits(["closeDialog", "reloadItems", "updated", "created"])

const model = computed(() => {
  return props.model
})

const type = computed(() => {
  return props.type
})

const dialogState = computed({
  get: () => props.show,
  set: () => {
    emit("closeDialog")
  },
})

watch(dialogState, (value) => {
  if (!value) {
    emit("reloadItems")
  }
})
</script>

<template>
  <v-dialog scrollable v-model="dialogState" width="1024">
    <v-card>
      <v-card-title class="mt-2 text-center">
        <span v-if="type == 'create'"> Crear elemento </span>
        <span v-else> Editar elemento </span>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <v-container>
          <slot
            name="prepend"
            :model="model"
            :type="type"
            :item="props.item"
            :reloadItems="() => emit('reloadItems')"
            :closeDialog="() => (dialogState = false)"
          >
          </slot>
          <slot
            name="auto-form"
            :model="model"
            :type="type"
            :item="props.item"
            :reloadItems="() => emit('reloadItems')"
            :closeDialog="() => (dialogState = false)"
          >
            <auto-form
              :model="model"
              :type="type"
              :item="props.item"
              @created="emit('created')"
              @updated="emit('updated')"
            />
          </slot>
          <slot
            name="append"
            :model="model"
            :type="type"
            :item="props.item"
            :reloadItems="() => emit('reloadItems')"
            :closeDialog="() => (dialogState = false)"
          >
          </slot>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
