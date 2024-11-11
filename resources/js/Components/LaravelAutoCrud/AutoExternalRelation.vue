<script setup>
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import AutocompleteServer from "./AutocompleteServer.vue"
import axios from "axios"
import { ruleRequired, getFieldRules } from "../../Utils/LaravelAutoCrud/rules"
import AutoFormDialog from "./AutoFormDialog.vue"
import {
  generateItemTitle,
  searchByWords,
} from "../../Utils/LaravelAutoCrud/autocompleteUtils"

const props = defineProps([
  "item",
  "endPoint",
  "externalRelation",
  "withTitle",
  "customFilters",
  "filteredItems",
  "customItemProps",
])

const emit = defineEmits(["bound", "unbound"])

const items = ref([])
const selectedItem = ref(null)
const pivotData = ref({})

const addForm = ref(false)
const updateForm = ref(false)

const storeExternalShortcutShow = ref(false)

const pivotEditData = ref({})
const pivotEditing = ref(null)

const item = ref(props.item)

const getItems = async () => {
  const response = await axios.get(`${props.externalRelation.endPoint}/all`)

  items.value = response.data
  items.value = items.value.filter((relatedItem) => {
    return !item.value[props.externalRelation.relation].some(
      (relatedItemFromItem) => relatedItem.id === relatedItemFromItem.id
    )
  })
}

const addItem = () => {
  if (selectedItem.value) {
    router.post(
      `${props.endPoint}/${item.value.id}/bind/${props.externalRelation.relation}/${selectedItem.value}`,
      pivotData.value,
      {
        onSuccess: (page) => {
          item.value = page.props.flash.data
          pivotData.value = {}
          selectedItem.value = null
          getItems()
          emit("bound")
        },
      }
    )
  }
}

const updateItem = (id) => {
  router.post(
    `${props.endPoint}/${item.value.id}/pivot/${props.externalRelation.relation}/${id}`,
    pivotEditData.value,
    {
      onSuccess: (page) => {
        item.value = page.props.flash.data
        pivotEditData.value = {}
        pivotEditing.value = null
      },
      onError: (error) => {
        pivotEditData.value = {}
        pivotEditing.value = null
      },
    }
  )
}

const editItem = (id) => {
  const pivotItem = item.value[props.externalRelation.relation].find(
    (pivotItem) => pivotItem.id === id
  ).pivot

  pivotEditData.value = { ...pivotItem }

  props.externalRelation.pivotFields.forEach((field) => {
    if (field.type === "boolean") {
      const booleanValue = Boolean(Number(pivotEditData.value[field.field]))
      pivotEditData.value = {
        ...pivotEditData.value,
        [field.field]: booleanValue,
      }
    }
  })

  pivotEditing.value = id
}

const removeItem = (value) => {
  router.post(
    `${props.endPoint}/${item.value.id}/unbind/${props.externalRelation.relation}/${value}`,
    {},
    {
      onSuccess: (page) => {
        item.value = page.props.flash.data
        selectedItem.value = null
        getItems()
        emit("unbound")
      },
    }
  )
}

getItems()
</script>

