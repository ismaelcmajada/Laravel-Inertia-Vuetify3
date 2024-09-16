<script setup>
import AutoTable from "@/Components/AutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const model = page.props.models.suscriptor
</script>

<template>
  <auto-table title="Suscriptores" :model="model"> </auto-table>
</template>
