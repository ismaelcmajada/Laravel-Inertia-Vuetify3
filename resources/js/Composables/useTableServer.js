import { ref, watch, reactive, computed } from "vue"
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

  const tableHeaders = computed(() =>
    itemHeaders.value.filter((e) => selectedHeaders.value.includes(e.key))
  )
  const selectedHeaders = ref([])
  const itemHeaders = ref([])
  const allHeaders = computed(() => selectedHeaders.value.length == itemHeaders.value.length)

  const toggleAllHeaders = () => {
    if (allHeaders.value) selectedHeaders.value = []
    else selectedHeaders.value = itemHeaders.value.map((i) => i.key)
  }

  const updateItems = debounce(() => loadItems(), 500)

  const loadItems = () => {
    if (loading.value) {
      return
    }

    loading.value = true

    const cleanedSearch = Object.entries(tableData.search).reduce(
      (a, [k, v]) => (v !== "" ? ((a[k] = v), a) : a),
      {}
    )

    const searchJson = JSON.stringify(cleanedSearch)
    const sortByJson = JSON.stringify(tableData.sortBy)

    axios
      .post(`${endPoint.value}/load-items`, {
        page: tableData.page,
        itemsPerPage: tableData.itemsPerPage,
        sortBy: sortByJson,
        search: searchJson,
        deleted: tableData.deleted,
      })
      .then((response) => {
        tableData.items = response.data.tableData.items
        tableData.itemsLength = response.data.tableData.itemsLength
        loading.value = false
      })
  }

  const resetTable = () => {
    tableData.page = 1
    tableData.itemsPerPage = 10
    tableData.sortBy = []
    tableData.search = {}
    tableData.itemsLength = 0
    tableData.deleted = false
    tableData.items = []

    loadItems()
  }

  watch(
    () => tableData.deleted,
    () => {
      loadItems()
    }
  )

  return {
    tableData,
    tableHeaders,
    selectedHeaders,
    allHeaders,
    itemHeaders,
    endPoint,
    loading,
    updateItems,
    itemsPerPageOptions,
    toggleAllHeaders,
    loadItems,
    resetTable,
  }
}