<template>
  <v-row v-if="props.withTitle" class="align-center justify-center my-3">
    <v-col class="justify-center align-center text-center" cols="12">
      <span class="text-h5">{{ props.externalRelation.name }}</span>
    </v-col>
  </v-row>

  <v-form v-model="addForm" @submit.prevent="addItem">
    <v-row
      class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
    >
      <v-col
        cols="12"
        :md="
          !props.externalRelation.pivotFields ||
          props.externalRelation.pivotFields.length > 1
            ? 12
            : 6
        "
      >
        <v-autocomplete
          v-if="
            !props.externalRelation.pivotFields &&
            !props.externalRelation.serverSide
          "
          :label="props.externalRelation.name"
          v-model="selectedItem"
          :items="
            props.filteredItems?.[props.externalRelation.relation]
              ? props.filteredItems[props.externalRelation.relation](items)
              : items
          "
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[props.externalRelation.relation]
              )
          "
          :item-props="props.customItemProps?.[props.externalRelation.relation]"
          :item-title="generateItemTitle(props.externalRelation.formKey)"
          item-value="id"
          hide-details
          @update:modelValue="addItem"
        >
          <template v-if="props.externalRelation.storeShortcut" v-slot:prepend>
            <v-btn
              icon="mdi-plus-circle"
              @click="storeExternalShortcutShow = true"
            ></v-btn>
            <auto-form-dialog
              v-model:show="storeExternalShortcutShow"
              type="create"
              :filteredItems="props.filteredItems"
              :customFilters="props.customFilters"
              :customItemProps="props.customItemProps"
              :modelName="props.externalRelation.model"
              @update:show="getItems"
            />
          </template>
        </v-autocomplete>

        <v-autocomplete
          v-else-if="!props.externalRelation.serverSide"
          :label="props.externalRelation.name"
          v-model="selectedItem"
          :items="
            props.filteredItems?.[props.externalRelation.relation]
              ? props.filteredItems[props.externalRelation.relation](items)
              : items
          "
          :item-title="generateItemTitle(props.externalRelation.formKey)"
          :item-props="props.customItemProps?.[props.externalRelation.relation]"
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[props.externalRelation.relation]
              )
          "
          item-value="id"
          :rules="[ruleRequired]"
          density="compact"
        >
          <template v-if="props.externalRelation.storeShortcut" v-slot:prepend>
            <v-btn
              icon="mdi-plus-circle"
              density="compact"
              @click="storeExternalShortcutShow = true"
            ></v-btn>
            <auto-form-dialog
              v-model:show="storeExternalShortcutShow"
              type="create"
              :filteredItems="props.filteredItems"
              :customFilters="props.customFilters"
              :customItemProps="props.customItemProps"
              :modelName="props.externalRelation.model"
              @update:show="getItems"
            />
          </template>
        </v-autocomplete>
        <autocomplete-server
          v-else-if="!props.externalRelation.pivotFields"
          :label="props.externalRelation.name"
          v-model="selectedItem"
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[props.externalRelation.relation]
              )
          "
          :end-point="props.externalRelation.endPoint"
          :item-props="props.customItemProps?.[props.externalRelation.relation]"
          :item-title="props.externalRelation.formKey"
          hide-details
          :items="items"
          @update:modelValue="addItem"
        >
          <template v-if="props.externalRelation.storeShortcut" v-slot:prepend>
            <v-btn
              icon="mdi-plus-circle"
              @click="storeExternalShortcutShow = true"
            ></v-btn>
            <auto-form-dialog
              v-model:show="storeExternalShortcutShow"
              type="create"
              :filteredItems="props.filteredItems"
              :customFilters="props.customFilters"
              :customItemProps="props.customItemProps"
              :modelName="props.externalRelation.model"
              @update:show="getItems"
            />
          </template>
        </autocomplete-server>
        <autocomplete-server
          v-else-if="props.externalRelation.serverSide"
          :label="props.externalRelation.name"
          v-model="selectedItem"
          :item-title="props.externalRelation.formKey"
          :item-props="props.customItemProps?.[props.externalRelation.relation]"
          :custom-filter="
            (item, queryText, itemText) =>
              searchByWords(
                item,
                queryText,
                itemText,
                props.customFilters?.[props.externalRelation.relation]
              )
          "
          :rules="[ruleRequired]"
          density="compact"
          :end-point="props.externalRelation.endPoint"
          :items="items"
        >
          <template v-if="props.externalRelation.storeShortcut" v-slot:prepend>
            <v-btn
              icon="mdi-plus-circle"
              density="compact"
              @click="storeExternalShortcutShow = true"
            ></v-btn>
            <auto-form-dialog
              v-model:show="storeExternalShortcutShow"
              type="create"
              :filteredItems="props.filteredItems"
              :customFilters="props.customFilters"
              :customItemProps="props.customItemProps"
              :modelName="props.externalRelation.model"
              @update:show="getItems"
            />
          </template>
        </autocomplete-server>
      </v-col>
      <v-col
        cols="12"
        :md="props.externalRelation.pivotFields?.length > 1 ? 12 : 6"
        v-for="field in props.externalRelation.pivotFields ?? []"
      >
        <v-text-field
          v-if="
            field.type !== 'boolean' &&
            field.type !== 'date' &&
            field.type !== 'password' &&
            field.type !== 'select' &&
            field.type !== 'text'
          "
          density="compact"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="pivotData[field.field]"
          :rules="getFieldRules(pivotData[field.field], field)"
        >
        </v-text-field>

        <v-checkbox
          v-else-if="field.type === 'boolean'"
          density="compact"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="pivotData[field.field]"
          :rules="getFieldRules(pivotData[field.field], field)"
        ></v-checkbox>

        <v-text-field
          v-else-if="field.type === 'date'"
          type="date"
          density="compact"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="pivotData[field.field]"
          :rules="getFieldRules(pivotData[field.field], field)"
        ></v-text-field>

        <v-text-field
          v-else-if="field.type === 'password'"
          density="compact"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="pivotData[field.field]"
          :rules="getFieldRules(pivotData[field.field], field)"
          type="password"
        ></v-text-field>

        <v-select
          v-else-if="field.type === 'select'"
          density="compact"
          :items="field.options"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="pivotData[field.field]"
          :rules="getFieldRules(pivotData[field.field], field)"
          :clearable="!field.rules?.required"
        ></v-select>

        <v-textarea
          v-else-if="field.type === 'text'"
          :label="field.rules?.required ? field.name + ' *' : field.name"
          v-model="pivotData[field.field]"
          :rules="getFieldRules(pivotData[field.field], field)"
        ></v-textarea>
      </v-col>
      <v-col
        v-if="props.externalRelation.pivotFields"
        cols="12"
        class="text-center pt-0"
      >
        <v-btn
          @click="addItem()"
          :disabled="!addForm"
          color="blue-darken-1"
          variant="text"
        >
          Agregar
        </v-btn>
      </v-col>
    </v-row>
  </v-form>

  <v-row
    v-if="item && item[props.externalRelation.relation]"
    v-for="relationItem in item[props.externalRelation.relation]"
    :key="relationItem.id"
    class="pa-0 ma-0"
  >
    <v-row
      v-if="relationItem.id !== pivotEditing"
      class="align-center justify-center my-2 mx-1 elevation-6 rounded pa-2"
    >
      <v-col class="my-3">
        {{ generateItemTitle(props.externalRelation.formKey)(relationItem) }}
      </v-col>
      <v-col
        class="d-flex align-center justify-center"
        v-for="field in props.externalRelation.pivotFields ?? []"
      >
        <v-chip>
          {{ field.name }}:
          <v-checkbox
            density="compact"
            class="mt-5"
            v-if="field.type === 'boolean'"
            :model-value="Boolean(Number(relationItem.pivot[field.field]))"
            disabled
          ></v-checkbox>
          <span v-else>{{ relationItem.pivot[field.field] }}</span>
        </v-chip>
      </v-col>
      <v-col class="text-end">
        <v-btn
          v-if="props.externalRelation.pivotFields"
          icon
          density="compact"
          variant="text"
          @click="editItem(relationItem.id)"
        >
          <v-icon>mdi-pencil</v-icon>
          <v-tooltip right>{{ "Editar" }}</v-tooltip>
        </v-btn>
        <v-btn
          icon
          density="compact"
          variant="text"
          @click="removeItem(relationItem.id)"
        >
          <v-icon>mdi-delete</v-icon>
          <v-tooltip right>{{ "Eliminar" }}</v-tooltip>
        </v-btn>
      </v-col>
    </v-row>
    <v-form
      v-else
      v-model="updateForm"
      @submit.prevent="updateItem"
      class="w-100"
    >
      <v-row
        class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
      >
        <v-col
          cols="12"
          :md="
            !props.externalRelation.pivotFields ||
            props.externalRelation.pivotFields.length > 1
              ? 12
              : 6
          "
        >
          <v-autocomplete
            :label="props.externalRelation.name"
            :model-value="relationItem"
            :items="[relationItem]"
            :item-title="generateItemTitle(props.externalRelation.formKey)"
            item-value="id"
            density="compact"
            disabled
          ></v-autocomplete>
        </v-col>
        <v-col
          cols="12"
          :md="props.externalRelation.pivotFields?.length > 1 ? 12 : 6"
          v-for="field in props.externalRelation.pivotFields ?? []"
        >
          <v-text-field
            v-if="
              field.type !== 'boolean' &&
              field.type !== 'date' &&
              field.type !== 'password' &&
              field.type !== 'select' &&
              field.type !== 'text'
            "
            density="compact"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="pivotEditData[field.field]"
            :rules="getFieldRules(pivotEditData[field.field], field)"
          >
          </v-text-field>

          <v-checkbox
            v-else-if="field.type === 'boolean'"
            density="compact"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="pivotEditData[field.field]"
            :rules="getFieldRules(pivotEditData[field.field], field)"
          ></v-checkbox>

          <v-text-field
            v-else-if="field.type === 'date'"
            density="compact"
            type="date"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="pivotEditData[field.field]"
            :rules="getFieldRules(pivotEditData[field.field], field)"
          ></v-text-field>

          <v-text-field
            v-else-if="field.type === 'password'"
            density="compact"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="pivotEditData[field.field]"
            :rules="getFieldRules(pivotEditData[field.field], field)"
            type="password"
          ></v-text-field>

          <v-select
            v-else-if="field.type === 'select'"
            density="compact"
            :items="field.options"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="pivotEditData[field.field]"
            :rules="getFieldRules(pivotEditData[field.field], field)"
            :clearable="!field.rules?.required"
          ></v-select>

          <v-textarea
            v-else-if="field.type === 'text'"
            density="compact"
            :label="field.rules?.required ? field.name + ' *' : field.name"
            v-model="pivotEditData[field.field]"
            :rules="getFieldRules(pivotEditData[field.field], field)"
          ></v-textarea>
        </v-col>
        <v-col cols="12" class="text-center">
          <v-btn
            @click="updateItem(relationItem.id)"
            color="blue-darken-1"
            variant="text"
            :disabled="!updateForm"
          >
            Guardar
          </v-btn>
          <v-btn
            @click="pivotEditing = null"
            color="red-darken-1"
            variant="text"
          >
            Cancelar
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </v-row>
</template>
