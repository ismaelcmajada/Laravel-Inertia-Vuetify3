import { ref } from "vue"

export default function useDialogs() {
  const showFormDialog = ref(false)
  const formDialogType = ref("create")

  const showDestroyDialog = ref(false)
  const showRestoreDialog = ref(false)
  const showDestroyPermanentDialog = ref(false)

  const item = ref({})

  const openDialog = (dialogType, newItem) => {
    if (newItem) {
      item.value = newItem
    }
    switch (dialogType) {
      case "create":
        formDialogType.value = "create"
        showFormDialog.value = true
        break
      case "edit":
        formDialogType.value = "edit"
        showFormDialog.value = true
        break
      case "destroy":
        showDestroyDialog.value = true
        break
      case "restore":
        showRestoreDialog.value = true
        break
      case "destroyPermanent":
        showDestroyPermanentDialog.value = true
        break
    }
  }

  return {
    showFormDialog,
    formDialogType,
    showDestroyDialog,
    showRestoreDialog,
    showDestroyPermanentDialog,
    item,
    openDialog,
  }
}
