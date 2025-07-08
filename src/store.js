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
    role: '', // Keep for backward compatibility
    AdminInfo: {
        id: '',
        user_id: '',
        doctor_id: null,
        token: '',
        authe: false,
        role: '',
        name: '',
        email: '',
        tctate_token: '',
        photo: '',
        phone: '',
        img_name: null,
        Permissions: [],
        dir: '',
        send_msg: 1,
        clinics_info: ''
    }
 
  },
  plugins: [
    createPersistedState({     
    })
  ],
  getters: {
    userRole: state => {
      const role = state.AdminInfo.role || state.role;
      console.log('Getting userRole:', { adminInfoRole: state.AdminInfo.role, stateRole: state.role, result: role });
      return role;
    },
    isSecretary: state => {
      const role = state.AdminInfo.role || state.role;
      const isSecretary = role === 'secretary';
      console.log('Getting isSecretary:', { adminInfoRole: state.AdminInfo.role, stateRole: state.role, role, isSecretary });
      return isSecretary;
    },
    isAuthenticated: state => state.AdminInfo.authe,
    userPermissions: state => state.AdminInfo.Permissions || [],
    hasPermission: (state) => (permission) => {
      return state.AdminInfo.Permissions.includes(permission);
    }
  },
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
      console.log('Setting role:', role);
      state.role = role;
      state.AdminInfo.role = role;
      console.log('Role set successfully. State:', { role: state.role, adminInfoRole: state.AdminInfo.role });
    },

    clearAuth(state) {
        state.AdminInfo = {
          id: '',
          user_id: '',
          doctor_id: null,
          token: '',
          authe: false,
          role: '',
          name: '',
          email: '',
          tctate_token: '',
          photo: '',
          phone: '',
          img_name: null,
          Permissions: [],
          dir: '',
          send_msg: 1,
          clinics_info: ''
        }
      },

   
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
        state.AdminInfo.user_id = userData.user_id
        state.AdminInfo.doctor_id = userData.doctor_id
        state.AdminInfo.name = userData.name
        state.AdminInfo.authe = true
        state.AdminInfo.email = userData.email
        state.AdminInfo.phone = userData.phone
        state.AdminInfo.photo = userData.photo
        state.AdminInfo.role = userData.role
        state.AdminInfo.img_name = userData.img_name
        state.AdminInfo.Permissions = userData.Permissions
        state.AdminInfo.dir = userData.dir
        state.AdminInfo.send_msg = userData.send_msg
        state.AdminInfo.tctate_token = userData.tctate_token
        state.AdminInfo.token = userData.token
        state.AdminInfo.clinics_info = userData.clinics_info
      },

      updatePermissions(state, permissions) {
        var Permissions=Array();
        for(var i=0;i<permissions.data.length;i++){
          Permissions.push(permissions.data[i].name);
        }
      

        state.AdminInfo.Permissions = Permissions;
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
        name: userData.name,
        policyNumber: userData.photp,
        userPhotoUrl: userData.email,
        phone: userData.phone,
        clinic_info:userData.Clinics,
        img_name:userData.img_file,
        send_msg:userData.send_msg
    
      });

     
    
   

    },


    login({
        commit
      }, loginResponse) {
      
      // Extract data from the new login response structure
      const userData = loginResponse.result;
      const token = loginResponse.token;
      const tctateToken = loginResponse.tctate_token;
      const dir = loginResponse.dir;
      
      // Store tokens in localStorage
      localStorage.setItem('tokinn', token);
      localStorage.setItem('tctate_token', tctateToken);
      
      // Process permissions array
      const Permissions = userData.Permissions.map(permission => permission.name);
      
      // Prepare user data for commit
      const processedUserData = {
        id: userData.id,
        user_id: userData.id,
        doctor_id: userData.doctor ? userData.doctor.id : null,
        token: token,
        tctate_token: tctateToken,
        name: userData.name,
        email: userData.email,
        phone: userData.phone,
        role: userData.role.name,
        img_name: userData.img_file,
        photo: userData.img_file,
        Permissions: Permissions,
        dir: dir,
        send_msg: userData.send_msg,
        clinics_info: loginResponse.clinic_info || userData.Clinics
      };
      
      // Commit to store
      commit('authUser', processedUserData);
      
      // Set up user authentication
      let authenticate = Promise.resolve({ role: userData.role.name });
      authenticate.then(user => { 
        if (Vue.prototype.$user) {
          Vue.prototype.$user.set(user);
        }
      });

      commit('setRole', userData.role.name);
      
      // Navigate based on permissions
      if (!Permissions.includes('show_accounts')) {
        router.push("/patients");
      } else if (dir === 'login') {
        router.push("/");
      } else {
        router.push("/patients");
      }
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
