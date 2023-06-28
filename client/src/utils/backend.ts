import { languagePrefix } from "@/utils/i18n";

export namespace api {
  const appUrl = "http://payday.local:5173";
  const getBaseUrl = () => `http://api.payday.local${languagePrefix.value}/api`;

  const fetch = (url: string, options: RequestInit = {}) => {
    return window.fetch(`${getBaseUrl()}${url}`, {
      ...options,
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        "Content-Allow-Origin": appUrl,
        "Cross-Origin-Resource-Policy": "same-site",
        "Access-Control-Allow-Origin": appUrl,
        "Access-Control-Allow-Credentials": "true",
        ...options.headers,
      },
    });
  };

  const fetchWithMethod =
    (method: string) =>
    (url: string, options: RequestInit = {}) =>
      fetch(url, { ...options, method });

  const fetchWithMethodAndBody =
    (method: string) =>
    (url: string, body: any = {}) =>
      fetchWithMethod(method)(url, { body: JSON.stringify(body) });

  const get = fetchWithMethod("GET");
  const post = fetchWithMethodAndBody("POST");
  const put = fetchWithMethodAndBody("PUT");
  const del = fetchWithMethod("DELETE");
  const patch = fetchWithMethodAndBody("PATCH");

  const wrapApiRequest = async <T, R>(
    request: Response,
    args: any[] = [],
    cacheable: boolean = true
  ): Promise<R | ApiError<T>> => {
    const url = args?.[0];
    if (url && cacheable) {
      const cachedResponse = sessionStorage.getItem(url);
      if (cachedResponse) {
        const data = JSON.parse(cachedResponse);

        if (data?.expires < Date.now() || !data?.response)
          sessionStorage.removeItem(url);
        else {
          data.expires = Date.now() + 30 * 1000;
          sessionStorage.setItem(url, JSON.stringify(data));
          return data.response as R;
        }
      }
    }

    const res = await request;

    if (
      res.status === 401 &&
      window.location.hash.includes("login") &&
      window.location.hash.includes("register")
    ) {
      sessionStorage.clear();
      window.location.hash = `#${languagePrefix.value}/login`;
    }

    switch (res.status) {
      case 200:
        const response = (await res.json()) as R;
        if (url && cacheable) {
          sessionStorage.setItem(
            url,
            JSON.stringify({
              response,
              // 30 seconds from now
              expires: Date.now() + 30 * 1000,
            })
          );
        }
        return response;
      case 422:
        return (await res.json()) as UnprocessableContentApiError<T>;
      default:
        return (await res.json()) as BaseApiError;
    }
  };

  type MethodParams<T> = T extends (...args: infer P) => any ? P : never;

  const createApiRequest =
    <T extends CallableFunction>(method: T, cacheable: boolean = true) =>
    <R>(...args: MethodParams<T>) =>
      wrapApiRequest<T, R>(method(...args), args, cacheable);

  const getApi = createApiRequest(get);
  const postApi = createApiRequest(post, false);
  const putApi = createApiRequest(put, false);
  const delApi = createApiRequest(del, false);
  const patchApi = createApiRequest(patch, false);

  export const login = (data: LoginData) =>
    postApi<LoginResponse>(Endpoint.Login, data);
  export const register = (data: RegisterData) =>
    postApi<RegisterResponse>(Endpoint.Register, data);
  export const me = () => getApi<GetMeResponse>(Endpoint.Me);
  export const logout = () => {
    invalidateCache();
    return postApi<Message>(Endpoint.Logout);
  };
  export const source = (id: number) =>
    getApi<GetSourceResponse>(`${Endpoint.Sources}/${id}`);
  export const sources = (page: number) =>
    getApi<GetSourcesResponse>(`${Endpoint.Sources}?page=${page}&limit=10`);
  export const sourcePairs = () =>
    getApi<SourcesPairResponse>(Endpoint.SourcePairs);
  export const addSource = (data: AddSourceData) =>
    putApi<AddSourceResponse>(Endpoint.Sources, data);
  export const updateSource = (id: number, data: UpdateSourceData) =>
    patchApi<UpdateSourceResponse>(`${Endpoint.Sources}/${id}`, data);
  export const deleteSource = (id: number) =>
    delApi<Message>(`${Endpoint.Sources}/${id}`);
  export const balances = (page: number) =>
    getApi<GetBalanceResponse>(`${Endpoint.Balances}?page=${page}&limit=10`);
  export const addBalance = (data: AddBalanceData) =>
    postApi<AddBalanceResponse>(Endpoint.Balances, data);
  export const deleteBalance = (id: number) =>
    delApi<Message>(`${Endpoint.Balances}/${id}`);
  export const overview = () => getApi<GetOverviewResponse>(Endpoint.Overview);
  export const overviewBySource = (sourceId: number) =>
    getApi<GetOverviewResponse>(`${Endpoint.Overview}/${sourceId}`);
  export const statistics = () =>
    getApi<GetStatisticsResponse>(Endpoint.Statistics);
  export const statisticsBySource = (sourceId: number) =>
    getApi<GetStatisticsResponse>(`${Endpoint.Statistics}/${sourceId}`);

  export const invalidateCache = () => sessionStorage.clear();
}

export enum Endpoint {
  Login = "/login",
  Register = "/register",
  Me = "/me",
  Logout = "/logout",
  Sources = "/sources",
  SourcePairs = "/source/all",
  Balances = "/balances",
  Overview = "/analytics/overview",
  Statistics = "/analytics/statistics",
}
