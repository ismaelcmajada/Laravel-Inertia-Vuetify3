<script setup>
import { Head } from "@inertiajs/vue3"
import { useDisplay } from "vuetify"
import AppLayout from "@/Layouts/App.vue"
import NavBar from "@/Components/NavBar.vue"
import NavBarMobile from "@/Components/NavBarMobile.vue"

const { mobile } = useDisplay()
</script>

<template>
  <Head title="Dashboard" />
  <app-layout>
    <nav-bar-mobile v-if="mobile" />
    <nav-bar v-else />
    <slot />
  </app-layout>
</template>
