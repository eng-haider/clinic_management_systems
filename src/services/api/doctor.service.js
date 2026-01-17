/**
 * Doctor Service
 * خدمة إدارة الأطباء
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class DoctorService {
  /**
   * جلب جميع الأطباء
   * @param {Object} params - معاملات البحث
   * @returns {Promise<Array>}
   */
  async getAll(params = {}) {
    return await apiClient.get('/doctors', { params })
  }

  /**
   * جلب طبيب واحد
   * @param {number} id - معرف الطبيب
   * @returns {Promise<Object>}
   */
  async getById(id) {
    return await apiClient.get(`/doctors/${id}`)
  }

  /**
   * إنشاء طبيب جديد
   * @param {Object} doctorData - بيانات الطبيب
   * @param {string} doctorData.name - الاسم
   * @param {string} doctorData.name_ar - الاسم بالعربي
   * @param {string} doctorData.phone - رقم الهاتف
   * @param {string} doctorData.specialty - التخصص
   * @returns {Promise<Object>}
   */
  async create(doctorData) {
    return await apiClient.post('/doctors', doctorData)
  }

  /**
   * تحديث بيانات طبيب
   * @param {number} id - معرف الطبيب
   * @param {Object} doctorData - البيانات المحدثة
   * @returns {Promise<Object>}
   */
  async update(id, doctorData) {
    return await apiClient.put(`/doctors/${id}`, doctorData)
  }

  /**
   * حذف طبيب
   * @param {number} id - معرف الطبيب
   * @returns {Promise<Object>}
   */
  async delete(id) {
    return await apiClient.delete(`/doctors/${id}`)
  }

  /**
   * جلب إحصائيات طبيب
   * @param {number} id - معرف الطبيب
   * @returns {Promise<Object>}
   */
  async getStats(id) {
    return await apiClient.get(`/doctors/${id}/stats`)
  }

  /**
   * جلب حالات طبيب
   * @param {number} id - معرف الطبيب
   * @param {Object} params - معاملات البحث
   * @returns {Promise<Array>}
   */
  async getCases(id, params = {}) {
    return await apiClient.get(`/doctors/${id}/cases`, { params })
  }

  /**
   * جلب مواعيد طبيب
   * @param {number} id - معرف الطبيب
   * @param {string} date - التاريخ (اختياري)
   * @returns {Promise<Array>}
   */
  async getAppointments(id, date = null) {
    const params = date ? { date } : {}
    return await apiClient.get(`/doctors/${id}/appointments`, { params })
  }
}

export default new DoctorService()
