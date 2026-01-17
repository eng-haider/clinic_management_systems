/**
 * Case Service
 * خدمة إدارة الحالات
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class CaseService {
  /**
   * جلب جميع الحالات
   * @param {Object} params - معاملات البحث
   * @param {number} params.page - رقم الصفحة
   * @param {number} params.limit - عدد العناصر
   * @param {string} params.status - حالة الحالة (active, completed)
   * @returns {Promise<Object>}
   */
  async getAll(params = {}) {
    return await apiClient.get('/cases', { params })
  }

  /**
   * جلب حالة واحدة
   * @param {number} id - معرف الحالة
   * @returns {Promise<Object>}
   */
  async getById(id) {
    return await apiClient.get(`/cases/${id}`)
  }

  /**
   * إنشاء حالة جديدة
   * @param {Object} caseData - بيانات الحالة
   * @param {number} caseData.patient_id - معرف المريض
   * @param {number} caseData.category_id - معرف التصنيف
   * @param {string} caseData.tooth_num - رقم السن
   * @param {number} caseData.price - السعر
   * @param {string} caseData.notes - الملاحظات
   * @returns {Promise<Object>}
   */
  async create(caseData) {
    return await apiClient.post('/cases', caseData)
  }

  /**
   * تحديث حالة
   * @param {number} id - معرف الحالة
   * @param {Object} caseData - البيانات المحدثة
   * @returns {Promise<Object>}
   */
  async update(id, caseData) {
    return await apiClient.put(`/cases/${id}`, caseData)
  }

  /**
   * حذف حالة
   * @param {number} id - معرف الحالة
   * @returns {Promise<Object>}
   */
  async delete(id) {
    return await apiClient.delete(`/cases/${id}`)
  }

  /**
   * تحديث حالة الحالة (مكتملة/غير مكتملة)
   * @param {number} id - معرف الحالة
   * @param {boolean} completed - هل مكتملة
   * @returns {Promise<Object>}
   */
  async updateStatus(id, completed) {
    return await apiClient.patch(`/cases/${id}/status`, { 
      status: completed ? 'completed' : 'active' 
    })
  }

  /**
   * جلب تصنيفات الحالات
   * @returns {Promise<Array>}
   */
  async getCategories() {
    return await apiClient.get('/case-categories')
  }

  /**
   * إضافة جلسة لحالة
   * @param {number} caseId - معرف الحالة
   * @param {Object} sessionData - بيانات الجلسة
   * @returns {Promise<Object>}
   */
  async addSession(caseId, sessionData) {
    return await apiClient.post(`/cases/${caseId}/sessions`, sessionData)
  }

  /**
   * تحديث ملاحظات الحالة
   * @param {number} caseId - معرف الحالة
   * @param {string} notes - الملاحظات
   * @returns {Promise<Object>}
   */
  async updateNotes(caseId, notes) {
    return await apiClient.patch(`/cases/${caseId}/notes`, { notes })
  }
}

export default new CaseService()
