<script setup>
import { usePage } from "@inertiajs/vue3"
import { ref, onBeforeMount } from "vue"
import { routes } from "@/Utils/routes"

const rail = ref(false)
const open = ref([])
const lastOpen = ref([])

onBeforeMount(() => {
  routes.value.forEach((e) => {
    if (e.hasOwnProperty("childs") && usePage().url.includes(e.path)) {
      open.value.push(e.value)
    }
  })
  routes.value = { newValue: usePage().props?.auth.user.name }
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
  <v-navigation-drawer theme="customDark" elevation="6" :rail="rail" permanent>
    <v-list>
      <v-list-item title="Sucriptores"> </v-list-item>
    </v-list>
    <template v-for="pageRoute in routes">
      <v-divider></v-divider>
      <v-list v-if="!pageRoute.hasOwnProperty('path')" nav>
        <v-list-item
          :title="pageRoute.value"
          :prepend-icon="pageRoute.icon"
        ></v-list-item>
      </v-list>
      <template v-else>
        <v-list
          v-if="pageRoute.hasOwnProperty('childs')"
          v-model:opened="open"
          nav
        >
          <v-list-group :value="pageRoute.value">
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :title="pageRoute.value"
                :prepend-icon="pageRoute.icon"
                :active="$page.url.includes(pageRoute.path)"
                @click="openDrawer"
              ></v-list-item>
            </template>
            <v-list-item
              v-for="child in pageRoute.childs"
              :prepend-icon="child.icon"
              :title="child.value"
              :active="$page.url.includes(child.path)"
              :to="child.path"
            ></v-list-item>
          </v-list-group>
        </v-list>
        <v-list v-else nav>
          <v-list-item
            :title="pageRoute.value"
            :prepend-icon="pageRoute.icon"
            :active="$page.url.includes(pageRoute.path)"
            :to="pageRoute.path"
          ></v-list-item>
        </v-list>
      </template>
    </template>
  </v-navigation-drawer>
  <v-app-bar elevation="1">
    <v-app-bar-nav-icon @click="closeAll"></v-app-bar-nav-icon>
    <v-toolbar-title>Suscriptores</v-toolbar-title>
  </v-app-bar>
</template>
