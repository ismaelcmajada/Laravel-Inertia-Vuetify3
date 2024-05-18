<script setup>
import AutoFormDialog from "./AutoFormDialog.vue"
import AutoForm from "./AutoForm.vue"
import DestroyDialog from "../DestroyDialog.vue"
import RestoreDialog from "../RestoreDialog.vue"
import DestroyPermanentDialog from "../DestroyPermanentDialog.vue"
import LoadingOverlay from "../LoadingOverlay.vue"
import useTableServer from "../../Composables/useTableServer"
import useDialogs from "../../Composables/useDialogs"

const props = defineProps([
  "title",
  "model",
  "modifiedRows",
  "search",
  "orderBy",
])

const model = props.model

const {
  endPoint,
  loading,
  itemsPerPageOptions,
  updateItems,
  tableData,
  loadItems,
  resetTable,
} = useTableServer()

if (props.search) {
  tableData.search = props.search
}

if (props.orderBy) {
  tableData.sortBy = props.orderBy
}

const {
  showFormDialog,
  formDialogType,
  showDestroyDialog,
  showRestoreDialog,
  showDestroyPermanentDialog,
  item,
  openDialog,
} = useDialogs()

const modifiedRows = props.modifiedRows

const title = props.title
endPoint.value = model.endPoint
</script>

<template>
  <slot
    name="auto-form-dialog"
    :show="showFormDialog"
    :type="formDialogType"
    :item="item"
    :model="model"
    :reloadItems="loadItems"
    :closeDialog="() => (showFormDialog = false)"
  >
    <auto-form-dialog
      :show="showFormDialog"
      @closeDialog="showFormDialog = false"
      @reloadItems="loadItems"
      v-model:type="formDialogType"
      :item="item"
      :model="model"
    >
      <template #prepend="{ model, type, item, closeDialog, reloadItems }">
        <slot
          name="auto-form-dialog.prepend"
          :model="model"
          :type="type"
          :item="item"
          :closeDialog="closeDialog"
          :reloadItems="reloadItems"
        ></slot>
      </template>
      <template #auto-form="{ model, type, item, closeDialog, reloadItems }">
        <slot
          name="auto-form-dialog.auto-form"
          :model="model"
          :type="type"
          :item="item"
          :closeDialog="closeDialog"
          :reloadItems="reloadItems"
        >
          <auto-form
            :model="model"
            :type="type"
            :item="item"
            @created="closeDialog"
          />
        </slot>
      </template>
      <template #append="{ model, type, item, closeDialog, reloadItems }">
        <slot
          name="auto-form-dialog.append"
          :model="model"
          :type="type"
          :item="item"
          :closeDialog="closeDialog"
          :reloadItems="reloadItems"
        ></slot>
      </template>
    </auto-form-dialog>
  </slot>

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
      multi-sort
      :loading="loading"
      :headers="model.tableHeaders"
      :items="tableData.items"
      :items-length="tableData.itemsLength"
      :items-per-page-options="itemsPerPageOptions"
      v-model:page="tableData.page"
      v-model:sort-by="tableData.sortBy"
      v-model:items-per-page="tableData.itemsPerPage"
      @update:options="loadItems()"
    >
      <template v-slot:top>
        <v-toolbar :class="{ 'bg-red-lighten-2': tableData.deleted }" flat>
          <v-toolbar-title>
            <span>{{ title }}</span>
          </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <slot
            name="table.actions"
            :openDialog="openDialog"
            :resetTable="resetTable"
            :tableData="tableData"
            :loadItems="loadItems"
          >
            <template v-if="!tableData.deleted">
              <v-btn icon @click="resetTable">
                <v-icon>mdi-refresh</v-icon>
                <v-tooltip activator="parent">Recargar tabla</v-tooltip>
              </v-btn>

              <v-btn icon @click="openDialog('create')">
                <v-icon>mdi-file-plus-outline</v-icon>
                <v-tooltip activator="parent">Crear tipo de articulo</v-tooltip>
              </v-btn>
            </template>

            <v-btn
              :active="tableData.deleted"
              icon
              @click="tableData.deleted = !tableData.deleted"
            >
              <v-icon>mdi-delete-variant</v-icon>
              <v-tooltip activator="parent">{{
                tableData.deleted ? "Ver activos" : "Ver eliminados"
              }}</v-tooltip>
            </v-btn>
          </slot>
        </v-toolbar>
      </template>

      <template v-slot:thead>
        <tr>
          <td
            v-for="header in model.tableHeaders.filter(
              (header) => header.key != 'actions'
            )"
            :key="header.key"
          >
            <v-text-field
              v-if="header.key"
              v-model="tableData.search[header.key]"
              @input="updateItems"
              type="text"
              class="px-1"
              variant="underlined"
            ></v-text-field>
          </td>
        </tr>
      </template>

      <template
        v-for="(modifier, key) in modifiedRows"
        v-slot:[`item.${key}`]="slotProps"
      >
        <template v-if="slotProps && 'item' in slotProps && slotProps.item">
          {{ modifier(slotProps.item[key]) }}
        </template>
      </template>

      <template v-slot:item.actions="{ item }">
        <slot
          name="item.actions"
          :item="item"
          :openDialog="openDialog"
          :resetTable="resetTable"
          :tableData="tableData"
          :loadItems="loadItems"
        >
          <div v-if="!tableData.deleted">
            <v-btn
              density="compact"
              variant="text"
              icon
              @click="openDialog('edit', item)"
            >
              <v-icon>mdi-pencil</v-icon>
              <v-tooltip activator="parent">Editar</v-tooltip>
            </v-btn>
            <v-btn
              density="compact"
              variant="text"
              icon
              @click="openDialog('destroy', item)"
            >
              <v-icon>mdi-delete</v-icon>
              <v-tooltip activator="parent">Eliminar</v-tooltip>
            </v-btn>
          </div>
          <div v-else>
            <v-btn
              density="compact"
              variant="text"
              icon
              @click="openDialog('restore', item)"
            >
              <v-icon>mdi-restore</v-icon>
              <v-tooltip activator="parent">Restaurar</v-tooltip>
            </v-btn>
            <v-btn
              density="compact"
              variant="text"
              icon
              @click="openDialog('destroyPermanent', item)"
            >
              <v-icon>mdi-delete-alert</v-icon>
              <v-tooltip activator="parent">Eliminar permanente</v-tooltip>
            </v-btn>
          </div>
        </slot>
      </template>
    </v-data-table-server>
    <loading-overlay v-if="loading" />
  </v-card>
</template>