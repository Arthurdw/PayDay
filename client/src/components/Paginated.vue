<script lang="ts" setup>
import { NPagination, NSkeleton } from "naive-ui";

defineProps<{
  total: number | null;
  isLoading: boolean;
  page: number;
}>();

defineEmits<{
  update: (page: number) => void;
}>();
</script>

<template>
  <div v-if="total !== 1" class="center-content">
    <NSkeleton
      v-if="isLoading && total === null"
      :sharp="false"
      height="1.75rem"
      width="8rem"
    />
    <NPagination
      v-else
      :page="page"
      :page-count="total"
      @update:page="$emit('update', $event)"
    />
  </div>
</template>

<style lang="scss" scoped>
.center-content {
  display: flex;
  justify-content: center;
}
</style>
