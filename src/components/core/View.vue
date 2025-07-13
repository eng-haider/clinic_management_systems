<template>
  <v-content :class="$vuetify.theme.dark ? undefined : 'grey lighten-3'">
    <transition name="page-transition" mode="out-in">
      <router-view />
    </transition>
  </v-content>
</template>

<script>
export default {};
</script>

<style scoped>
.page-transition-enter-active,
.page-transition-leave-active {
  transition: all 0.3s ease;
}

.page-transition-enter {
  opacity: 0;
  transform: translateY(20px);
}

.page-transition-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
