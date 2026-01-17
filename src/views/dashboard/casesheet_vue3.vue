<template>
  <div class="casesheet-page">
    <!-- Owner Booking Dialog -->
    <OwnerBooking 
      v-if="showBookingDialog" 
      :patientFound="true" 
      :patientInfo="selectedPatient" 
      :doctors="doctors" 
    />

    <!-- Case Dialog -->
    <v-dialog v-model="showCaseDialog" max-width="1000px" v-if="caseCategories.length > 0">
      <CaseForm 
        :editedItem="selectedPatient" 
        :doctors="doctors" 
        :CaseCategories="caseCategories" 
        :gocase="goCase" 
      />
    </v-dialog>

    <!-- Bill Dialog -->
    <v-dialog v-model="showBillDialog" max-width="1000px">
      <BillReport :patient="selectedPatient" />
    </v-dialog>

    <!-- Recipe Dialog -->
    <v-dialog v-model="showRecipeDialog" max-width="900px" v-if="caseCategories.length > 0">
      <RecipeForm 
        :patients="patients" 
        :RecipeInfo="recipeInfo" 
        :recipes="recipes" 
        :CaseCategories="caseCategories" 
      />
    </v-dialog>

    <!-- WhatsApp Dialog -->
    <v-dialog v-model="showWhatsappDialog" max-width="500px">
      <v-card>
        <v-toolbar color="green" dark>
          <v-icon left>mdi-whatsapp</v-icon>
          <v-toolbar-title>إرسال رسالة واتساب</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="closeWhatsappDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        
        <v-card-text class="pt-4">
          <div class="patient-info mb-3">
            <p class="subtitle-1 mb-1">
              <strong>المراجع:</strong> {{ selectedPatient?.name || '' }}
            </p>
            <p class="subtitle-2 mb-1">
              <strong>الهاتف:</strong> {{ selectedPatient?.phone || '' }}
            </p>
          </div>
          
          <v-textarea
            v-model="whatsappMessage"
            label="الرسالة"
            rows="5"
            outlined
            counter
            :loading="sendingMessage"
          />
          
          <div class="d-flex mt-2 mb-2">
            <v-chip-group>
              <v-chip 
                @click="setQuickMessage('appointment')"
                color="green" 
                text-color="white"
              >
                تذكير بالموعد
              </v-chip>
              <v-chip 
                @click="setQuickMessage('payment')"
                color="green" 
                text-color="white"
              >
                تذكير بالدفع
              </v-chip>
              <v-chip 
                @click="setQuickMessage('followup')"
                color="green" 
                text-color="white"
              >
                متابعة الحالة
              </v-chip>
            </v-chip-group>
          </div>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text color="grey darken-1" @click="closeWhatsappDialog" :disabled="sendingMessage">
            إلغاء
          </v-btn>
          <v-btn 
            color="green" 
            dark 
            @click="sendWhatsappMessage"
            :loading="sendingMessage"
            :disabled="!whatsappMessage.trim()"
          >
            <v-icon left>mdi-open-in-new</v-icon>
            فتح واتساب
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Patient Card Component -->
    <PatientCard 
      v-model="showPatientCard" 
      :patient="selectedPatientForCard" 
    />

    <!-- Main Container -->
    <v-container id="dashboard" fluid tag="section">
      <v-data-table 
        :headers="tableHeaders"
        :items="patients"
        :loading="isLoading"
        loading-text="جاري تحميل البيانات..."
        :options.sync="tableOptions"
        :server-items-length="totalItems"
        :footer-props="{
          'items-per-page-options': [5, 10, 15, 20, 25],
          'items-per-page-text': $t('rows_per_page')
        }"
        @update:options="handleTableUpdate"
        class="elevation-1 request_table"
        @click:row="handleRowClick"
      >
        <!-- Loading Progress -->
        <template v-slot:progress>
          <v-progress-linear color="primary" indeterminate height="4" />
        </template>
        
        <template v-slot:loading>
          <div class="text-center pa-4">
            <v-progress-circular indeterminate color="primary" size="40" />
            <div class="mt-2">جاري تحميل البيانات...</div>
          </div>
        </template>
        
        <!-- Toolbar -->
        <template v-slot:top>
          <v-toolbar flat>
            <v-toolbar-title style="font-family: 'Cairo', sans-serif;">
              {{ $t("header.casesheet") }}
            </v-toolbar-title>

            <v-divider class="mx-4" inset vertical />
            <v-spacer />
            
            <!-- QR Scanner Button -->
            <v-btn 
              color="primary" 
              @click="openQRScanner" 
              dark 
              class="mb-2 ml-2"
            >
              <v-icon left>mdi-qrcode-scan</v-icon>
              مسح QR
            </v-btn>

            <!-- Patient Edit Dialog -->
            <PatientEditDialog
              v-model="showEditDialog"
              :patient="editedIndex > -1 ? editedItem : null"
              :doctors="doctors"
              :loading="isSaving"
              @save="savePatient"
              @close="closeEditDialog"
            />
            
            <!-- QR Scanner Component -->
            <QRScanner 
              v-model="showQRScanner" 
              @scanned="handleQRScanned"
            />
          </v-toolbar>

          <!-- Filters Row -->
          <v-row class="filters-row px-4">
            <v-col cols="12" sm="6" md="2">
              <v-text-field 
                ref="searchInput"
                v-model="searchQuery" 
                @keyup.enter="performSearch"
                :placeholder="$t('patients.Referencesnameorphonenumber')" 
                clearable
                dense
              />
            </v-col>

            <v-col cols="6" sm="3" md="1" class="d-flex align-center justify-center">
              <v-btn color="green" dark @click="performSearch">
                {{ $t("search") }}
              </v-btn>
            </v-col>

            <v-col cols="6" sm="3" md="1" class="d-flex align-center justify-center">
              <v-btn 
                v-if="isFiltered" 
                color="blue" 
                dark 
                @click="resetFilters"
              >
                {{ $t("all") }}
              </v-btn>
            </v-col>

            <v-col cols="12" sm="6" md="2">
              <v-select
                v-model="billingStatusFilter"
                :items="billingStatusOptions"
                item-text="text"
                item-value="value"
                label="حالة الدفع"
                @change="loadPatients"
                clearable
                dense
                outlined
              />
            </v-col>

            <v-col cols="12" sm="6" md="2" v-if="useCreditSystem">
              <v-select
                v-model="creditStatusFilter"
                :items="creditStatusOptions"
                item-text="text"
                item-value="value"
                label="حالة الرصيد"
                @change="loadPatients"
                clearable
                dense
                outlined
              />
            </v-col>
          </v-row>
        </template>

        <!-- Name Column -->
        <template v-slot:[`item.name`]="{ item }">
          <span @click="editPatient(item)" class="clickable">
            {{ item.name }}
          </span>
        </template>

        <!-- Phone Column -->
        <template v-slot:[`item.phone`]="{ item }">
          <p @click="editPatient(item)" style="direction: ltr; text-align: end;" class="clickable">
            {{ item.phone }}
          </p>
        </template>

        <!-- Age Column -->
        <template v-slot:[`item.age`]="{ item }">
          <p @click="editPatient(item)" style="direction: ltr; text-align: end;">
            {{ item.age }}
          </p>
        </template>

        <!-- Created At Column (Secretary Only) -->
        <template v-if="isSecretary" v-slot:[`item.created_at`]="{ item }">
          {{ formatDate(item.created_at) }}
        </template>

        <!-- Doctor Column -->
        <template v-slot:[`item.doctor`]="{ item }">
          <span>{{ getDoctorName(item) }}</span>
        </template>

        <!-- Recipe Button -->
        <template v-slot:[`item.Recipe`]="{ item }" v-if="canShowRx">
          <v-btn 
            @click.stop="openRecipeDialog(item)" 
            dense 
            color="#3b6a75"
            dark
            small
          >
            <i class="fas fa-prescription mr-1"></i>
            {{ $t("rx") }}
          </v-btn>
        </template>

        <!-- Booking Button -->
        <template v-slot:[`item.booking`]="{ item }">
          <v-btn 
            @click.stop="openBookingDialog(item)" 
            dense 
            color="#3b6a75"
            dark
            small
          >
            <v-icon left small>mdi-clock-outline</v-icon>
            {{ $t("patients.AppointmentBooking") }}
          </v-btn>
        </template>

        <!-- Bills Button -->
        <template v-slot:[`item.bills`]="{ item }" v-if="canShowAccounts">
          <v-btn 
            v-if="item.cases && item.cases.length > 0"
            @click.stop="openBillDialog(item)" 
            dense 
            color="#3b6a75"
            dark
            small
          >
            {{ $t("patients.account") }}
          </v-btn>
        </template>

        <!-- Actions Column -->
        <template v-slot:[`item.actions`]="{ item }">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon 
                class="ml-2" 
                @click.stop="openPatientCard(item)" 
                v-bind="attrs" 
                v-on="on"
                color="primary"
              >
                mdi-card
              </v-icon>
            </template>
            <span>بطاقة المراجع</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon 
                class="ml-2" 
                @click.stop="openWhatsappDialogForPatient(item)" 
                v-bind="attrs" 
                v-on="on"
                color="green"
              >
                mdi-whatsapp
              </v-icon>
            </template>
            <span>فتح واتساب</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon 
                class="ml-2" 
                @click.stop="editPatient(item)" 
                v-if="!item.isDeleted" 
                v-bind="attrs" 
                v-on="on"
              >
                mdi-pencil
              </v-icon>
            </template>
            <span>{{ $t("edit") }}</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon 
                class="ml-2" 
                @click.stop="deletePatient(item)" 
                v-if="!item.isDeleted" 
                v-bind="attrs" 
                v-on="on"
                color="error"
              >
                mdi-delete
              </v-icon>
            </template>
            <span>{{ $t("Delete") }}</span>
          </v-tooltip>
        </template>

        <!-- No Data -->
        <template v-slot:no-data>
          <v-btn color="primary" @click="loadPatients">
            {{ $t("Reloading") }}
          </v-btn>
        </template>
      </v-data-table>
    </v-container>

    <!-- Pagination -->
    <v-pagination 
      class="pagination mt-4" 
      total-visible="20" 
      v-model="currentPage" 
      color="#c7000b"
      circle 
      :length="pageCount"
      @input="handlePaginationChange"
    />
  </div>
