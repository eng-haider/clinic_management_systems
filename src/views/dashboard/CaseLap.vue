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
                :label="$t('patient_name') || 'اسم المراجع'"
                clearable
                @input="applyFilters"
              ></v-text-field>
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
          <div class="d-flex align-center">
            <v-chip 
              :color="getStatusColor(item.status)" 
              dark 
              small
              class="mr-2"
            >
              {{ getStatusText(item.status) }}
            </v-chip>
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  x-small
                  v-bind="attrs"
                  v-on="on"
                  :disabled="updating"
                >
                  <v-icon small>mdi-chevron-down</v-icon>
                </v-btn>
              </template>
              <v-list dense>
                <v-list-item
                  v-for="status in statusOptions"
                  :key="status.value"
                  @click="quickStatusUpdate(item, status.value)"
                  :disabled="item.status === status.value || updating"
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ status.text }}</v-list-item-title>
                  </v-list-item-content>
                  <v-list-item-icon v-if="item.status === status.value">
                    <v-icon small color="primary">mdi-check</v-icon>
                  </v-list-item-icon>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
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

    <!-- Edit Status Dialog -->
    <v-dialog v-model="editDialog" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="text-h5">تعديل حالة المختبر</span>
          <v-spacer></v-spacer>
          <v-btn icon @click="closeEditDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text>
          <v-form ref="editForm" v-if="editingCase">
            <v-row>
              <v-col cols="12">
                <div class="mb-4">
                  <strong>رقم الحالة:</strong> {{ editingCase.case_id }}
                </div>
                <div class="mb-4" v-if="editingCase.Patient">
                  <strong>المراجع:</strong> {{ editingCase.Patient.name }}
                </div>
              </v-col>

              <v-col cols="12">
                <v-select
                  v-model="editForm.status"
                  :items="statusOptions"
                  item-text="text"
                  item-value="value"
                  label="الحالة"
                  outlined
                  dense
                  :rules="[v => !!v || 'الحالة مطلوبة']"
                  required
                ></v-select>
              </v-col>

          
              <v-col cols="12">
                <v-text-field
                  v-model="editForm.price"
                  label="السعر"
                  outlined
                  dense
                  suffix="دينار"
                  @input="formatPriceInput"
                  @blur="validatePrice"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="editForm.note"
                  label="الملاحظات"
                  outlined
                  dense
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" text @click="closeEditDialog" :disabled="updating">
            إلغاء
          </v-btn>
          <v-btn 
            color="primary" 
            @click="updateLapCaseStatus" 
            :loading="updating"
            :disabled="updating"
          >
            حفظ التغييرات
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
                <v-card-title class="text-h6">معلومات المراجع</v-card-title>
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
      editDialog: false,
      dateMenu: false,
      selectedCase: null,
      editingCase: null,
      updating: false,
      totalItems: 0,
      itemsPerPage: 15,
      options: {},
      search: {
        status: null,
        patientName: '',
        doctorName: '',
        date: null
      },
      editForm: {
        status: '',
        stage: '',
        price: '',
        note: ''
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
          text: 'المراجع',
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
    ...mapGetters(['userPermissions', 'isAuthenticated', 'canShowLapCases']),
    hasLapCasesPermission() {
      return this.canShowLapCases
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
      if (!this.hasLapCasesPermission) {
        this.$router.push({ name: 'statistics' })
        return
      }
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
        const response = await this.$http.get('https://mina-api.tctate.com/api/lap-cases')
        
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
      this.editingCase = item
      this.editForm = {
        status: item.status || '',
        stage: item.stage || '',
        price: item.price || '',
        note: item.note || ''
      }
      this.editDialog = true
    },

    closeEditDialog() {
      this.editDialog = false
      this.editingCase = null
      this.editForm = {
        status: '',
        stage: '',
        price: '',
        note: ''
      }
      if (this.$refs.editForm) {
        this.$refs.editForm.resetValidation()
      }
    },

    async quickStatusUpdate(item, newStatus) {
      if (item.status === newStatus) {
        return
      }

      this.updating = true
      try {
        const response = await this.$http.patch(
          `https://mina-api.tctate.com/api/lap-cases/${item.id}/status`,
          { status: newStatus }
        )

        if (response.data && response.data.success) {
          // Update the local data
          const updatedCase = response.data.data
          const index = this.lapCases.findIndex(c => c.id === item.id)
          
          if (index !== -1) {
            this.$set(this.lapCases, index, updatedCase)
            this.applyFilters()
          }

          // Show quick success notification
          this.$swal.fire({
            icon: 'success',
            title: 'تم التحديث',
            text: `تم تغيير الحالة إلى: ${this.getStatusText(newStatus)}`,
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
          })
        }
      } catch (error) {
        console.error('Error in quick status update:', error)
        
        let errorMessage = 'فشل في تحديث الحالة'
        if (error.response && error.response.status === 401) {
          errorMessage = 'غير مخول للقيام بهذا الإجراء'
        }

        this.$swal.fire({
          icon: 'error',
          title: 'خطأ',
          text: errorMessage,
          confirmButtonText: 'موافق'
        })
      } finally {
        this.updating = false
      }
    },

    async updateLapCaseStatus() {
      if (!this.$refs.editForm.validate()) {
        return
      }

      this.updating = true
      try {
        const updateData = {
          status: this.editForm.status
        }

        // Only include optional fields if they have values
        if (this.editForm.stage && this.editForm.stage.trim()) {
          updateData.stage = this.editForm.stage.trim()
        }
        
        if (this.editForm.price && parseFloat(this.editForm.price) > 0) {
          updateData.price = parseFloat(this.editForm.price)
        }
        
        if (this.editForm.note && this.editForm.note.trim()) {
          updateData.note = this.editForm.note.trim()
        }

        console.log('Updating lap case:', this.editingCase.id, updateData)
        
        const response = await this.$http.patch(
          `https://mina-api.tctate.com/api/lap-cases/${this.editingCase.id}/status`,
          updateData
        )

        if (response.data && response.data.success) {
          // Update the local data
          const updatedCase = response.data.data
          const index = this.lapCases.findIndex(c => c.id === this.editingCase.id)
          
          if (index !== -1) {
            // Update the case in the original array
            this.$set(this.lapCases, index, updatedCase)
            // Reapply filters to update the filtered view
            this.applyFilters()
          }

          this.$swal.fire({
            icon: 'success',
            title: 'تم بنجاح',
            text: 'تم تحديث حالة المختبر بنجاح',
            confirmButtonText: 'موافق'
          })

          this.closeEditDialog()
        } else {
          throw new Error('Failed to update lap case')
        }
      } catch (error) {
        console.error('Error updating lap case:', error)
        
        let errorMessage = 'حدث خطأ أثناء تحديث الحالة'
        
        if (error.response) {
          if (error.response.status === 401) {
            errorMessage = 'غير مخول للقيام بهذا الإجراء'
          } else if (error.response.status === 404) {
            errorMessage = 'لم يتم العثور على الحالة'
          } else if (error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message
          }
        }

        this.$swal.fire({
          icon: 'error',
          title: 'خطأ',
          text: errorMessage,
          confirmButtonText: 'موافق'
        })
      } finally {
        this.updating = false
      }
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
      if (!price || price === '' || price === null || price === undefined) {
        return '0';
      }
      // Remove any existing commas first
      const cleanPrice = price.toString().replace(/,/g, '');
      const numericPrice = parseFloat(cleanPrice);
      if (isNaN(numericPrice)) {
        return '0';
      }
      return numericPrice.toLocaleString('en-US');
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
