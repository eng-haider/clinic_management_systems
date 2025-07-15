<!-- <template>
  <div v-if="showInstallButton" class="pwa-install-banner">
    <v-banner
      two-line
      :color="$vuetify.theme.dark ? 'grey darken-4' : 'blue lighten-5'"
      :dark="$vuetify.theme.dark"
      class="ma-2"
    >
      <v-avatar
        slot="icon"
        color="primary"
        size="40"
      >
        <v-icon color="white">mdi-download</v-icon>
      </v-avatar>
      
      <div>
        <div class="text-h6">{{ $t('install_app') || 'Install App' }}</div>
        <div class="text-body-2">
          {{ $t('install_app_description') || 'Install Smart Clinic for a better experience' }}
        </div>
      </div>
      
      <template v-slot:actions>
        <v-btn
          text
          color="primary"
          @click="installApp"
          :loading="installing"
        >
          {{ $t('install') || 'Install' }}
        </v-btn>
        
        <InstallGuide />
        
        <v-btn
          text
          @click="dismissInstall"
        >
          {{ $t('dismiss') || 'Dismiss' }}
        </v-btn>
      </template>
    </v-banner>
  </div>
</template>

<script>
import InstallGuide from './InstallGuide.vue'

export default {
  name: 'PWAInstall',
  components: {
    InstallGuide
  },
  data() {
    return {
      deferredPrompt: null,
      showInstallButton: false,
      installing: false
    }
  },
  
  mounted() {
    // Check if PWA is already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
      console.log('PWA is already installed');
      return;
    }
    
    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt);
    
    // Listen for appinstalled event
    window.addEventListener('appinstalled', this.handleAppInstalled);
    
    // Check if already dismissed
    const dismissed = localStorage.getItem('pwa-install-dismissed');
    if (dismissed) {
      const dismissedTime = new Date(dismissed);
      const now = new Date();
      const daysPassed = (now - dismissedTime) / (1000 * 60 * 60 * 24);
      
      // Show again after 7 days
      if (daysPassed < 7) {
        return;
      }
    }
  },
  
  beforeDestroy() {
    window.removeEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt);
    window.removeEventListener('appinstalled', this.handleAppInstalled);
  },
  
  methods: {
    handleBeforeInstallPrompt(event) {
      console.log('Before install prompt event received');
      
      // Prevent the mini-infobar from appearing on mobile
      event.preventDefault();
      
      // Store the event for later use
      this.deferredPrompt = event;
      
      // Show our custom install button
      this.showInstallButton = true;
    },
    
    handleAppInstalled() {
      console.log('App installed successfully');
      this.showInstallButton = false;
      this.deferredPrompt = null;
      
      // Show success message
      this.$swal.fire({
        icon: 'success',
        title: this.$t('app_installed') || 'App Installed!',
        text: this.$t('app_installed_description') || 'Smart Clinic has been installed successfully.',
        timer: 3000,
        showConfirmButton: false
      });
    },
    
    async installApp() {
      if (!this.deferredPrompt) {
        console.log('No deferred prompt available');
        return;
      }
      
      this.installing = true;
      
      try {
        // Show the install prompt
        this.deferredPrompt.prompt();
        
        // Wait for the user to respond to the prompt
        const result = await this.deferredPrompt.userChoice;
        
        console.log('User choice:', result.outcome);
        
        if (result.outcome === 'accepted') {
          console.log('User accepted the install prompt');
        } else {
          console.log('User dismissed the install prompt');
        }
        
        // Clear the deferred prompt
        this.deferredPrompt = null;
        this.showInstallButton = false;
        
      } catch (error) {
        console.error('Error during app installation:', error);
        
        this.$swal.fire({
          icon: 'error',
          title: this.$t('install_error') || 'Installation Error',
          text: this.$t('install_error_description') || 'There was an error installing the app. Please try again.',
          confirmButtonText: this.$t('ok') || 'OK'
        });
      } finally {
        this.installing = false;
      }
    },
    
    dismissInstall() {
      this.showInstallButton = false;
      this.deferredPrompt = null;
      
      // Remember dismissal
      localStorage.setItem('pwa-install-dismissed', new Date().toISOString());
      
      console.log('Install prompt dismissed');
    }
  }
}
</script>

<style scoped>
.pwa-install-banner {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .pwa-install-banner {
    position: relative;
    margin: 0;
  }
}
</style> -->
