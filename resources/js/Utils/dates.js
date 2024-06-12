export function formatDate(date) {
  let parts = date.split("-")
  let newDate = parts[2] + "-" + parts[1] + "-" + parts[0]
  return newDate
}