</template>

<script setup>
/**
 * Casesheet Component - Vue 3 Composition API
 * صفحة إدارة المرضى والحالات
 * 
 * @author Clinic Management System
 * @version 3.0.0 - Vue 3 Composition API
 */

import { ref, reactive, computed, onMounted, watch, defineAsyncComponent } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Swal from 'sweetalert2'
import { PatientService, DoctorService, CaseService } from '@/services/api/services'

// ==================== Async Components ====================
const OwnerBooking = defineAsyncComponent(() => import('./sub_components/ownerBookinfDed.vue'))
const CaseForm = defineAsyncComponent(() => import('./case.vue'))
const RecipeForm = defineAsyncComponent(() => import('./Recipe.vue'))
const BillReport = defineAsyncComponent(() => import('./sub_components/billsReport.vue'))
const PatientEditDialog = defineAsyncComponent(() => import('@/components/PatientEditDialog.vue'))
const PatientCard = defineAsyncComponent(() => import('@/components/PatientCard.vue'))
const QRScanner = defineAsyncComponent(() => import('@/components/QRScanner.vue'))

// ==================== Composables ====================
const store = useStore()
const router = useRouter()
const { t } = useI18n()

// ==================== State ====================

// Loading States
const isLoading = ref(false)
const isSaving = ref(false)
const sendingMessage = ref(false)

