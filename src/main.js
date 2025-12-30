import Vue from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import i18n from './i18n'
import store from './store'

// Fix for chunk loading errors - override webpack's public path at runtime
/* eslint-disable */
if (typeof __webpack_public_path__ !== 'undefined') {
  // Check if running in Electron
  const isElectron = typeof window !== 'undefined' && window.process && window.process.type;
  
  if (isElectron) {
    // In Electron, use relative path
    __webpack_public_path__ = './';
    console.log('🖥️ Running in Electron - using relative paths');
  } else {
    // In browser, use origin
    __webpack_public_path__ = window.location.origin + '/';
    console.log('🌐 Running in browser - using origin:', window.location.origin);
  }
}

// Also handle dynamic imports that might fail
const originalImport = window.__webpack_require__ && window.__webpack_require__.e;
if (originalImport) {
  window.__webpack_require__.e = function(chunkId) {
    return originalImport.call(this, chunkId).catch(error => {
      // If chunk loading fails, try loading from current origin
      console.warn('Chunk loading failed, retrying:', error);
      
      const isElectron = typeof window !== 'undefined' && window.process && window.process.type;
      __webpack_public_path__ = isElectron ? './' : window.location.origin + '/';
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
      Url: 'https://mina-api.tctate.com',  // Updated to v4 API
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
  console.log('🚀 Starting app initialization...')
  try {
    console.log('📦 Initializing store...')
    await initializeStore()
    console.log('✅ Store initialized')
    
    // Set initial language and direction
    const currentLang = localStorage.getItem("lang") || 'ar';
    console.log('🌐 Setting language:', currentLang)
    document.documentElement.dir = currentLang === "ar" ? "rtl" : "ltr";
    document.documentElement.lang = currentLang;
    document.body.classList.toggle('rtl', currentLang === "ar");
    document.body.classList.toggle('ltr', currentLang === "en");
    
    console.log('🎨 Creating Vue instance...')
    const app = new Vue({
      router,
      store,
      i18n,
      vuetify,
      render: h => h(App)
    })
    
    console.log('🔌 Mounting app to #app...')
    app.$mount('#app')
    console.log('✅ App mounted successfully!')
    
  } catch (error) {
    console.error('❌ Failed to initialize app:', error)
    console.error('Error stack:', error.stack)
  }
}

console.log('📄 main.js loaded, calling initializeApp()')
initializeApp()
