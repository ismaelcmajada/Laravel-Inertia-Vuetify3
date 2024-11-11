<script setup>
import { Head, useForm } from "@inertiajs/vue3"
import { ref } from "vue"
import { ruleRequired, ruleEmail } from "@/Utils/LaravelAutoCrud/rules"

defineProps({
  status: String,
})

const form = ref(false)

const formData = useForm({
  email: "",
  password: "",
  remember: false,
})

const submit = () => {
  formData.post(route("login"), {
    onFinish: () => formData.reset("password"),
  })
}
</script>

<template>
  <Head title="Log in" />

  <div class="d-flex align-center justify-center" style="height: 100vh">
    <v-hover v-slot="{ isHovering, props }">
      <v-card
        title="Iniciar sesión"
        theme="appTheme"
        v-bind="props"
        :elevation="isHovering ? 24 : 6"
        rounded="xl"
        width="400"
        class="mx-auto pa-10"
      >
        <v-form v-model="form" @submit.prevent="submit">
          <v-text-field
            type="Email"
            v-model="formData.email"
            label="Correo"
            :rules="[ruleRequired, ruleEmail]"
          ></v-text-field>

          <v-text-field
            type="password"
            v-model="formData.password"
            label="Contraseña"
            :rules="[ruleRequired]"
            class="mt-2"
          ></v-text-field>

          <v-btn
            :disabled="!form"
            type="submit"
            color="primary"
            block
            class="mt-2"
            >Iniciar sesión</v-btn
          >
        </v-form>
      </v-card>
    </v-hover>
  </div>
</template>
