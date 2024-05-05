import "./bootstrap"
import "../css/app.css"

import { createApp, h } from "vue"
import { createInertiaApp } from "@inertiajs/vue3"
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers"
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m"

import Toast from "vue-toastification"
import "vue-toastification/dist/index.css"

import "vuetify/styles"
import { createVuetify } from "vuetify"
import { es } from "vuetify/locale"

import link from "@/Plugins/link"

import { aliases, mdi } from "vuetify/lib/iconsets/mdi"
import "@mdi/font/css/materialdesignicons.css"

import AppLayout from "@/Layouts/App.vue"
import DashboardLayout from "@/Layouts/Dashboard.vue"

const appName =
  window.document.getElementsByTagName("title")[0]?.innerText || "Dashboard"

const vuetify = createVuetify({
  theme: {
    defaultTheme: "customLight",
    themes: {
      customLight: {
        dark: false,
        colors: {
          background: "#F3F4F6",
        },
      },
      customDark: {
        dark: true,
        colors: {
          surface: "#161d31",
          primary: "#3f51b5",
          secondary: "#03dac6",
          error: "#f44336",
          info: "#2196F3",
          success: "#4caf50",
          warning: "#fb8c00",
        },
      },
    },
  },
  locale: {
    locale: "es",
    messages: { es },
  },
  Icons: {
    defaultSet: "mdi",
    aliases,
    sets: {
      mdi,
    },
  },
})

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: async (name) => {
    const page = await resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob("./Pages/**/*.vue")
    )
    page.default.layout = name.startsWith("Dashboard/")
      ? DashboardLayout
      : AppLayout
    return page
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(Toast)
      .use(vuetify)
      .use(link)
      .use(ZiggyVue, Ziggy)
      .mount(el)
  },
  progress: {
    color: "#4B5563",
  },
})
