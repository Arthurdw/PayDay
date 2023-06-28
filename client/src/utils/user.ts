import { api } from "@/utils/backend";
import { state } from "@/utils/state";
import { isBaseApiError, isUnprocessableContentApiError } from "@/utils/guards";

export const initUser = async () => {
  const res = await api.me();

  if (isBaseApiError(res) || isUnprocessableContentApiError(res)) return false;

  state.user = res;
  return true;
};

export const signOut = async () => {
  const res = await api.logout();

  if (isBaseApiError(res) || isUnprocessableContentApiError(res)) return false;

  state.user = null;
  return true;
};
