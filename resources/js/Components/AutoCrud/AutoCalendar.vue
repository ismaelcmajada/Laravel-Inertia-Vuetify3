<script setup>
import VueCal from "vue-cal"
import "vue-cal/dist/vuecal.css"
import axios from "axios"
import { ref } from "vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

const props = defineProps(["model"])
const endPoint = props.model.endPoint

const events = ref([])

function convertirFecha(fechaISO) {
  const fecha = new Date(fechaISO)

  const year = fecha.getFullYear()
  const month = String(fecha.getMonth() + 1).padStart(2, "0")
  const day = String(fecha.getDate()).padStart(2, "0")
  const hours = String(fecha.getHours()).padStart(2, "0")
  const minutes = String(fecha.getMinutes()).padStart(2, "0")

  return `${year}-${month}-${day} ${hours}:${minutes}`
}

const loadEvents = () => {
  axios.post(`${endPoint}/load-calendar-events`, {}).then((response) => {
    events.value = response.data.eventsData.items
    events.value = events.value.map((event) => {
      event.start = convertirFecha(event.start)
      event.end = convertirFecha(event.end)
      return event
    })
  })
}

loadEvents()

const today = new Date()
</script>

<template>
  <vue-cal
    class="ma-5"
    events-on-month-view="short"
    :selected-date="today"
    active-view="month"
    :events="events"
    :time="true"
    locale="es"
    :editable-events="{ title: true, drag: true, resize: true }"
  ></vue-cal>
</template>

<style>
.vuecal__event.cell {
  background-color: #424242;
  border-style: solid;
  border-width: 1px 0px;
  border-color: #212121;
  color: #fff;
  text-align: center;
}

.vuecal__header,
.vuecal__menu,
.vuecal__title-bar,
.vuecal__title button,
.vuecal__arrow {
  background-color: #424242;
  color: white;
}

.vuecal__header,
.vuecal__menu {
  border-radius: 4px;
}

.vuecal__body,
.vuecal__weekdays-headings {
  background-color: #212121;
  color: white;
}

.vuecal__body {
  border-radius: 4px;
}

.vuecal__cell--out-of-scope {
  color: gray;
}

.vuecal {
  border-radius: 4px;
  border: thin solid white;
  height: 807px;
}

.vuecal--short-events .vuecal__event-title {
  text-align: center;
}

.vuecal__time-column .vuecal__time-cell {
  text-align: center;
  padding-right: 0px;
}
</style>
