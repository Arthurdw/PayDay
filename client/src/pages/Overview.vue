<script lang="ts" setup>
import { state } from "@/utils/state";
import { useLoadingBar, useNotification } from "naive-ui";
import Layout from "@/components/Layout.vue";
import { initUser } from "@/utils/user";
import { reactive, ref, watch } from "vue";
import { api } from "@/utils/backend";
import { isBaseApiError } from "@/utils/guards";
import BarList from "@/components/BarList.vue";
import Paginated from "@/components/Paginated.vue";
import BaseOverview from "@/components/BaseOverview.vue";
import { defaultOverviewData, sourceFailed } from "@/utils/sampleData";
import { statisticsResponseToChartStatistics } from "@/utils/cast";
import { formatNumberAsCurrency } from "@/utils/formatting";
import { languagePrefix } from "@/utils/i18n";

const loadingBar = useLoadingBar();
const notification = useNotification();

let loadingOverview = ref(true);
let loadingStatistics = ref(true);
let loadingBalances = ref(true);
let balancePage = ref(1);
let maxPages = ref<number | null>(null);

let expensesData = ref<BarItem[]>([]);
let incomesData = ref<BarItem[]>([]);

let overviewData = reactive<GetOverviewResponse>(
  Object.assign({}, defaultOverviewData)
);
let statisticsData = ref<ChartStatistics[]>([]);

state.loadingScreen = true;

const initOverview = async () => {
  loadingOverview.value = true;
  const res = await api.overview();

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load overview data. Please try again later.",
      content: res.error,
      duration: 2500,
      keepAliveOnHover: true,
    });
    return;
  }

  overviewData = res as GetOverviewResponse;
  loadingOverview.value = false;
};

const initStatistics = async () => {
  loadingStatistics.value = true;
  const res = await api.statistics();

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load statistics data. Please try again later.",
      content: res.error,
      duration: 2500,
      keepAliveOnHover: true,
    });
    return;
  }

  const data = res as GetStatisticsResponse;
  statisticsData.value = statisticsResponseToChartStatistics(data);
  loadingStatistics.value = false;
};

const fetchSourcesWithRetry = async (
  ids: number[],
  left: number
): Promise<GetSourceResponse[]> => {
  if (left === 0) {
    notification.error({
      title: `Could not get the source information for ${ids.length} sources.`,
    });
    return [];
  }

  const getWrapper = async (id: number) => {
    const res = await api.source(id);

    if (isBaseApiError(res)) return [id, null];

    return [id, res as GetSourceResponse];
  };

  const sources = await Promise.all(ids.map(getWrapper));

  interface SourceResult {
    failed: number[];
    success: GetSourceResponse[];
  }

  const { failed, success } = sources.reduce<SourceResult>(
    (result, [id, source]) => {
      if (source === null) result.failed.push(id);
      else result.success.push(source);

      return result;
    },
    { failed: [], success: [] }
  );

  if (failed.length > 0)
    return [...success, ...(await fetchSourcesWithRetry(failed, left - 1))];

  return success;
};

const initBalances = async () => {
  loadingBalances.value = true;
  const res = await api.balances(balancePage.value);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load balances data. Please try again later.",
      content: res.error,
      duration: 2500,
      keepAliveOnHover: true,
    });
    return;
  }

  const balances = res as GetAllBalancesResponse;
  const toFetchSources = new Set(balances.data.map((b) => b.source_id));
  const sources = await fetchSourcesWithRetry(Array.from(toFetchSources), 3);

  if (maxPages.value === null) maxPages.value = balances.last_page;

  const incomes = [];
  const expenses = [];

  for (const balance of balances.data) {
    const source =
      sources.find((s) => s.id === balance.source_id) ?? sourceFailed;
    const sharedData = {
      icon: source.imageUrl,
      name: source.name,
      tags: [
        {
          type: "info",
          content: source.type,
        },
      ],
      value: balance.id,
    };

    if (balance.in > 0 || balance.out < 0) {
      incomes.push({
        ...sharedData,
        description: formatNumberAsCurrency(Math.abs(balance.in)),
      } as BarItem);
    }

    if (balance.out > 0 || balance.in < 0) {
      expenses.push({
        ...sharedData,
        description: formatNumberAsCurrency(Math.abs(balance.out)),
      } as BarItem);
    }
  }

  incomesData.value = incomes;
  expensesData.value = expenses;

  loadingBalances.value = false;
};

const init = async () => {
  loadingBar.start();
  let successInit = true;

  if (!state.user) {
    successInit = await initUser();

    if (!successInit) {
      window.location.hash = `#${languagePrefix.value}/login`;
    }
  }

  if (successInit) {
    state.loadingScreen = false;
    await Promise.all([initOverview(), initStatistics(), initBalances()]);
  }

  loadingBar.finish();
};

init();
watch(balancePage, initBalances);
</script>

<template>
  <Layout>
    <BaseOverview
      :loading-overview="loadingOverview"
      :loading-statistics="loadingStatistics"
      :overview-data="overviewData"
      :statistics-data="statisticsData"
    />
    <BarList
      :data="expensesData"
      :expected-items="expensesData.length > 0 ? expensesData.length : 5"
      :loading="loadingBalances"
      title="Expenses"
    />
    <BarList
      :data="incomesData"
      :expected-items="incomesData.length > 0 ? incomesData.length : 5"
      :loading="loadingBalances"
      title="Incomes"
    />
    <Paginated
      :is-loading="loadingBalances"
      :page="balancePage"
      :total="maxPages"
      @update="(page: number) => balancePage= page"
    />
  </Layout>
</template>

<style lang="scss" scoped>
p {
  font-size: 1.5rem;
  line-height: 1.5rem;
  font-weight: 600;
}
</style>
