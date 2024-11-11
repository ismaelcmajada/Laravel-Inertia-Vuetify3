<script setup>
import AutoTable from "@/Components/LaravelAutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const model = page.props.models.user
</script>

<template>
  <auto-table title="Usuarios" :model="model" />
</template>
