<script lang="ts" setup>
import { computed, ref } from "vue";
import { NSkeleton } from "naive-ui";
import errorImage from "@/assets/error.svg";

defineProps<{
  title?: string;
  src: string;
  alt: string;
  width?: string;
  height?: string;
}>();

const loaded = ref(false);
const error = ref(false);

const onImageLoad = () => {
  loaded.value = true;
};

const onImageError = () => {
  error.value = true;
  loaded.value = false;
};

const noSize = computed(() => !loaded.value && !error.value);
</script>

<template>
  <div>
    <NSkeleton
      v-if="noSize"
      :height="height ?? '100%'"
      :sharp="false"
      :width="width ?? '100%'"
    />
    <img
      :alt="alt"
      :src="error ? errorImage : src"
      :style="{
        width,
        height,
        display: noSize ? 'none' : 'block',
      }"
      :title="title"
      @error="onImageError"
      @load="onImageLoad"
    />
  </div>
</template>

<style lang="scss" scoped>
img {
  border-radius: 0.25rem;
  object-fit: cover;
}
</style>
