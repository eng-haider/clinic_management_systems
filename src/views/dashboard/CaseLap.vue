<template>
  <div>
    <v-container id="case-lap" fluid tag="section">
      <v-data-table 
        :headers="headers" 
        :items="filteredCases"
        :options.sync="options"
        :server-items-length="totalItems"
        :loading="loadingData"
        :items-per-page="itemsPerPage"
        :footer-props="{
          'items-per-page-options': [5, 10, 15, 20],
          'items-per-page-text': 'صفوف لكل صفحة'
        }"
        class="elevation-1 request_table"
      >
        <template v-slot:top>
          <v-toolbar flat>
            <v-toolbar-title style="font-family: 'Cairo', sans-serif;">
              {{ $t("Case Lap") || "حالات المختبر" }}
            </v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-btn 
              color="primary" 
              @click="fetchLapCases" 
              :loading="loadingData"
              :disabled="loadingData"
            >
              <v-icon left>mdi-refresh</v-icon>
              تحديث
            </v-btn>
          </v-toolbar>

          <!-- Search Filters -->
          <v-layout row wrap pa-5>
            <v-flex xs12 md3 sm6 pr-4>
              <v-select 
                dense 
                v-model="search.status" 
                :label="$t('status') || 'الحالة'" 
                clearable
                :items="statusOptions" 
                outlined 
                item-text="text" 
                item-value="value"
                @change="applyFilters"
              ></v-select>
            </v-flex>

            <v-flex xs12 md3 sm6 pr-4>
              <v-text-field
                dense
                outlined
                v-model="search.patientName"
                :label="$t('patient_name') || 'اسم المريض'"
                clearable
                @input="applyFilters"
              ></v-text-field>
            </v-flex>

            <v-flex xs12 md3 sm6 pr-4>
              <v-text-field
                dense
                outlined
                v-model="search.doctorName"
                :label="$t('doctor_name') || 'اسم الطبيب'"
                clearable
                @input="applyFilters"
              ></v-text-field>
            </v-flex>

            <v-flex xs12 md3 sm6 pr-4>
              <v-menu v-model="dateMenu" :close-on-content-click="false" :nudge-right="40"
                transition="scale-transition" offset-y min-width="auto">
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field 
                    dense 
                    outlined 
                    v-model="search.date" 
                    :label="$t('date') || 'التاريخ'"
                    v-bind="attrs" 
                    v-on="on"
                    clearable
                    @click:clear="search.date = null; applyFilters()"
                  ></v-text-field>
                </template>
                <v-date-picker 
                  v-model="search.date" 
                  no-title 
                  scrollable
                  @change="dateMenu = false; applyFilters()"
                >
                  <v-spacer></v-spacer>
                </v-date-picker>
              </v-menu>
            </v-flex>
          </v-layout>
        </template>

        <!-- Custom item rendering -->
        <template v-slot:item.patient_name="{ item }">
          <div v-if="item.Patient">
            <div class="font-weight-medium">{{ item.Patient.name }}</div>
            <div class="text-caption text--secondary">{{ item.Patient.phone }}</div>
          </div>
          <div v-else class="text--disabled">لا يوجد مريض</div>
        </template>

        <template v-slot:item.doctor="{ item }">
          <div v-if="item.doctor">
            <div class="font-weight-medium">{{ item.doctor.name }}</div>
            <div class="text-caption text--secondary" v-if="item.doctor.specialization">
              {{ item.doctor.specialization }}
            </div>
          </div>
        </template>

        <template v-slot:item.price="{ item }">
          <div class="text-right">
            <span class="font-weight-bold">{{ formatPrice(item.price) }}</span>
            <span class="text-caption"> دينار</span>
          </div>
        </template>

        <template v-slot:item.status="{ item }">
          <v-chip 
            :color="getStatusColor(item.status)" 
            dark 
            small
          >
            {{ getStatusText(item.status) }}
          </v-chip>
        </template>

        <template v-slot:item.created_at="{ item }">
          <div>
            <div>{{ formatDate(item.created_at) }}</div>
            <div class="text-caption text--secondary">{{ formatTime(item.created_at) }}</div>
          </div>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-icon
            small
            class="mr-2"
            @click="viewCaseDetails(item)"
          >
            mdi-eye
          </v-icon>
          <v-icon
            small
            @click="editCase(item)"
          >
            mdi-pencil
          </v-icon>
        </template>

        <!-- No data message -->
        <template v-slot:no-data>
          <div class="text-center pa-4">
            <v-icon size="64" color="grey lighten-1">mdi-flask-empty</v-icon>
            <div class="text-h6 grey--text mt-2">لا توجد حالات مختبر</div>
            <div class="text-body-2 grey--text">لم يتم العثور على أي حالات مختبر</div>
          </div>
        </template>
      </v-data-table>
    </v-container>

    <!-- Case Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="800px">
      <v-card v-if="selectedCase">
        <v-card-title>
          <span class="text-h5">تفاصيل حالة المختبر</span>
          <v-spacer></v-spacer>
          <v-btn icon @click="detailsDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-card outlined>
                <v-card-title class="text-h6">معلومات المريض</v-card-title>
                <v-card-text v-if="selectedCase.Patient">
                  <div><strong>الاسم:</strong> {{ selectedCase.Patient.name }}</div>
                  <div><strong>الهاتف:</strong> {{ selectedCase.Patient.phone }}</div>
                  <div><strong>العمر:</strong> {{ selectedCase.Patient.age }} سنة</div>
                  <div v-if="selectedCase.patient_id && selectedCase.patient_id.address">
                    <strong>العنوان:</strong> {{ selectedCase.patient_id.address }}
                  </div>
                  <div v-if="selectedCase.patient_id && selectedCase.patient_id.systemic_conditions">
                    <strong>الحالات المرضية:</strong> {{ selectedCase.patient_id.systemic_conditions }}
                  </div>
                </v-card-text>
                <v-card-text v-else>
                  <div class="text--disabled">لا توجد معلومات مريض</div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card outlined>
                <v-card-title class="text-h6">معلومات الطبيب</v-card-title>
                <v-card-text v-if="selectedCase.doctor">
                  <div><strong>الاسم:</strong> {{ selectedCase.doctor.name }}</div>
                  <div v-if="selectedCase.doctor.specialization">
                    <strong>التخصص:</strong> {{ selectedCase.doctor.specialization }}
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12">
              <v-card outlined>
                <v-card-title class="text-h6">تفاصيل الحالة</v-card-title>
                <v-card-text>
                  <div><strong>رقم الحالة:</strong> {{ selectedCase.case_id }}</div>
                  <div><strong>الحالة:</strong> 
                    <v-chip 
                      :color="getStatusColor(selectedCase.status)" 
                      dark 
                      small
                    >
                      {{ getStatusText(selectedCase.status) }}
                    </v-chip>
                  </div>
                  <div><strong>السعر:</strong> {{ formatPrice(selectedCase.price) }} دينار</div>
                  <div><strong>تاريخ الإنشاء:</strong> {{ formatDateTime(selectedCase.created_at) }}</div>
                  <div v-if="selectedCase.updated_at !== selectedCase.created_at">
                    <strong>آخر تحديث:</strong> {{ formatDateTime(selectedCase.updated_at) }}
                  </div>
                  <div v-if="selectedCase.stage">
                    <strong>المرحلة:</strong> {{ selectedCase.stage }}
                  </div>
                  <div v-if="selectedCase.note">
                    <strong>الملاحظات:</strong> {{ selectedCase.note }}
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" text @click="detailsDialog = false">
            إغلاق
          </v-btn>
          <v-btn color="primary" @click="editCase(selectedCase)">
            تعديل
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Loading overlay -->
    <v-overlay :value="loadingData">
      <div class="text-center">
        <v-progress-circular
          indeterminate
          size="64"
          color="primary"
        ></v-progress-circular>
        <div class="mt-4 white--text">جاري تحميل حالات المختبر...</div>
      </div>
    </v-overlay>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'CaseLap',
  data() {
    return {
      lapCases: [],
      filteredCases: [],
      loadingData: false,
      detailsDialog: false,
      dateMenu: false,
      selectedCase: null,
      totalItems: 0,
      itemsPerPage: 15,
      options: {},
      search: {
        status: null,
        patientName: '',
        doctorName: '',
        date: null
      },
      headers: [
        {
          text: 'رقم الحالة',
          align: 'center',
          sortable: true,
          value: 'case_id',
          width: '120px'
        },
        {
          text: 'المريض',
          align: 'start',
          sortable: false,
          value: 'patient_name',
          width: '200px'
        },
        {
          text: 'الطبيب',
          align: 'start',
          sortable: false,
          value: 'doctor',
          width: '180px'
        },
        {
          text: 'السعر',
          align: 'center',
          sortable: true,
          value: 'price',
          width: '120px'
        },
        {
          text: 'الحالة',
          align: 'center',
          sortable: true,
          value: 'status',
          width: '120px'
        },
        {
          text: 'تاريخ الإنشاء',
          align: 'center',
          sortable: true,
          value: 'created_at',
          width: '150px'
        },
        {
          text: 'الإجراءات',
          align: 'center',
          sortable: false,
          value: 'actions',
          width: '100px'
        }
      ],
      statusOptions: [
        { text: 'في الانتظار', value: 'pending' },
        { text: 'قيد التنفيذ', value: 'in_progress' },
        { text: 'مكتملة', value: 'completed' },
        { text: 'ملغية', value: 'cancelled' }
      ]
    }
  },
  computed: {
    ...mapGetters(['userPermissions', 'isAuthenticated']),
    hasLapCasesPermission() {
      return this.userPermissions.some(permission => 
        permission.name === 'show_lap_cases'
      )
    }
  },
  watch: {
    options: {
      handler() {
        this.applyFilters()
      },
      deep: true
    }
  },
  async mounted() {
    try {
    //   if (!this.hasLapCasesPermission) {
    //     this.$router.push({ name: 'statistics' })
    //     return
    //   }
      // Automatically load data when component mounts
      await this.fetchLapCases()
    } catch (error) {
      console.error('Error during component mount:', error)
    }
  },
  methods: {
    async fetchLapCases() {
      this.loadingData = true
      try {
        console.log('Fetching lab cases...')
        const response = await this.$http.get('lap-cases')
        
        if (response.data && response.data.success) {
          this.lapCases = response.data.data || []
          this.totalItems = response.data.pagination?.total || this.lapCases.length
          this.filteredCases = [...this.lapCases] // Initialize filtered cases
          this.applyFilters()
          console.log(`Loaded ${this.lapCases.length} lab cases`)
        } else if (response.data && Array.isArray(response.data)) {
          // Handle case where API returns array directly
          this.lapCases = response.data
          this.totalItems = this.lapCases.length
          this.filteredCases = [...this.lapCases]
          this.applyFilters()
          console.log(`Loaded ${this.lapCases.length} lab cases (direct array)`)
        } else {
          this.$swal.fire({
            icon: 'warning',
            title: 'تنبيه',
            text: 'لا توجد حالات مختبر متاحة',
            confirmButtonText: 'موافق'
          })
          this.lapCases = []
          this.filteredCases = []
          this.totalItems = 0
        }
      } catch (error) {
        console.error('Error fetching lap cases:', error)
        this.$swal.fire({
          icon: 'error',
          title: 'خطأ',
          text: 'حدث خطأ أثناء تحميل البيانات',
          confirmButtonText: 'موافق'
        })
        // Initialize empty arrays to prevent undefined errors
        this.lapCases = []
        this.filteredCases = []
        this.totalItems = 0
      } finally {
        this.loadingData = false
      }
    },
    
    applyFilters() {
      let filtered = [...this.lapCases]
      
      // Filter by status
      if (this.search.status) {
        filtered = filtered.filter(item => item.status === this.search.status)
      }
      
      // Filter by patient name
      if (this.search.patientName) {
        filtered = filtered.filter(item => 
          item.Patient && 
          item.Patient.name.toLowerCase().includes(this.search.patientName.toLowerCase())
        )
      }
      
      // Filter by doctor name
      if (this.search.doctorName) {
        filtered = filtered.filter(item => 
          item.doctor && 
          item.doctor.name.toLowerCase().includes(this.search.doctorName.toLowerCase())
        )
      }
      
      // Filter by date
      if (this.search.date) {
        filtered = filtered.filter(item => 
          item.created_at.startsWith(this.search.date)
        )
      }
      
      this.filteredCases = filtered
      this.totalItems = filtered.length
    },
    
    viewCaseDetails(item) {
      this.selectedCase = item
      this.detailsDialog = true
    },
    
    editCase(item) {
      // Navigate to case edit page or open edit dialog
      // For now, just show a message
      console.log('Edit case:', item.id)
      this.$swal.fire({
        icon: 'info',
        title: 'معلومات',
        text: 'ميزة التعديل قيد التطوير',
        confirmButtonText: 'موافق'
      })
    },
    
    getStatusColor(status) {
      const colors = {
        pending: 'orange',
        in_progress: 'blue',
        completed: 'green',
        cancelled: 'red'
      }
      return colors[status] || 'grey'
    },
    
    getStatusText(status) {
      const texts = {
        pending: 'في الانتظار',
        in_progress: 'قيد التنفيذ',
        completed: 'مكتملة',
        cancelled: 'ملغية'
      }
      return texts[status] || status
    },
    
    formatPrice(price) {
      return parseFloat(price).toLocaleString('ar-IQ')
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('ar-IQ')
    },
    
    formatTime(dateString) {
      return new Date(dateString).toLocaleTimeString('ar-IQ', {
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    formatDateTime(dateString) {
      const date = new Date(dateString)
      return `${date.toLocaleDateString('ar-IQ')} ${date.toLocaleTimeString('ar-IQ', {
        hour: '2-digit',
        minute: '2-digit'
      })}`
    }
  }
}
</script>

<style scoped>
.request_table {
  direction: rtl;
}

.v-data-table >>> .v-data-table__wrapper {
  direction: rtl;
}

.v-data-table >>> th {
  text-align: center !important;
  font-family: 'Cairo', sans-serif;
  font-weight: 600;
}

.v-data-table >>> td {
  text-align: center !important;
  font-family: 'Cairo', sans-serif;
}

.v-toolbar-title {
  font-weight: 600;
  color: #1976d2;
}

.elevation-1 {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.v-chip {
  font-family: 'Cairo', sans-serif;
  font-weight: 500;
}

.v-card {
  border-radius: 8px;
}

.v-card-title {
  background-color: #f5f5f5;
  border-bottom: 1px solid #e0e0e0;
}
</style>
