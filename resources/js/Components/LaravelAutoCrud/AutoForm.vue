<script setup>
import { useForm } from "@inertiajs/vue3"
import axios from "axios"
import { ref, computed, watch } from "vue"
import AutoExternalRelation from "./AutoExternalRelation.vue"
import AutocompleteServer from "./AutocompleteServer.vue"
import { formatDate, formatDateTime } from "../../Utils/LaravelAutoCrud/dates"
import { getFieldRules } from "../../Utils/LaravelAutoCrud/rules"
import AutoFormDialog from "./AutoFormDialog.vue"
import {
  searchByWords,
  generateItemTitle,
} from "../../Utils/LaravelAutoCrud/autocompleteUtils"
import VDatetimePicker from "./VDatetimePicker.vue"

const props = defineProps([
  "item",
  "type",
  "model",
  "customFilters",
  "filteredItems",
  "customItemProps",
])

const emit = defineEmits([
  "update:item",
  "update:type",
  "formChange",
  "isDirty",
])

const model = computed(() => {
  return props.model
})

const type = computed({
  get: () => props.type,
  set: (value) => emit("update:type", value),
})

const item = computed({
  get: () => props.item,
  set: (value) => emit("update:item", value),
})

const filteredFormFields = computed(() => {
  return model.value.formFields
})

const hiddenFormFieldsLength = computed(() => {
  return (
    filteredFormFields.value.filter(
      (field) => field.hidden || (type.value === "create" && field.onlyUpdate)
    ).length ?? 0
  )
})

const relations = ref({})
const storeShortcutShows = ref({})
const storeExternalShortcutShows = ref({})

const imagePreview = ref({})
const filePreview = ref({})

