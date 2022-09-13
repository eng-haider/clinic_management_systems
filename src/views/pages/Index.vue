<template>
  <v-app>
 <v-content id="pages" :class="$vuetify.theme.dark ? undefined : 'grey lighten-3'">
    <router-view />
    </v-content>
  </v-app>
</template>

<script>
export default {
  name: "PagesIndex",
  created() {
    // this.$vuetify.theme.dark = false;
  },

  beforeDestroy() {
    // this.$vuetify.theme.dark = false;
  }
};
</script>
