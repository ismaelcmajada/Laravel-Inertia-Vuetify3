<script setup>
import { useForm } from "@inertiajs/vue3"
import axios from "axios"
import { ref, computed } from "vue"
import AutoExternalRelation from "./AutoExternalRelation.vue"
import { getFieldRules } from "@/Utils/rules"

const props = defineProps([
  "item",
  "type",
  "model",
  "customFilters",
  "filteredItems",
  "customItemProps",
])
const emit = defineEmits(["updated", "created"])

const model = computed(() => {
  return props.model
})

const type = computed(() => {
  return props.type
})

const filteredFormFields = computed(() => {
  return type.value === "create"
    ? model.value.formFields.filter((field) => !field.onlyUpdate)
    : model.value.formFields
})

const hiddenFormFieldsLength = computed(() => {
  return filteredFormFields.value.filter((field) => field.hidden).length ?? 0
})

const relations = ref({})
const item = ref(props.item)
const imagePreview = ref({})

const getRelations = () => {
  const relationsFromFormFields = filteredFormFields.value.filter(
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
  Object.fromEntries(filteredFormFields.value.map(({ field }) => [field, null]))
)

const initFields = () => {
  if (type.value === "edit") {
    filteredFormFields.value.forEach((field) => {
      if (field.type === "password") {
        formData[field.field] = ""
      } else {
        formData[field.field] = item.value[field.field]
        if (field.type === "image") {
          imagePreview.value[field.field] = item.value[field.field]
        }
      }
    })
  } else if (type.value === "create") {
    filteredFormFields.value.forEach((field) => {
      if (field.default) {
        formData[field.field] = field.default
      } else {
        formData[field.field] = null
      }
    })
  }
}

const submit = () => {
  if (type.value === "edit") {
    formData.post(`${model.value.endPoint}/${item.value.id}`, {
      _method: "put",
      forceFormData: true,
      onSuccess: (page) => {
        item.value = page.props.flash.data
        emit("updated")
      },
    })
  } else if (type.value === "create") {
    formData.post(model.value.endPoint, {
      onSuccess: (page) => {
        item.value = page.props.flash.data
        emit("created")
      },
    })
  }
}

const handleImageUpload = (file, imageFieldName) => {
  formData.transform((data) => ({
    ...data,
    [imageFieldName + "_edited"]: true,
  }))
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value[imageFieldName] = e.target.result
    }
    reader.readAsDataURL(file.target.files[0])
    formData[imageFieldName] = file.target.files[0]
  } else {
    formData[imageFieldName] = null
    imagePreview.value[imageFieldName] = null
  }
}

const removeImage = (imageFieldName) => {
  formData.transform((data) => ({
    ...data,
    [imageFieldName + "_edited"]: true,
  }))
  formData[imageFieldName + "_edited"] = true
  imagePreview.value[imageFieldName] = null
  formData[imageFieldName] = null
}

initFields()
getRelations()
</script>

<template>
  <v-form v-model="form" @submit.prevent="submit">
    <v-row>
      <v-col
        cols="12"
        v-show="!field.hidden"
        :md="
          filteredFormFields.length - hiddenFormFieldsLength > 1 &&
          field.type !== 'image'
            ? 6
            : 12
        "
        v-for="field in filteredFormFields"
      >
        <v-text-field
          v-if="
            !field.relation &&
            field.type !== 'boolean' &&
            field.type !== 'date' &&
            field.type !== 'password' &&
            field.type !== 'select' &&
            field.type !== 'text' &&
            field.type !== 'image'
          "
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-text-field>

        <div v-if="field.type === 'image'">
          <v-file-input
            v-if="!imagePreview[field.field]"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="formData[field.field]"
            :rules="getFieldRules(formData[field.field], field)"
            @change="(file) => handleImageUpload(file, field.field)"
            accept="image/*"
            prepend-icon="mdi-file-image"
          ></v-file-input>

          <v-row
            v-else
            class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-2"
          >
            <v-col cols="12" md="1" class="text-center">
              {{ field.name }}
            </v-col>
            <v-col cols="12" md="10" class="my-3 d-flex justify-center">
              <v-img
                v-if="imagePreview"
                :src="imagePreview[field.field]"
                max-width="200"
                max-height="200"
                contain
              ></v-img>
            </v-col>
            <v-col cols="12" md="1" class="text-center">
              <v-btn
                icon
                @click="removeImage(field.field)"
                color="red"
                class="mt-2"
              >
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-col>
          </v-row>
        </div>

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
          v-else-if="field.relation"
          :items="
            props.filteredItems?.[field.relation.relation]
              ? props.filteredItems[field.relation.relation](
                  relations[field.field]
                )
              : relations[field.field]
          "
          :label="field.rules?.required ? field.name + ' *' : field.name"
          :item-props="props.customItemProps?.[field.relation.relation]"
          :item-title="field.relation.formKey"
          :custom-filter="props.customFilters?.[field.relation.relation]"
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
    v-if="type === 'edit' && model.externalRelations.length > 0"
    v-for="relation in model.externalRelations"
  >
    <v-divider :thickness="3" class="mt-2"></v-divider>
    <auto-external-relation
      v-if="type === 'edit'"
      :endPoint="model.endPoint"
      :item="item"
      :externalRelation="relation"
      :filteredItems="props.filteredItems"
      :customFilters="props.customFilters"
      :customItemProps="props.customItemProps"
      :withTitle="false"
      @bound="emit('updated')"
      @unbound="emit('updated')"
    ></auto-external-relation>
  </div>
</template>
