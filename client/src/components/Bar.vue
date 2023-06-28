<script lang="ts" setup>
import { NButton, NRow, NSkeleton, NTag } from "naive-ui";
import Image from "@/components/Image.vue";
import close from "@/assets/close.svg";
import { ref } from "vue";

const props = defineProps<{
  icon: string;
  name: string;
  value: number;
  tags: Tag[];
  description: string;
  loading: boolean;
  removable?: boolean;
  clickable?: boolean;
  onRemove?: (number) => void;
}>();

defineEmits<{
  (e: "select", value: number): void;
}>();

const isRemoveLoading = ref(false);

const handleRemove = async (e: MouseEvent) => {
  e.stopPropagation();

  if (props.onRemove) {
    isRemoveLoading.value = true;
    const cb: void | Promise<void> = props.onRemove(props.value);
    const isAsync = cb instanceof Promise;
    if (isAsync) await cb;
    isRemoveLoading.value = false;
  }
};
</script>

<template>
  <div :class="{ clickable }" @click="$emit('select', value)">
    <NRow v-if="loading" align-items="center" justify-content="space-between">
      <NSkeleton :sharp="false" height="2rem" width="2rem" />
      <NSkeleton :sharp="false" height="1.5rem" text width="5%" />
      <NSkeleton :sharp="false" height="1.5rem" text width="10%" />
      <NSkeleton :sharp="false" height="1.5rem" text width="30%" />
      <div class="horizontal-space">
        <NSkeleton height="1.5rem" round width="4rem" />
        <NSkeleton
          v-if="removable"
          :sharp="false"
          height="2rem"
          width="2.75rem"
        />
      </div>
    </NRow>

    <NRow v-else align-items="center" justify-content="space-between">
      <Image :alt="`logo of ${name}`" :src="icon" height="2rem" width="2rem" />
      <p class="bar-source">{{ name }}</p>
      <p :class="{ 'bar-description': true, 'bar-removable': true }">
        {{ description }}
      </p>

      <div :class="{ 'horizontal-space': true, 'bar-removable': removable }">
        <ul>
          <li v-for="tag in tags">
            <NTag :type="tag.type" round>
              {{ tag.content }}
            </NTag>
          </li>
        </ul>
        <NButton
          v-if="removable"
          :disabled="isRemoveLoading"
          :loading="isRemoveLoading"
          type="info"
          @click="handleRemove"
        >
          <template v-if="!isRemoveLoading" #icon>
            <Image
              :alt="`close icon of ${name}`"
              :src="close"
              height="1rem"
              width="1rem"
            />
          </template>
        </NButton>
      </div>
    </NRow>
  </div>
</template>

<style lang="scss" scoped>
ul {
  display: flex;
  gap: 0.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.horizontal-space {
  display: flex;
  align-items: center;
  justify-content: end;
  gap: 0.5rem;
  height: 2rem;
  width: 10%;
  min-width: fit-content;

  &.bar-removable {
    width: 20%;
  }
}

.clickable {
  cursor: pointer;
}

.bar-source {
  width: 20%;
}

.bar-description {
  width: calc(70% - 7rem);

  &.bar-removable {
    width: calc(60% - 7rem);
  }
}
</style>
