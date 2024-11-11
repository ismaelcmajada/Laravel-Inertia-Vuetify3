export function formatDate(date) {
  // Eliminar la parte del tiempo, manteniendo solo la fecha
  let dateOnly = date.split("T")[0]

  // Dividir por guiones y reorganizar la fecha (si ya viene en día-mes-año, no es necesario cambiar el orden)
  let parts = dateOnly.split("-")

  // Retornar la fecha en el mismo formato de día-mes-año
  return parts[2] + "-" + parts[1] + "-" + parts[0]
}

export function formatCalendarDate(date, inverted = false) {
  // Convertir a objeto Date para manejar correctamente el formato ISO
  let dateObj = new Date(date)

  // Obtener solo la parte de la fecha sin la hora
  let year = dateObj.getUTCFullYear()
  let month = String(dateObj.getUTCMonth() + 1).padStart(2, "0") // Los meses van de 0 a 11
  let day = String(dateObj.getUTCDate()).padStart(2, "0")

  let newDate = `${year}-${month}-${day}`

  if (inverted) {
    newDate = `${day}-${month}-${year}`
  }

  return newDate
}

export function formatDateTime(date) {
  const [fechaPart, horaPart] = date.split(" ")
  const [dia, mes, anio] = fechaPart.split("-")
  const [horas, minutos] = horaPart.split(":")

  // Creamos un objeto Date con los componentes
  return new Date(`${anio}-${mes}-${dia}T${horas}:${minutos}:00`)
}
