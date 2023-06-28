<script lang="ts" setup>
import { NDropdown, useLoadingBar } from "naive-ui";
import { signOut } from "@/utils/user";
import { onMounted, ref } from "vue";
import { state } from "@/utils/state";
import { languagePrefix, languages, validLanguageKeys } from "@/utils/i18n";
import Loading from "@/components/Loading.vue";
import { api } from "@/utils/backend";

const loadingBar = useLoadingBar();

const currentHash = ref("");

const locations = {
  dashboard: "Dashboard",
  sources: "Sources",
  balance: "Balance",
};

const languageDisabledInit = () => {
  languages.value.forEach((lang) => {
    lang.disabled = lang.key === state.language;
  });
};

const selectLanguage = (key: string) => {
  state.language = key;
  languageDisabledInit();

  const url = window.location.hash;
  const actualPath = url.slice(1);

  if (actualPath.startsWith("/")) {
    const [language, path] = actualPath.slice(1).split("/");

    if (validLanguageKeys.includes(language))
      window.location.hash = `#${languagePrefix.value}/${path}`;
    else window.location.hash = `#${languagePrefix.value}${actualPath}`;
  } else window.location.hash = `#${languagePrefix.value}/${actualPath}`;

  api.invalidateCache();
  window.location.reload();
};

const signOutWrapper = async () => {
  loadingBar.start();
  const res = await signOut();

  if (res) {
    loadingBar.finish();
    window.location.hash = `${languagePrefix.value}/login`;
  } else {
    loadingBar.error();
  }
};

onMounted(() => {
  currentHash.value = window.location.hash;
});
languageDisabledInit();
</script>

<template>
  <Loading v-if="state.loadingScreen" />
  <div v-else class="content-wrapper">
    <div class="sidebar">
      <div class="top">
        <header>
          <img alt="logo" src="@/assets/logo.svg" />
          <h1>PayDay</h1>
        </header>
        <p class="navigation-title">navigation</p>
        <nav>
          <a
            v-for="[url, text] in Object.entries(locations)"
            :class="{ active: currentHash === url }"
            :href="`#${languagePrefix}/${url}`"
            >{{ text }}</a
          >
        </nav>
      </div>

      <div class="quick-bar">
        <NDropdown
          :options="languages"
          :show-arrow="true"
          :value="state.language"
          trigger="hover"
          @select="selectLanguage"
        >
          <button>
            <img
              alt="language"
              src="@/assets/language.svg"
              title="change language"
            />
          </button>
        </NDropdown>
        <button @click="signOutWrapper">
          <img alt="sign out" src="@/assets/logout.svg" title="sign out" />
        </button>
      </div>
    </div>
    <div class="content">
      <slot></slot>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.content-wrapper {
  display: flex;
  flex-direction: row;
  height: 100dvh;
  width: 100dvw;
}

.sidebar {
  $padding: 1rem;

  position: relative;

  width: 10rem;
  height: calc(100% - #{$padding} * 2);

  display: flex;
  flex-direction: column;
  justify-content: space-between;

  padding: $padding;

  header {
    display: flex;
    align-items: center;
    gap: 0.5rem;

    img {
      $size: 3rem;

      width: $size;
      height: $size;
    }

    h1 {
      font-size: 1.4rem;
      font-weight: 600;
      padding: 0;
    }
  }

  .navigation-title {
    margin-top: 2.5rem;
    text-transform: uppercase;
    opacity: 0.5;
    letter-spacing: 0.35rem;
    font-size: 0.8rem;
  }

  nav {
    display: flex;
    flex-direction: column;
    margin-top: 1.5rem;
    margin-left: 0.75rem;
    gap: 1rem;

    a {
      text-decoration: none;
      color: #000;
      font-size: 1rem;
      font-weight: 500;
      opacity: 0.6;

      &.active {
        opacity: 1;
      }
    }
  }

  .top {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .quick-bar {
    $iconSize: 1.75rem;

    display: flex;
    justify-content: space-between;

    &::before {
      content: "";
      position: absolute;
      width: 100%;
      height: 0.15rem;
      background-color: #f2f4f7;

      left: 0;
      margin-top: -1rem;
    }

    button {
      background-color: transparent;
      border: none;
      padding: 0;
      cursor: pointer;
    }

    button,
    img {
      width: $iconSize;
      height: $iconSize;
    }
  }
}

.content {
  $margin: 2rem;

  position: relative;
  padding: $margin;
  overflow-y: scroll;
  width: calc(100% - #{$margin} * 2);
  height: calc(100% - #{$margin} * 2);

  background-color: #f2f4f7;

  display: flex;
  flex-direction: column;
  gap: 2rem;
}
</style>
