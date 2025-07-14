<template>
  <v-bottom-navigation
    v-show="$vuetify.breakpoint.mobile"
    class="mobile-bottom-nav"
    app
    grow
    fixed
    :height="80"
    color="white"
    style="color: rgb(255, 255, 255); caret-color: rgb(255, 255, 255); z-index: 1000;"
  >
    <!-- Patients/Case Sheet -->
    <v-btn
      v-if="$store.state.AdminInfo.Permissions.includes('show_patients')"
      :to="'/patients'"
      router
      exact
      class="nav-btn"
      text
      :color="$route.path === '/patients' ? 'rgb(24, 119, 242)' : 'rgb(101, 103, 107)'"
      @click="scrollToTop"
    >
      <span class="v-btn__content">
        <div class="nav-item-content">
          <v-icon 
            class="nav-icon" 
            :style="{ color: $route.path === '/patients' ? 'rgb(24, 119, 242)' : 'rgb(101, 103, 107)' }"
          >
            fas fa-clipboard
          </v-icon>
          <span 
            class="nav-text"
            :class="{ 'active-text': $route.path === '/patients' }"
          >
            {{ $t("header.casesheet") }}
          </span>
        </div>
      </span>
    </v-btn>

    <!-- Cases (Not shown to secretary) -->
    <v-btn
      v-if="$store.state.AdminInfo.Permissions.includes('show_cases') && !$store.getters.isSecretary"
      :to="'/cases'"
      router
      exact
      class="nav-btn"
      text
      :color="$route.path === '/cases' ? 'rgb(24, 119, 242)' : 'rgb(101, 103, 107)'"
      @click="scrollToTop"
    >
      <span class="v-btn__content">
        <div class="nav-item-content">
          <v-icon 
            class="nav-icon" 
            :style="{ color: $route.path === '/cases' ? 'rgb(24, 119, 242)' : 'rgb(101, 103, 107)' }"
          >
            fa-light fa-tooth
          </v-icon>
          <span 
            class="nav-text"
            :class="{ 'active-text': $route.path === '/cases' }"
          >
            {{ $t("header.showCases") }}
          </span>
        </div>
      </span>
    </v-btn>

    <!-- Booking -->
    <v-btn
      v-if="$store.state.AdminInfo.Permissions.includes('show_appointment')"
      :to="'/requestBooking_test'"
      router
      exact
      class="nav-btn"
      text
      :color="$route.path === '/requestBooking_test' ? 'rgb(24, 119, 242)' : 'rgb(101, 103, 107)'"
      @click="scrollToTop"
    >
      <span class="v-btn__content">
        <div class="nav-item-content">
          <v-icon 
            class="nav-icon" 
            :style="{ color: $route.path === '/requestBooking_test' ? 'rgb(24, 119, 242)' : 'rgb(101, 103, 107)' }"
          >
            fa-solid fa-calendar
          </v-icon>
          <span 
            class="nav-text"
            :class="{ 'active-text': $route.path === '/requestBooking_test' }"
          >
            {{ $t("Drawer.Booking") }}
          </span>
        </div>
      </span>
    </v-btn>

    <!-- Menu Button -->
    <v-btn
      class="nav-btn"
      text
      @click="toggleDrawer"
      color="rgb(101, 103, 107)"
    >
      <span class="v-btn__content">
        <div class="nav-item-content">
          <v-icon 
            class="nav-icon" 
            style="color: rgb(101, 103, 107);"
          >
            mdi-menu
          </v-icon>
          <span class="nav-text">
            القائمة
          </span>
        </div>
      </span>
    </v-btn>
  </v-bottom-navigation>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: 'MobileBottomNav',
  
  computed: {
    ...mapState(["barColor"]),
  },

  methods: {
    scrollToTop() {
      // Smooth scroll to top
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
      
      // Alternative for older browsers or immediate scroll
      // window.scrollTo(0, 0);
    },
    
    toggleDrawer() {
      this.$store.commit("SET_DRAWER", !this.$store.state.drawer);
    }
  }
}
</script>

<style scoped>
.mobile-bottom-nav {
  z-index: 1000 !important;
}

.nav-btn {
  flex-direction: column !important;
  height: 100% !important;
  min-width: 64px !important;
}

.nav-item-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
}

.nav-icon {
  font-size: 20px !important;
  margin-bottom: 4px !important;
}

.nav-text {
  font-size: 10px !important;
  line-height: 1.2 !important;
  text-transform: none !important;
  font-weight: 400 !important;
  color: rgb(101, 103, 107) !important;
  transition: color 0.3s ease !important;
}

.nav-text.active-text {
  color: rgb(24, 119, 242) !important;
  font-weight: 500 !important;
}

/* RTL Support */
.v-application--is-rtl .nav-text {
  direction: rtl;
}

/* Mobile specific adjustments */
@media (max-width: 600px) {
  .nav-text {
    font-size: 9px !important;
  }
  
  .nav-icon {
    font-size: 18px !important;
  }
}
</style>
