<template>
  <v-dialog v-model="dialog" max-width="500">
    <template v-slot:activator="{ on, attrs }">
      <v-btn
        color="primary"
        v-bind="attrs"
        v-on="on"
        outlined
        small
      >
        <v-icon left>mdi-help-circle</v-icon>
        {{ $t('how_to_install') || 'How to Install' }}
      </v-btn>
    </template>
    
    <v-card>
      <v-card-title class="headline">
        <v-icon left>mdi-download</v-icon>
        {{ $t('install_guide') || 'Installation Guide' }}
      </v-card-title>
      
      <v-card-text>
        <div v-if="deviceInfo.isIOS">
          <v-icon color="blue" size="48">mdi-apple</v-icon>
          <h3 class="mt-2 mb-3">iPhone/iPad</h3>
          <v-stepper v-model="step" vertical>
            <v-stepper-step :complete="step > 1" step="1">
              {{ $t('ios_step_1') || 'Tap the Share button' }}
              <small>{{ $t('ios_step_1_desc') || 'Look for the share icon in Safari' }}</small>
            </v-stepper-step>
            <v-stepper-content step="1">
              <v-icon>mdi-share</v-icon>
              <v-btn color="primary" @click="step = 2" text>Continue</v-btn>
            </v-stepper-content>

            <v-stepper-step :complete="step > 2" step="2">
              {{ $t('ios_step_2') || 'Find "Add to Home Screen"' }}
              <small>{{ $t('ios_step_2_desc') || 'Scroll down in the share menu' }}</small>
            </v-stepper-step>
            <v-stepper-content step="2">
              <v-icon>mdi-home-plus</v-icon>
              <v-btn color="primary" @click="step = 3" text>Continue</v-btn>
            </v-stepper-content>

            <v-stepper-step :complete="step > 3" step="3">
              {{ $t('ios_step_3') || 'Tap "Add"' }}
              <small>{{ $t('ios_step_3_desc') || 'Confirm installation' }}</small>
            </v-stepper-step>
            <v-stepper-content step="3">
              <v-icon>mdi-check</v-icon>
              <v-btn color="success" @click="step = 1" text>Done</v-btn>
            </v-stepper-content>
          </v-stepper>
        </div>
        
        <div v-else-if="deviceInfo.isAndroid">
          <v-icon color="green" size="48">mdi-android</v-icon>
          <h3 class="mt-2 mb-3">Android</h3>
          <v-stepper v-model="step" vertical>
            <v-stepper-step :complete="step > 1" step="1">
              {{ $t('android_step_1') || 'Tap the menu button' }}
              <small>{{ $t('android_step_1_desc') || 'Three dots in the top right' }}</small>
            </v-stepper-step>
            <v-stepper-content step="1">
              <v-icon>mdi-dots-vertical</v-icon>
              <v-btn color="primary" @click="step = 2" text>Continue</v-btn>
            </v-stepper-content>

            <v-stepper-step :complete="step > 2" step="2">
              {{ $t('android_step_2') || 'Tap "Add to Home screen"' }}
              <small>{{ $t('android_step_2_desc') || 'Look for this option in the menu' }}</small>
            </v-stepper-step>
            <v-stepper-content step="2">
              <v-icon>mdi-home-plus</v-icon>
              <v-btn color="primary" @click="step = 3" text>Continue</v-btn>
            </v-stepper-content>

            <v-stepper-step :complete="step > 3" step="3">
              {{ $t('android_step_3') || 'Tap "Add"' }}
              <small>{{ $t('android_step_3_desc') || 'Confirm installation' }}</small>
            </v-stepper-step>
            <v-stepper-content step="3">
              <v-icon>mdi-check</v-icon>
              <v-btn color="success" @click="step = 1" text>Done</v-btn>
            </v-stepper-content>
          </v-stepper>
        </div>
        
        <div v-else>
          <v-icon color="blue" size="48">mdi-desktop-tower</v-icon>
          <h3 class="mt-2 mb-3">Desktop</h3>
          <v-alert type="info" outlined>
            {{ $t('desktop_install_info') || 'Look for the install icon in your browser\'s address bar or use the Install button above.' }}
          </v-alert>
          
          <div class="mt-4">
            <h4>Chrome/Edge:</h4>
            <p>{{ $t('chrome_install_desc') || 'Look for the install icon in the address bar' }}</p>
            
            <h4 class="mt-3">Firefox:</h4>
            <p>{{ $t('firefox_install_desc') || 'Use the Install button when available' }}</p>
          </div>
        </div>
      </v-card-text>
      
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" text @click="dialog = false">
          {{ $t('close') || 'Close' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import pwaManager from '@/utils/pwa'

export default {
  name: 'InstallGuide',
  data() {
    return {
      dialog: false,
      step: 1,
      deviceInfo: {}
    }
  },
  
  mounted() {
    this.deviceInfo = pwaManager.getDeviceInfo()
  }
}
</script>

<style scoped>
h3 {
  color: #1976d2;
}

h4 {
  color: #424242;
  margin-top: 16px;
}

.v-stepper {
  box-shadow: none;
}
</style>
