import Vue from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import i18n from './i18n'
import store from './store'
import axios from './axios'


import 'vuetify/dist/vuetify.min.css'
// import '@mdi/font/css/materialdesignicons.css'
import '@fortawesome/fontawesome-free/css/all.css'
import "sweetalert2/dist/sweetalert2.min.css"

// Vue plugins
import VueRouterUserRoles from "vue-router-user-roles"
import VueSweetalert2 from "vue-sweetalert2"
import VueNumber from 'vue-number-animation'
// import VueHtmlToPaper from 'vue-html-to-paper'
import VueCurrencyFilter from 'vue-currency-filter'

// Setup Vue plugins
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

// const paperOptions = {
//   name: '_blank',
//   specs: ['fullscreen=yes', 'titlebar=yes', 'scrollbars=yes'],
//   styles: [
//     'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
//     'http://smartclinic.tctate.com/css/style.css',
//     'https://unpkg.com/kidlat-css/css/kidlat.css',
//   ],
//   timeout: 1000,
//   autoClose: true,
//   windowTitle: window.document.title,
// }

// Vue.use(VueHtmlToPaper, paperOptions)

// Global mixin for common data https://apismartclinicv2.tctate.com
Vue.mixin({
  data() {
    return {
      Url: 'http://127.0.0.1:8000',
      http: 'http://'
    }
  }
})

// Silent mode for Vue
Vue.config.silent = true
Vue.config.productionTip = false

let authenticate = Promise.resolve({ role: store.state.role });

authenticate.then(user => {
  Vue.prototype.$user.set(user);

  new Vue({
    router,
    vuetify,
    i18n,
    axios,
    store,
    render: h => h(App),
    data: {
      isLoading: true, // Initially show the loading spinner
    },
    mounted() {
      // Simulating asynchronous data loading (adjust as needed)
      setTimeout(() => {
        this.isLoading = false; // Hide the loading spinner after data is loaded
      }, 2000);
    },
  }).$mount('#app')
})