const getRelations = () => {
  const relationsFromFormFields = filteredFormFields.value.filter(
    (field) => field.relation && !field.relation.serverSide
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
  if (type.value === "edit" && item.value) {
    filteredFormFields.value.forEach((field) => {
      if (field.type === "password") {
        formData[field.field] = ""
        field.rules.required = false
      } else if (field.type === "date") {
        if (item.value[field.field]) {
          formData[field.field] = formatDate(item.value[field.field])
        }
      } else if (field.type === "datetime") {
        if (item.value[field.field]) {
          formData[field.field] = formatDateTime(item.value[field.field])
        }
      } else {
        formData[field.field] = item.value[field.field]
        if (field.type === "image") {
          imagePreview.value[field.field] = item.value[field.field]
        }
        if (field.type === "file") {
          filePreview.value[field.field] = item.value[field.field]
        }
        if (field.type === "select") {
          if (field.multiple) {
            formData[field.field] = item.value[field.field].split(", ")
          }
        }
      }

      if (field.relation?.storeShortcut) {
        storeShortcutShows.value[field.field] = false
      }
    })
  } else if (type.value === "create") {
    filteredFormFields.value.forEach((field) => {
      formData[field.field] = field.default ?? null

      if (item.value?.[field.field] && field.type === "date") {
        formData[field.field] = formatDate(item.value[field.field])
      }

      if (field.relation?.storeShortcut) {
        storeShortcutShows.value[field.field] = false
      }
    })
  }

  model.value.externalRelations.forEach((relation) => {
    if (relation.storeShortcut) {
      storeExternalShortcutShows.value[relation.relation] = false
    }
  })
  formData.defaults()
  emit("isDirty", false)
}

const submit = () => {
  if (type.value === "edit") {
    formData.post(`${model.value.endPoint}/${item.value.id}`, {
      _method: "put",
      forceFormData: true,
      onSuccess: (page) => {
        item.value = page.props.flash.data
      },
    })
  } else if (type.value === "create") {
    formData.post(model.value.endPoint, {
      onSuccess: (page) => {
        item.value = page.props.flash.data
        if (model.value.externalRelations.length > 0) {
          type.value = "edit"
        }
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

const handleFileUpload = (file, fileFieldName) => {
  formData.transform((data) => ({
    ...data,
    [fileFieldName + "_edited"]: true,
  }))
  if (file) {
    const reader = new FileReader()
    reader.readAsDataURL(file.target.files[0])
    formData[fileFieldName] = file.target.files[0]
  } else {
    formData[fileFieldName] = null
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

const removeFile = (fileFieldName) => {
  formData.transform((data) => ({
    ...data,
    [fileFieldName + "_edited"]: true,
  }))
  formData[fileFieldName + "_edited"] = true
  filePreview.value[fileFieldName] = null
  formData[fileFieldName] = null
}

const downloadFile = (fileFieldName) => {
  const link = document.createElement("a")
  link.href = filePreview.value[fileFieldName]
  link.download = fileFieldName
  link.click()
}

watch(
  formData,
  (newValue) => {
    emit("formChange", newValue)
  },
  { deep: true }
)

watch(
  item,
  () => {
    initFields()
  },
  { immediate: true }
)

const updateRelatedFields = (foreignKey, value) => {
  const relatedFields = filteredFormFields.value.filter(
    (field) => field.foreignKey && field.foreignKey === foreignKey
  )

  relatedFields.forEach((field) => {
    formData[field.field] = relations.value[foreignKey].find(
      (relation) => relation.id === value
    )[field.field]
  })
}

const updateComboField = (field, value) => {
  if (typeof value === "object") {
    formData[field.field] = value.id
    formData[field.comboField] = value[field.relation.formKey]
  } else {
    if (
      relations.value[field.field].find(
        (relation) => relation[field.relation.formKey] === value
      )
    ) {
      formData[field.field] = relations.value[field.field].find(
        (relation) => relation[field.relation.formKey] === value
      ).id
    } else {
      formData[field.field] = null
    }
    formData[field.comboField] = value
  }
}

getRelations()

const isFormDirty = computed(() => {
  return formData.isDirty
})

watch(isFormDirty, (value) => {
  emit("isDirty", value)
})
</script>

<template>
  <v-form v-model="form" @submit.prevent="submit">
    <slot
      name="prepend"
      :model="model"
      :type="type"
      :item="item"
      :formData="formData"
      :submit="submit"
    >
    </slot>
    <v-row>
      <v-col
        cols="12"
        v-show="!field.hidden && (type !== 'create' || !field.onlyUpdate)"
        :md="
          filteredFormFields.length - hiddenFormFieldsLength > 1 &&
          field.type !== 'image' &&
          field.type !== 'file'
            ? 6
            : 12
        "
        v-for="field in filteredFormFields"
      >
        <v-text-field
          v-if="
            !field.relation &&
            !field.comboField &&
            field.type !== 'boolean' &&
            field.type !== 'date' &&
            field.type !== 'datetime' &&
            field.type !== 'password' &&
            field.type !== 'select' &&
            field.type !== 'text' &&
            field.type !== 'image' &&
            field.type !== 'file'
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

        <div v-if="field.type === 'file'">
          <v-file-input
            v-if="!filePreview[field.field]"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="formData[field.field]"
            :rules="getFieldRules(formData[field.field], field)"
            @change="(file) => handleFileUpload(file, field.field)"
            :accept="field.rules?.accept"
            prepend-icon="mdi-file"
          ></v-file-input>

          <v-row
            v-else
            class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-2"
          >
            <v-col cols="12" md="10" class="text-center">
              {{ field.name }}
            </v-col>

            <v-col cols="12" md="2" class="text-center">
              <v-btn
                icon
                @click="downloadFile(field.field)"
                color="blue"
                class="mr-2"
              >
                <v-icon>mdi-download</v-icon>

                <v-tooltip activator="parent">Descargar</v-tooltip>
              </v-btn>
              <v-btn icon @click="removeFile(field.field)" color="red" class="">
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

        <v-datetime-picker
          v-else-if="field.type === 'datetime'"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model:datetime="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-datetime-picker>

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
          :multiple="field.multiple"
        ></v-select>

        <v-textarea
          v-else-if="field.type === 'text'"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
        ></v-textarea>

        <v-autocomplete
          v-else-if="
            field.relation && !field.comboField && !field.relation.serverSide
          "
          :items="
            props.filteredItems?.[field.relation.relation]
              ? props.filteredItems[field.relation.relation](
                  relations[field.field]
                )
              : relations[field.field]
          "
          :label="field.rules?.required ? field.name + ' *' : field.name"
          :item-props="props.customItemProps?.[field.relation.relation]"
          :item-title="generateItemTitle(field.relation.formKey)"
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[field.relation.relation]
              )
          "
          item-value="id"
          v-model="formData[field.field]"
          :rules="getFieldRules(formData[field.field], field)"
          @update:model-value="updateRelatedFields(field.field, $event)"
        >
          <template v-if="field.relation.storeShortcut" v-slot:prepend>
            <v-btn
              icon="mdi-plus-circle"
              @click="storeShortcutShows[field.field] = true"
            >
            </v-btn>
            <auto-form-dialog
              v-model:show="storeShortcutShows[field.field]"
              type="create"
              :filteredItems="props.filteredItems"
              :customFilters="props.customFilters"
              :customItemProps="props.customItemProps"
              @update:show="getRelations"
              :modelName="field.relation.model"
            />
          </template>
        </v-autocomplete>

        <autocomplete-server
          v-else-if="
            field.relation && !field.comboField && field.relation.serverSide
          "
          :label="field.rules?.required ? field.name + ' *' : field.name"
          :item-props="props.customItemProps?.[field.relation.relation]"
          :item-title="field.relation.formKey"
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[field.relation.relation]
              )
          "
          v-model="formData[field.field]"
          :end-point="field.relation.endPoint"
          :rules="getFieldRules(formData[field.field], field)"
          @update:model-value="updateRelatedFields(field.field, $event)"
          :item="formData[field.field] ? item?.[field.relation.relation] : null"
        >
          <template v-if="field.relation.storeShortcut" v-slot:prepend>
            <v-btn
              icon="mdi-plus-circle"
              @click="storeShortcutShows[field.field] = true"
            >
            </v-btn>
            <auto-form-dialog
              v-model:show="storeShortcutShows[field.field]"
              type="create"
              :filteredItems="props.filteredItems"
              :customFilters="props.customFilters"
              :customItemProps="props.customItemProps"
              @update:show="getRelations"
              :modelName="field.relation.model"
            />
          </template>
        </autocomplete-server>

        <v-combobox
          v-else-if="field.relation && field.comboField"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="formData[field.comboField]"
          :rules="getFieldRules(formData[field.field], field)"
          :items="
            props.filteredItems?.[field.relation.relation]
              ? props.filteredItems[field.relation.relation](
                  relations[field.field]
                )
              : relations[field.field]
          "
          :item-title="generateItemTitle(field.relation.formKey)"
          :item-props="props.customItemProps?.[field.relation.relation]"
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[field.relation.relation]
              )
          "
          @update:model-value="updateComboField(field, $event)"
        ></v-combobox>
      </v-col>
    </v-row>
    <slot
      name="append"
      :model="model"
      :type="type"
      :item="item"
      :formData="formData"
      :submit="submit"
    ></slot>
    <div class="d-flex justify-center">
      <v-btn
        color="blue-darken-1"
        :disabled="!isFormDirty || !form"
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
    >
    </auto-external-relation>
  </div>
</template>
