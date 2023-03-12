<template>


  <v-navigation-drawer id="core-navigation-drawer" v-model="drawer"
    :dark="barColor !== 'rgba(255, 255, 255, 1), rgba(255, 255, 255, 0.7)'" :expand-on-hover="expandOnHover"
    :right="$vuetify.rtl" mobile-break-point="960" app width="260" mini-variant-width="85" v-bind="$attrs"
    :color="barColor" :class="barColor">
    <!-- user info ui -->
    <v-list style="margin-top:27px" nav class="py-0">

      <div class="app-sidebar__user active is-expanded">
        <div class="dropdown user-pro-body text-center">
          <div class="user-pic">

            <img src="http://127.0.0.1:8005/uploads/profile/user-profile.png" class="avatar-xxl rounded-circle mb-1"
              style="width:100px;height:100px" alt="default">

          </div>
          <div class="user-info" style="    text-align: center;">
            <h5 class=" mb-2">{{ $store.state.AdminInfo.clinics_info.name }}</h5>

            <h5  class="mb-2  app-sidebar__user-name "> {{ $store.state.AdminInfo.name }}</h5 >


          </div>
        </div>
      </div>





      <hr>
    </v-list>



    <!--Star Owner Sidbar-->
    <div>




      <v-list dense nav style=" font-family: 'cairo' " v-for="(item, i) in items" :key="i" class="my-0 py-0"
        :class="$i18n.locale == 'ar' ? 'pr-0' : 'pl-0'">
        <v-list-item style="color:#fff !important" link v-if="item.auth" :to="item.to">
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




</template>

<script>
  import {
    mapState
  } from "vuex";
  export default {

    data() {
      return {
        items: [

          {
            title: this.$t("header.dashbourd"),
            icon: 'fas fa-clipboard',
            auth: true,
            to: '/',
          },



          {
            title: this.$t("header.casesheet"),
            icon: 'fas fa-clipboard',
            to: '/patients',
            auth: true,
          },


          {
            title: this.$t("header.showCases"),
            icon: 'fas fa-clipboard',
            auth: true,
            to: '/cases',
          },


          {
            title: this.$t("header.accounts"),
            icon: 'fas fa-clipboard',
            auth: true,
            to: '/accounts',
          },


          {
            title: this.$t("Drawer.Booking"),
            icon: 'far fa-clock',
            auth: true,
            to: "/requestBooking"
          },





          {
            title: 'المواعيد تجريبي',
            icon: 'far fa-clock',
            auth: true,
            to: "/requestBooking_test"
          },
        ],
        right: null,
      }
    },

    computed: {
      ...mapState(["pathUrl"]),
      ...mapState(["setLogo"]),
      ...mapState(["barColor"]),
      ...mapState(["tile"]),

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
        this.items[2].auth = false;
      } else {
        this.items[1].auth = true;
        this.items[2].auth = true;
      }

    },
    methods: {
      mapItem(item) {
        return {
          ...item,
          children: item.children ? item.children.map(this.mapItem) : undefined,
          title: this.$t(item.title)
        };
      },
      changeLang() {


        this.items[0].title = this.$t("header.casesheet")
        this.items[1].title = this.$t("header.showCases")
        this.items[2].title = this.$t("Drawer.dashbourd")
        this.items[3].title = this.$t("Drawer.Booking")




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