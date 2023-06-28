<script lang="ts" setup>
import Layout from "@/components/Layout.vue";
import BarList from "@/components/BarList.vue";
import Paginated from "@/components/Paginated.vue";
import { ref } from "vue";
import { api } from "@/utils/backend";
import { isBaseApiError } from "@/utils/guards";
import { useNotification } from "naive-ui";
import { sourceFailed } from "@/utils/sampleData";
import { formatNumberAsCurrency } from "@/utils/formatting";
import AddBalance from "@/components/AddBalance.vue";

const notification = useNotification();

const loadingBalances = ref(true);
const page = ref(1);
const maxPages = ref<number | null>(null);
const expectedItems = ref(10);
const balances = ref<GetAllBalancesResponse | null>(null);
const balancesAsBarItems = ref<BarItem[]>([]);

const deleteBalance = async (id: number) => {
  const res = await api.deleteBalance(id);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not delete balance. Please try again later.",
      content: res.error,
      duration: 2500,
    });
    return;
  }

  api.invalidateCache();
  await initBalances();
};

const getSource = async (id: number): Promise<GetSourceResponse> => {
  const res = await api.source(id);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load source. Please try again later.",
      content: res.error,
      duration: 2500,
    });
    return sourceFailed;
  }

  return res as GetSourceResponse;
};

const initBalances = async () => {
  loadingBalances.value = true;
  const res = await api.balances(page.value);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load balances. Please try again later.",
      content: res.error,
      duration: 2500,
    });
    return;
  }

  balances.value = res as GetAllBalancesResponse;
  const toFetchSources = new Set(balances.value!!.data.map((b) => b.source_id));
  const sources = await Promise.all(
    Array.from(toFetchSources).map((id) => getSource(id))
  );

  if (maxPages.value === null) maxPages.value = balances.value!!.last_page;

  balancesAsBarItems.value = balances.value!!.data.map((balance) => {
    const source = sources.find((s) => s.id === balance.source_id);
    return {
      value: balance.id,
      description: `Income: ${formatNumberAsCurrency(
        balance.in
      )} | Expenses: ${formatNumberAsCurrency(balance.out)}`,
      icon: source.imageUrl,
      tags: [
        {
          type: "info",
          content: source.type,
        },
      ],
      name: source.name,
    };
  });

  expectedItems.value = balancesAsBarItems.value.length;
  loadingBalances.value = false;
};

initBalances();
</script>

<template>
  <Layout>
    <BarList
      :data="balancesAsBarItems"
      :expected-items="expectedItems"
      :loading="loadingBalances"
      :on-remove="deleteBalance"
      clickable
      removable
      title="Balance"
    />
    <Paginated
      :is-loading="loadingBalances"
      :page="page"
      :total="maxPages"
      @update="(pg: number) => (page = pg)"
    />
    <AddBalance :on-new-balance="initBalances" />
  </Layout>
</template>

<style lang="scss" scoped></style>
