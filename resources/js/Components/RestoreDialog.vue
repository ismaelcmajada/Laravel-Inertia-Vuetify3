<script setup>
import { computed } from "vue"
import { router } from "@inertiajs/vue3"

const props = defineProps(["show", "elementName", "item", "endPoint"])

const emit = defineEmits(["updateDialog"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("updateDialog", value)
  },
})

const submit = () => {
  router.post(
    `${props.endPoint}/${props.item.id}/restore`,
    {},
    {
      only: ["tableData", "flash", "errors"],
    }
  )
  dialogState.value = false
}
</script>

<template>
  <v-dialog v-model="dialogState" width="auto">
    <v-card>
      <v-card-title>
        <span class="text-h5"
          >Restaurar {{ props.elementName ?? "elemento" }}</span
        >
      </v-card-title>
      <v-card-text>
        <v-container> ¿Seguro que desea restaurar el elemento? </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          color="blue-darken-1"
          variant="text"
          @click="dialogState = false"
        >
          Cerrar
        </v-btn>
        <v-btn color="blue-darken-1" variant="text" @click="submit">
          Restaurar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
