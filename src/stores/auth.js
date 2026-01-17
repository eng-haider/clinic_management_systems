/**
 * Pinia Store - Vue 3
 * بديل Vuex لـ Vue 3
 * 
 * @author Clinic Management System
 * @version 3.0.0
 */

import { defineStore } from 'pinia'
import { AuthService } from '@/services/api/services'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    // User Info
    AdminInfo: {
      id: '',
      user_id: '',
      doctor_id: null,
      token: localStorage.getItem('tokinn') || '',
      authe: false,
      role: '',
      name: '',
      email: '',
      tctate_token: localStorage.getItem('tctate_token') || '',
      photo: '',
      phone: '',
      img_name: null,
      Permissions: [],
      dir: '',
      send_msg: 1,
      clinics_info: null,
      doctor_info: null,
      show_lap: 0,
      api_whatsapp: 0,
      paid_to_doctor: 0
    },
    
    // UI State
    drawer: null,
    lang: localStorage.getItem('lang') || 'ar',
    barColor: '#272727'
  }),

  getters: {
    /**
     * الحصول على دور المستخدم
     */
    userRole: (state) => state.AdminInfo.role,

    /**
     * هل المستخدم سكرتير
     */
    isSecretary: (state) => state.AdminInfo.role === 'secretary',

    /**
     * هل المستخدم مسجل دخول
     */
    isAuthenticated: (state) => state.AdminInfo.authe && !!state.AdminInfo.token,

    /**
     * صلاحيات المستخدم
     */
    userPermissions: (state) => state.AdminInfo.Permissions || [],

    /**
     * التحقق من صلاحية معينة
     */
    hasPermission: (state) => (permission) => {
      return state.AdminInfo.Permissions.includes(permission)
    },

    /**
     * هل يمكن عرض حالات المختبر
     */
    canShowLapCases: (state) => {
      const hasPermission = state.AdminInfo.Permissions.includes('show_lap_cases')
      const showLap = state.AdminInfo.show_lap === 1
      return hasPermission && showLap
    },

    /**
     * هل نظام الرصيد مفعل
     */
    useCreditSystem: (state) => {
      return state.AdminInfo.clinics_info?.use_credit === 1
    },

    /**
     * الحصول على Token
     */
    getToken: (state) => state.AdminInfo.token
  },

  actions: {
    /**
     * تسجيل الدخول
     */
    async login(credentials) {
      try {
        const response = await AuthService.login(credentials)
        
        if (response.token) {
          this.setAuthData(response)
          return { success: true, data: response }
        }
        
        return { success: false, error: 'Login failed' }
      } catch (error) {
        console.error('Login error:', error)
        return { success: false, error: error.message }
      }
    },

    /**
     * تسجيل الخروج
     */
    async logout() {
      try {
        await AuthService.logout()
      } catch (error) {
        console.warn('Logout API failed:', error)
      } finally {
        this.clearAuth()
      }
    },

    /**
     * تعيين بيانات المصادقة
     */
    setAuthData(userData) {
      this.AdminInfo = {
        id: userData.id || '',
        user_id: userData.user_id || '',
        doctor_id: userData.doctor_id || null,
        token: userData.token || '',
        authe: true,
        role: userData.role?.name || userData.role || '',
        name: userData.name || '',
        email: userData.email || '',
        tctate_token: userData.tctate_token || '',
        photo: userData.photo || '',
        phone: userData.phone || '',
        img_name: userData.img_name || null,
        Permissions: userData.Permissions || [],
        dir: userData.dir || '',
        send_msg: userData.send_msg || 1,
        clinics_info: userData.clinic_info || null,
        doctor_info: userData.doctor_info || null,
        show_lap: userData.show_lap || 0,
        api_whatsapp: userData.api_whatsapp || 0,
        paid_to_doctor: userData.paid_to_doctor || 0
      }

      // Save to localStorage
      localStorage.setItem('tokinn', userData.token)
      if (userData.tctate_token) {
        localStorage.setItem('tctate_token', userData.tctate_token)
      }
    },

    /**
     * مسح بيانات المصادقة
     */
    clearAuth() {
      this.AdminInfo = {
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
        clinics_info: null,
        doctor_info: null,
        show_lap: 0,
        api_whatsapp: 0,
        paid_to_doctor: 0
      }

      localStorage.removeItem('tokinn')
      localStorage.removeItem('tctate_token')
      localStorage.removeItem('AdminInfo')
    },

    /**
     * تغيير اللغة
     */
    setLanguage(lang) {
      this.lang = lang
      localStorage.setItem('lang', lang)
      document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr'
      document.documentElement.lang = lang
    },

    /**
     * التحكم بالـ Drawer
     */
    setDrawer(value) {
      this.drawer = value
    },

    /**
     * تحديث معلومات المستخدم
     */
    updateUserInfo(userData) {
      this.AdminInfo.name = userData.name || this.AdminInfo.name
      this.AdminInfo.email = userData.email || this.AdminInfo.email
      this.AdminInfo.photo = userData.photo || this.AdminInfo.photo
      this.AdminInfo.img_name = userData.img_name || this.AdminInfo.img_name
      this.AdminInfo.clinics_info = userData.clinic_info || this.AdminInfo.clinics_info
    },

    /**
     * تهيئة من localStorage
     */
    initFromStorage() {
      const token = localStorage.getItem('tokinn')
      const tctateToken = localStorage.getItem('tctate_token')
      
      if (token) {
        this.AdminInfo.token = token
        this.AdminInfo.tctate_token = tctateToken || ''
        
        // Try to restore from persisted state
        try {
          const vuexData = localStorage.getItem('vuex')
          if (vuexData) {
            const parsed = JSON.parse(vuexData)
            if (parsed.AdminInfo) {
              Object.assign(this.AdminInfo, parsed.AdminInfo)
              this.AdminInfo.authe = true
            }
          }
        } catch (e) {
          console.warn('Failed to restore state:', e)
        }
      }
    }
  },

  // Persist state
  persist: {
    key: 'auth-store',
    storage: localStorage,
    paths: ['AdminInfo', 'lang']
  }
})
