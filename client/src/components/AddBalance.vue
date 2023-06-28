<script lang="ts" setup>
import {
  FormInst,
  NAutoComplete,
  NButton,
  NCard,
  NForm,
  NFormItem,
  NInputNumber,
  NSpace,
  NSwitch,
  useLoadingBar,
  useNotification,
} from "naive-ui";
import { computed, ref } from "vue";
import { api } from "@/utils/backend";
import { isBaseApiError, isUnprocessableContentApiError } from "@/utils/guards";

interface SourceType {
  label: string;
  value: string;
}

const props = defineProps<{
  onNewBalance: () => void;
}>();

const loadingBar = useLoadingBar();
const notification = useNotification();

const initialBalanceData = {
  source: "",
  in: 0,
  out: 0,
  subscription: false,
};
const companies = ref<SourceType[]>([]);

const sendingRequest = ref(false);
const formRef = ref<FormInst | null>(null);
const formData = ref(Object.assign({}, initialBalanceData));
const sourceId = ref<number | null>(null);
const formRules = {
  source: {
    required: true,
    trigger: ["blur", "input"],
  },
};

const submitForm = async (e: MouseEvent) => {
  e.preventDefault();
  sendingRequest.value = true;
  loadingBar.start();
  try {
    await formRef.value?.validate();
    const res = await api.addBalance({
      out: formData.value.out,
      in: formData.value.in,
      source_id: sourceId.value!!,
      subscription: formData.value.subscription,
    });

    let errorHandled = false;

    if (isBaseApiError(res)) {
      notification.error({
        title: "Could not add balance. Please try again later.",
        content: res.error,
        duration: 2500,
      });
      errorHandled = true;
    } else if (isUnprocessableContentApiError<AddBalanceData>(res)) {
      notification.error({
        title: "Could not add balance.",
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
        title: "Balance added successfully!",
        duration: 2500,
      });

      api.invalidateCache();
      formData.value = Object.assign({}, initialBalanceData);
      const cb: void | Promise<void> = props.onNewBalance();
      if (cb instanceof Promise) await cb;
      loadingBar.finish();
    }
  } catch (e) {
    loadingBar.error();
  }
  sendingRequest.value = false;
};

const initCompanies = async () => {
  const res = await api.sources(1);

  if (isBaseApiError(res)) {
    notification.error({
      title: "Could not load companies. Please try again later.",
      content: res.error,
      duration: 2500,
    });
    return;
  }

  const sources = res as GetSourcesResponse;

  companies.value = sources.data.map((source) => ({
    label: source.name,
    value: source.id.toString(),
  }));
};

const companyValidationStatus = computed(() => {
  const searchedCompany = companies.value.find(
    (company) => company.label === formData.value.source
  );
  sourceId.value = searchedCompany ? parseInt(searchedCompany.value) : null;

  return searchedCompany ? "success" : "error";
});

const companyValidationMessage = computed(() => {
  if (companyValidationStatus.value === "error") {
    return "Please select an existing company";
  }
  return "";
});

const autoCompleteCompanies = computed(() => {
  return companies.value.filter((company) =>
    company.label.toLowerCase().includes(formData.value.source.toLowerCase())
  );
});
initCompanies();
</script>

<template>
  <NCard :segmented="true" title="Add balance">
    <NForm ref="formRef" :model="formData" :rules="formRules">
      <NFormItem
        :feedback="companyValidationMessage"
        :validation-status="companyValidationStatus"
        label="Company"
        path="source_id"
      >
        <NAutoComplete
          v-model:value="formData.source"
          :disabled="sendingRequest"
          :options="autoCompleteCompanies"
          placeholder="Company name"
        />
      </NFormItem>
      <div class="row-wrapper">
        <NSpace item-style="width: calc(50% - 8px);" justify="space-between">
          <NFormItem label="In" path="in">
            <div class="full-width">
              <NInputNumber
                v-model:value="formData.in"
                :disabled="sendingRequest"
                :min="0"
                :precision="2"
                placeholder="Amount"
                @keydown.enter="submitForm"
              />
            </div>
          </NFormItem>
          <NFormItem label="Out" path="out">
            <div class="full-width">
              <NInputNumber
                v-model:value="formData.out"
                :disabled="sendingRequest"
                :min="0"
                :precision="2"
                placeholder="Amount"
                @keydown.enter="submitForm"
              />
            </div>
          </NFormItem>
        </NSpace>
        <div class="switch">
          <NFormItem label="Monthly" path="subscription">
            <NSwitch
              v-model:value="formData.subscription"
              :disabled="sendingRequest"
              @keydown.enter="submitForm"
            />
          </NFormItem>
        </div>
      </div>
      <NButton
        :disabled="sendingRequest"
        :loading="sendingRequest"
        type="primary"
        @click="submitForm"
      >
        Add balance
      </NButton>
    </NForm>
  </NCard>
</template>

<style lang="scss" scoped>
$switch-width: 4.16rem;

.full-width {
  width: 100%;
}

.switch {
  width: $switch-width;
}

.row-wrapper {
  display: flex;
  flex-direction: row;
  justify-content: space-between;

  .n-space {
    width: calc(100% - 12px - #{$switch-width});
  }
}
</style>
