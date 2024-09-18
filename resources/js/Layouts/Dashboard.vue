<script setup>
import { Head } from "@inertiajs/vue3"
import { useDisplay } from "vuetify"
import AppLayout from "@/Layouts/App.vue"
import NavBar from "@/Components/NavBar.vue"
import NavBarMobile from "@/Components/NavBarMobile.vue"
import { router } from "@inertiajs/vue3"

const { mobile } = useDisplay()

const mode = computed(() => {
  return page.props?.mode ?? "customLight"
})

const selectedStore = ref(mode.value)

watch(mode, (value) => {
  selectedStore.value = value
})

const updateSession = () => {
  router.post(
    "/dashboard/session/setSession",
    {
      key: "mode",
      value: selectedStore.value,
    },
    { preserveState: true, preserveScroll: true }
  )
}
</script>

<template>
  <Head title="Dashboard" />
  <app-layout>
    <nav-bar-mobile v-if="mobile" />
    <nav-bar v-else />
    <slot />
  </app-layout>
</template>
