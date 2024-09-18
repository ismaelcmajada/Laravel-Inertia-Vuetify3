<script setup>
import { Head } from "@inertiajs/vue3"
import { useDisplay, useTheme } from "vuetify"
import AppLayout from "@/Layouts/App.vue"
import NavBar from "@/Components/NavBar.vue"
import NavBarMobile from "@/Components/NavBarMobile.vue"
import { watch, ref } from "vue"

const { mobile } = useDisplay()
const theme = useTheme()

const themeFromStorage = localStorage.getItem("theme")
const themeMode = ref(themeFromStorage ?? "customLight")

watch(themeMode, (value) => {
  theme.global.name.value = value
  localStorage.setItem("theme", value)
})
</script>

<template>
  <Head title="Dashboard" />
  <app-layout>
    <nav-bar-mobile v-model:theme="themeMode" v-if="mobile" />
    <nav-bar v-model:theme="themeMode" v-else />
    <slot />
  </app-layout>
</template>
