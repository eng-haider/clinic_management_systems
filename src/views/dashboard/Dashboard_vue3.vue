<template>
  <v-container id="dashboard" fluid tag="section">
    <!-- Statistics Cards -->
    <v-row class="pt-4" style="padding-top: 20px !important;">
      <v-col cols="6" md="3" sm="6" class="pr-2 pb-4 pt-4">
        <dash_card 
          name=" المراجعين" 
          icon="fa-solid fa-hospital-user" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.patients" 
        />
      </v-col>

      <v-col cols="6" md="3" sm="6" class="pr-2 pb-4 pt-4">
        <dash_card 
          name=" الحالات" 
          icon="fa-light fa-tooth" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.Casesall" 
        />
      </v-col>

      <v-col cols="6" md="3" sm="6" class="pr-2 pb-4 pt-4">
        <dash_card 
          name="  المنتهيه" 
          icon="fa-solid fa-check" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.Casescompleted" 
        />
      </v-col>

      <v-col cols="6" md="3" sm="6" class="pr-2 pb-4 pt-4">
        <dash_card 
          name="  غير المنتهيه" 
          icon="fas fa-layer-group" 
          text_color="#53D3F8" 
          icon_color="#035aa6" 
          :count="dashboardCounts.Casesactive" 
        />
      </v-col>
    </v-row>

    <!-- Charts Section -->
    <v-row class="pt-4">
      <!-- Bar Chart -->
      <v-col md="6" cols="12">
        <v-card class="pa-3">
          <canvas id="bar-chart" ref="barChartRef"></canvas>
        </v-card>
      </v-col>

      <!-- Today's Appointments -->
      <v-col md="6" sm="6" cols="12" class="pt-4">
        <v-card>
          <h2 style="text-align: center; font-family: 'Cairo' !important;" class="sssaw">
            مواعيد المراجعين اليوم
          </h2>
          <v-data-table 
            :headers="tableHeaders" 
            disable-filtering 
            disable-sort 
            :loading="isLoading" 
            :page="page"
            hide-default-footer 
            :items="todayBookings"
            @update:page="page = $event"
          />
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
/**
 * Dashboard Component - Vue 3 Composition API
 * لوحة التحكم الرئيسية مع الإحصائيات والرسوم البيانية
 * 
 * @author Clinic Management System
 * @version 3.0.0 - Vue 3 Composition API
 */

import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, defineExpose } from 'vue'
import { useStore } from 'vuex'
import { Chart, registerables } from 'chart.js'
import DashCard from '@/components/core/dash_card.vue'
import { DashboardService } from '@/services/api/services'

// تسجيل Chart.js
Chart.register(...registerables)

// ==================== Components ====================
// eslint-disable-next-line no-unused-vars
const dash_card = DashCard

// ==================== Store ====================
const store = useStore()

// ==================== Refs ====================
const barChartRef = ref(null)

// ==================== Reactive State ====================
const isLoading = ref(false)
const page = ref(1)
const pageCount = ref(0)

// إحصائيات لوحة التحكم
const dashboardCounts = reactive({
  patients: 0,
  Casesall: 0,
  Casescompleted: 0,
  Casesactive: 0
})

// بيانات الرسوم البيانية
const caseCategories = ref([])
const accountsStatistics = ref([])

// مواعيد اليوم
const todayBookings = ref([])

// مراجع الرسوم البيانية
let barChart = null
let pieChart = null

// ==================== Table Headers ====================
const tableHeaders = [
  { text: '#id', align: 'start', value: 'id' },
  { text: 'الاسم', align: 'start', value: 'user.full_name' },
  { text: 'رقم الهاتف', align: 'start', value: 'user.user_phone' },
  { text: 'الوقت', align: 'start', value: 'reservation_from_time' }
]

// ==================== Computed ====================
/**
 * التحقق من وضع التطوير
 */
const isDevelopment = computed(() => {
  return import.meta.env.DEV || process.env.NODE_ENV === 'development'
})

/**
 * الحصول على Token الحجز
 */
const bookingToken = computed(() => {
  return store.state.AdminInfo?.tctate_token || ''
})

// ==================== Methods ====================

/**
 * تحميل جميع بيانات لوحة التحكم
 */
async function loadDashboardData() {
  if (isLoading.value) return

  isLoading.value = true

  try {
    // تحميل جميع البيانات دفعة واحدة
    const data = await DashboardService.getAllDashboardData(bookingToken.value)

    // تحديث البيانات
    Object.assign(dashboardCounts, data.counts)
    caseCategories.value = data.caseCategories
    accountsStatistics.value = data.accountsStats
    todayBookings.value = data.bookings

    // إنشاء الرسوم البيانية بعد تحميل البيانات
    await nextTick()
    createCharts()

  } catch (error) {
    console.error('❌ Error loading dashboard data:', error)
    showError('حدث خطأ في تحميل البيانات')
  } finally {
    isLoading.value = false
  }
}

/**
 * تحميل الإحصائيات فقط
 */
async function loadCounts() {
  try {
    const counts = await DashboardService.getCounts()
    Object.assign(dashboardCounts, counts)
  } catch (error) {
    console.error('❌ Error loading counts:', error)
  }
}

/**
 * تحميل إحصائيات الحالات
 */
async function loadCaseCategories() {
  try {
    const response = await DashboardService.getCaseCategoriesCounts()
    caseCategories.value = response.data || response
  } catch (error) {
    console.error('❌ Error loading case categories:', error)
  }
}

/**
 * تحميل مواعيد اليوم
 */
async function loadTodayBookings() {
  try {
    const today = new Date()
    const dateString = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`
    todayBookings.value = await DashboardService.getTodayBookings(dateString, bookingToken.value)
  } catch (error) {
    console.error('❌ Error loading bookings:', error)
  }
}

/**
 * إنشاء الرسوم البيانية
 */
function createCharts() {
  createBarChart()
}

/**
 * إنشاء الرسم البياني العمودي
 */
function createBarChart() {
  const canvas = barChartRef.value
  if (!canvas) {
    console.warn('⚠️ Bar chart canvas not found')
    return
  }

  if (!caseCategories.value || caseCategories.value.length === 0) {
    console.warn('⚠️ No data available for bar chart')
    return
  }

  // تدمير الرسم البياني القديم إن وجد
  if (barChart) {
    barChart.destroy()
  }

  try {
    const ctx = canvas.getContext('2d')
    
    barChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: caseCategories.value.map(item => item.name_ar || item.name),
        datasets: [{
          label: 'عدد الحالات',
          data: caseCategories.value.map(item => item.case_count || item.count),
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
}

/**
 * تدمير الرسوم البيانية
 */
function destroyCharts() {
  if (barChart) {
    barChart.destroy()
    barChart = null
  }
  if (pieChart) {
    pieChart.destroy()
    pieChart = null
  }
}

/**
 * عرض رسالة خطأ
 */
function showError(message) {
  console.error(message)
}

/**
 * تحديث البيانات
 */
function refresh() {
  loadDashboardData()
}

// ==================== Lifecycle ====================
onMounted(() => {
  loadDashboardData()
})

onBeforeUnmount(() => {
  destroyCharts()
})

// ==================== Expose ====================
// تصدير الدوال للاستخدام الخارجي (اختياري)
defineExpose({
  refresh,
  loadDashboardData
})
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
