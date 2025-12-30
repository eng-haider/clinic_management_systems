<template>
  <div id="app">
    <!-- PWA Install Banner -->
    <PWAInstall />
    
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
import PWAInstall from '@/components/PWAInstall.vue'

export default {
  name: 'App',
  components: {
    GlobalLoadingSpinner,
    PWAInstall
  },
  data() {
    return {
      showGlobalLoading: false
    }
  },
  async created() {
    console.log('🎯 App.vue created - setting up router guard')
    
    // Simple version check without loading states
    this.$router.beforeEach(async (to, from, next) => {
      console.log('🔀 Router navigation:', from.path, '→', to.path)
      
      // Skip version check in Electron to avoid delays
      const isElectron = typeof window !== 'undefined' && window.process && window.process.type;
      
      if (!isElectron) {
        try {
          const apiUrl = this.Url + '/api/version'
          console.log('📡 Checking API version:', apiUrl)
          const localVersion = localStorage.getItem('apiVersion')
          const response = await fetch(apiUrl, { timeout: 3000 })
          const data = await response.json()
          const apiVersion = data.web
          console.log('✅ API version:', apiVersion, '| Local:', localVersion)

          if (!localVersion || localVersion !== apiVersion) {
            console.log('🔄 Version mismatch, reloading...')
            localStorage.setItem('apiVersion', apiVersion)
            location.reload()
            return
          }
        } catch (error) {
          console.error('❌ Error fetching API version:', error)
        }
      } else {
        console.log('⚡ Skipping version check in Electron')
      }
      
      console.log('➡️ Navigating to:', to.path)
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