<script setup>
import VueCal from "vue-cal"
import "vue-cal/dist/vuecal.css"
import axios from "axios"
import AutoFormDialog from "./AutoFormDialog.vue"
import useDialogs from "../../Composables/useDialogs"
import { formatCalendarDate, formatDate } from "@/Utils/dates"
import { ref, watch } from "vue"

const props = defineProps(["model"])
const endPoint = props.model.endPoint
const currentDateInterval = ref({
  startDate: null,
  endDate: null,
})

const { showFormDialog, formDialogType, item, openDialog } = useDialogs()

const events = ref([])

const changeCurrentMonth = (event) => {
  if (event.view === "month" || event.view === "week" || event.view === "day") {
    currentDateInterval.value.startDate = event.startDate.toISOString()
    currentDateInterval.value.endDate = event.endDate.toISOString()
    loadEvents()
  }
}

const handleDialog = (date, type, item) => {
  if (
    activeView.value === "month" ||
    activeView.value === "week" ||
    activeView.value === "day"
  ) {
    if (type === "create") {
      if (date) {
        let createItem = {}
        createItem[props.model.calendarFields.start] = formatCalendarDate(
          date,
          true
        )

        createItem[props.model.calendarFields.end] = formatCalendarDate(
          date,
          true
        )

        openDialog("create", createItem)
      } else {
        openDialog("create")
      }
    } else if (type === "edit") {
      openDialog("edit", item)
    }
  }
}

const loadEvents = () => {
  axios
    .post(`${endPoint}/load-calendar-events`, {
      start: currentDateInterval.value.startDate,
      end: currentDateInterval.value.endDate,
    })
    .then((response) => {
      events.value = response.data.eventsData.items
      events.value = events.value.map((event) => {
        event.start = formatCalendarDate(event.start)
        event.end = formatCalendarDate(event.end)
        return event
      })
    })
}

watch(showFormDialog, (value) => {
  if (!value) {
    loadEvents()
  }
})

const activeView = ref("month")
</script>

<template>
  <auto-form-dialog
    v-model:show="showFormDialog"
    v-model:type="formDialogType"
    v-model:item="item"
    :model="props.model"
  >
  </auto-form-dialog>
  <vue-cal
    class="ma-5"
    events-on-month-view="short"
    v-model:active-view="activeView"
    @view-change="changeCurrentMonth"
    @ready="changeCurrentMonth"
    :events="events"
    :time="false"
    locale="es"
    :onEventClick="(item) => handleDialog(null, 'edit', item.item)"
    @cell-click="($event) => handleDialog($event, 'create')"
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
  border-radius: 4px 4px 0px 0px;
}

.vuecal__body,
.vuecal__weekdays-headings {
  background-color: #212121;
  color: white;
}

.vuecal__body {
  border-radius: 0px 0px 4px 4px;
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