// Pagination
const currentPage = ref(1)
const pageCount = ref(0)
const totalItems = ref(0)
const tableOptions = reactive({
  page: 1,
  itemsPerPage: 10
})

// Search & Filters
const searchQuery = ref('')
const isFiltered = ref(false)
const billingStatusFilter = ref(null)
const creditStatusFilter = ref(null)

// Data
const patients = ref([])
const doctors = ref([])
const caseCategories = ref([])
const recipes = ref([])

// Selected Items
const selectedPatient = ref({})
const selectedPatientForCard = ref({})
const editedItem = ref({})
const editedIndex = ref(-1)
const recipeInfo = reactive({
  rx_img: null,
  notes: '',
  case: { case_categories: '' }
})

// Dialogs
const showEditDialog = ref(false)
const showBookingDialog = ref(false)
const showCaseDialog = ref(false)
const showBillDialog = ref(false)
const showRecipeDialog = ref(false)
const showWhatsappDialog = ref(false)
const showPatientCard = ref(false)
const showQRScanner = ref(false)
const goCase = ref(false)

// WhatsApp
const whatsappMessage = ref('')

// ==================== Filter Options ====================
const billingStatusOptions = [
  { text: 'جميع المراجعين', value: null },
  { text: 'مدفوع بالكامل', value: 'all_paid' },
  { text: 'غير مدفوع نهائياً', value: 'none_paid' },
  { text: 'مدفوع جزئياً', value: 'some_paid' }
]

const creditStatusOptions = [
  { text: 'جميع المراجعين', value: null },
  { text: 'لديه رصيد', value: 'has_credit' },
  { text: 'ليس لديه رصيد', value: 'no_credit' }
]

// ==================== Computed ====================

