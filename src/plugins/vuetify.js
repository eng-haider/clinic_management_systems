import Vue from 'vue';
import Vuetify from 'vuetify/lib/framework';  // Import only the required Vuetify features
import '@mdi/font/css/materialdesignicons.css';  // Import only the required font

Vue.use(Vuetify);

const theme = {
  primary: '#17638D',
  secondary: 'red',
  accent: '#9C27b0',
  info: '#00CAE3',
};

// Get the language from localStorage and set RTL accordingly
const currentLang = localStorage.getItem("lang") || 'ar';
const isRTL = currentLang === 'ar';

export default new Vuetify({
  rtl: isRTL,
  theme: {
    dark: localStorage.getItem('darkMode') === 'true',
    themes: {
      dark: theme,
      light: theme,
    },
  },
  icons: {
    iconfont: 'mdi',  // Specify only the @mdi/font icons
  },
});
