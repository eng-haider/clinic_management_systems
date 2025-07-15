<template>
  <div>

    
    <v-app-bar app color="#0a304e" dark absolute height="50" style="width: auto" elevation="1">
      <v-btn fab small text @click="
        $vuetify.breakpoint.mdAndDown
          ? setDrawer(!drawer)
          : $emit('input', !value)
      ">
    <v-icon >   mdi mdi-menu</v-icon>
        <!-- <v-icon v-if="value">mdi-view-quilt</v-icon>
        <v-icon v-else>mdi-dots-vertical</v-icon> -->
      </v-btn>
      <!-- <v-app-bar-nav-icon></v-app-bar-nav-icon> -->

      <v-toolbar-title style="color:#fff" v-text="$t('header.'+$route.name)"></v-toolbar-title>

      <v-spacer></v-spacer>

      <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-btn fab icon v-bind="attrs" v-on="on" @click="reColor()">
            <v-icon large color="amber">mdi-theme-light-dark</v-icon>
          </v-btn>
        </template>
        <span>{{ $t("setting.darkMode") }}</span>
      </v-tooltip>


      <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-btn fab icon @click="refreshPageData()" v-bind="attrs" v-on="on">
            <v-icon large color="light-blue darken-1">mdi-refresh</v-icon>
          </v-btn>
        </template>
        <span>{{ $t("setting.refresh") }}</span>
      </v-tooltip>
      
      <!-- PWA Install Button -->
      <!-- <InstallButton /> -->
      
      <v-menu bottom left min-width="200" offset-y origin="top right" transition="scale-transition">
        <template v-slot:activator="{ attrs, on }">
          <v-btn min-width="0" icon v-bind="attrs" v-on="on">
            <v-icon large>mdi-account-circle</v-icon>
          </v-btn>
        </template>

        <v-list dense>
          <v-list-item v-for="(item, i) in profile" :key="i" :to="item.to">
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title v-text="item.text"></v-list-item-title>
            </v-list-item-content>
          </v-list-item>

          <v-list-item v-if="linkedAccounts.length > 0" @click="switchAccount()">
            <v-list-item-icon>
              <v-icon>mdi-account-switch</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>{{ $t('AppBar.switch_account') }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>

          <v-list-item @click="logout()">
            <v-list-item-icon>
              <v-icon v-text="'mdi-logout'"></v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title v-text="$t('AppBar.sign_out')"></v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>

      </v-menu>


    </v-app-bar>





    
  </div>
</template>


<style>
  .navbar-brand img {
    max-height: 74px !important;
  }
</style>
<script>
  import Vue from 'vue'
  import {
    mapState,
    mapMutations
  } from "vuex";
  import Axios from "axios";
  // import InstallButton from '@/components/InstallButton.vue'

  export default {
    components: {
      // InstallButton
    },
    
    props: {
      value: {
        type: Boolean,
        default: false,
      },
    },
  
    data() {
      return {

        selectedItem: 1,
        

        profile: [
          // {
          //   text: this.$t('AppBar.profile'),
          //   icon: 'mdi-account',
          //   to: {
          //     path: "/profile",
          //   },
          // },
          {
            text: this.$t('AppBar.Settings'),
            icon: 'mdi-cog-outline',
            to: {
                 path: "/profile",
            },
          },


          // {
          //   text: this.$t('AppBar.sign_out'),
          //   icon: 'mdi-logout',
          //   to: { 1674864000

          //     name: "Login",
          //   },
          // },
        ],
        linkedAccounts: [],
      }


    },

    computed: {
      ...mapState(["drawer"]),
    },

    methods: {
      refreshPageData() {
        // Emit event to refresh page data
        this.$emit('refresh-data');
        
        // Clear localStorage cache
        Object.keys(localStorage).forEach(key => {
          if (key.includes('_cache') || key.includes('cache_')) {
            localStorage.removeItem(key);
          }
        });
        
        // Reload current route to refresh data
        this.$router.go(0);
      },
      async fetchLinkedAccounts() {
        try {
          const response = await Axios.get("/users/getLinkedAccounts", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: `Bearer ${this.$store.state.AdminInfo.token}`,
            },
          });
          this.linkedAccounts = response.data.accounts;
        } catch (error) {
          console.error('Error fetching linked accounts:', error);
        }
      },
      async switchAccount() {
        try {
          const response = await Axios.post("/users/switchAccount", {}, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: `Bearer ${this.$store.state.AdminInfo.token}`,
            },
          });
          const { token, tctate_token, result } = response.data;
          result.clinic_info = response.data.clinic_info;
          localStorage.setItem('tokinn', token);
          localStorage.setItem('tctate_token', tctate_token);
          this.$store.dispatch("login", response.data);
          window.location.reload();
        } catch (error) {
          console.error('Error switching account:', error);
        }
      },
      logout(){
        this.$store.dispatch("logout");

      },
        mapItem(item) {
      return {
        ...item,
        children: item.children ? item.children.map(this.mapItem) : undefined,
        title: this.$t(item.title)
      };
    },
      reColor() {
        localStorage.setItem("darkMode", !this.$vuetify.theme.dark);
        this.$vuetify.theme.dark = !this.$vuetify.theme.dark
      },

      langChang() {

        let authenticate = Promise.resolve({
          role: "admin"
        });
        authenticate.then(user => {
          Vue.prototype.$user.set(user);
        })
        this.$i18n.locale = this.$i18n.locale == "ar" ? "en" : "ar";
        localStorage.setItem("lang", this.$i18n.locale);
        this.$store.dispatch("UpdateLang", this.$i18n.locale);
        this.$vuetify.rtl = localStorage.getItem("lang") == "ar" ? true : false;
        window.location.reload();
        //    moment.locale(this.$i18n.locale == "en" ? "en" : "ar-kw");
      },
      ...mapMutations({
        setDrawer: "SET_DRAWER",
      }),


    },
    created() {
      this.fetchLinkedAccounts();
    },
  }
</script>

<style>
@media only screen and (max-width: 600px) {
    .v-btn.reg_owner_btn, .v-btn[type=submit], .v-card-actions .v-btn, form .v-btn:last-of-type {
        position: relative;
        z-index: 1001 !important;
    }
}

@media only screen and (max-width: 600px) {
    .reg_owner_btn, .v-btn:last-child, .v-btn:last-of-type, .v-card {
        margin-bottom: 20px !important;
    }
}

</style>