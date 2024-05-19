<script setup>
import AutoTable from "@/Components/AutoCrud/AutoTable.vue"

const props = defineProps(["model"])
</script>

<template>
  <auto-table title="Paises" :model="props.model" />
</template>
