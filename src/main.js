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

// Minimal plugin imports
import VueRouterUserRoles from "vue-router-user-roles"
import VueSweetalert2 from "vue-sweetalert2"
import VueNumber from 'vue-number-animation'
import VueCurrencyFilter from 'vue-currency-filter'

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

// Global config
Vue.mixin({
  data() {
    return {
      Url: 'https://apismartclinicv2.tctate.com',
      http: 'http://'
    }
  }
})

Vue.config.productionTip = false
Vue.config.silent = true

// Skip unnecessary auth if not needed, simplify user logic
new Vue({
  router,
  vuetify,
  i18n,
  axios,
  store,
  render: h => h(App),
  data: {
    isLoading: true,
  },
  mounted() {
    // Use requestIdleCallback for smoother mount
    window.requestIdleCallback(() => {
      this.isLoading = false
    })
  }
}).$mount('#app')
