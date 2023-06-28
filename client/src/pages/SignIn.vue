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
import {
  isBaseApiError,
  isUnAuthorizedApiError,
  isUnprocessableContentApiError,
} from "@/utils/guards";

let hasInitialized = ref(false);

let isLoading = ref(false);
const loadingBar = useLoadingBar();

let errorMessage = ref<string>("");

const formRef = ref<FormInst | null>(null);
let formData = ref({
  name: "",
  password: "",
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
  ],
};

const handleFormSubmit = async (e: MouseEvent) => {
  e.preventDefault();
  isLoading.value = true;
  loadingBar.start();

  try {
    await formRef.value?.validate();
    const res = await api.login({
      name: formData.value.name,
      password: formData.value.password,
    });
    let errorHandled = false;

    if (isBaseApiError(res)) {
      errorMessage.value = isUnAuthorizedApiError(res)
        ? "Invalid username or password"
        : "An unknown error occurred";
      errorHandled = true;
    } else if (isUnprocessableContentApiError<LoginData>(res)) {
      errorMessage.value =
        res.errors?.name[0] ??
        res.errors?.password[0] ??
        "An unknown error occurred";
      errorHandled = true;
    }

    if (errorHandled) loadingBar.error();
    else {
      window.location.hash = `${languagePrefix.value}/dashboard`;
      loadingBar.finish();
    }
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
      <h1>Sign in</h1>
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
          Sign in
        </NButton>
      </NForm>
      <p class="register">
        Don't have a account yet?
        <a :href="`#${languagePrefix}/register`">Register</a> now!
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
