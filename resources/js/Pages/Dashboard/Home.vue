<script setup>
import { ref, computed } from 'vue';

// Variables reactivas
const multiSearch = ref({});
const equipos = ref([]);

for (let i = 1; i <= 20; i++) {
    equipos.value.push({
        codigo: `Cod-${i}`,
        procesador: `Procesador ${i}`,
        ram: `${i * 2} GB`,
        almacenamiento: `${i * 100} GB`,
        portatil: i % 2 === 0 ? 'Si' : 'No',
        ip: `192.168.1.${i}`,
        so: i % 2 === 0 ? 'Windows' : 'Linux',
        wifi: i % 2 === 0 ? 'Si' : 'No',
        id: `${i}`,
    });
}

const headers = ref([
    {
        align: "center",
        key: "codigo",
        title: "Código",
    },
    { title: "Procesador", key: "procesador", align: "center" },
    { title: "Ram", key: "ram", align: "center" },
    { title: "Almacenamiento", key: "almacenamiento", align: "center" },
    { title: "Portatil", key: "portatil", align: "center" },
    { title: "Ip", key: "ip", align: "center" },
    { title: "Sistema", key: "so", align: "center" },
    { title: "Wifi", key: "wifi", align: "center" },
    { title: 'Acciones', key: 'actions', align: "center", sortable: false },
    {
        key: "id",
        title: "Id",
        align: " d-none"
    },
])

// Propiedades computadas
const filteredData = computed(() => {
    if (equipos.value.length) {
        return equipos.value.filter((item) => {
            return Object.entries(multiSearch.value).every(([key, value]) => {
                return (item[key] || "")
                    .toString()
                    .toUpperCase()
                    .includes(value.toString().toUpperCase());
            });
        });
    }
});
</script>

<template>
    <v-card class="ma-5" variant="outlined">
        <v-data-table show-select :headers="headers" :items="filteredData">
            <template v-slot:top>
                <v-toolbar flat>
                    <v-toolbar-title>Equipos</v-toolbar-title>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-btn icon="mdi-file-plus">
                    </v-btn>
                    <v-btn icon="mdi-file-excel">
                    </v-btn>
                    <v-btn icon="mdi-file-document-remove">
                    </v-btn>
                </v-toolbar>
            </template>
            <template v-slot:thead>
                <tr>
                    <td></td>
                    <td v-for="header in headers.filter(header => header.key != 'actions' && header.key != 'id')"
                        :key="header.key">
                        <v-text-field v-model="multiSearch[header.key]" type="text" class="px-1"
                            variant="underlined"></v-text-field>
                    </td>
                </tr>
            </template>
            <template v-slot:item.actions>
                <v-btn icon="mdi-pencil"></v-btn>
            </template>
        </v-data-table>
    </v-card>
</template>
