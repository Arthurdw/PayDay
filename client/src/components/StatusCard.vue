<script lang="ts" setup>
import {
  NCard,
  NNumberAnimation,
  NSkeleton,
  NTag,
  NumberAnimationInst,
} from "naive-ui";
import { computed, ref } from "vue";

const props = defineProps<{
  title: string;
  tagType: TagType;
  tagContent: number;
  loading: boolean;
}>();

const numberAnimationInstRef = ref<NumberAnimationInst | null>(null);

const roundNumber = (num: number) =>
  Math.round((num + Number.EPSILON) * 100) / 100;

const content = computed(() => roundNumber(props.tagContent));
const prefix = content > 0 ? "+" : "";
</script>

<template>
  <NCard :title="title">
    <template #header-extra>
      <div class="tag">
        <NSkeleton v-if="loading" height="1.75rem" round width="5.5rem" />
        <NTag v-else :type="tagType" round>
          {{ prefix }}
          <NNumberAnimation
            ref="numberAnimationInstRef"
            :active="true"
            :from="0"
            :precision="2"
            :to="isNaN(content) ? 0 : content"
          />
          %
        </NTag>
      </div>
    </template>
    <NSkeleton
      v-if="loading"
      :sharp="false"
      height="1.5rem"
      text
      width="100%"
    />
    <slot v-else></slot>
  </NCard>
</template>

<style lang="scss" scoped>
.tag {
  margin-left: 0.5rem;
}
</style>
