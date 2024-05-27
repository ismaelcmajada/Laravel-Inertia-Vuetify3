<script setup>
import AutoTable from "@/Components/AutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const model = page.props.models.pais
</script>

<template>
  <auto-table title="Paises" :model="model" />
</template>
