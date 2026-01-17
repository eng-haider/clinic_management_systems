<template>
  <v-app>
    <router-view />
  </v-app>
</template>

<script setup>
/**
 * App Component - Vue 3
 * المكون الرئيسي للتطبيق
 */

import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  // Initialize from localStorage
  authStore.initFromStorage()
  
  // Set language direction
  const lang = localStorage.getItem('lang') || 'ar'
  document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = lang
})
</script>

<style>
* {
  font-family: "Cairo", sans-serif;
}

html {
  overflow-y: auto;
}

/* Scrollbar Styles */
::-webkit-scrollbar {
  width: 5px;
}

::-webkit-scrollbar-track {
  background: #313942;
}

::-webkit-scrollbar-thumb {
  background: #3F51B5;
}
</style>
