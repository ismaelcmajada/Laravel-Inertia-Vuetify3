<script setup>
import { usePage } from "@inertiajs/vue3"
import { ref } from "vue"

const page = usePage()

const navigation = page.props.navigation

const drawer = ref(false)
const open = ref([])

Object.values(navigation).forEach((route) => {
  if (route.hasOwnProperty("childs")) {
    route.childs.forEach((child) => {
      if (page.url.includes(child.path)) {
        open.value.push(route.name)
      }
    })
  }
})
</script>

<template>
  <v-navigation-drawer theme="customDark" v-model="drawer" temporary>
    <v-list>
      <v-list-item title="Suscriptores"> </v-list-item>
    </v-list>
    <template v-for="pageRoute in navigation">
      <v-divider
        v-if="
          !pageRoute.hasOwnProperty('path') &&
          !pageRoute.hasOwnProperty('childs')
        "
      ></v-divider>
      <v-list
        v-if="
          !pageRoute.hasOwnProperty('path') &&
          !pageRoute.hasOwnProperty('childs')
        "
        nav
      >
        <v-list-item
          :title="pageRoute.name"
          :prepend-icon="pageRoute.icon"
        ></v-list-item>
      </v-list>
      <v-divider
        v-if="
          !pageRoute.hasOwnProperty('path') &&
          !pageRoute.hasOwnProperty('childs')
        "
      ></v-divider>
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
        <v-list v-else nav>
          <v-list-item
            :title="pageRoute.name"
            :prepend-icon="pageRoute.icon"
            :active="$page.url.includes(pageRoute.path)"
            :to="pageRoute.path"
          ></v-list-item>
        </v-list>
      </template>
    </template>
  </v-navigation-drawer>
  <v-app-bar elevation="1">
    <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
    <slot name="top">
      <v-toolbar-title>Suscriptores</v-toolbar-title>
    </slot>
  </v-app-bar>
</template>
