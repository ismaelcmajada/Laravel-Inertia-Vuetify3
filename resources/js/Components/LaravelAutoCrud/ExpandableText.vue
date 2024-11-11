<script setup>
import { ref, computed } from "vue"

const props = defineProps(["text", "length"])

const text = computed(() => props.text ?? "")
const length = computed(() => props.length ?? 10)
const state = ref(false)

const truncateString = () => {
  if (text.value.length > length.value && !state.value) {
    return text.value.slice(0, length.value).trim() + "..."
  } else {
    state.value = true
    return text.value
  }
}
</script>

<template>
  <div @click="state = !state">{{ truncateString() }}</div>
</template>
