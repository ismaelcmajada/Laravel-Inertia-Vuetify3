<script setup>
import { onBeforeMount } from "vue"
import FormDialog from "@/Components/Suscriptor/FormDialog.vue"
import DestroyDialog from "@/Components/DestroyDialog.vue"
import RestoreDialog from "@/Components/RestoreDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import LoadingOverlay from "@/Components/LoadingOverlay.vue"
import ExpandableText from "@/Components/ExpandableText.vue"
import ExpandableList from "@/Components/ExpandableList.vue"
import useTableServer from "@/Composables/useTableServer"
import useDialogs from "@/Composables/useDialogs"
import { exportToExcel } from "@/Utils/excel"

const {
  endPoint,
  loading,
  itemsPerPageOptions,
  updateItems,
  tableData,
  tableHeaders,
  selectedHeaders,
  allHeaders,
  itemHeaders,
  toggleAllHeaders,
  loadItems,
  resetTable,
} = useTableServer()

const {
  showFormDialog,
  formDialogType,
  showDestroyDialog,
  showRestoreDialog,
  showDestroyPermanentDialog,
  item,
  openDialog,
} = useDialogs()

const headers = [
  { title: "Id", key: "id", align: "center" },
  { title: "Nombre", key: "nombre", align: "center" },
  { title: "Apellidos", key: "apellidos", align: "center" },
  { title: "DNI", key: "dni", align: "center" },
  { title: "Email", key: "email", align: "center" },
  { title: "Teléfono", key: "telefono", align: "center" },
  { title: "Sexo", key: "sexo", align: "center" },
  {
    title: "Acciones",
    key: "actions",
    align: "center",
    sortable: false,
    exportable: false,
  },
]

onBeforeMount(() => {
  itemHeaders.value = headers
  selectedHeaders.value = headers.map((h) => h.key)
})

const modifiedRows = {}

endPoint.value = "/dashboard/suscriptores"
</script>

<template>
  <form-dialog
    :show="showFormDialog"
    @closeDialog="showFormDialog = false"
    @reloadItems="loadItems()"
    :type="formDialogType"
    v-model:item="item"
    :endPoint="endPoint"
  />
  <destroy-dialog
    :show="showDestroyDialog"
    @closeDialog="showDestroyDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
  />
  <restore-dialog
    :show="showRestoreDialog"
    @closeDialog="showRestoreDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
  />
  <destroy-permanent-dialog
    :show="showDestroyPermanentDialog"
    @closeDialog="showDestroyPermanentDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
  />

  <v-card elevation="6" class="ma-5" variant="outlined">
    <v-data-table-server
      :loading="loading"
      multi-sort
      :items-per-page-options="itemsPerPageOptions"
      v-model:items-per-page="tableData.itemsPerPage"
      v-model:sort-by="tableData.sortBy"
      v-model:page="tableData.page"
      :headers="tableHeaders"
      :items-length="tableData.itemsLength"
      :items="tableData.items"
      @update:options="loadItems()"
    >
      <template v-slot:top>
        <v-toolbar :class="{ 'bg-red-lighten-2': tableData.deleted }" flat>
          <v-toolbar-title>
            Suscriptores
            <span v-if="tableData.deleted"> - ELIMINADOS</span>
          </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <div v-if="!tableData.deleted">
            <v-btn icon="mdi-refresh" @click="resetTable"> </v-btn>
            <v-btn icon="mdi-file-plus-outline" @click="openDialog('create')"> </v-btn>
            <v-btn
              icon="mdi-file-excel-outline"
              @click="exportToExcel(endPoint, headers, modifiedRows)"
            >
            </v-btn>
          </div>
          <v-btn
            :active="tableData.deleted"
            icon="mdi-delete-variant"
            @click="tableData.deleted = !tableData.deleted"
          >
          </v-btn>
        </v-toolbar>
      </template>

      <template v-slot:thead>
        <tr>
          <td
            v-for="header in headers.filter(
              (header) => selectedHeaders.includes(header.key) && header.key != 'actions'
            )"
            :key="header.key"
          >
            <v-text-field
              v-model="tableData.search[header.key]"
              @input="updateItems"
              type="text"
              class="px-1"
              variant="underlined"
            ></v-text-field>
          </td>
          <td>
            <v-select
              label="Columnas"
              v-model="selectedHeaders"
              :items="itemHeaders"
              item-title="title"
              item-value="key"
              variant="underlined"
              class="px-1 overflow-hidden text-no-wrap"
              multiple
              clearable
            >
              <template v-slot:selection="{ item, index }">
                <span
                  v-if="selectedHeaders.length > 0 && index === 0"
                  class="text-grey text-caption align-self-center"
                >
                  {{ selectedHeaders.length }} seleccionadas
                </span>
              </template>

              <template v-slot:prepend-item>
                <v-list-item title="Todas" @click="toggleAllHeaders">
                  <template v-slot:prepend>
                    <v-checkbox-btn :model-value="allHeaders"></v-checkbox-btn>
                  </template>
                </v-list-item>
              </template>
            </v-select>
          </td>
        </tr>
      </template>

      <template v-for="(modifier, key) in modifiedRows" v-slot:[`item.${key}`]="{ item }">
        {{ modifier(item.raw[key]) }}
      </template>

      <template v-slot:item.actions="{ item }">
        <div v-if="!tableData.deleted">
          <v-btn
            density="compact"
            variant="text"
            icon="mdi-pencil"
            @click="openDialog('edit', item.raw)"
          ></v-btn>
          <v-btn
            density="compact"
            variant="text"
            icon="mdi-delete"
            @click="openDialog('destroy', item.raw)"
          ></v-btn>
        </div>
        <div v-if="tableData.deleted">
          <v-btn
            density="compact"
            variant="text"
            icon="mdi-restore"
            @click="openDialog('restore', item.raw)"
          ></v-btn>
          <v-btn
            density="compact"
            variant="text"
            icon="mdi-delete-alert"
            @click="openDialog('destroyPermanent', item.raw)"
          ></v-btn>
        </div>
      </template>
    </v-data-table-server>
    <loading-overlay v-if="loading" />
  </v-card>
</template>
