<script setup>
    import NavBar from '@/Components/NavBar.vue'
    import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Dashboard" />

    <v-app id="inspire">
        <NavBar />
        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
