/**
 * Upload Service
 * خدمة رفع الملفات والصور
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

class UploadService {
  /**
   * رفع صورة واحدة
   * @param {File} file - الملف
   * @param {string} type - نوع الصورة (patient, case, xray)
   * @param {Function} onProgress - callback للتقدم
   * @returns {Promise<Object>}
   */
  async uploadImage(file, type = 'patient', onProgress = null) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)

    const config = {
      headers: { 'Content-Type': 'multipart/form-data' }
    }

    // إضافة callback للتقدم
    if (onProgress) {
      config.onUploadProgress = (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
        onProgress(percentCompleted)
      }
    }

    return await apiClient.post('/upload/image', formData, config)
  }

  /**
   * رفع عدة صور
   * @param {Array<File>} files - الملفات
   * @param {string} type - نوع الصور
   * @param {Function} onProgress - callback للتقدم
   * @returns {Promise<Array>}
   */
  async uploadMultiple(files, type = 'patient', onProgress = null) {
    const formData = new FormData()
    
    files.forEach((file, index) => {
      formData.append(`files[${index}]`, file)
    })
    formData.append('type', type)

    const config = {
      headers: { 'Content-Type': 'multipart/form-data' }
    }

    if (onProgress) {
      config.onUploadProgress = (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
        onProgress(percentCompleted)
      }
    }

    return await apiClient.post('/upload/multiple', formData, config)
  }

  /**
   * رفع صورة لمريض
   * @param {number} patientId - معرف المريض
   * @param {File} file - الملف
   * @param {Function} onProgress - callback للتقدم
   * @returns {Promise<Object>}
   */
  async uploadPatientImage(patientId, file, onProgress = null) {
    const formData = new FormData()
    formData.append('image', file)

    const config = {
      headers: { 'Content-Type': 'multipart/form-data' }
    }

    if (onProgress) {
      config.onUploadProgress = (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
        onProgress(percentCompleted)
      }
    }

    return await apiClient.post(`/patients/${patientId}/upload`, formData, config)
  }

  /**
   * حذف صورة
   * @param {string} imageUrl - رابط الصورة
   * @returns {Promise<Object>}
   */
  async deleteImage(imageUrl) {
    return await apiClient.delete('/upload/image', {
      data: { url: imageUrl }
    })
  }

  /**
   * حذف صورة مريض
   * @param {number} patientId - معرف المريض
   * @param {number} imageId - معرف الصورة
   * @returns {Promise<Object>}
   */
  async deletePatientImage(patientId, imageId) {
    return await apiClient.delete(`/patients/${patientId}/images/${imageId}`)
  }

  /**
   * الحصول على رابط الصورة الكامل
   * @param {string} imagePath - مسار الصورة
   * @returns {string}
   */
  getImageUrl(imagePath) {
    if (!imagePath) return ''
    
    // إذا كان رابط كامل
    if (imagePath.startsWith('http')) {
      return imagePath
    }
    
    // إضافة الـ Base URL
    const baseUrl = process.env.VUE_APP_UPLOAD_URL || 'https://mina-api.tctate.com'
    return `${baseUrl}/${imagePath}`
  }
}

export default new UploadService()
