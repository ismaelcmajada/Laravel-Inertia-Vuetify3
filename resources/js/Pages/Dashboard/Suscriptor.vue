<script setup>
import AutoTable from "@/Components/AutoCrud/AutoTable.vue"

const props = defineProps(["model"])

console.log(props.model)
</script>

<template>
  <auto-table title="Suscriptores" :model="props.model" />
</template>
