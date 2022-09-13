import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import VueAxios from "vue-axios";
import router from './router'
import createPersistedState from 'vuex-persistedstate'
Vue.use(Vuex)
Vue.use(VueAxios, axios);



export default new Vuex.Store({
  state: {
    
    barColor: '#272727',
    lang:'ar',
    art_uploude:{},
    drawer: null,

    idToken:localStorage.getItem('tokinn'),
    tctate_token:localStorage.getItem('tctate_token'),
    AdminInfo:{
        id:'',
        token:'',
        authe:false,
        role:'',
        name:'',
        email:'',
        tctate_token:'',
        photo:''

    }
 
  },
  plugins: [
    createPersistedState({     
    })
  ],
  mutations: {
    SET_DRAWER(state, payload) {
      state.drawer = payload;
    },
    UpdateLang(state, UpdateLang) {
      state.lang= UpdateLang.lang
    },


    art_uploudes(state,arts)
    {
      console.log(arts);
      state.art_uploude=arts
    },

    setRole(state,role) {
      state.role = role
     
    },

    clearAuth(state) {
        state.AdminInfo={
          id:'',
          token:'',
          authe:false,
          name:'',
          email:'',
          role:'',
          photo:''
        }
      } ,

    authUser(state, userData) {
        state.AdminInfo.id = userData.id
        state.AdminInfo.name = userData.name
        state.AdminInfo.authe=true
        state.AdminInfo.email = userData.email
        state.AdminInfo.photo = userData.photo
        state.AdminInfo.role = userData.role.name
        state.AdminInfo.tctate_token=localStorage.getItem('tctate_token'),
        state.AdminInfo.token =localStorage.getItem('tokinn')
  

        
      },


  
    
    
  },
  actions: {

    art_uploudess({
      commit
    }, arts) {

     console.log(arts);
      commit('art_uploudes',arts);
    
    
  
    },
    login({
        commit
      }, userData) {
  
     
      
        commit('authUser', {
          token:localStorage.getItem('tokinn'),
          userId: userData.id,
          tctate_token:userData.tctate_token,
          name: userData.name,
          policyNumber: userData.photp,
          userPhotoUrl: userData.email,
          role:userData.role.name,

          
        });
      
        let authenticate = Promise.resolve({ role:userData.role.name});
        authenticate.then(user => { Vue.prototype.$user.set(user);})

        commit('setRole',userData.role.name);
        router.push("/admin/casesheet")
    
      },

      logout({
        commit
      }) {
        commit('clearAuth')
        localStorage.clear();
        localStorage.setItem("darkMode", this.$vuetify.theme.dark);
        this.$i18n.locale = this.$i18n.locale == "ar" ? "ar" : "en";
        localStorage.setItem("lang", this.$i18n.locale);
        this.$vuetify.rtl = localStorage.getItem("lang") == "ar" ? true : false;
        axios.defaults.headers.get["Authorization"] = null;
  
      },


UpdateLang({
  commit
}, lang) {
  commit('UpdateLang', {
    lang:lang.lang,

  });

},
   

   
  }
})