const tableHeaders = computed(() => {
  const headers = [
    { text: t('datatable.name'), align: 'start', value: 'name' },
    { text: t('datatable.phone'), align: 'start', value: 'phone' },
    { text: t('datatable.age'), align: 'start', value: 'age' }
  ]

  if (isSecretary.value) {
    headers.push({ text: t('datatable.data_create'), align: 'start', value: 'created_at' })
  }

  if (isSecretary.value || userRole.value === 'adminDoctor' || userRole.value === 'accounter') {
    headers.push({ text: t('datatable.last_reservation_doctor'), align: 'start', value: 'doctor' })
  }

  headers.push(
    { text: '', value: 'Recipe', sortable: false },
    { text: '', value: 'booking', sortable: false },
    { text: t('Processes'), value: 'actions', sortable: false }
  )

  return headers
})

const userRole = computed(() => store.getters.userRole)
const isSecretary = computed(() => store.getters.isSecretary)
const useCreditSystem = computed(() => store.getters.useCreditSystem)
const canShowRx = computed(() => store.state.AdminInfo?.Permissions?.includes('show_rx'))
const canShowAccounts = computed(() => store.state.AdminInfo?.Permissions?.includes('show_accounts'))

// ==================== Methods ====================

/**
 * تحميل قائمة المرضى
 */
async function loadPatients() {
  isLoading.value = true
  
  try {
    const params = {
      page: tableOptions.page,
      limit: tableOptions.itemsPerPage,
      search: searchQuery.value,
      billing_status: billingStatusFilter.value,
      credit_status: creditStatusFilter.value
    }

    const response = await PatientService.getAll(params)
    
    patients.value = response.data || response
    totalItems.value = response.total || response.length
    pageCount.value = response.last_page || Math.ceil(totalItems.value / tableOptions.itemsPerPage)
    
  } catch (error) {
    console.error('❌ Error loading patients:', error)
    showErrorMessage('حدث خطأ في تحميل البيانات')
  } finally {
    isLoading.value = false
  }
}

/**
 * تحميل الأطباء
 */
async function loadDoctors() {
  try {
    const response = await DoctorService.getAll()
    doctors.value = response.data || response
  } catch (error) {
    console.error('❌ Error loading doctors:', error)
  }
}

/**
 * تحميل تصنيفات الحالات
 */
async function loadCaseCategories() {
  try {
    const response = await CaseService.getCategories()
    caseCategories.value = response.data || response
  } catch (error) {
    console.error('❌ Error loading case categories:', error)
  }
}

/**
 * البحث
 */
function performSearch() {
  isFiltered.value = true
  tableOptions.page = 1
  currentPage.value = 1
  loadPatients()
}

/**
 * إعادة تعيين الفلاتر
 */
function resetFilters() {
  searchQuery.value = ''
  billingStatusFilter.value = null
  creditStatusFilter.value = null
  isFiltered.value = false
  tableOptions.page = 1
  currentPage.value = 1
  loadPatients()
}

/**
 * التعامل مع تغيير الصفحة
 */
function handlePaginationChange(newPage) {
  tableOptions.page = newPage
  loadPatients()
}

/**
 * التعامل مع تحديث الجدول
 */
function handleTableUpdate(options) {
  if (options.page !== tableOptions.page || options.itemsPerPage !== tableOptions.itemsPerPage) {
    tableOptions.page = options.page
    tableOptions.itemsPerPage = options.itemsPerPage
    loadPatients()
  }
}

/**
 * النقر على صف
 */
function handleRowClick(item) {
  router.push(`/patient/${item.id}`)
}

/**
 * تعديل مريض
 */
function editPatient(item) {
  editedIndex.value = patients.value.indexOf(item)
  editedItem.value = { ...item }
  showEditDialog.value = true
}

/**
 * حفظ المريض
 */
async function savePatient(patientData) {
  isSaving.value = true
  
  try {
    if (editedIndex.value > -1) {
      await PatientService.update(patientData.id, patientData)
    } else {
      await PatientService.create(patientData)
    }
    
    await loadPatients()
    closeEditDialog()
    showSuccessMessage('تم الحفظ بنجاح')
    
  } catch (error) {
    console.error('❌ Error saving patient:', error)
    showErrorMessage('حدث خطأ في الحفظ')
  } finally {
    isSaving.value = false
  }
}

/**
 * حذف مريض
 */
async function deletePatient(item) {
  const result = await Swal.fire({
    title: 'هل أنت متأكد؟',
    text: 'لن تتمكن من التراجع عن هذا!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم، احذف!',
    cancelButtonText: 'إلغاء'
  })

  if (result.isConfirmed) {
    try {
      await PatientService.delete(item.id)
      await loadPatients()
      showSuccessMessage('تم الحذف بنجاح')
    } catch (error) {
      console.error('❌ Error deleting patient:', error)
      showErrorMessage('حدث خطأ في الحذف')
    }
  }
}

