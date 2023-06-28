<script lang="ts" setup>
import { NAlert, NCard, NList, NListItem } from "naive-ui";
import Bar from "@/components/Bar.vue";

defineProps<{
  title: string;
  data: BarItem[];
  loading: boolean;
  expectedItems?: number;
  removable?: boolean;
  clickable?: boolean;
  onRemove?: (number) => void;
}>();

defineEmits<{
  select: (number) => void;
}>();

const defaultBarItem: BarItem = {
  icon: "",
  name: "",
  value: 0,
  tags: [],
  description: "",
};
</script>

<template>
  <NCard :segmented="{ content: true }" :title="title">
    <NList
      :data="data"
      :loading="loading"
      item-layout="vertical"
      item-layout-align="center"
      item-layout-gutter="0"
      item-layout-wrap
    >
      <NListItem v-for="_ in expectedItems ?? 5" v-if="loading">
        <Bar :loading="true" :removable="removable" v-bind="defaultBarItem" />
      </NListItem>
      <div v-else-if="data.length === 0" class="no-data">
        <NAlert title="No data available!" type="error" />
      </div>
      <NListItem v-for="item in data" v-else>
        <Bar
          :loading="false"
          :removable="removable"
          v-bind="item"
          :on-remove="onRemove"
          :clickable="clickable"
          @select="(value) => $emit('select', value)"
        />
      </NListItem>
    </NList>
  </NCard>
</template>

<style lang="scss" scoped>
.no-data {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}
</style>
