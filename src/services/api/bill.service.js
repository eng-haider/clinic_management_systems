/**
 * Bill Service
 * خدمة إدارة الفواتير
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class BillService {
  /**
   * جلب جميع الفواتير
   * @param {Object} params - معاملات البحث
   * @returns {Promise<Object>}
   */
  async getAll(params = {}) {
    return await apiClient.get('/bills', { params })
  }

  /**
   * جلب فاتورة واحدة
   * @param {number} id - معرف الفاتورة
   * @returns {Promise<Object>}
   */
  async getById(id) {
    return await apiClient.get(`/bills/${id}`)
  }

  /**
   * إنشاء فاتورة جديدة
   * @param {Object} billData - بيانات الفاتورة
   * @param {number} billData.patient_id - معرف المريض
   * @param {number} billData.case_id - معرف الحالة (اختياري)
   * @param {number} billData.price - المبلغ
   * @param {boolean} billData.is_paid - هل مدفوعة
   * @param {string} billData.PaymentDate - تاريخ الدفع
   * @returns {Promise<Object>}
   */
  async create(billData) {
    return await apiClient.post('/bills', billData)
  }

  /**
   * تحديث فاتورة
   * @param {number} id - معرف الفاتورة
   * @param {Object} billData - البيانات المحدثة
   * @returns {Promise<Object>}
   */
  async update(id, billData) {
    return await apiClient.put(`/bills/${id}`, billData)
  }

  /**
   * حذف فاتورة
   * @param {number} id - معرف الفاتورة
   * @returns {Promise<Object>}
   */
  async delete(id) {
    return await apiClient.delete(`/bills/${id}`)
  }

  /**
   * تحديث حالة الدفع
   * @param {number} id - معرف الفاتورة
   * @param {boolean} isPaid - هل مدفوعة
   * @returns {Promise<Object>}
   */
  async updatePaymentStatus(id, isPaid) {
    return await apiClient.patch(`/bills/${id}/payment-status`, { 
      is_paid: isPaid ? 1 : 0 
    })
  }

  /**
   * جلب فواتير مريض
   * @param {number} patientId - معرف المريض
   * @returns {Promise<Array>}
   */
  async getByPatient(patientId) {
    return await apiClient.get(`/patients/${patientId}/bills`)
  }

  /**
   * جلب فواتير حالة
   * @param {number} caseId - معرف الحالة
   * @returns {Promise<Array>}
   */
  async getByCase(caseId) {
    return await apiClient.get(`/cases/${caseId}/bills`)
  }

  /**
   * طباعة فاتورة
   * @param {number} id - معرف الفاتورة
   * @returns {Promise<Blob>}
   */
  async print(id) {
    return await apiClient.get(`/bills/${id}/print`, {
      responseType: 'blob'
    })
  }

  /**
   * جلب تقرير الفواتير
   * @param {Object} params - معاملات التقرير
   * @param {string} params.from - تاريخ البداية
   * @param {string} params.to - تاريخ النهاية
   * @returns {Promise<Object>}
   */
  async getReport(params = {}) {
    return await apiClient.get('/bills/report', { params })
  }
}

export default new BillService()
