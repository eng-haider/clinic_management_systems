import Vue from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import i18n from './i18n'
import store from './store'
import axios from './axios'

// Essential CSS only
import 'vuetify/dist/vuetify.min.css'
import '@fortawesome/fontawesome-free/css/all.css'
import "sweetalert2/dist/sweetalert2.min.css"
import '@/assets/performance.css'
import '@/assets/transitions.css'

// Minimal plugin imports
import VueRouterUserRoles from "vue-router-user-roles"
import VueSweetalert2 from "vue-sweetalert2"
import VueNumber from 'vue-number-animation'
import VueCurrencyFilter from 'vue-currency-filter'
import DropzonePlugin from './plugins/dropzone'

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

// Global config
Vue.mixin({
  data() {
    return {
      Url: 'https://apismartclinicv2.tctate.com',
      http: 'http://'
    }
  }
})

// Performance optimizations
Vue.config.productionTip = false
Vue.config.silent = true
Vue.config.devtools = process.env.NODE_ENV === 'development'

// Disable prefetch loading globally - we'll manually handle it in router
Vue.config.performance = process.env.NODE_ENV === 'development'

// Add progress bar for route changes
router.beforeEach((to, from, next) => {
  // Start loading indicator if you have one
  next()
})

router.afterEach(() => {
  // Stop loading indicator if you have one
  
  // Optimize for mobile devices
  if (window.innerWidth <= 768) {
    window.scrollTo(0, 0)
  }
})

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
    
    // Register service worker for PWA
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
          .then(registration => {
            console.log('SW registered: ', registration);
          })
          .catch(registrationError => {
            console.log('SW registration failed: ', registrationError);
          });
      });
    }
    
  } catch (error) {
    console.error('Failed to initialize app:', error)
  }
}

initializeApp()
