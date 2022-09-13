import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import i18n from '@/i18n'

Vue.use(Vuetify)

const theme = {
  primary: '#17638D',
  secondary: 'red',
  accent: '#9C27b0',
  info: '#00CAE3'
}

export default new Vuetify({
  rtl:  localStorage.getItem("lang") ? (localStorage.getItem("lang") == "ar" ? true : false) : true,
  lang: {
    t: (key, ...params) => i18n.t(key, params)
  },
  icons: {
    iconfont: 'mdi', // default - only for display purposes
  },
  theme: {
    dark:localStorage.getItem("darkMode")?localStorage.getItem("darkMode") == "true"
    
      ? true
      : false
    : false,
    themes: {
      dark: theme,
      light: theme
    }
  }
})
