import { ref, watch, reactive } from "vue"
import { getUrlParam } from "@/Utils/url"
import { usePage, router } from "@inertiajs/vue3"
import debounce from "lodash.debounce"

export default function useTableServer() {
  const loading = ref(false)

  const endPoint = ref("")

  const tableData = reactive({
    page: 1,
    itemsPerPage: 10,
    sortBy: [],
    items: [],
    search: {},
    itemsLength: 0,
    deleted: false,
  })

  let itemsPerPageOptions = [
    { value: 10, title: "10" },
    { value: 25, title: "25" },
    { value: 50, title: "50" },
    { value: 100, title: "100" },
  ]

  const updateItems = debounce(() => loadItems(), 500)

  const loadItems = () => {
    const options = { ...tableData }

    if (!usePage().props.hasOwnProperty("tableData")) {
      options.page = getUrlParam("page") ? getUrlParam("page") : 1
      options.itemsPerPage = getUrlParam("itemsPerPage")
        ? getUrlParam("itemsPerPage")
        : 10
      options.sortBy = getUrlParam("sortBy")
        ? JSON.parse(getUrlParam("sortBy"))
        : []
      options.search = getUrlParam("search")
        ? JSON.parse(getUrlParam("search"))
        : {}
      options.deleted = getUrlParam("deleted")
        ? JSON.parse(getUrlParam("deleted"))
        : false
    }

    const cleanedSearch = Object.entries(options.search).reduce(
      (a, [k, v]) => (v !== "" ? ((a[k] = v), a) : a),
      {}
    )

    const searchJson = JSON.stringify(cleanedSearch)
    const sortByJson = JSON.stringify(options.sortBy)

    loading.value = true

    router.get(
      endPoint.value,
      {
        page: options.page,
        itemsPerPage: options.itemsPerPage,
        sortBy: sortByJson,
        search: searchJson,
        deleted: options.deleted,
      },
      {
        only: ["tableData", "flash", "errors"],
        replace: true,
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          loading.value = false
        },
      }
    )
  }

  const resetTable = () => {
    router.get(
      endPoint.value,
      {},
      {
        replace: true,
        preserveState: false,
        preserveScroll: true,
      }
    )
  }

  watch(
    () => usePage().props.tableData,
    (newTableData) => {
      Object.assign(tableData, newTableData)
    },
    { deep: true }
  )

  watch(
    () => tableData.deleted,
    () => {
      loadItems()
    }
  )

  return {
    tableData,
    endPoint,
    loading,
    updateItems,
    itemsPerPageOptions,
    loadItems,
    resetTable,
  }
}
