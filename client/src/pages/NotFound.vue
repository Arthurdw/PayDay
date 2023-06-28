<script lang="ts" setup>
import Layout from "@/components/Layout.vue";
import { NGradientText } from "naive-ui";
</script>

<template>
  <Layout>
    <div class="wrapper">
      <h1>
        <NGradientText type="error"> 404</NGradientText>
      </h1>
      <h2>The requested page could not be found!</h2>
      <p>
        Go back
        <a href="/"> <NGradientText type="info"> home</NGradientText> </a>.
      </p>
    </div>
  </Layout>
</template>

<style lang="scss" scoped>
.wrapper {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;

  h1,
  h2,
  p {
    text-align: center;
  }

  h1 {
    font-size: 10rem;
    font-weight: 600;
    margin: 1rem;
  }

  p {
    font-size: 1.5rem;

    a {
      text-decoration: none;
    }
  }
}
</style>
