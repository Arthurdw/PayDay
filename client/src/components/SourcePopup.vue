<script lang="ts" setup>
import { NButton, NModal, NSpace, useNotification } from "naive-ui";
import Image from "@/components/Image.vue";
import closeImage from "@/assets/close-blue.svg";
import { reactive, ref } from "vue";
import BaseOverview from "@/components/BaseOverview.vue";
import { defaultOverviewData } from "@/utils/sampleData";
import { isBaseApiError } from "@/utils/guards";
import { api } from "@/utils/backend";
import { statisticsResponseToChartStatistics } from "@/utils/cast";

const props = defineProps<{
  id: number;
}>();

defineEmits<{
  (e: "close"): void;
  (e: "delete", id: number): void;
}>();

const notification = useNotification();

const loadingOverview = ref(true);
const loadingStatistics = ref(true);

let overviewData = reactive<GetOverviewResponse>(
  Object.assign({}, defaultOverviewData)
);
let statisticsData = ref<ChartStatistics[]>([]);
const source = ref<GetSourceResponse | null>(null);

const attemptApiAction = async <T>(
  action: (number) => Promise<T | BaseApiError>,
  errorMessage: string
) => {
  const res = await action(props.id);

  if (isBaseApiError(res)) {
    notification.error({
      title: errorMessage,
      content: res.error,
      duration: 2500,
      keepAliveOnHover: true,
    });
    return;
  }

  return res;
};

const initSource = async () => {
  const res = await attemptApiAction(
    api.source,
    "Could not load source data. Please try again later."
  );

  if (!res) {
    source.value = {
      imageUrl: "",
      name: "Unknown",
      description: "",
      id: -1,
      type: "unknown",
    };
  }

  source.value = res as GetSourceResponse;
};

const initOverview = async () => {
  loadingOverview.value = true;
  const res = await attemptApiAction<GetOverviewResponse>(
    api.overviewBySource,
    "Could not load overview data. Please try again later."
  );

  if (res) overviewData = res;

  loadingOverview.value = false;
};

const initStatistics = async () => {
  loadingStatistics.value = true;
  const res = await attemptApiAction<GetStatisticsResponse>(
    api.statisticsBySource,
    "Could not load statistics data. Please try again later."
  );

  if (res) statisticsData.value = statisticsResponseToChartStatistics(res);

  loadingStatistics.value = false;
};

initSource();
initOverview();
initStatistics();
</script>

<template>
  <NModal :show="true">
    <div class="popup-wrapper">
      <div class="title-section">
        <h2>{{ source?.name ?? "Loading..." }}</h2>
        <NSpace>
          <NButton
            :disabled="!source?.name"
            type="error"
            @click="$emit('delete', props.id)"
          >
            <p>DELETE</p>
          </NButton>
          <NButton ghost type="info" @click="$emit('close')">
            <template #icon>
              <Image
                :src="closeImage"
                alt="Close popup"
                height="1rem"
                title="Close popup"
                width="1rem"
              />
            </template>
          </NButton>
        </NSpace>
      </div>
      <BaseOverview
        :loading-overview="loadingOverview"
        :loading-statistics="loadingStatistics"
        :overview-data="overviewData"
        :statistics-data="statisticsData"
      />
    </div>
  </NModal>
</template>

<style lang="scss" scoped>
.popup-wrapper {
  width: 80%;
  min-height: 60dvh;
  background-color: #eaecf0;

  display: flex;
  flex-direction: column;
  gap: 1rem;

  padding: 2rem;
  border-radius: 0.25rem;
}

.title-section {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;

  padding: 1.5rem;
  background-color: #fff;
  border-radius: 0.25rem;
}

h2 {
  padding: 0;
  margin: 0;
  font-weight: 600;
}

.loading {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
</style>
