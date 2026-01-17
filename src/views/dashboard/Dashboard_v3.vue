<template>
  <v-container id="dashboard" fluid tag="section">
    <!-- Statistics Cards -->
    <v-layout row wrap pt-4 style="padding-top: 20px !important;">
      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card 
          name=" المراجعين" 
          icon="fa-solid fa-hospital-user" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.patients" 
        />
      </v-flex>

      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card 
          name=" الحالات" 
          icon="fa-light fa-tooth" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.Casesall" 
        />
      </v-flex>

      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card 
          name="  المنتهيه" 
          icon="fa-solid fa-check" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.Casescompleted" 
        />
      </v-flex>

      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card 
          name="  غير المنتهيه" 
          icon="fas fa-layer-group" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.Casesactive" 
        />
      </v-flex>
    </v-layout>

    <!-- Charts Section -->
    <v-layout row wrap pt-4>
      <!-- Bar Chart -->
      <v-flex md-6 sm12>
        <v-card pa-3>
          <canvas id="bar-chart" ref="barChart"></canvas>
        </v-card>
      </v-flex>

      <!-- Today's Appointments -->
      <v-flex md-6 sm-6 xs12 pt-4>
        <v-card>
          <h2 style="text-align: center; font-family: 'Cairo' !important;" class="sssaw">
            مواعيد المراجعين اليوم
          </h2>
          <v-data-table 
            :headers="tableHeaders" 
            disable-filtering 
            disable-sort 
            :loading="isLoading" 
            :page.sync="page" 
            @page-count="pageCount = $event" 
            hide-default-footer 
            :items="todayBookings"
          />
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
/**
 * Dashboard Component
 * لوحة التحكم الرئيسية مع الإحصائيات والرسوم البيانية
 * 
 * @author Clinic Management System
 * @version 2.0.0 - Refactored with Services
 */

import { Chart, registerables } from 'chart.js'
import dash_card from '@/components/core/dash_card.vue'
import { DashboardService } from '@/services/api/services'

// تسجيل Chart.js
Chart.register(...registerables)

export default {
  name: 'Dashboard',

  components: {
    dash_card
  },

  data() {
    return {
      // حالة التحميل
      isLoading: false,

      // إحصائيات لوحة التحكم
      dashboardCounts: {
        patients: 0,
        Casesall: 0,
        Casescompleted: 0,
        Casesactive: 0
      },

      // بيانات الرسوم البيانية
      caseCategories: [],
      accountsStatistics: [],

      // مواعيد اليوم
      todayBookings: [],

      // إعدادات الجدول
      tableHeaders: [
        { text: '#id', align: 'start', value: 'id' },
        { text: 'الاسم', align: 'start', value: 'user.full_name' },
        { text: 'رقم الهاتف', align: 'start', value: 'user.user_phone' },
        { text: 'الوقت', align: 'start', value: 'reservation_from_time' }
      ],

      // الترقيم
      page: 1,
      pageCount: 0,

      // مراجع الرسوم البيانية
      barChart: null,
      pieChart: null
    }
  },

  computed: {
    /**
     * التحقق من وضع التطوير
     */
    isDevelopment() {
      return process.env.NODE_ENV === 'development'
    },

    /**
     * الحصول على Token الحجز
     */
    bookingToken() {
      return this.$store.state.AdminInfo?.tctate_token || ''
    }
  },

  mounted() {
    this.loadDashboardData()
  },

  beforeDestroy() {
    // تنظيف الرسوم البيانية
    this.destroyCharts()
  },

  methods: {
    /**
     * تحميل جميع بيانات لوحة التحكم
     */
    async loadDashboardData() {
      if (this.isLoading) return

      this.isLoading = true

      try {
        // تحميل جميع البيانات دفعة واحدة
        const data = await DashboardService.getAllDashboardData(this.bookingToken)

        // تحديث البيانات
        this.dashboardCounts = data.counts
        this.caseCategories = data.caseCategories
        this.accountsStatistics = data.accountsStats
        this.todayBookings = data.bookings

        // إنشاء الرسوم البيانية بعد تحميل البيانات
        this.$nextTick(() => {
          this.createCharts()
        })

      } catch (error) {
        console.error('❌ Error loading dashboard data:', error)
        this.showError('حدث خطأ في تحميل البيانات')
      } finally {
        this.isLoading = false
      }
    },

    /**
     * تحميل الإحصائيات فقط
     */
    async loadCounts() {
      try {
        this.dashboardCounts = await DashboardService.getCounts()
      } catch (error) {
        console.error('❌ Error loading counts:', error)
      }
    },

    /**
     * تحميل إحصائيات الحالات
     */
    async loadCaseCategories() {
      try {
        const response = await DashboardService.getCaseCategoriesCounts()
        this.caseCategories = response.data || response
      } catch (error) {
        console.error('❌ Error loading case categories:', error)
      }
    },

    /**
     * تحميل مواعيد اليوم
     */
    async loadTodayBookings() {
      try {
        const today = new Date()
        const dateString = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`
        this.todayBookings = await DashboardService.getTodayBookings(dateString, this.bookingToken)
      } catch (error) {
        console.error('❌ Error loading bookings:', error)
      }
    },

    /**
     * إنشاء الرسوم البيانية
     */
    createCharts() {
      this.createBarChart()
    },

    /**
     * إنشاء الرسم البياني العمودي
     */
    createBarChart() {
      const canvas = this.$refs.barChart
      if (!canvas) {
        console.warn('⚠️ Bar chart canvas not found')
        return
      }

      if (!this.caseCategories || this.caseCategories.length === 0) {
        console.warn('⚠️ No data available for bar chart')
        return
      }

      // تدمير الرسم البياني القديم إن وجد
      if (this.barChart) {
        this.barChart.destroy()
      }

      try {
        const ctx = canvas.getContext('2d')
        
        this.barChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: this.caseCategories.map(item => item.name_ar || item.name),
            datasets: [{
              label: 'عدد الحالات',
              data: this.caseCategories.map(item => item.case_count || item.count),
              backgroundColor: 'rgba(53, 113, 255, 0.6)',
              borderColor: 'rgba(53, 113, 255, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: {
                display: true,
                position: 'top',
                labels: {
                  font: {
                    family: 'Cairo'
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  font: {
                    family: 'Cairo'
                  }
                }
              },
              x: {
                ticks: {
                  font: {
                    family: 'Cairo'
                  }
                }
              }
            }
          }
        })
      } catch (error) {
        console.error('❌ Error creating bar chart:', error)
      }
    },

    /**
     * تدمير الرسوم البيانية
     */
    destroyCharts() {
      if (this.barChart) {
        this.barChart.destroy()
        this.barChart = null
      }
      if (this.pieChart) {
        this.pieChart.destroy()
        this.pieChart = null
      }
    },

    /**
     * عرض رسالة خطأ
     */
    showError(message) {
      // يمكن استخدام Snackbar أو Toast
      console.error(message)
    },

    /**
     * تحديث البيانات
     */
    refresh() {
      this.loadDashboardData()
    }
  }
}
</script>

<style scoped>
canvas {
  width: 100% !important;
  height: 400px !important;
}

.ma {
  display: flex;
  justify-content: center;
}

.sssaw {
  color: #075aa6 !important;
}
</style>
