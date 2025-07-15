<template>
  <v-btn
    v-if="showInstallButton"
    @click="installApp"
    :loading="installing"
    color="primary"
    small
    outlined
    class="install-btn"
  >
    <v-icon left small>mdi-download</v-icon>
    {{ $t('install') || 'Install' }}
  </v-btn>
</template>

<script>
import pwaManager from '@/utils/pwa'

export default {
  name: 'InstallButton',
  data() {
    return {
      showInstallButton: false,
      installing: false
    }
  },
  
  mounted() {
    // Check if PWA can be installed
    this.checkInstallAvailability()
    
    // Listen for PWA install availability
    window.addEventListener('pwa-install-available', this.handleInstallAvailable)
    window.addEventListener('pwa-installed', this.handleInstalled)
  },
  
  beforeDestroy() {
    window.removeEventListener('pwa-install-available', this.handleInstallAvailable)
    window.removeEventListener('pwa-installed', this.handleInstalled)
  },
  
  methods: {
    checkInstallAvailability() {
      const status = pwaManager.getInstallStatus()
      this.showInstallButton = status.canInstall
    },
    
    handleInstallAvailable() {
      this.showInstallButton = true
    },
    
    handleInstalled() {
      this.showInstallButton = false
    },
    
    async installApp() {
      this.installing = true
      
      try {
        const result = await pwaManager.promptInstall()
        
        if (result === 'accepted') {
          this.showInstallButton = false
          
          this.$swal.fire({
            icon: 'success',
            title: this.$t('app_installed') || 'App Installed!',
            text: this.$t('app_installed_description') || 'Smart Clinic has been installed successfully.',
            timer: 2000,
            showConfirmButton: false
          })
        }
      } catch (error) {
        console.error('Install error:', error)
        
        this.$swal.fire({
          icon: 'error',
          title: this.$t('install_error') || 'Installation Error',
          text: this.$t('install_error_description') || 'There was an error installing the app. Please try again.',
          confirmButtonText: this.$t('ok') || 'OK'
        })
      } finally {
        this.installing = false
      }
    }
  }
}
</script>

<style scoped>
.install-btn {
  margin-left: 8px;
}
</style>
