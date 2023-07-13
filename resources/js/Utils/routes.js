/*
 * Parent routes will have "path" parameters instead or "route" ones
 * "routes" will be used in their childs
 */

export const routes = [
  {
    value: "Suscriptores",
    route: "dashboard.suscriptores",
    icon: "mdi-account-group",
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
