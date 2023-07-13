<script setup>
import { usePage } from "@inertiajs/vue3"
import { ref, onBeforeMount } from "vue"
import { checkRoute } from "@/Utils/url"
import { routes } from "@/Utils/routes"

const rail = ref(false)
const open = ref([])
const lastOpen = ref([])

onBeforeMount(() => {
  routes.forEach((e) => {
    if (e.hasOwnProperty("childs") && usePage().url.includes(e.path)) {
      open.value.push(e.value)
    }
  })
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
    <v-list nav>
      <v-list-item
        prepend-icon="mdi-account-circle"
        :title="$page.props.auth.user.name"
      ></v-list-item>
    </v-list>

    <v-divider></v-divider>

    <template v-for="pageRoute in routes">
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
            :active="checkRoute(child.route)"
            :to="route(child.route)"
          ></v-list-item>
        </v-list-group>
      </v-list>

      <v-list v-else nav>
        <v-list-item
          :title="pageRoute.value"
          :prepend-icon="pageRoute.icon"
          :active="checkRoute(pageRoute.route)"
          :to="route(pageRoute.route)"
        ></v-list-item>
      </v-list>
    </template>

    <v-divider></v-divider>

    <v-list nav>
      <v-list-item
        prepend-icon="mdi-logout-variant"
        :to="route('logout')"
        method="post"
        title="Cerrar sesión"
      ></v-list-item>
    </v-list>
  </v-navigation-drawer>

  <v-app-bar elevation="1">
    <v-app-bar-nav-icon @click="closeAll"></v-app-bar-nav-icon>
    <v-toolbar-title>Suscriptores</v-toolbar-title>
  </v-app-bar>
</template>
