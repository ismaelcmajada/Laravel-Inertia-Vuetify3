export function generateItemTitle(templateString, dataObject) {
  if (!dataObject) return ""
  return templateString.replace(/\{([\w\.]+)\}/g, (match, p1) => {
    const keys = p1.split(".")
    let value = dataObject
    for (let key of keys) {
      if (!value[key]) {
        return ""
      }
      value = value[key]
    }
    return value
  })
}
