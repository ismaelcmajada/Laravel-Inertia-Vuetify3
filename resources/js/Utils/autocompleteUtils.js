export function generateItemTitle(template) {
  const functionBody = template.replace(/\{(\w+)\}/g, (_, key) => {
    return `\${item.${key}}`
  })

  return new Function("item", `return \`${functionBody}\`;`)
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
