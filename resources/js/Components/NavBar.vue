<script setup>
import { usePage } from "@inertiajs/vue3"
import { ref } from "vue"
import { routes } from "@/Utils/routes"
import axios from "axios"

const page = usePage()

const user = page.props.auth.user

const forbiddenActions = ref(null)
const filteredRoutes = ref([])

const rail = ref(false)
const open = ref([])
const lastOpen = ref([])

const getRoutes = async () => {
  const response = await axios.get("/dashboard/get-forbidden-accesses")
  forbiddenActions.value = response.data

  routes.value = { newValue: page.props?.auth.user.name }

  filteredRoutes.value = routes.value.filter((route) => {
    let model = route.path?.split("/").pop()

    return (
      forbiddenActions.value[model] === undefined ||
      forbiddenActions.value[model][user.role].indexOf("index") === -1
    )
  })

  filteredRoutes.value.forEach((route) => {
    if (route.hasOwnProperty("childs")) {
      route.childs = route.childs.filter((child) => {
        let model = child.path?.split("/").pop()

        if (page.url.includes(child.path)) {
          open.value.push(route.value)
        }

        return (
          forbiddenActions.value[model] === undefined ||
          forbiddenActions.value[model][user.role].indexOf("index") === -1
        )
      })
    }
  })
}

getRoutes()

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
    <template v-for="pageRoute in filteredRoutes">
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
