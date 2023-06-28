<script lang="ts" setup>
import "@/assets/reset.css";
import "@/assets/general.scss";

import { NLoadingBarProvider, NNotificationProvider } from "naive-ui";
import { computed, reactive, ref } from "vue";

import SignIn from "@/pages/SignIn.vue";
import Register from "@/pages/Register.vue";
import NotFound from "@/pages/NotFound.vue";
import Overview from "@/pages/Overview.vue";
import { validLanguageKeys } from "@/utils/i18n";
import { state } from "@/utils/state";
import Sources from "@/pages/Sources.vue";
import Balance from "@/pages/Balance.vue";

const routes = {
  "/": SignIn,
  "/404": NotFound,
  "/login": SignIn,
  "/register": Register,
  "/dashboard": Overview,
  "/balance": Balance,
  "/sources": Sources,
  "/sources/{id}": Sources,
};

if (window.location.pathname !== "/") {
  window.location.hash = window.location.pathname;
  window.location.pathname = "/";
}

if (!window.location.hash) {
  window.location.hash = "/";
}

const currentPath = ref(window.location.hash);
let componentProps = ref({});

window.addEventListener("hashchange", () => {
  currentPath.value = window.location.hash;
});

const getPathParameters = (path: string, route: string) => {
  const segments = route.split("/").filter((s) => s !== "");
  const pathSegments = path.split("/").filter((s) => s !== "");

  const params = {};

  for (let i = 0; i < segments.length; i++) {
    const segment = segments[i];
    const pathSegment = pathSegments[i];

    if (segment.startsWith("{") && segment.endsWith("}")) {
      const key = segment.slice(1, -1);
      params[key] = pathSegment;
    } else if (segment !== pathSegment) {
      break;
    }
  }

  return params;
};

const getRouteMatch = (path: string) => {
  const plainRoute = routes[path];
  componentProps.value = {};

  if (plainRoute) return plainRoute;

  for (const [route, component] of Object.entries(routes)) {
    const segments = route.split("/").filter((s) => s !== "");
    const pathSegments = path.split("/").filter((s) => s !== "");

    if (segments.length !== pathSegments.length) continue;

    componentProps.value = getPathParameters(path, route);
    return component;
  }

  return NotFound;
};

const currentView = computed(() => {
  const actualPath = currentPath.value.slice(1);

  if (actualPath.startsWith("/")) {
    const [language, ...pathValues] = actualPath.slice(1).split("/");
    const path = pathValues.join("/");

    if (validLanguageKeys.includes(language)) {
      state.language = language;
      return getRouteMatch(`/${path}`);
    }
  }

  return getRouteMatch(actualPath);
});
</script>

<template>
  <NNotificationProvider>
    <NLoadingBarProvider>
      <component :is="currentView" v-bind="componentProps" />
    </NLoadingBarProvider>
  </NNotificationProvider>
</template>