/**
 * إغلاق حوار التعديل
 */
function closeEditDialog() {
  showEditDialog.value = false
  editedIndex.value = -1
  editedItem.value = {}
}

/**
 * فتح حوار الحجز
 */
function openBookingDialog(patient) {
  selectedPatient.value = patient
  showBookingDialog.value = true
}

/**
 * فتح حوار الفاتورة
 */
function openBillDialog(patient) {
  selectedPatient.value = patient
  showBillDialog.value = true
}

/**
 * فتح حوار الوصفة
 */
function openRecipeDialog(patient) {
  selectedPatient.value = patient
  showRecipeDialog.value = true
}

/**
 * فتح بطاقة المريض
 */
function openPatientCard(patient) {
  selectedPatientForCard.value = {
    id: patient.id,
    name: patient.name,
    sex: patient.sex,
    qr_code: patient.qr_code,
    phone: patient.phone
  }
  showPatientCard.value = true
}

/**
 * فتح حوار واتساب
 */
function openWhatsappDialogForPatient(patient) {
  selectedPatient.value = patient
  whatsappMessage.value = ''
  showWhatsappDialog.value = true
}

/**
 * إغلاق حوار واتساب
 */
function closeWhatsappDialog() {
  showWhatsappDialog.value = false
  whatsappMessage.value = ''
}

/**
 * تعيين رسالة سريعة
 */
function setQuickMessage(type) {
  const messages = {
    appointment: 'السلام عليكم، هذه رسالة تذكيرية بموعدك في العيادة.',
    payment: 'السلام عليكم، نود تذكيركم بوجود مبلغ مستحق.',
    followup: 'السلام عليكم، نتمنى أن تكونوا بصحة جيدة. كيف حالكم بعد الزيارة؟'
  }
  whatsappMessage.value = messages[type] || ''
}

/**
 * إرسال رسالة واتساب
 */
function sendWhatsappMessage() {
  if (!selectedPatient.value?.phone || !whatsappMessage.value.trim()) return
  
  const phone = selectedPatient.value.phone.replace(/\D/g, '')
  const message = encodeURIComponent(whatsappMessage.value)
  const url = `https://wa.me/${phone}?text=${message}`
  
  window.open(url, '_blank')
  closeWhatsappDialog()
}

/**
 * فتح ماسح QR
 */
function openQRScanner() {
  showQRScanner.value = true
}

/**
 * التعامل مع نتيجة مسح QR
 */
async function handleQRScanned(result) {
  console.log('QR Scanned:', result)
  
  let qrCode = result
  
  try {
    if (result.includes('http') || result.includes('patient-lookup')) {
      const url = new URL(result)
      qrCode = url.searchParams.get('code') || result
    }
  } catch (e) {
    qrCode = result
  }
  
  // البحث عن المريض بـ QR code
  searchQuery.value = qrCode
  await performSearch()
}

/**
 * الحصول على اسم الطبيب
 */
function getDoctorName(item) {
  if (isSecretary.value && item.reservation?.length > 0) {
    const lastReservation = item.reservation[item.reservation.length - 1]
    if (lastReservation.doctor?.name) {
      return lastReservation.doctor.name
    }
  }
  
  if (item.doctors?.name) return item.doctors.name
  if (Array.isArray(item.doctors) && item.doctors[0]?.name) return item.doctors[0].name
  
  return ''
}

/**
 * تنسيق التاريخ
 */
function formatDate(dateString) {
  if (!dateString) return ''
  return dateString.slice(0, 10)
}

/**
 * عرض رسالة نجاح
 */
function showSuccessMessage(message) {
  Swal.fire({
    icon: 'success',
    title: 'نجاح',
    text: message,
    timer: 2000,
    showConfirmButton: false
  })
}

/**
 * عرض رسالة خطأ
 */
function showErrorMessage(message) {
  Swal.fire({
    icon: 'error',
    title: 'خطأ',
    text: message,
    confirmButtonText: 'حسناً'
  })
}

// ==================== Watchers ====================
watch(currentPage, (newPage) => {
  tableOptions.page = newPage
})

// ==================== Lifecycle ====================
onMounted(async () => {
  await Promise.all([
    loadPatients(),
    loadDoctors(),
    loadCaseCategories()
  ])
})
</script>

<style scoped>
.casesheet-page {
  padding-bottom: 60px;
}

.clickable {
  cursor: pointer;
}

.clickable:hover {
  color: #1976d2;
  text-decoration: underline;
}

.filters-row {
  background-color: #f5f5f5;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.pagination {
  position: relative;
  top: 20px;
}
</style>
