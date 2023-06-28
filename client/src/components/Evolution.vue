<script lang="ts" setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Area, Chart, Grid, Line, Tooltip } from "vue3-charts";
import type { Data } from "vue3-charts/src/types";
import { NAlert, NCard, NSkeleton } from "naive-ui";
import { languagePrefix } from "@/utils/i18n";

defineProps<{
  statisticsData: Data[];
  statisticsKeys: string[];
  colors: string[];
  loading: boolean;
}>();

const parentRef = ref<HTMLElement | null>(null);

let chartWidth = ref(0);
let chartHeight = ref(0);
const parentRefRefresh = () => {
  if (parentRef.value) {
    chartWidth.value = parentRef.value?.clientWidth ?? 0;
    chartHeight.value = parentRef.value?.clientHeight ?? 0;
  }
};

watch(parentRef, parentRefRefresh);
onMounted(() => window.addEventListener("resize", parentRefRefresh));
onBeforeUnmount(() => window.removeEventListener("resize", parentRefRefresh));
</script>

<template>
  <NCard :segmented="{ content: true }" title="Evolution">
    <div ref="parentRef" class="chart-wrapper">
      <NSkeleton v-if="loading" :sharp="false" height="100%" width="100%" />
      <div v-else-if="statisticsData.length === 0" class="no-data">
        <NAlert title="Oops..." type="error">
          <p>
            No data available, once you start using the application a chart of
            your history will present itself here.
          </p>
          <p>
            To get started, go to the
            <a :href="`#${languagePrefix}/balance`">balance</a> page and add
            your first transaction.
          </p>
        </NAlert>
      </div>
      <Chart
        v-else
        :axis="{
          primary: {
            type: 'linear',
            domain: ['dataMin', 'dataMax'],
          },
          secondary: {
            type: 'linear',
            domain: ['dataMin - 100', 'dataMax + 100'],
          },
        }"
        :data="statisticsData"
        :size="{ width: chartWidth, height: chartHeight }"
      >
        <template #layers>
          <Grid strokeDasharray="2,2" />

          <Area
            v-for="(_, idx) in colors"
            :areaStyle="{ fill: `url(#grad-${idx})` }"
            :dataKeys="['index', statisticsKeys[idx]]"
            type="monotone"
          />

          <Line
            v-for="(color, idx) in colors"
            :data-keys="['index', statisticsKeys[idx]]"
            :line-style="{ stroke: color }"
            type="monotone"
          />

          <defs>
            <linearGradient
              v-for="(color, idx) in colors"
              :id="`grad-${idx}`"
              gradientTransform="rotate(90)"
            >
              <stop :stop-color="color" offset="0%" stop-opacity="1" />
              <stop offset="100%" stop-color="white" stop-opacity="0.4" />
            </linearGradient>
          </defs>
        </template>
        <template #widgets>
          <Tooltip
            :config="{
              index: { hide: true },
              Income: { color: '#5a9cfe' },
              Spending: { color: '#20c997' },
              CashFlow: { color: '#fd933a' },
              Portfolio: { color: '#e25563' },
            }"
            borderColor="#ff0000"
          />
        </template>
      </Chart>
    </div>
  </NCard>
</template>

<style lang="scss" scoped>
.no-data {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;

  a {
    text-decoration: none;
  }

  p:first-child {
    margin-top: 1rem;
  }
}

.chart-wrapper {
  width: 100%;
  height: 25rem;
}
</style>
