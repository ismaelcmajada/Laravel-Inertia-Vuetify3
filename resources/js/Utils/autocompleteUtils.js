export function generateItemTitle(template) {
  // Construir el cuerpo de la función reemplazando los placeholders con propiedades del item
  const functionBody = template.replace(/\{([\w\.]+)\}/g, (match, propPath) => {
    // Reemplazar {prop} con ' + (item.prop?.subprop ?? '') + '
    const safeAccess = propPath
      .split(".")
      .map((key) => `?.${key}`)
      .join("")
    return `' + (item${safeAccess} ?? '') + '`
  })

  // Envolver el cuerpo de la función con una declaración de retorno
  const functionString = `return '${functionBody}';`

  // Crear una nueva función con 'item' como argumento y el cuerpo generado
  const generatedFunction = new Function("item", functionString)

  return generatedFunction
}

export function searchByWords(item, queryText, itemText, customFilters) {
  if (!queryText) return true

  const query = queryText.toLowerCase()
  const itemLower = itemText.title.toLowerCase()

  const searchWords = query.split(" ")

  const matches = searchWords.every((word) => itemLower.includes(word))

  if (customFilters) {
    return matches && customFilters(item, queryText, itemText)
  }

  return matches
}
