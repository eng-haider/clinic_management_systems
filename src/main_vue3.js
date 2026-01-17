/**
 * Main Entry Point - Vue 3
 * 
 * @author Clinic Management System
 * @version 3.0.0
 */

import { createApp } from 'vue'
import App from './App_vue3.vue'
import router from './router_vue3'
import { createPinia } from 'pinia'

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import '@fortawesome/fontawesome-free/css/all.css'

// i18n
import { createI18n } from 'vue-i18n'
import ar from './locales/ar.json'
import en from './locales/en.json'

// Styles
import './assets/performance.css'

// Create Vuetify instance
const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light',
    themes: {
      light: {
        colors: {
          primary: '#17638D',
          secondary: '#ff0000',
          accent: '#9C27b0',
          info: '#00CAE3'
        }
      },
      dark: {
        colors: {
          primary: '#17638D',
          secondary: '#ff0000',
          accent: '#9C27b0',
          info: '#00CAE3'
        }
      }
    }
  },
  defaults: {
    global: {
      ripple: true
    }
  }
})

// Create i18n instance
const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('lang') || 'ar',
  fallbackLocale: 'en',
  messages: { ar, en }
})

// Create Pinia store
const pinia = createPinia()

// Create app
const app = createApp(App)

// Global properties
app.config.globalProperties.Url = 'https://mina-api.tctate.com'
app.config.globalProperties.http = 'https://'

// Use plugins
app.use(pinia)
app.use(router)
app.use(vuetify)
app.use(i18n)

// Set RTL direction
const currentLang = localStorage.getItem('lang') || 'ar'
document.documentElement.dir = currentLang === 'ar' ? 'rtl' : 'ltr'
document.documentElement.lang = currentLang

// Mount app
app.mount('#app')
