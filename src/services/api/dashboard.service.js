/**
 * Dashboard Service
 * خدمة لوحة التحكم والإحصائيات
 * 
 * @author Clinic Management System
 * @version 1.0.0
 */

import apiClient from './index'

// Booking API (مختلف عن الـ API الرئيسي)
const BOOKING_API_URL = 'https://tctate.com/api/api'

class DashboardService {
  /**
   * جلب إحصائيات لوحة التحكم
   * @returns {Promise<Object>}
   */
  async getCounts() {
    return await apiClient.get('/cases/getDashbourdCounts')
  }

  /**
   * جلب إحصائيات الحالات حسب التصنيف
   * @returns {Promise<Array>}
   */
  async getCaseCategoriesCounts() {
    return await apiClient.get('/cases/getCaseCategoriesCounts')
  }

  /**
   * جلب إحصائيات الحسابات
   * @param {number} page - رقم الصفحة
   * @returns {Promise<Object>}
   */
  async getAccountsStats(page = 1) {
    return await apiClient.get('/patientsAccounsts/dash', {
      params: { page }
    })
  }

  /**
   * جلب مواعيد اليوم
   * @param {string} date - التاريخ (YYYY-MM-DD)
   * @param {string} token - Token الحجز (مختلف عن الـ Token الرئيسي)
   * @returns {Promise<Array>}
   */
  async getTodayBookings(date, token) {
    try {
      const response = await fetch(
        `${BOOKING_API_URL}/reservation/owner/search?filter[BetweenDate]=${date}_${date}.&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`,
        {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        }
      )
      
      const data = await response.json()
      return data.data || []
    } catch (error) {
      console.error('❌ Error fetching bookings:', error)
      return []
    }
  }

  /**
   * جلب جميع بيانات لوحة التحكم دفعة واحدة
   * @param {string} bookingToken - Token الحجز
   * @returns {Promise<Object>}
   */
  async getAllDashboardData(bookingToken) {
    const today = new Date()
    const dateString = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`

    // تحميل جميع البيانات بالتوازي
    const [counts, caseCategories, accountsStats, bookings] = await Promise.all([
      this.getCounts().catch(() => ({
        patients: 0,
        Casesall: 0,
        Casescompleted: 0,
        Casesactive: 0
      })),
      this.getCaseCategoriesCounts().catch(() => ({ data: [] })),
      this.getAccountsStats().catch(() => []),
      this.getTodayBookings(dateString, bookingToken).catch(() => [])
    ])

    return {
      counts,
      caseCategories: caseCategories.data || caseCategories,
      accountsStats,
      bookings
    }
  }

  /**
   * جلب تقرير الدخل
   * @param {Object} params - معاملات التقرير
   * @param {string} params.from - تاريخ البداية
   * @param {string} params.to - تاريخ النهاية
   * @returns {Promise<Object>}
   */
  async getIncomeReport(params = {}) {
    return await apiClient.get('/reports/income', { params })
  }

  /**
   * جلب تقرير الحالات
   * @param {Object} params - معاملات التقرير
   * @returns {Promise<Object>}
   */
  async getCasesReport(params = {}) {
    return await apiClient.get('/reports/cases', { params })
  }

  /**
   * جلب تقرير المرضى
   * @param {Object} params - معاملات التقرير
   * @returns {Promise<Object>}
   */
  async getPatientsReport(params = {}) {
    return await apiClient.get('/reports/patients', { params })
  }
}

export default new DashboardService()
