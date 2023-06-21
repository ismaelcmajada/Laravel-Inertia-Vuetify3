<script setup>

import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
import Auth from '@/Layouts/Auth.vue';

defineProps({
    status: String,
})

const form = ref(false)
const rules = ref({
    required: value => !!value || 'Field is required',
    email: v => !v || /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
})


const formData = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    formData.post(route('login'), {
        onFinish: () => formData.reset('password'),
    })
}
</script>

<template>
    <Head title="Log in" />

    <Auth>

        <v-alert v-if="status">
            {{ status }}
        </v-alert>

        <div class="d-flex align-center justify-center" style="height: 100vh">
            <v-hover v-slot="{ isHovering, props }">
                <v-card title="Sign in" theme="customDark" v-bind="props" :elevation="isHovering ? 24 : 6" rounded="xl"
                    width="400" class="mx-auto pa-10">
                    <v-form v-model="form" @submit.prevent="submit">
                        <v-text-field type="Email" v-model="formData.email" label="Email"
                            :rules="[rules.required, rules.email]"></v-text-field>

                        <v-text-field type="password" v-model="formData.password" label="Password" :rules="[rules.required]"
                            class="mt-2"></v-text-field>

                        <v-btn :disabled="!form" type="submit" color="primary" block class="mt-2">Sign In</v-btn>
                    </v-form>
                    <v-btn color="primary" block class="mt-2" to="/register">Create new account</v-btn>
                </v-card>
            </v-hover>
        </div>
        
    </Auth>
</template>
