import Vue from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import i18n from './i18n'
import store from './store'
import axios from './axios'

// Fix for chunk loading errors - override webpack's public path at runtime
/* eslint-disable */
if (typeof __webpack_public_path__ !== 'undefined') {
  // Set public path to current origin to fix chunk loading from wrong domain
  __webpack_public_path__ = window.location.origin + '/';
}

// Also handle dynamic imports that might fail
const originalImport = window.__webpack_require__ && window.__webpack_require__.e;
if (originalImport) {
  window.__webpack_require__.e = function(chunkId) {
    return originalImport.call(this, chunkId).catch(error => {
      // If chunk loading fails, try loading from current origin
      console.warn('Chunk loading failed, retrying from current origin:', error);
      
      // Update public path and retry
      __webpack_public_path__ = window.location.origin + '/';
      return originalImport.call(this, chunkId);
    });
  };
}
/* eslint-enable */

// Essential CSS only
import 'vuetify/dist/vuetify.min.css'
import '@fortawesome/fontawesome-free/css/all.css'
import "sweetalert2/dist/sweetalert2.min.css"
import '@/assets/performance.css'
import '@/assets/transitions.css'
import './assets/date-picker-styles.css'  // Import global styles for date picker

// Minimal plugin imports
import VueRouterUserRoles from "vue-router-user-roles"
import VueSweetalert2 from "vue-sweetalert2"
import VueNumber from 'vue-number-animation'
import VueCurrencyFilter from 'vue-currency-filter'
import DropzonePlugin from './plugins/dropzone'
import PrintPlugin from './utils/print'

// No loading or performance tracking

// Only use what’s needed
Vue.use(VueRouterUserRoles, { router })
Vue.use(VueSweetalert2)
Vue.use(VueNumber)
Vue.use(VueCurrencyFilter, {
  symbol: '',
  thousandsSeparator: '.',
  fractionCount: 0,
  fractionSeparator: ',',
  symbolPosition: 'front',
  symbolSpacing: true
})

// Configure Dropzone before any components are loaded
Vue.use(DropzonePlugin)
Vue.use(PrintPlugin)

// Global config
Vue.mixin({
  data() {
    return {
      Url: 'https://apismartclinicv3.tctate.com',  // Updated to v4 API
      http: 'https://'  // Changed to https for security
    }
  }
})

// Performance optimizations
Vue.config.productionTip = false
Vue.config.silent = true
Vue.config.devtools = process.env.NODE_ENV === 'development'

// Disable prefetch loading globally - we'll manually handle it in router
Vue.config.performance = process.env.NODE_ENV === 'development'

// Note: Router guards are defined in router.js to avoid conflicts

// Initialize store with data from localStorage if available
const initializeStore = () => {
  try {
    const adminInfo = localStorage.getItem('vuex');
    if (adminInfo) {
      const parsedData = JSON.parse(adminInfo);
      if (parsedData.AdminInfo && parsedData.AdminInfo.role) {
        // Ensure both role fields are set consistently
        store.commit('setRole', parsedData.AdminInfo.role);
        console.log('Initialized role from localStorage:', parsedData.AdminInfo.role);
      }
    }
  } catch (error) {
    console.warn('Error initializing store from localStorage:', error);
  }
};

// Initialize app with async store
async function initializeApp() {
  try {
    await initializeStore()
    
    new Vue({
      router,
      store,
      i18n,
      vuetify,
      render: h => h(App)
    }).$mount('#app')
    
    // Service worker registration removed
    
  } catch (error) {
    console.error('Failed to initialize app:', error)
  }
}

// PWA update detection
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => {
        console.log('SW registered: ', registration)
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New content available, prompt user to refresh
              if (confirm('New version available! Refresh to update?')) {
                newWorker.postMessage({ type: 'SKIP_WAITING' })
                window.location.reload()
              }
            }
          })
        })
      })
      .catch(error => {
        console.log('SW registration failed: ', error)
      })
  })
}

initializeApp()
