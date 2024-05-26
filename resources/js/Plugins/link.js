import { router, usePage } from "@inertiajs/vue3"
import { computed } from "vue"

const page = usePage()

export default {
  install(app) {
    app.component("RouterLink", {
      useLink(props) {
        const href = props.to
        const currentUrl = computed(() => page.url)
        return {
          route: computed(() => ({ href })),
          isActive: computed(() => currentUrl.value.startsWith(href)),
          isExactActive: computed(() => href === currentUrl),
          navigate(e) {
            if (e.shiftKey || e.metaKey || e.ctrlKey) return
            e.preventDefault()
            router.visit(href.value, {
              method: e.currentTarget.getAttribute("method") || "get",
            })
          },
        }
      },
    })
  },
}
