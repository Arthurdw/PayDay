<script lang="ts" setup>
import {
  FormInst,
  NButton,
  NForm,
  NFormItem,
  NInput,
  useLoadingBar,
} from "naive-ui";
import { ref } from "vue";
import { api } from "@/utils/backend";
import Loading from "@/components/Loading.vue";
import { initUser } from "@/utils/user";
import { languagePrefix } from "@/utils/i18n";
import { isBaseApiError, isUnprocessableContentApiError } from "@/utils/guards";

let hasInitialized = ref(false);

let isLoading = ref(false);
const loadingBar = useLoadingBar();

let errorMessage = ref<string>("");

const formRef = ref<FormInst | null>(null);
let formData = ref({
  name: "",
  password: "",
  confirmPassword: "",
});

let formRules = {
  name: {
    required: true,
    message: "Please enter your username",
    trigger: ["blur", "input"],
  },
  password: [
    {
      required: true,
      message: "Please enter your password",
      trigger: ["blur", "input"],
    },
    {
      validator: (rule: any, value: string) => value.length >= 8,
      message: "Password must be at least 8 characters long",
      trigger: ["blur", "input"],
    },
  ],
  confirmPassword: [
    {
      required: true,
      message: "Please confirm your password",
      trigger: ["blur", "input"],
    },
    {
      validator: (rule: any, value: string) =>
        value === formData.value.password,
      message: "Passwords do not match",
      trigger: ["blur", "input"],
    },
  ],
};

const handleFormSubmit = async (e: MouseEvent) => {
  e.preventDefault();
  isLoading.value = true;
  loadingBar.start();
  errorMessage.value = "";

  try {
    await formRef.value?.validate();
    const res = await api.register({
      name: formData.value.name,
      password: formData.value.password,
    });
    let errorHandled = false;

    if (isBaseApiError(res)) {
      errorMessage.value = res.error;
      errorHandled = true;
    } else if (isUnprocessableContentApiError<RegisterData>(res)) {
      errorMessage.value =
        res.errors?.name[0] ??
        res.errors?.password[0] ??
        "An unknown error occurred";
      errorHandled = true;
    }

    if (errorHandled) {
      loadingBar.error();
      isLoading.value = false;
      return;
    }

    loadingBar.finish();
    window.location.hash = `${languagePrefix.value}/dashboard`;
  } catch (e) {
    loadingBar.error();
  }
  isLoading.value = false;
};

const handleInit = async () => {
  loadingBar.start();
  const success = await initUser();
  loadingBar.finish();

  if (success) window.location.hash = `${languagePrefix.value}/dashboard`;
  else hasInitialized.value = true;
};
handleInit();
</script>

<template>
  <main v-if="hasInitialized">
    <div class="wrapper">
      <img alt="PayDay icon" class="icon" src="@/assets/logo.svg" />
      <h1>Register</h1>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      <NForm
        ref="formRef"
        :model="formData"
        :rules="formRules"
        class="sign-in-form"
      >
        <NFormItem label="Username" path="name">
          <NInput
            v-model:value="formData.name"
            :disabled="isLoading"
            clearable
            placeholder="example@xiler.net"
            type="text"
          />
        </NFormItem>
        <NFormItem label="Password" path="password">
          <NInput
            v-model:value="formData.password"
            :disabled="isLoading"
            :minlength="8"
            clearable
            placeholder=""
            show-password-on="click"
            type="password"
          />
        </NFormItem>
        <NFormItem label="Confirm password" path="confirmPassword">
          <NInput
            v-model:value="formData.confirmPassword"
            :disabled="isLoading"
            :minlength="8"
            clearable
            placeholder=""
            show-password-on="click"
            type="password"
            @keydown.enter="handleFormSubmit"
          />
        </NFormItem>
        <NButton
          :disabled="isLoading"
          :loading="isLoading"
          type="primary"
          @click="handleFormSubmit"
        >
          Register
        </NButton>
      </NForm>
      <p class="register">
        Already have a account?
        <a :href="`#${languagePrefix}/login`">Sign in</a>.
      </p>
    </div>
  </main>
  <Loading v-else />
</template>

<style lang="scss" scoped>
main {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;

  .icon {
    width: 10rem;
    height: 10rem;
    max-width: 90vw;
    max-height: 90vw;

    display: block;
    margin: 0 auto;
  }

  h1 {
    margin-top: 4rem;
    text-align: center;
    font-weight: 600;
    font-size: 2rem;
  }

  .error-message {
    color: #d03050;
    text-align: center;
  }

  .wrapper {
    width: 30rem;
    max-width: 90vw;
  }

  .sign-in-form {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .register {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.8rem;

    a {
      text-decoration: none;
    }
  }
}
</style>
