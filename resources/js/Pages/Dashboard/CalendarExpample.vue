<script setup>
import AutoCalendar from "@/Components/LaravelAutoCrud/AutoCalendar.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const model = page.props.models.pais
</script>

<template>
  <AutoCalendar :model="model"></AutoCalendar>
</template>
