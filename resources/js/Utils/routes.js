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
    path: "/dashboard/suscriptor",
    icon: "mdi-account-group",
  },
  {
    value: "Países",
    path: "/dashboard/pais",
    icon: "mdi-earth",
  },
  {
    value: "Cerrar sesión",
    icon: "mdi-logout-variant",
    path: "/logout",
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
        path: "relative-path",
        icon: "icon",
      },
      {
        value: "title",
        path: "relative-path",
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
