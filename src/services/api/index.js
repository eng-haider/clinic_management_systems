/**
 * Axios Instance Configuration
 * إعدادات Axios الأساسية مع Interceptors
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import axios from 'axios'
import store from '@/store'
import router from '@/router'

// Base URL من environment variable
const BASE_URL = process.env.VUE_APP_API_URL || 'https://mina-api.tctate.com/api'

/**
 * إنشاء Axios instance مع الإعدادات الافتراضية
 */
const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

/**
 * Request Interceptor
 * - إضافة Token للـ Headers
 * - تسجيل الطلبات
 * - مراقبة الأداء
 */
apiClient.interceptors.request.use(
  (config) => {
    // إضافة Token من localStorage
    const token = localStorage.getItem('tokinn') || store?.state?.AdminInfo?.token
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // إضافة اللغة
    const language = localStorage.getItem('language') || 'ar'
    config.headers['Accept-Language'] = language

    // مراقبة الأداء
    config.metadata = { startTime: performance.now() }

    // تسجيل الطلب (في وضع التطوير فقط)
    if (process.env.NODE_ENV === 'development') {
      console.log(`📡 API Request: ${config.method?.toUpperCase()} ${config.url}`)
    }

    return config
  },
  (error) => {
    console.error('❌ Request Error:', error)
    return Promise.reject(error)
  }
)

/**
 * Response Interceptor
 * - معالجة الأخطاء
 * - تسجيل الطلبات البطيئة
 * - إدارة حالة التحميل
 */
apiClient.interceptors.response.use(
  (response) => {
    // حساب مدة الطلب
    if (response.config.metadata) {
      const duration = performance.now() - response.config.metadata.startTime

      // تسجيل الطلبات البطيئة (أكثر من 2 ثانية)
      if (duration > 2000 && process.env.NODE_ENV === 'development') {
        console.warn(`⚠️ Slow API Request (${duration.toFixed(0)}ms): ${response.config.url}`)
      }
    }

    return response.data
  },
  (error) => {
    // معالجة الأخطاء
    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 401:
          // غير مصرح - توجيه لصفحة تسجيل الدخول
          console.error('🔒 Unauthorized - Redirecting to login')
          localStorage.removeItem('tokinn')
          localStorage.removeItem('AdminInfo')
          router.push('/login')
          break

        case 403:
          // محظور
          console.error('🚫 Forbidden - Access denied')
          break

        case 404:
          // غير موجود
          console.error('❓ Not Found:', error.config.url)
          break

        case 422:
          // خطأ في التحقق
          console.error('⚠️ Validation Error:', data)
          break

        case 500:
          // خطأ في الخادم
          console.error('💥 Server Error:', data)
          break

        default:
          console.error(`❌ API Error (${status}):`, data?.message || 'Unknown error')
      }
    } else if (error.request) {
      // لا يوجد استجابة
      console.error('📡 Network Error - No response received')
    } else {
      // خطأ في إعداد الطلب
      console.error('⚙️ Request Setup Error:', error.message)
    }

    return Promise.reject(error)
  }
)

export default apiClient
