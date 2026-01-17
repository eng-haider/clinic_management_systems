/**
 * Patient Service
 * خدمة إدارة المرضى
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class PatientService {
  /**
   * جلب جميع المرضى مع التصفية والترتيب
   * @param {Object} params - معاملات البحث
   * @param {number} params.page - رقم الصفحة
   * @param {number} params.limit - عدد العناصر في الصفحة
   * @param {string} params.search - نص البحث
   * @param {string} params.sort - ترتيب حسب
   * @returns {Promise<Object>}
   */
  async getAll(params = {}) {
    const { page = 1, limit = 50, search = '', sort = '-id' } = params
    
    return await apiClient.get('/patients', {
      params: {
        page,
        limit,
        search,
        sort
      }
    })
  }

  /**
   * جلب مريض واحد بالـ ID
   * @param {number} id - معرف المريض
   * @returns {Promise<Object>}
   */
  async getById(id) {
    return await apiClient.get(`/getPatientById/${id}`)
  }

  /**
   * إنشاء مريض جديد
   * @param {Object} patientData - بيانات المريض
   * @param {string} patientData.name - اسم المريض
   * @param {string} patientData.phone - رقم الهاتف
   * @param {number} patientData.age - العمر
   * @param {string} patientData.address - العنوان
   * @param {string} patientData.sex - الجنس
   * @param {string} patientData.systemic_conditions - الحالات الصحية
   * @returns {Promise<Object>}
   */
  async create(patientData) {
    return await apiClient.post('/patients', patientData)
  }

  /**
   * تحديث بيانات مريض
   * @param {number} id - معرف المريض
   * @param {Object} patientData - البيانات المحدثة
   * @returns {Promise<Object>}
   */
  async update(id, patientData) {
    return await apiClient.put(`/patients/${id}`, patientData)
  }

  /**
   * حذف مريض
   * @param {number} id - معرف المريض
   * @returns {Promise<Object>}
   */
  async delete(id) {
    return await apiClient.delete(`/patients/${id}`)
  }

  /**
   * البحث عن مريض
   * @param {string} query - نص البحث (اسم أو رقم هاتف)
   * @returns {Promise<Object>}
   */
  async search(query) {
    return await apiClient.get('/patients/search', {
      params: { q: query }
    })
  }

  /**
   * جلب حالات مريض
   * @param {number} patientId - معرف المريض
   * @returns {Promise<Array>}
   */
  async getCases(patientId) {
    return await apiClient.get(`/patients/${patientId}/cases`)
  }

  /**
   * إضافة حالة لمريض
   * @param {number} patientId - معرف المريض
   * @param {Object} caseData - بيانات الحالة
   * @returns {Promise<Object>}
   */
  async addCase(patientId, caseData) {
    return await apiClient.post(`/patients/${patientId}/cases`, caseData)
  }

  /**
   * جلب فواتير مريض
   * @param {number} patientId - معرف المريض
   * @returns {Promise<Array>}
   */
  async getBills(patientId) {
    return await apiClient.get(`/patients/${patientId}/bills`)
  }

  /**
   * إضافة فاتورة لمريض
   * @param {number} patientId - معرف المريض
   * @param {Object} billData - بيانات الفاتورة
   * @returns {Promise<Object>}
   */
  async addBill(patientId, billData) {
    return await apiClient.post(`/patients/${patientId}/bills`, billData)
  }

  /**
   * جلب صور مريض
   * @param {number} patientId - معرف المريض
   * @returns {Promise<Array>}
   */
  async getImages(patientId) {
    return await apiClient.get(`/patients/${patientId}/images`)
  }

  /**
   * رفع صورة لمريض
   * @param {number} patientId - معرف المريض
   * @param {File} file - الملف
   * @returns {Promise<Object>}
   */
  async uploadImage(patientId, file) {
    const formData = new FormData()
    formData.append('image', file)
    
    return await apiClient.post(`/patients/${patientId}/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  /**
   * حذف صورة مريض
   * @param {number} patientId - معرف المريض
   * @param {number} imageId - معرف الصورة
   * @returns {Promise<Object>}
   */
  async deleteImage(patientId, imageId) {
    return await apiClient.delete(`/patients/${patientId}/images/${imageId}`)
  }

  /**
   * إضافة رصيد لمريض
   * @param {number} patientId - معرف المريض
   * @param {number} amount - المبلغ
   * @returns {Promise<Object>}
   */
  async addCredit(patientId, amount) {
    return await apiClient.post(`/patients/${patientId}/credit`, { amount })
  }

  /**
   * تحديث ملاحظات المريض
   * @param {number} patientId - معرف المريض
   * @param {string} notes - الملاحظات
   * @returns {Promise<Object>}
   */
  async updateNotes(patientId, notes) {
    return await apiClient.patch(`/patients/${patientId}/notes`, { notes })
  }

  /**
   * حفظ بيانات المريض كاملة (الحالات والفواتير)
   * @param {number} patientId - معرف المريض
   * @param {Object} data - البيانات الكاملة
   * @returns {Promise<Object>}
   */
  async savePatientData(patientId, data) {
    return await apiClient.put(`/savePatient/${patientId}`, data)
  }

  /**
   * جلب التاريخ الطبي للمريض
   * @param {number} patientId - معرف المريض
   * @returns {Promise<Object>}
   */
  async getMedicalHistory(patientId) {
    return await apiClient.get(`/patients/${patientId}/medical-history`)
  }
}

export default new PatientService()
