<script setup>
import NavBar from "@/Components/NavBar.vue"
import AppLayout from "@/Layouts/App.vue"
import { Head } from "@inertiajs/vue3"
</script>

<template>
  <Head title="Dashboard" />
  <app-layout>
    <nav-bar />
    <slot />
  </app-layout>
</template>
