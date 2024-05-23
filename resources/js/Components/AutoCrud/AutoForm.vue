<script setup>
import { useForm } from "@inertiajs/vue3"
import axios from "axios"
import { ref, onBeforeMount } from "vue"
import AutoExternalRelation from "./AutoExternalRelation.vue"
import {
  ruleRequired,
  ruleMaxLength,
  ruleNumber,
  ruleLessThan,
  ruleDNI,
  ruleEmail,
  ruleGreaterThan,
  ruleMinLength,
  ruleTelephone,
  ruleFloat,
} from "@/Utils/rules"

const props = defineProps(["item", "type", "model"])
const emit = defineEmits(["updated", "created"])

const relations = ref({})
const item = ref(props.item)

const getRelations = () => {
  const relationsFromFormFields = props.model.formFields.filter(
    (field) => field.relation
  )

  relationsFromFormFields.forEach((field) => {
    axios.get(`${field.relation.endPoint}/all`).then((response) => {
      relations.value[field.field] = response.data
    })
  })
}

const form = ref(false)

const formData = useForm(
  Object.fromEntries(props.model.formFields.map(({ field }) => [field, null]))
)

onBeforeMount(() => {
  getRelations()
  if (props.type === "edit") {
    props.model.formFields.forEach((field) => {
      if (field.type === "password") {
        formData[field.field] = ""
      } else {
        formData[field.field] = item.value[field.field]
      }
    })
  } else if (props.type === "create") {
    props.model.formFields.forEach((field) => {
      if (field.default) {
        formData[field.field] = field.default
      } else {
        formData[field.field] = null
      }
    })
  }
})

const submit = () => {
  if (props.type === "edit") {
    formData.put(`${props.model.endPoint}/${item.value.id}`, {
      onSuccess: (page) => {
        item.value = page.props.flash.data
        emit("updated")
      },
    })
  } else if (props.type === "create") {
    formData.post(props.model.endPoint, {
      onSuccess: (page) => {
        item.value = page.props.flash.data
        emit("created")
      },
    })
  }
}

const getFieldRules = (v, field) => {
  const rules = []
  switch (field.type) {
    case "string":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength) {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "text":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength) {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "number":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      rules.push(ruleNumber(v))
      if (field.rules?.min) {
        rules.push(ruleGreaterThan(v, field.rules.min))
      }
      if (field.rules?.max) {
        rules.push(ruleLessThan(v, field.rules.max))
      }
      break
    case "float":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      rules.push(ruleFloat(v))
      if (field.rules?.min) {
        rules.push(ruleGreaterThan(v, field.rules.min))
      }
      if (field.rules?.max) {
        rules.push(ruleLessThan(v, field.rules.max))
      }
      break
    case "email":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      rules.push(ruleEmail(v))
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength) {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "boolean":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      break
    case "date":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      break
    case "dni":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      rules.push(ruleDNI(v))
      break
    case "telephone":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      rules.push(ruleTelephone(v))
      break
    case "password":
      if (field.rules?.required && props.type === "create") {
        rules.push(ruleRequired(v))
      }
      if (field.rules?.maxLength) {
        rules.push(ruleMaxLength(v, field.rules.maxLength))
      }
      if (field.rules?.minLength && props.type === "create") {
        rules.push(ruleMinLength(v, field.rules.minLength))
      }
      break
    case "select":
      if (field.rules?.required) {
        rules.push(ruleRequired(v))
      }
      break
    default:
      break
  }
  return rules
}
</script>

<template>
  <v-form v-model="form" @submit.prevent="submit">
    <v-row>
      <v-col
        cols="12"
        :md="props.model.formFields.length > 1 ? 6 : 12"
        v-for="field in props.model.formFields"
      >
        <v-text-field
          v-if="
            !field.relation &&
            field.type !== 'boolean' &&
            field.type !== 'date' &&
            field.type !== 'password' &&
            field.type !== 'select' &&
            field.type !== 'text'
          "
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-text-field>

        <v-checkbox
          v-else-if="field.type === 'boolean'"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-checkbox>

        <v-text-field
          v-else-if="field.type === 'date'"
          type="date"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-text-field>

        <v-text-field
          v-else-if="field.type === 'password'"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
          type="password"
        ></v-text-field>

        <v-select
          v-else-if="field.type === 'select'"
          :items="field.options"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
          :clearable="!field.rules?.required"
        ></v-select>

        <v-textarea
          v-else-if="field.type === 'text'"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-textarea>

        <v-autocomplete
          v-else
          :items="relations[field.field]"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          :item-title="field.relation.formKey"
          item-value="id"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-autocomplete>
      </v-col>
    </v-row>
    <div class="d-flex justify-center">
      <v-btn
        color="blue-darken-1"
        :disabled="!form"
        variant="text"
        @click="submit"
      >
        Guardar
      </v-btn>
    </div>
  </v-form>
  <div
    v-if="props.type === 'edit' && props.model.externalRelations.length > 0"
    v-for="relation in props.model.externalRelations"
  >
    <v-divider :thickness="3" class="mt-2"></v-divider>
    <auto-external-relation
      v-if="type === 'edit'"
      :endPoint="props.model.endPoint"
      :item="item"
      :externalRelation="relation"
      @bound="emit('updated')"
      @unbound="emit('updated')"
    ></auto-external-relation>
  </div>
</template>
