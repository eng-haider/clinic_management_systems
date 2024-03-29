import Vue from 'vue'

import App from './App.vue'
import router from './router'
import 'vuetify/dist/vuetify.min.css';
import vuetify from './plugins/vuetify'
import i18n from './i18n';
import '@mdi/font/css/materialdesignicons.css' 
import store from './store'
import axios from './axios'
import '@fortawesome/fontawesome-free/css/all.css'    
import '@fortawesome/fontawesome-free/js/all.js'  
import VueRouterUserRoles from "vue-router-user-roles";
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import VueNumber from 'vue-number-animation'
import './registerServiceWorker';
Vue.use(VueNumber)

import VueHtmlToPaper from 'vue-html-to-paper';

//VueCurrencyFilter
import VueCurrencyFilter from 'vue-currency-filter'

Vue.use(VueCurrencyFilter,
  {
    symbol : '',
    thousandsSeparator: '.',
    fractionCount: 0,
    fractionSeparator: ',',
    symbolPosition: 'front',
    symbolSpacing: true
  })


const options = {
  name: '_blank',
  specs: [
    'fullscreen=yes',
    'titlebar=yes',
    'scrollbars=yes'
  ],
  styles: [
    'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
    'http://smartclinic.tctate.com/css/style.css',
    'https://unpkg.com/kidlat-css/css/kidlat.css',
 
  ],
  timeout: 1000, // default timeout before the print window appears
  autoClose: true, // if false, the window will not close after printing
  windowTitle: window.document.title, // override the window title
}

Vue.use(VueHtmlToPaper, options);

// or, using the defaults with no stylesheet
Vue.use(VueHtmlToPaper);




Vue.config.productionTip = false




Vue.mixin({
  data: function () {
    return {
      Url: 'http://apismartclinic.tctate.com',
      http: 'http://'
    }
  }
})


Vue.use(VueRouterUserRoles, {router});
Vue.use(VueSweetalert2);
Vue.config.silent = true
Vue.config.productionTip = false
let authenticate = Promise.resolve({ role:store.state.role });

authenticate.then(user => {
  Vue.prototype.$user.set(user);


  new Vue({
    router,
    render: h => h(App),
    vuetify,
    i18n,
    axios,
    store,
    data: {
      isLoading: true, // Initially show the loading spinner
    },
    mounted() {
      // Simulate some asynchronous data loading (e.g., fetching from an API)
      // Replace this with your actual data loading process
      setTimeout(() => {
        // Once the data is loaded and the Vue.js application is ready
        this.isLoading = false; // Hide the loading spinner and show the Vue.js content
      }, 2000); // Simulating a 2-second loading time, adjust as needed
    },
  }).$mount('#app')
  


})


;
