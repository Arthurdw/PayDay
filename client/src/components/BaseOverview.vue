<script setup lang="ts">
import { Data } from "vue3-charts/src/types";
import StatusCards from "@/components/StatusCards.vue";
import Evolution from "@/components/Evolution.vue";
import { computed } from "vue";
import { capitalize } from "@/utils/formatting";

const props = defineProps<{
  loadingOverview: boolean;
  loadingStatistics: boolean;
  overviewData: GetOverviewResponse;
  statisticsData: ChartStatistics[];
}>();

const colors = ["#5a9cfe", "#20c997", "#fd933a", "#e25563"];

const statisticsKeys = computed(() =>
  props.statisticsData.length > 0
    ? Object.keys(props.statisticsData?.[0]).map((key) => capitalize(key))
    : []
);
</script>

<template>
  <StatusCards :loading="loadingOverview" :overview-data="overviewData" />
  <Evolution
    :colors="colors"
    :loading="loadingStatistics"
    :statistics-data="statisticsData as Data[]"
    :statistics-keys="statisticsKeys"
  />
</template>

<style scoped></style>
