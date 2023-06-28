import { reactive } from "vue";

interface User {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export const state = reactive({
  user: null as User | null,
  language: "en" as string,
  loadingScreen: false as boolean,
});
