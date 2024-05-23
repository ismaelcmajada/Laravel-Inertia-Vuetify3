<script setup>
import { ref, onBeforeMount } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"

const props = defineProps(["item", "endPoint", "externalRelation"])

const emit = defineEmits(["bound", "unbound"])

const items = ref([])
const selectedItem = ref(null)

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

const addItem = (value) => {
  if (value) {
    router.post(
      `${props.endPoint}/${item.value.id}/bind/${props.externalRelation.relation}/${value}`,
      {},
      {
        onSuccess: (page) => {
          item.value = page.props.flash.data
          selectedItem.value = null
          getItems()
          emit("bound")
        },
      }
    )
  }
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

onBeforeMount(() => {
  getItems()
})
</script>

<template>
  <v-row class="align-center justify-center my-3">
    <v-col class="justify-center align-center text-center" cols="12">
      <span class="text-h5">{{ props.externalRelation.name }}</span>
    </v-col>
  </v-row>

  <v-row
    class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
  >
    <v-col cols="12">
      <v-autocomplete
        class="ma-3"
        :label="props.externalRelation.name"
        v-model="selectedItem"
        :items="items"
        :item-title="props.externalRelation.formKey"
        item-value="id"
        @update:modelValue="(v) => addItem(v)"
        hide-details
      >
      </v-autocomplete>
    </v-col>
  </v-row>

  <v-row
    v-if="item && item[props.externalRelation.relation]"
    v-for="relationItem in item[props.externalRelation.relation]"
    class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-2"
  >
    <v-col cols="12" md="10" class="my-3">
      {{ relationItem[props.externalRelation.formKey] }}
    </v-col>
    <v-col cols="12" md="1" class="text-center">
      <v-btn
        icon
        density="compact"
        variant="text"
        @click="removeItem(relationItem.id)"
      >
        <v-icon>mdi-delete</v-icon>
        <v-tooltip activator="parent">Eliminar</v-tooltip>
      </v-btn>
    </v-col>
  </v-row>
</template>
