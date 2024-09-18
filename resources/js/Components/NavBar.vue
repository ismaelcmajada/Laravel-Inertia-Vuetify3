<script setup>
import { usePage } from "@inertiajs/vue3"
import { ref, computed } from "vue"

const page = usePage()
const props = defineProps(["theme"])
const emit = defineEmits(["update:theme"])

const theme = computed({
  get: () => props.theme,
  set: (value) => {
    emit("update:theme", value)
  },
})

const navigation = page.props.navigation

const rail = ref(false)
const open = ref([])
const lastOpen = ref([])

Object.values(navigation).forEach((route) => {
  if (route.hasOwnProperty("childs")) {
    route.childs.forEach((child) => {
      if (page.url.includes(child.path)) {
        open.value.push(route.name)
      }
    })
  }
})

const closeAll = () => {
  if (!rail.value) {
    lastOpen.value = open.value
    open.value = []
    rail.value = true
  } else {
    rail.value = false
    open.value = lastOpen.value
  }
}

const openDrawer = () => {
  if (rail.value) {
    rail.value = false
  }
}
</script>

<template>
  <v-navigation-drawer theme="appTheme" elevation="6" :rail="rail" permanent>
    <v-list>
      <v-list-item title="Suscriptores"></v-list-item>
    </v-list>
    <template v-for="pageRoute in navigation">
      <template
        v-if="
          !pageRoute.hasOwnProperty('path') &&
          !pageRoute.hasOwnProperty('childs')
        "
      >
        <v-divider></v-divider>
        <v-list nav>
          <v-list-item
            :title="pageRoute.name"
            :prepend-icon="pageRoute.icon"
          ></v-list-item>
        </v-list>
        <v-divider></v-divider>
      </template>
      <template v-else>
        <v-list
          v-if="pageRoute.hasOwnProperty('childs')"
          v-model:opened="open"
          nav
        >
          <v-list-group :value="pageRoute.name">
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :title="pageRoute.name"
                :prepend-icon="pageRoute.icon"
                @click="openDrawer"
              ></v-list-item>
            </template>
            <v-list-item
              v-for="child in pageRoute.childs"
              :prepend-icon="child.icon"
              :title="child.name"
              :active="$page.url.includes(child.path)"
              :to="child.path"
            ></v-list-item>
          </v-list-group>
        </v-list>
        <template v-else>
          <v-divider
            v-if="pageRoute.hasOwnProperty('dividers') && pageRoute.dividers"
          ></v-divider>
          <v-list nav>
            <v-list-item
              :title="pageRoute.name"
              :prepend-icon="pageRoute.icon"
              :active="$page.url.includes(pageRoute.path)"
              :to="pageRoute.path"
            ></v-list-item>
          </v-list>
          <v-divider
            v-if="pageRoute.hasOwnProperty('dividers') && pageRoute.dividers"
          ></v-divider>
        </template>
      </template>
    </template>
  </v-navigation-drawer>
  <v-app-bar elevation="1">
    <v-app-bar-nav-icon @click="closeAll"></v-app-bar-nav-icon>
    <slot name="top">
      <v-toolbar-title>Suscriptores</v-toolbar-title>
    </slot>
    <slot name="top-right">
      <v-switch
        inset
        color="info"
        v-model="theme"
        false-value="customLight"
        true-value="customDark"
        class="mr-5"
        hide-details
      ></v-switch>
    </slot>
  </v-app-bar>
</template>
