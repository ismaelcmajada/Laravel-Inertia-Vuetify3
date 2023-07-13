<script setup>
import { ref, computed } from "vue"

const props = defineProps(["text", "length"])

const text = computed(() => props.text ?? "")
const length = computed(() => props.length ?? 10)
const state = ref(false)

const list = computed(() => text.value.split(","))

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
  <div @click="state = !state">
    <ul v-if="state && list.length > 1">
      <li v-for="item in list">{{ item }}</li>
    </ul>
    <div v-else>
      {{ truncateString() }}
    </div>
  </div>
</template>
