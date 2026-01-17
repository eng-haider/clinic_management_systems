/**
 * Appointment Service
 * خدمة إدارة المواعيد
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class AppointmentService {
  /**
   * جلب جميع المواعيد
   * @param {Object} params - معاملات البحث
   * @param {string} params.date - التاريخ
   * @param {number} params.doctor_id - معرف الطبيب
   * @param {string} params.status - الحالة
   * @returns {Promise<Object>}
   */
  async getAll(params = {}) {
    return await apiClient.get('/appointments', { params })
  }

  /**
   * جلب موعد واحد
   * @param {number} id - معرف الموعد
   * @returns {Promise<Object>}
   */
  async getById(id) {
    return await apiClient.get(`/appointments/${id}`)
  }

  /**
   * إنشاء موعد جديد
   * @param {Object} appointmentData - بيانات الموعد
   * @param {number} appointmentData.patient_id - معرف المريض
   * @param {number} appointmentData.doctor_id - معرف الطبيب
   * @param {string} appointmentData.date - التاريخ
   * @param {string} appointmentData.time - الوقت
   * @param {string} appointmentData.notes - الملاحظات
   * @returns {Promise<Object>}
   */
  async create(appointmentData) {
    return await apiClient.post('/appointments', appointmentData)
  }

  /**
   * تحديث موعد
   * @param {number} id - معرف الموعد
   * @param {Object} appointmentData - البيانات المحدثة
   * @returns {Promise<Object>}
   */
  async update(id, appointmentData) {
    return await apiClient.put(`/appointments/${id}`, appointmentData)
  }

  /**
   * حذف موعد
   * @param {number} id - معرف الموعد
   * @returns {Promise<Object>}
   */
  async delete(id) {
    return await apiClient.delete(`/appointments/${id}`)
  }

  /**
   * جلب مواعيد يوم محدد
   * @param {string} date - التاريخ (YYYY-MM-DD)
   * @returns {Promise<Array>}
   */
  async getByDate(date) {
    return await apiClient.get('/appointments/by-date', {
      params: { date }
    })
  }

  /**
   * جلب مواعيد طبيب
   * @param {number} doctorId - معرف الطبيب
   * @param {string} date - التاريخ (اختياري)
   * @returns {Promise<Array>}
   */
  async getByDoctor(doctorId, date = null) {
    const params = { doctor_id: doctorId }
    if (date) params.date = date
    
    return await apiClient.get('/appointments/by-doctor', { params })
  }

  /**
   * جلب مواعيد مريض
   * @param {number} patientId - معرف المريض
   * @returns {Promise<Array>}
   */
  async getByPatient(patientId) {
    return await apiClient.get(`/patients/${patientId}/appointments`)
  }

  /**
   * تأكيد موعد
   * @param {number} id - معرف الموعد
   * @returns {Promise<Object>}
   */
  async confirm(id) {
    return await apiClient.patch(`/appointments/${id}/confirm`)
  }

  /**
   * إلغاء موعد
   * @param {number} id - معرف الموعد
   * @param {string} reason - سبب الإلغاء
   * @returns {Promise<Object>}
   */
  async cancel(id, reason = '') {
    return await apiClient.patch(`/appointments/${id}/cancel`, { reason })
  }

  /**
   * إكمال موعد
   * @param {number} id - معرف الموعد
   * @returns {Promise<Object>}
   */
  async complete(id) {
    return await apiClient.patch(`/appointments/${id}/complete`)
  }

  /**
   * جلب الأوقات المتاحة
   * @param {string} date - التاريخ
   * @param {number} doctorId - معرف الطبيب
   * @returns {Promise<Array>}
   */
  async getAvailableSlots(date, doctorId) {
    return await apiClient.get('/appointments/available-slots', {
      params: { date, doctor_id: doctorId }
    })
  }
}

export default new AppointmentService()
