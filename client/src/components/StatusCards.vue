<script lang="ts" setup>
import { NSpace } from "naive-ui";
import StatusCard from "@/components/StatusCard.vue";
import { calculateDiff } from "@/utils/math";
import { capitalize, formatNumberAsCurrency } from "@/utils/formatting";

defineProps<{
  loading: boolean;
  overviewData: GetOverviewResponse;
}>();

interface Card {
  total: number;
  lastMonth: number;
}

const moreIsBetter = ["income", "cashFlow", "portfolio"];

const getIsTagType = (title: string, card: Card): "success" | "error" => {
  const diff = card.total - card.lastMonth;
  const isMoreBetter = moreIsBetter.includes(title);

  return (diff > 0 && isMoreBetter) || (diff <= 0 && !isMoreBetter)
    ? "success"
    : "error";
};
</script>

<template>
  <NSpace item-style="width: calc(25% - 32px);" justify="space-between">
    <StatusCard
      v-for="[title, card] in Object.entries(overviewData)"
      :loading="loading"
      :tag-content="calculateDiff(card.total, card.lastMonth)"
      :tag-type="getIsTagType(title, card)"
      :title="capitalize(title)"
    >
      <p>{{ formatNumberAsCurrency(card.total) }}</p>
    </StatusCard>
  </NSpace>
</template>
