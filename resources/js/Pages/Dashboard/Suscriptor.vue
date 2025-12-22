<script setup>
import AutoTable from "@/Components/LaravelAutoCrud/AutoTable.vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const model = page.props.models.suscriptor
console.log(model)
</script>

<template>
  <auto-table title="Suscriptores" :model="model"> </auto-table>
</template>
