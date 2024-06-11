<script setup>
import AutoTable from "@/Components/AutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const model = page.props.models.user
</script>

<template>
  <auto-table title="Usuarios" :model="model" />

  <auto-table title="Paises" :model="page.props.models.pais" />
</template>
