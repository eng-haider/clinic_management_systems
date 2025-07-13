<template>
  <div id="app">
    <!-- Global Loading Spinner -->
    <GlobalLoadingSpinner 
      ref="globalLoadingSpinner"
      :is-visible="showGlobalLoading"
    />
    
    <!-- Main Router View -->
    <router-view class="nav_phone" />
  </div>
</template>

<script>
import GlobalLoadingSpinner from '@/components/GlobalLoadingSpinner.vue'

export default {
  name: 'App',
  components: {
    GlobalLoadingSpinner
  },
  data() {
    return {
      showGlobalLoading: false
    }
  },
  async created() {
    // Simple version check without loading states
    this.$router.beforeEach(async (to, from, next) => {
      try {
        const apiUrl = this.Url + '/api/version'
        const localVersion = localStorage.getItem('apiVersion')
        const response = await fetch(apiUrl)
        const data = await response.json()
        const apiVersion = data.web

        if (!localVersion || localVersion !== apiVersion) {
          localStorage.setItem('apiVersion', apiVersion)
          location.reload()
        }
      } catch (error) {
        console.error('Error fetching API version:', error)
      }
      next()
    })
  },
  mounted() {
    // Make global loading system available
    window.globalLoading = {
      show: () => {
        this.showGlobalLoading = true
      },
      hide: () => {
        this.showGlobalLoading = false
      }
    }
  },
  methods: {
    hideGlobalLoading() {
      this.showGlobalLoading = false
    }
  }
}
</script>

<style>
.card_num {
  font-size: 27px !important;
}

@media only screen and (max-width: 600px) {}

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

::-webkit-scrollbar-thumb:hover {
  background: #2e3d96;
}
</style>