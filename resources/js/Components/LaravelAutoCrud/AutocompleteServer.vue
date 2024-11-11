<script setup>
import { ref, watch } from "vue"
import axios from "axios"
import debounce from "lodash.debounce"
import { generateItemTitle } from "../../Utils/LaravelAutoCrud/autocompleteUtils"

const props = defineProps([
  "modelValue",
  "itemTitle",
  "label",
  "rules",
  "endPoint",
  "hideDetails",
  "customFilter",
  "density",
  "itemProps",
  "customFilters",
  "item",
  "items",
])
const emit = defineEmits(["update:modelValue"])

const loading = ref(false)
const items = ref([])

const selectedItem = ref(props.item)

watch(selectedItem, (value) => {
  emit("update:modelValue", value?.id)
})

let waitingForData = false

const loadAutocompleteItems = (search) => {
  if (!loading.value) {
    loading.value = true
  }

  debounceLoadAutocompleteItems(search)
}

const debounceLoadAutocompleteItems = debounce((search) => {
  if (search) {
    if (waitingForData) return

    waitingForData = true

    axios
      .post(`${props.endPoint}/load-autocomplete-items`, {
        search: search,
        key: props.itemTitle,
      })
      .then((response) => {
        items.value = response.data.autocompleteItems

        if (props.items) {
          items.value = items.value?.filter((item) =>
            props.items.some((relatedItem) => relatedItem.id === item.id)
          )
        }
        waitingForData = false
        loading.value = false
      })
  } else {
    items.value = []
    loading.value = false
  }
}, 500)
</script>

<template>
  <v-autocomplete
    clearable
    :item-title="generateItemTitle(props.itemTitle)"
    :label="props.label"
    v-model="selectedItem"
    :loading="loading"
    @update:search="loadAutocompleteItems"
    return-object
    :rules="props.rules"
    :items="items"
    :item-props="props.itemProps"
    :custom-filter="props.customFilter"
    :hide-details="props.hideDetails"
    :density="props.density"
  >
    <template v-if="$slots.append" v-slot:append>
      <slot name="append"></slot>
    </template>
    <template v-if="$slots.prepend" v-slot:prepend>
      <slot name="prepend"></slot>
    </template>
  </v-autocomplete>
</template>
