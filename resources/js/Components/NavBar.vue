<script setup>
import { ref } from 'vue'

const drawer = ref(null)

</script>

<template>
    <v-navigation-drawer theme="customDark" :elevation="12" v-model="drawer">

        <v-list nav>
            <v-list-item prepend-icon="mdi-account-circle" :title="$page.props.auth.user.name"></v-list-item>
        </v-list>

        <v-divider></v-divider>
        <v-list nav>
            <v-list-item prepend-icon="mdi-home" title="Home" :to="route('dashboard.home')"></v-list-item>
        </v-list>
        <v-list nav>
            <v-list-item prepend-icon="mdi-home" title="Another" :to="route('dashboard.another')"></v-list-item>
        </v-list>
        <v-divider></v-divider>
        <v-list nav>
            <v-list-item prepend-icon="mdi-logout-variant" :to="route('logout')" method="post" title="Sign out"></v-list-item>
        </v-list>
    </v-navigation-drawer>

    <v-app-bar :elevation="1">
        <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title>Laravel Inertia Vuetify3</v-toolbar-title>
    </v-app-bar>
</template>