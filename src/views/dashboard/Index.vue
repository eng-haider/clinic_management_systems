<template>
  <v-app  id="inspire">
    
   
    <dashboard-core-app-bar v-model="expandOnHover" />
    <dashboard-core-drawer :expand-on-hover.sync="expandOnHover" />
    <dashboard-core-view />

    
  </v-app>
</template>
<!-- <style>
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  font-size: 24px;
}</style> -->
<script>
export default {
  name: "DashboardIndex",

  components: {
    DashboardCoreAppBar: () => import("../../components/core/AppBar.vue"),
    DashboardCoreDrawer: () => import("../../components/core/Drawer"),
    DashboardCoreView: () => import("../../components/core/View")
  },

  data: () => ({
    expandOnHover: false
  })
};
</script>
