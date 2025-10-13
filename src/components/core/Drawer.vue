<template>
  <div>
    <v-navigation-drawer id="core-navigation-drawer" v-model="drawer"
      :dark="barColor !== 'rgba(255, 255, 255, 1), rgba(255, 255, 255, 0.7)'" :expand-on-hover="expandOnHover"
      :right="$vuetify.rtl" mobile-break-point="960" app width="260" mini-variant-width="85" v-bind="$attrs"
      :color="barColor" :class="barColor" >
      <!-- user info ui -->
      <v-list style="margin-top:27px" nav class="py-0">

        <div class="app-sidebar__user active is-expanded">
          <div class="dropdown user-pro-body text-center">
            <div class="user-pic" style="position: relative; display: inline-block;">
              <img 
                v-if="$store.state.AdminInfo.img_name == null"
                src="profile.png"
                class="avatar-xxl rounded-circle mb-1"
                style="width:100px;height:100px"
                :alt="$t('Drawer.profile_picture')"
              />
              <img 
                v-else 
                :src="Url + '/users/' + $store.state.AdminInfo.img_name"
                class="avatar-xxl rounded-circle mb-1"
                style="width:100px;height:100px"
                :alt="$t('Drawer.profile_picture')"
              />
              <!-- Green point for online status -->
              <div
                style="
                  position: absolute;
                  bottom:13px;
                  right: 5px;
                  width: 15px;
                  height: 15px;
                  background-color: green;
                  border-radius: 50%;
                  border: 2px solid white;
                "
                :title="$t('Drawer.online_status')"
              ></div>
            </div>
            <div class="user-info" style="text-align: center;">
              <h5 class="mb-2">{{ $store.state.AdminInfo.clinics_info.name }}</h5>
              <h5 class="mb-2 app-sidebar__user-name">{{ $store.state.AdminInfo.name }}</h5>
            </div>
          </div>
        </div>
        <hr>
      </v-list>
      
      <!--Star Owner Sidbar-->
      <div>
        <v-list dense nav style=" font-family: 'cairo' " v-for="(item, i) in items" :key="i" class="my-0 py-0"
          :class="$i18n.locale == 'ar' ? 'pr-0' : 'pl-0'">
          <v-list-item 
            style="color:#fff !important" 
            link 
            :to="item.to"
            v-if="shouldShowMenuItem(item)"
          >
       
            
            <v-list-item-icon>
              <v-icon style="color:#fff !important">{{ item.icon }}</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title style="font-size:14px">{{ item.title }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </div>
      <!--End Owner Sidbar-->
    </v-navigation-drawer>

    <!-- Mobile Bottom Navigation -->
    <MobileBottomNav />
  </div>
</template>

<script>
  import {
    mapState,
    mapGetters
  } from "vuex";
  import MobileBottomNav from './MobileBottomNav.vue';

  export default {
    components: {
      MobileBottomNav
    },

    data() {
      return {
        items: [
          {
            title: this.$t("header.dashbourd"),
            icon: 'fa-solid fa-chart-line',
            auth: true,
            to: '/',
            name: 'show_dashbourd'
          },
          {
            title: this.$t("header.casesheet"),
            icon: 'fas fa-clipboard',
            to: '/patients',
            auth: false,
            name: 'show_patients'
          },
          {
            title: this.$t("header.showCases"),
            icon: 'fa-light fa-tooth',
            auth: true,
            to: '/cases',
            name: 'show_cases'
          },
          {
            title: this.$t("header.store"),
            icon: 'fa-light fa-store',
            auth: true,
            to: '/store',
            name: 'show_cases'
          },
          {
            title: this.$t("header.Conjugations"),
            icon: 'fa-sharp fa-solid fa-layer-group',
            auth: true,
            to: '/ConjugationsCategories',
            name: 'show_conjugations_categories'
          },
          {
            title: this.$t("header.CaseCategories"),
            icon: 'fa-solid fa-list-ul',
            auth: true,
            to: '/case-categories',
            name: 'show_CaseCategories'
          },
          {
            title: this.$t("header.accounts"),
            icon: 'fa-solid fa-money-bill',
            auth: true,
            to: '/accounts',
            name: 'show_accounts'
          },
          {
            title: "header.notifications",
            icon: 'fa-solid fa-bell',
            auth: true,
            to: '/notifications',
            name: 'show_notifications'
          },

          {
            title: this.$t("header.caseLap") || "حالات المختبر",
            icon: 'fa-solid fa-flask',
            auth: true,
            to: '/case-lap',
            name: 'show_lap_cases'
          },

          {
            title: this.$t("header.reports"),
            icon: 'fa-solid fa-file-export',
            auth: true,
            to: '/reports',
            name: 'export_reports'
          },
          {
            title: this.$t("header.requestBooking"),
            icon: 'fa-solid fa-calendar',
            auth: true,
            to: "/requestBooking_test",
            name: 'show_appointment'
          },
          {
            title: this.$t("header.recipes"),
            icon: 'fa-solid fa-prescription',
            auth: true,
            to: "/recipes",
            name: 'show_recipes'
          },
          {
            title: this.$t("header.doctors"),
            icon: 'fa-solid fa-user',
            auth: true,
            to: '/doctors',
            name: 'show_doctors'
          },
          {
            title: this.$t("header.birthDay"),
            icon: 'fa-sharp fa-solid fa-user',
            auth: true,
            to: '/BirthDay',
            name: 'show_doctors'
          },
          {
            title: this.$t("header.waitinglist"),
            icon: 'far fa-clock',
            auth: true,
            to: '/waitinglist',
            name: 'show_waitinglist'
          }
        ],
        right: null,
      }
    },

    computed: {
      ...mapState(["pathUrl"]),
      ...mapState(["setLogo"]),
      ...mapState(["barColor"]),
      ...mapState(["tile"]),
      ...mapGetters(['canShowLapCases']),

      drawer: {
        get() {
          return this.$store.state.drawer;
        },
        set(val) {
          this.$store.commit("SET_DRAWER", val);
        }
      },
      computedItems() {
        return this.items.map(this.mapItem);
      }
    },
    watch: {
      "$vuetify.breakpoint.smAndDown"(val) {
        this.$emit("update:expandOnHover", !val);
      }
    },
    created() {

      if (this.$store.state.role == 'secretary') {

        this.items[1].auth = false;
        // this.items[2].auth = false;
      } else {
        this.items[1].auth = true;
        this.items[2].auth = true;
      }

    },
    methods: {
      hasItemPermission(item) {
        // Special case for show_lap_cases - check both permission and doctor show_lap field
        if (item.name === 'show_lap_cases') {
          return this.canShowLapCases;
        }
        // For all other permissions, check the regular way
        return this.$store.state.AdminInfo.Permissions.includes(item.name);
      },
      shouldShowMenuItem(item) {
        // Special case for notifications - only show to accountants
        if (item.name === 'show_notifications') {
          return this.$store.state.AdminInfo?.role === 'accounter';
        }
        
        // Use existing permission check logic
        return this.hasItemPermission(item);
      },
      mapItem(item) {
        return {
          ...item,
          children: item.children ? item.children.map(this.mapItem) : undefined,
          title: this.$t(item.title)
        };
      },
      changeLang() {
        // The navigation titles will automatically update due to reactivity
        // when the language changes, as they use this.$t()
        this.$forceUpdate();
      },
    },
  }
</script>

<style>
  .profile-cover__action .user-pic {
    left: 0;
    right: 0;
    margin: 0 auto;
    text-align: center;
  }

  .profile-cover__action .user-pic-right {
    right: 0;
    left: 0;
    margin: 0 auto;
    text-align: center;
    bottom: -38%;
  }

  .user-pro-list2 .user-pic {
    position: absolute;
    bottom: -20px;
    left: 20px;
  }

  .user-pro-list2 .user-pic .avatar {
    border: 3px solid #fff;
    width: 6rem;
    height: 6rem;
  }

  .user-pro-list2 .user-pic .avatar .avatar-status {
    right: 15px;
  }

  .user-pro-list2 .user-pic-right {
    position: absolute;
    bottom: 20px;
    right: 20px;
  }




  .v-pagination>li {
    align-items: center;
    display: contents !important;
  }

  .user-pic {
    position: relative;
  }
</style>