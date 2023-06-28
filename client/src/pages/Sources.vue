<script lang="ts" setup>
import Layout from "@/components/Layout.vue";
import BarList from "@/components/BarList.vue";
import { ref, watch } from "vue";
import { api } from "@/utils/backend";
import { isBaseApiError } from "@/utils/guards";
import { useNotification } from "naive-ui";
import Paginated from "@/components/Paginated.vue";
import AddSource from "@/components/AddSource.vue";
import SourcePopup from "@/components/SourcePopup.vue";

const props = defineProps<{
  id?: string;
}>();

const notification = useNotification();

const loadingSources = ref(true);
const page = ref(1);
const maxPages = ref<number | null>(null);
const expectedItems = ref(10);
const sourcesAsBarItems = ref<BarItem[]>([]);
const selectedSource = ref<number | null>(
  props?.id ? parseInt(props.id) : null
);

const initSources = async () => {
  loadingSources.value = true;
  const res = await api.sources(page.value);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load sources. Please try again later.",
      content: res.error,
      duration: 2500,
    });
    return;
  }

  const sources = res as GetSourcesResponse;

  sourcesAsBarItems.value = sources.data.map((source) => ({
    icon: source.imageUrl,
    name: source.name,
    value: source.id,
    description: source.description,
    tags: [
      {
        content: source.type,
        type: "primary",
      },
    ],
  }));

  page.value = sources.current_page;
  maxPages.value = sources.last_page;
  expectedItems.value = sourcesAsBarItems.value.length;
  loadingSources.value = false;
};

const deleteSource = async (id: number) => {
  const res = await api.deleteSource(id);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not remove source. Please try again later.",
      content: res.error,
      duration: 2500,
    });
    return;
  }

  notification.success({
    title: "Source removed successfully.",
    duration: 2500,
  });
  api.invalidateCache();
  await initSources();
};

const popupDelete = async (id: number) => {
  selectedSource.value = null;
  await deleteSource(id);
};

const selectResource = (id: number) => {
  selectedSource.value = id;
  window.location.hash = window.location.hash + `/${id}`;
};

const closePopup = () => {
  selectedSource.value = null;
  const segments = window.location.hash.split("/");
  window.location.hash = segments.slice(0, segments.length - 1).join("/");
};

initSources();
watch(page, initSources);
</script>

<template>
  <SourcePopup
    v-if="selectedSource !== null"
    :id="selectedSource"
    @close="closePopup"
    @delete="popupDelete"
  />
  <Layout>
    <BarList
      :data="sourcesAsBarItems"
      :expected-items="expectedItems"
      :loading="loadingSources"
      :on-remove="deleteSource"
      clickable
      removable
      title="Sources"
      @select="selectResource"
    />
    <Paginated
      :is-loading="loadingSources"
      :page="page"
      :total="maxPages"
      @update="(pg: number) => (page = pg)"
    />
    <AddSource :on-new-source="initSources" />
  </Layout>
</template>

<style lang="scss" scoped></style>
