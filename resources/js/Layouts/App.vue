<script setup>
import { useToast } from "vue-toastification"
import { watch } from "vue"
import { usePage } from "@inertiajs/vue3"

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.success) {
      useToast().success(flash.success)
    }
  },
  { deep: true }
)

watch(
  () => usePage().props.errors,
  (errors) => {
    Object.values(errors).forEach((error) => {
      useToast().error(error)
    })
  },
  { deep: true }
)
</script>

<template>
  <v-app id="inspire">
    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>
