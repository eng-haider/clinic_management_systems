import Vue from 'vue'
import axios from 'axios'
import VueAxios from "vue-axios";
import router from './router'

//Axios
Vue.use(VueAxios, axios);
Vue.config.silent = true
Vue.config.productionTip = false
axios.defaults.baseURL = process.env.VUE_APP_URL
axios.defaults.headers.get['Accepts'] = 'application/json'
// axios.defaults.headers.get['lang'] = this.$i18n.locale
axios.interceptors.request.use(
  config => {
    const token = localStorage.getItem("tokin");
    const auth = token ? `Bearer ${token}` : "";
    config.headers.common.Authorization = auth;
    return config;
  },
  error => Promise.reject(error)
);

//Vue.prototype.$http = axios;
axios.defaults.baseURL =  'http://apismartclinic.tctate.com/api/';


axios.interceptors.response.use(
  function(response) {
    return response;
  },
  function(error) {
    if (error.response.status === 401 || error.response.status === 403) {
      router.push({ name: "Login" });
    }
    return Promise.reject(error);
  }
);