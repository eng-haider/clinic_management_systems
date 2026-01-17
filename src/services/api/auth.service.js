/**
 * Auth Service
 * خدمة المصادقة والتسجيل
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class AuthService {
  /**
   * تسجيل الدخول
   * @param {Object} credentials - بيانات تسجيل الدخول
   * @param {string} credentials.phone - رقم الهاتف
   * @param {string} credentials.password - كلمة المرور
   * @returns {Promise<Object>} - بيانات المستخدم والـ Token
   */
  async login(credentials) {
    try {
      const response = await apiClient.post('/auth/login', credentials)

      if (response.token) {
        // حفظ Token في localStorage
        localStorage.setItem('tokinn', response.token)
        localStorage.setItem('AdminInfo', JSON.stringify(response.user || response))
      }

      return response
    } catch (error) {
      console.error('❌ Login failed:', error)
      throw error
    }
  }

  /**
   * تسجيل الخروج
   * @returns {Promise<boolean>}
   */
  async logout() {
    try {
      await apiClient.post('/auth/logout')
    } catch (error) {
      console.warn('⚠️ Logout API call failed, clearing local data anyway')
    } finally {
      // مسح البيانات المحلية دائماً
      localStorage.removeItem('tokinn')
      localStorage.removeItem('AdminInfo')
    }
    
    return true
  }

  /**
   * التحقق من صلاحية الـ Token
   * @returns {Promise<Object>}
   */
  async verifyToken() {
    return await apiClient.get('/auth/verify')
  }

  /**
   * تغيير كلمة المرور
   * @param {Object} passwords - كلمات المرور
   * @param {string} passwords.old_password - كلمة المرور القديمة
   * @param {string} passwords.new_password - كلمة المرور الجديدة
   * @param {string} passwords.new_password_confirmation - تأكيد كلمة المرور الجديدة
   * @returns {Promise<Object>}
   */
  async changePassword(passwords) {
    return await apiClient.put('/auth/change-password', passwords)
  }

  /**
   * الحصول على بيانات المستخدم الحالي
   * @returns {Object|null}
   */
  getCurrentUser() {
    const userInfo = localStorage.getItem('AdminInfo')
    return userInfo ? JSON.parse(userInfo) : null
  }

  /**
   * الحصول على الـ Token الحالي
   * @returns {string|null}
   */
  getToken() {
    return localStorage.getItem('tokinn')
  }

  /**
   * التحقق من تسجيل الدخول
   * @returns {boolean}
   */
  isAuthenticated() {
    return !!this.getToken()
  }

  /**
   * تحديث بيانات المستخدم في localStorage
   * @param {Object} userData - بيانات المستخدم المحدثة
   */
  updateUserData(userData) {
    const currentUser = this.getCurrentUser() || {}
    const updatedUser = { ...currentUser, ...userData }
    localStorage.setItem('AdminInfo', JSON.stringify(updatedUser))
  }
}

export default new AuthService()
