import Vue from 'vue'
import axios from 'axios'
import VueAxios from "vue-axios";
import router from './router'
import store from './store'
//Axios
Vue.use(VueAxios, axios);
Vue.config.silent = true
Vue.config.productionTip = false
axios.defaults.baseURL = process.env.VUE_APP_URL
axios.defaults.headers.get['Accepts'] = 'application/json'
// axios.defaults.headers.get['lang'] = this.$i18n.locale
axios.interceptors.request.use(
  config => {
    const token = localStorage.getItem("tokinn");
    const auth = token ? `Bearer ${token}` : "";
    config.headers.common.Authorization = auth;
    return config;
  },
  error => Promise.reject(error)
);
// var xx=this;

axios.interceptors.response.use((response) => {
  return response;
  
}, function (error) {
  router
  // Do something with response error
  // router.push("/login")
  if (error.response.status === 401 || error.response.status === 403 || error.response.status === 500) {
    store
    //  store.dispatch("logout");
 
  }
  return Promise.reject(error.response);
});





//Vue.prototype.$http = axios;
axios.defaults.baseURL =  'https://apismartclinicv4.tctate.com/api/';
 //axios.defaults.baseURL =  'http://127.0.0.1:8003/api/';

//https://apismartclinicv4.tctate.com
axios.interceptors.response.use(
  function(response) {
    return response;
  },
  function(error) {
    if (error.response.status === 401 || error.response.status === 403) {
      // router.push({ name: "Login" });
    }
    // return Promise.reject(error);
  }
);