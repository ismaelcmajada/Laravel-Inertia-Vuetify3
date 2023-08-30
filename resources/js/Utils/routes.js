import { computed } from "vue"

/*
 * Parent routes will have "path" parameters instead or "route" ones
 * "routes" will be used in their childs
 */

export const routesArray = [
  {
    value: "Usuario",
    icon: "mdi-account-circle",
  },
  {
    value: "Suscriptores",
    route: "dashboard.suscriptores",
    icon: "mdi-account-group",
  },
  {
    value: "Cerrar sesión",
    icon: "mdi-logout-variant",
    route: "logout",
  },
  // Route with childs example:
  /*
  {
    value: "title",
    icon: "icon",
    path: "relative-path",
    childs: [
      {
        value: "title",
        route: "route name",
        icon: "icon",
      },
      {
        value: "title",
        route: "route name",
        icon: "icon",
      },
    ],
  },
  */
]

export const routes = computed({
  get() {
    return routesArray
  },
  set({
    newValue,
    element = 1,
    key = "value",
    child = false,
    childElement = 1,
  }) {
    let handler = routesArray

    if (child) {
      handler[element - 1].childs[childElement - 1][key] = newValue
    } else {
      handler[element - 1][key] = newValue
    }

    return handler
  },
})
