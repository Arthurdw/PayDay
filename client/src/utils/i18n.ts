import { computed, ref } from "vue";
import { state } from "@/utils/state";

export const defaultLanguage = "en" as const;
export const languagePrefix = computed(() =>
  state.language === defaultLanguage ? "" : `/${state.language}`
);

export const languages = ref([
  {
    label: "English",
    key: "en",
    disabled: false,
  },
  {
    label: "Dutch",
    key: "be",
    disabled: false,
  },
]);

export const validLanguageKeys = Object.values(languages.value).map(
  (lang) => lang.key
);
