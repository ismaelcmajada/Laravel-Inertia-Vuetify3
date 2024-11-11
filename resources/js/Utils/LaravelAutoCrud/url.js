import { usePage } from "@inertiajs/vue3"

const page = usePage()

export function formatUrl(url) {
  if (!/^https?:\/\//i.test(url)) {
    return "http://" + url
  }
  return url
}

export const checkRoute = (r) => {
  const url = new URL(route(r))
  return page.url.includes(url.pathname)
}

export const getUrlParam = (param) => {
  const urlParams = new URLSearchParams(window.location.search)
  return urlParams.get(param)
}
