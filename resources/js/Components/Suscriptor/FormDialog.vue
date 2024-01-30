<script setup>
import { useForm } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import {
  ruleRequired,
  ruleMaxLength,
  ruleTelephone,
  ruleEmail,
  ruleDNI,
} from "@/Utils/rules"
import { sexoItems } from "@/Utils/arrays"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const form = ref(false)

const formData = useForm({
  nombre: "",
  apellidos: "",
  dni: "",
  email: "",
  telefono: "",
  sexo: "",
})

watch(dialogState, (value) => {
  if (value) {
    if (props.type === "edit") {
      Object.assign(formData, props.item)
    } else if (props.type === "create") {
      formData.nombre = ""
      formData.apellidos = ""
      formData.dni = ""
      formData.email = ""
      formData.telefono = ""
      formData.sexo = ""
    }
  } else {
    emit("reloadItems")
  }
})

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.endPoint}/${props.item.id}`, {
      only: ["tableData", "flash", "errors"],
      onSuccess: () => {
        dialogState.value = false
      },
    })
  } else if (props.type === "create") {
    formData.post(props.endPoint, {
      only: ["tableData", "flash", "errors"],
      onSuccess: () => {
        dialogState.value = false
      },
    })
  }
}
</script>

<template>
  <v-dialog v-model="dialogState" width="1024">
    <v-card>
      <v-card-title>
        <span class="text-h5"
          >{{
            props.type == "create"
              ? "Crear"
              : props.type == "edit"
              ? "Editar"
              : ""
          }}
          suscriptor</span
        >
      </v-card-title>

      <v-card-text>
        <v-container>
          <v-form v-model="form" @submit.prevent="submit">
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Nombre*"
                  :rules="[ruleRequired, (v) => ruleMaxLength(v, 191)]"
                  v-model="formData.nombre"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Apellidos*"
                  :rules="[ruleRequired, (v) => ruleMaxLength(v, 191)]"
                  v-model="formData.apellidos"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="DNI*"
                  :rules="[ruleRequired, (v) => ruleMaxLength(v, 191), ruleDNI]"
                  v-model="formData.dni"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Correo*"
                  :rules="[
                    ruleRequired,
                    (v) => ruleMaxLength(v, 191),
                    ruleEmail,
                  ]"
                  v-model="formData.email"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  label="Teléfono"
                  :rules="[ruleTelephone]"
                  v-model="formData.telefono"
                  type="number"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  label="Sexo*"
                  :rules="[ruleRequired, (v) => ruleMaxLength(v, 191)]"
                  v-model="formData.sexo"
                  :items="sexoItems"
                ></v-select>
              </v-col>
            </v-row>
          </v-form>
        </v-container>
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
        <v-btn
          color="blue-darken-1"
          :disabled="!form"
          variant="text"
          @click="submit"
        >
          Guardar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
