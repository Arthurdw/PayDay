<script lang="ts" setup>
import { CirclesToRhombusesSpinner } from "epic-spinners";
</script>

<template>
  <div class="loading">
    <CirclesToRhombusesSpinner :size="100" color="#3eaf7c" />
  </div>
</template>

<style scoped>
.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
</style>
