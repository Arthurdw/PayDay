<script lang="ts" setup>
import {
  FormInst,
  NButton,
  NCard,
  NForm,
  NFormItem,
  NInput,
  NSelect,
  NSpace,
  useLoadingBar,
  useNotification,
} from "naive-ui";
import { ref } from "vue";
import { api } from "@/utils/backend";
import { isBaseApiError, isUnprocessableContentApiError } from "@/utils/guards";

const props = defineProps<{
  onNewSource: () => void;
}>();

const loadingBar = useLoadingBar();
const notification = useNotification();

const sourceTypes = [
  {
    label: "Store",
    value: "store",
  },
  {
    label: "Portfolio",
    value: "portfolio",
  },
];

const initialSourceData = {
  name: "",
  type: sourceTypes[0].value,
  imageUrl: "",
  descriptionEnglish: "",
  descriptionDutch: "",
} as AddSourceData;

const sendingRequest = ref(false);
const formRef = ref<FormInst | null>(null);
const formData = ref(Object.assign({}, initialSourceData));
const formRules = {
  name: {
    required: true,
    message: "Please enter a company name",
    trigger: ["blur", "input"],
  },
  type: {
    required: true,
    message: "Please select a source type",
    trigger: ["blur", "input"],
  },
  imageUrl: {
    required: true,
    message: "Please enter an image URL",
    trigger: ["blur", "input"],
  },
  descriptionEnglish: {
    required: true,
    message: "Please enter an English description",
    trigger: ["blur", "input"],
  },
  descriptionDutch: {
    required: true,
    message: "Please enter a Dutch description",
    trigger: ["blur", "input"],
  },
};

const submitForm = async (e: MouseEvent) => {
  e.preventDefault();
  sendingRequest.value = true;
  loadingBar.start();

  try {
    await formRef.value?.validate();
    const res = await api.addSource(formData.value);

    let errorHandled = false;

    if (isBaseApiError(res)) {
      notification.error({
        title: "Could not add source. Please try again later.",
        content: res.error,
        duration: 2500,
      });
      errorHandled = true;
    } else if (isUnprocessableContentApiError<AddSourceData>(res)) {
      notification.error({
        title: "Could not add source.",
        content: Object.values(res.errors)
          .map((e) => e.join("\n"))
          .join("\n"),
        duration: 2500,
      });
      errorHandled = true;
    }

    if (errorHandled) loadingBar.error();
    else {
      notification.success({
        title: "Source added successfully.",
        duration: 2500,
      });
      api.invalidateCache();
      formData.value = Object.assign({}, initialSourceData);
      const cb: void | Promise<void> = props.onNewSource();
      if (cb instanceof Promise) await cb;
      loadingBar.finish();
    }
  } catch (e) {
    loadingBar.error();
  }
  sendingRequest.value = false;
};
</script>

<template>
  <NCard :segmented="true" title="Add source">
    <NForm ref="formRef" :model="formData" :rules="formRules">
      <NSpace item-style="width: calc(50% - 8px);" justify="space-between">
        <NFormItem label="Name" path="name">
          <NInput
            v-model:value="formData.name"
            :disabled="sendingRequest"
            clearable
            placeholder="Company Name"
            type="text"
          />
        </NFormItem>
        <NFormItem label="Icon" path="imageUrl">
          <NInput
            v-model:value="formData.imageUrl"
            :disabled="sendingRequest"
            clearable
            placeholder="https://xiler.net/logo.png"
            type="text"
          />
        </NFormItem>
      </NSpace>
      <NFormItem label="Type" path="type">
        <NSelect
          v-model:value="formData.type"
          :disabled="sendingRequest"
          :options="sourceTypes"
          :value="formData.type"
        />
      </NFormItem>
      <NFormItem label="Description English" path="descriptionEnglish">
        <NInput
          v-model:value="formData.descriptionEnglish"
          :disabled="sendingRequest"
          clearable
          placeholder="Description in English"
          type="text"
        />
      </NFormItem>
      <NFormItem label="Description Dutch" path="descriptionDutch">
        <NInput
          v-model:value="formData.descriptionDutch"
          :disabled="sendingRequest"
          clearable
          placeholder="Description in Dutch"
          type="text"
          @keydown.enter="submitForm"
        />
      </NFormItem>
      <NButton
        :disabled="sendingRequest"
        :loading="sendingRequest"
        type="primary"
        @click="submitForm"
      >
        Add Source
      </NButton>
    </NForm>
  </NCard>
</template>

<style scoped></style>
