import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import VueAxios from "vue-axios";
import router from './router'
import createPersistedState from 'vuex-persistedstate'
// import { use } from 'vue/types/umd.js';
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
          photo:'',
          clinics_info:''

          
        }
      } ,

   
      UpdateUserInfo(state, userData) {
       
        ///state.AdminInfo.id = userData.id
        state.AdminInfo.name = userData.name
        //state.AdminInfo.authe=true
        state.AdminInfo.email = userData.email
        state.AdminInfo.photo = userData.photo
      //  state.AdminInfo.role = userData.role.name
        state.AdminInfo.img_name = userData.img_name
  
  
        state.AdminInfo.clinics_info =userData.clinic_info
  
       
      },


      

    authUser(state, userData) {
        state.AdminInfo.id = userData.id
        state.AdminInfo.name = userData.name
        state.AdminInfo.authe=true
        state.AdminInfo.email = userData.email
        state.AdminInfo.phone = userData.phone
        state.AdminInfo.photo = userData.photo
        state.AdminInfo.role = userData.role.name
        state.AdminInfo.img_name = userData.img_name
        state.AdminInfo.Permissions = userData.Permissions
        state.AdminInfo.dir = userData.dir

        state.AdminInfo.send_msg=userData.send_msg
        
        
       
         state.AdminInfo.tctate_token=localStorage.getItem('tctate_token'),

        state.AdminInfo.token =localStorage.getItem('tokinn')
      

        //state.AdminInfo.tctate_token="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvdGN0YXRlLmNvbVwvYXBpXC9hcGlcL2F1dGhcL3YyXC9sb2dpbk93bmVyU21hcnQiLCJpYXQiOjE2ODE0ODIxMzcsImV4cCI6MTY4MjE1MTczNywibmJmIjoxNjgxNDgyMTM3LCJqdGkiOiJtOVZMRDBlU0hHckw1b2xWIiwic3ViIjozODksInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.Sn-M-aYb5RhMYlMMy4qcOemeuITdUGEfT9mnTfUUCmU";
        state.AdminInfo.clinics_info =userData.clinic_info

       
      },


  
    
    
  },
  actions: {

    art_uploudess({
      commit
    }, arts) {

     console.log(arts);
      commit('art_uploudes',arts);
    
    
  
    },

    //updateInfo


    


    updateInfo({
      commit
    }, userData) {
      commit('UpdateUserInfo', {

        userId: userData.id,
        // tctate_token:userData.tctate_token,
        name: userData.name,
        policyNumber: userData.photp,
        userPhotoUrl: userData.email,
        phone: userData.phone,
        //role:1,
        clinic_info:userData.Clinics,
        img_name:userData.img_file,

send_msg:userData.send_msg
    
      });

     
    
      // let authenticate = Promise.resolve({ role:userData.role.name});
      // authenticate.then(user => { Vue.prototype.$user.set(user);})

      // commit('setRole',userData.role.name);
      // router.push("/");
      ///location.reload();
   

    },


    login({
        commit
      }, rt) {
  var userData=rt.result;
      
     var Permissions=Array();
      for(var i=0;i<userData.Permissions.length;i++){
        Permissions.push(userData.Permissions[i].name);
      }
    
      
      //alert(userData.img_name);
        commit('authUser', {
          token:localStorage.getItem('tokinn'),
          userId: userData.id,
          tctate_token:userData.tctate_token,
          name: userData.name,
          send_msg:userData.send_msg,
          policyNumber: userData.photp,
          userPhotoUrl: userData.email,
          role:userData.role.name,
          clinic_info:userData.clinic_info,
          img_name:userData.img_file,
          phone:userData.phone,
          Permissions:Permissions
      

    
        });
      
        let authenticate = Promise.resolve({ role:userData.role.name});
        authenticate.then(user => { Vue.prototype.$user.set(user);})

        commit('setRole',userData.role.name);
        if(Permissions.includes('show_accounts')==false){
          router.push("/patients");
        }

     
        else    if(rt.dir=='login'){
          router.push("/");
        }
        else{
          router.push("/patients");
          
        }
       
      //  location.reload();
     

      },

      logout({
        commit
      }) {
        commit('clearAuth')
        localStorage.clear();
        window.location.reload();
        
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
