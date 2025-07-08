<template>
  <v-container id="patient-management" fluid tag="section">
    <!-- Header Section -->
    <v-card class="mb-4">
      <v-toolbar flat>
        <v-toolbar-title style="font-family: 'Cairo', sans-serif;">
          <i class="fas fa-user-injured mr-2"></i>
          {{ $t("patients.management") }}
        </v-toolbar-title>
        <v-spacer></v-spacer>
        
        <!-- Add Patient Button -->
        <v-btn color="primary" @click="openAddDialog" dark class="mb-2">
          <i class="fas fa-plus mr-2"></i>
          {{ $t("patients.addnewpatients") }}
        </v-btn>
      </v-toolbar>
    </v-card>

    <!-- Search and Filter Section -->
    <v-card class="mb-4">
      <v-card-text>
        <v-layout row wrap>
          <v-flex xs12 md4 sm6 pr-2>
            <v-text-field 
              v-model="search" 
              @keyup.enter="searchPatients"
              :placeholder="$t('patients.Referencesnameorphonenumber')"
              prepend-inner-icon="mdi-magnify"
              outlined
              clearable
              dense
            />
          </v-flex>
          
          <v-flex xs12 md3 sm6 pr-2>
            <v-select
              v-model="selectedDoctor"
              :items="doctors"
              :label="$t('doctor')"
              item-text="name"
              item-value="id"
              outlined
              dense
              clearable
            />
          </v-flex>
          
          <v-flex xs12 md3 sm6 pr-2>
            <v-select
              v-model="ageFilter"
              :items="ageRanges"
              :label="$t('datatable.age')"
              outlined
              dense
              clearable
            />
          </v-flex>
          
          <v-flex xs12 md2 sm6>
            <v-btn color="primary" @click="searchPatients" block>
              {{ $t("search") }}
            </v-btn>
          </v-flex>
        </v-layout>
      </v-card-text>
    </v-card>

    <!-- Statistics Cards -->
    <v-layout row wrap class="mb-4">
      <v-flex xs6 md3 sm6 pr-2>
        <v-card class="text-center pa-4" color="blue lighten-5">
          <v-icon color="blue" size="40">fas fa-users</v-icon>
          <h3 class="mt-2 blue--text">{{ totalPatients }}</h3>
          <p class="mb-0">{{ $t("patients.total") }}</p>
        </v-card>
      </v-flex>
      
      <v-flex xs6 md3 sm6 pr-2>
        <v-card class="text-center pa-4" color="green lighten-5">
          <v-icon color="green" size="40">fas fa-calendar-check</v-icon>
          <h3 class="mt-2 green--text">{{ todayAppointments }}</h3>
          <p class="mb-0">{{ $t("patients.today_appointments") }}</p>
        </v-card>
      </v-flex>
      
      <v-flex xs6 md3 sm6 pr-2>
        <v-card class="text-center pa-4" color="orange lighten-5">
          <v-icon color="orange" size="40">fas fa-clock</v-icon>
          <h3 class="mt-2 orange--text">{{ waitingPatients }}</h3>
          <p class="mb-0">{{ $t("patients.waiting") }}</p>
        </v-card>
      </v-flex>
      
      <v-flex xs6 md3 sm6>
        <v-card class="text-center pa-4" color="red lighten-5">
          <v-icon color="red" size="40">fas fa-exclamation-triangle</v-icon>
          <h3 class="mt-2 red--text">{{ emergencyPatients }}</h3>
          <p class="mb-0">{{ $t("patients.emergency") }}</p>
        </v-card>
      </v-flex>
    </v-layout>

    <!-- Patients Data Table -->
    <v-card>
      <v-data-table
        :headers="headers"
        :items="patients"
        :loading="loading"
        :server-items-length="totalItems"
        :options.sync="options"
        @update:options="loadPatients"
        class="elevation-1"
        item-key="id"
      >
        <!-- Custom slots for table columns -->
        <template v-slot:item.avatar="{ item }">
          <v-avatar size="40" class="my-2">
            <v-img v-if="item.avatar" :src="item.avatar" />
            <v-icon v-else color="grey">fas fa-user</v-icon>
          </v-avatar>
        </template>

        <template v-slot:item.sex="{ item }">
          <v-chip :color="item.sex === 1 ? 'pink' : 'blue'" small text-color="white">
            {{ item.sex === 1 ? $t('sex.female') : $t('sex.male') }}
          </v-chip>
        </template>

        <template v-slot:item.age="{ item }">
          <span>{{ calculateAge(item.birth_date) }}</span>
        </template>

        <template v-slot:item.last_visit="{ item }">
          <span>{{ formatDate(item.last_visit) }}</span>
        </template>

        <template v-slot:item.status="{ item }">
          <v-chip :color="getStatusColor(item.status)" small text-color="white">
            {{ getStatusText(item.status) }}
          </v-chip>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small @click="viewPatient(item)" v-bind="attrs" v-on="on">
                <v-icon color="blue">fas fa-eye</v-icon>
              </v-btn>
            </template>
            <span>{{ $t('view') }}</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small @click="editPatient(item)" v-bind="attrs" v-on="on">
                <v-icon color="green">fas fa-edit</v-icon>
              </v-btn>
            </template>
            <span>{{ $t('edit') }}</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small @click="openCaseSheet(item)" v-bind="attrs" v-on="on">
                <v-icon color="orange">fas fa-file-medical</v-icon>
              </v-btn>
            </template>
            <span>{{ $t('casesheet') }}</span>
          </v-tooltip>

          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon small @click="deletePatient(item)" v-bind="attrs" v-on="on">
                <v-icon color="red">fas fa-trash</v-icon>
              </v-btn>
            </template>
            <span>{{ $t('delete') }}</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Add/Edit Patient Dialog -->
    <v-dialog v-model="patientDialog" max-width="900px" persistent>
      <v-form ref="patientForm" v-model="valid">
        <v-card>
          <v-toolbar dark color="primary">
            <v-toolbar-title>
              <h3 style="color:#fff;font-family: 'Cairo'">
                {{ editMode ? $t('patients.edit') : $t('patients.add') }}
              </h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="closePatientDialog" icon>
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text class="pa-6">
            <v-container>
              <v-row>
                <!-- Patient Photo -->
                <v-col cols="12" class="text-center">
                  <v-avatar size="100" class="mb-4">
                    <v-img v-if="editedPatient.avatar" :src="editedPatient.avatar" />
                    <v-icon v-else color="grey" size="60">fas fa-user</v-icon>
                  </v-avatar>
                  <br>
                  <v-btn small color="primary" @click="$refs.fileInput.click()">
                    <v-icon small left>fas fa-camera</v-icon>
                    {{ $t('patients.upload_photo') }}
                  </v-btn>
                  <input 
                    ref="fileInput" 
                    type="file" 
                    accept="image/*" 
                    style="display: none" 
                    @change="uploadPhoto"
                  />
                </v-col>

                <!-- Personal Information -->
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedPatient.name"
                    :rules="[rules.required]"
                    :label="$t('datatable.name')"
                    outlined
                    dense
                    style="direction: rtl;text-align: right;"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedPatient.phone"
                    :rules="[rules.required, rules.phone]"
                    :label="$t('datatable.phone')"
                    outlined
                    dense
                    placeholder="07XX XXX XXXX"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedPatient.birth_date"
                    :rules="[rules.required]"
                    :label="$t('datatable.birth_date')"
                    type="date"
                    outlined
                    dense
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="calculatedAge"
                    :label="$t('datatable.age')"
                    readonly
                    outlined
                    dense
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-radio-group v-model="editedPatient.sex" row>
                    <v-radio :label="$t('sex.female')" :value="1"></v-radio>
                    <v-radio :label="$t('sex.male')" :value="0"></v-radio>
                  </v-radio-group>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedPatient.address"
                    :label="$t('datatable.address')"
                    outlined
                    dense
                    style="direction: rtl;text-align: right;"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedPatient.email"
                    :rules="[rules.email]"
                    :label="$t('datatable.email')"
                    outlined
                    dense
                    type="email"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editedPatient.emergency_contact"
                    :label="$t('patients.emergency_contact')"
                    outlined
                    dense
                  />
                </v-col>

                <!-- Medical Information -->
                <v-col cols="12">
                  <v-divider class="my-3" />
                  <h4 class="mb-3">{{ $t('patients.medical_information') }}</h4>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="editedPatient.blood_type"
                    :items="bloodTypes"
                    :label="$t('patients.blood_type')"
                    outlined
                    dense
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="editedPatient.insurance_type"
                    :items="insuranceTypes"
                    :label="$t('patients.insurance_type')"
                    outlined
                    dense
                  />
                </v-col>

                <v-col cols="12">
                  <v-textarea
                    v-model="editedPatient.allergies"
                    :label="$t('patients.allergies')"
                    outlined
                    dense
                    rows="3"
                  />
                </v-col>

                <v-col cols="12">
                  <v-textarea
                    v-model="editedPatient.systemic_conditions"
                    :label="$t('patients.systemicdisease')"
                    outlined
                    dense
                    rows="3"
                  />
                </v-col>

                <v-col cols="12">
                  <v-textarea
                    v-model="editedPatient.notes"
                    :label="$t('patients.notes')"
                    outlined
                    dense
                    rows="3"
                  />
                </v-col>

                <!-- Doctor Assignment -->
                <v-col cols="12" md="6" v-if="$store.state.role === 'secretary' && doctors.length > 1">
                  <v-select
                    v-model="editedPatient.doctor_id"
                    :items="doctors"
                    :label="$t('doctor')"
                    item-text="name"
                    item-value="id"
                    outlined
                    dense
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <v-switch
                    v-model="editedPatient.is_scheduled_today"
                    :label="$t('patients.add_to_today_sequence')"
                    color="primary"
                    inset
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-card-actions class="pa-4">
            <v-spacer />
            <v-btn color="red darken-1" text @click="closePatientDialog">
              {{ $t("close") }}
            </v-btn>
            <v-btn 
              :loading="saveLoading" 
              color="green darken-1" 
              @click="savePatient"
              :disabled="!valid"
            >
              {{ $t("save") }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- Patient View Dialog -->
    <v-dialog v-model="viewDialog" max-width="800px">
      <v-card v-if="selectedPatient">
        <v-toolbar dark color="blue">
          <v-toolbar-title>
            <h3 style="color:#fff;font-family: 'Cairo'">
              {{ $t('patients.patient_details') }}
            </h3>
          </v-toolbar-title>
          <v-spacer />
          <v-btn @click="viewDialog = false" icon>
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text class="pa-6">
          <v-container>
            <v-row>
              <v-col cols="12" class="text-center">
                <v-avatar size="120" class="mb-4">
                  <v-img v-if="selectedPatient.avatar" :src="selectedPatient.avatar" />
                  <v-icon v-else color="grey" size="80">fas fa-user</v-icon>
                </v-avatar>
                <h3>{{ selectedPatient.name }}</h3>
                <p class="grey--text">{{ selectedPatient.phone }}</p>
              </v-col>

              <v-col cols="12" md="6">
                <v-list dense>
                  <v-list-item>
                    <v-list-item-icon>
                      <v-icon>fas fa-birthday-cake</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ $t('datatable.age') }}</v-list-item-title>
                      <v-list-item-subtitle>{{ calculateAge(selectedPatient.birth_date) }} {{ $t('years') }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>

                  <v-list-item>
                    <v-list-item-icon>
                      <v-icon>fas fa-venus-mars</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ $t('sex.title') }}</v-list-item-title>
                      <v-list-item-subtitle>{{ selectedPatient.sex === 1 ? $t('sex.female') : $t('sex.male') }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>

                  <v-list-item>
                    <v-list-item-icon>
                      <v-icon>fas fa-map-marker-alt</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ $t('datatable.address') }}</v-list-item-title>
                      <v-list-item-subtitle>{{ selectedPatient.address || $t('not_specified') }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
              </v-col>

              <v-col cols="12" md="6">
                <v-list dense>
                  <v-list-item>
                    <v-list-item-icon>
                      <v-icon>fas fa-envelope</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ $t('datatable.email') }}</v-list-item-title>
                      <v-list-item-subtitle>{{ selectedPatient.email || $t('not_specified') }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>

                  <v-list-item>
                    <v-list-item-icon>
                      <v-icon>fas fa-tint</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ $t('patients.blood_type') }}</v-list-item-title>
                      <v-list-item-subtitle>{{ selectedPatient.blood_type || $t('not_specified') }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>

                  <v-list-item>
                    <v-list-item-icon>
                      <v-icon>fas fa-calendar-alt</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ $t('patients.last_visit') }}</v-list-item-title>
                      <v-list-item-subtitle>{{ formatDate(selectedPatient.last_visit) }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
              </v-col>

              <v-col cols="12" v-if="selectedPatient.allergies">
                <v-alert type="warning" outlined>
                  <h4>{{ $t('patients.allergies') }}</h4>
                  <p>{{ selectedPatient.allergies }}</p>
                </v-alert>
              </v-col>

              <v-col cols="12" v-if="selectedPatient.systemic_conditions">
                <v-alert type="info" outlined>
                  <h4>{{ $t('patients.systemicdisease') }}</h4>
                  <p>{{ selectedPatient.systemic_conditions }}</p>
                </v-alert>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn color="primary" @click="editPatient(selectedPatient)">
            <v-icon left>fas fa-edit</v-icon>
            {{ $t('edit') }}
          </v-btn>
          <v-btn color="orange" @click="openCaseSheet(selectedPatient)">
            <v-icon left>fas fa-file-medical</v-icon>
            {{ $t('casesheet') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="headline">{{ $t('confirm_delete') }}</v-card-title>
        <v-card-text>
          {{ $t('patients.delete_confirmation') }}
          <br>
          <strong>{{ patientToDelete?.name }}</strong>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="blue darken-1" text @click="deleteDialog = false">
            {{ $t('cancel') }}
          </v-btn>
          <v-btn color="red darken-1" text @click="confirmDelete" :loading="deleteLoading">
            {{ $t('delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from '../../../axios'

export default {
  name: 'PatientManagement',
  data() {
    return {
      // Table and data
      patients: [],
      totalItems: 0,
      loading: false,
      
      // Search and filters
      search: '',
      selectedDoctor: null,
      ageFilter: null,
      
      // Statistics
      totalPatients: 0,
      todayAppointments: 0,
      waitingPatients: 0,
      emergencyPatients: 0,
      
      // Dialog states
      patientDialog: false,
      viewDialog: false,
      deleteDialog: false,
      
      // Form data
      editedPatient: this.getDefaultPatient(),
      selectedPatient: null,
      patientToDelete: null,
      editMode: false,
      valid: false,
      saveLoading: false,
      deleteLoading: false,
      
      // Pagination
      options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['name'],
        sortDesc: [false]
      },
      
      // Dropdown data
      doctors: [],
      bloodTypes: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
      insuranceTypes: [
        { text: 'حكومي', value: 'government' },
        { text: 'خاص', value: 'private' },
        { text: 'بدون تأمين', value: 'none' }
      ],
      ageRanges: [
        { text: 'أقل من 18 سنة', value: 'under_18' },
        { text: '18-30 سنة', value: '18_30' },
        { text: '31-50 سنة', value: '31_50' },
        { text: '51-65 سنة', value: '51_65' },
        { text: 'أكثر من 65 سنة', value: 'over_65' }
      ],
      
      // Validation rules
      rules: {
        required: value => !!value || this.$t('validation.required'),
        phone: value => /^07\d{8}$/.test(value) || this.$t('validation.phone'),
        email: value => !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || this.$t('validation.email')
      },
      
      // Table headers
      headers: [
        { text: '', value: 'avatar', sortable: false, width: '60px' },
        { text: 'الاسم', value: 'name', align: 'start' },
        { text: 'رقم الهاتف', value: 'phone', align: 'start' },
        { text: 'الجنس', value: 'sex', align: 'start' },
        { text: 'العمر', value: 'age', align: 'start' },
        { text: 'العنوان', value: 'address', align: 'start' },
        { text: 'آخر زيارة', value: 'last_visit', align: 'start' },
        { text: 'الحالة', value: 'status', align: 'start' },
        { text: 'الإجراءات', value: 'actions', sortable: false, width: '200px' }
      ]
    }
  },
  
  computed: {
    calculatedAge() {
      if (this.editedPatient.birth_date) {
        return this.calculateAge(this.editedPatient.birth_date)
      }
      return null
    }
  },
  
  watch: {
    'editedPatient.birth_date'(newVal) {
      if (newVal) {
        this.editedPatient.age = this.calculateAge(newVal)
      }
    }
  },
  
  mounted() {
    this.loadPatients()
    this.loadDoctors()
    this.loadStatistics()
  },
  
  methods: {
    // Data loading methods
    async loadPatients() {
      this.loading = true
      try {
        const { page, itemsPerPage, sortBy, sortDesc } = this.options
        
        const params = {
          page,
          per_page: itemsPerPage,
          search: this.search,
          doctor_id: this.selectedDoctor,
          age_filter: this.ageFilter
        }
        
        if (sortBy.length > 0) {
          params.sort_by = sortBy[0]
          params.sort_desc = sortDesc[0]
        }
        
        const response = await axios.get('/api/patients', { params })
        
        this.patients = response.data.data
        this.totalItems = response.data.total
        
      } catch (error) {
        console.error('Error loading patients:', error)
        this.$toast.error(this.$t('errors.load_patients'))
      } finally {
        this.loading = false
      }
    },
    
    async loadDoctors() {
      try {
        const response = await axios.get('/api/doctors')
        this.doctors = response.data.data || []
      } catch (error) {
        console.error('Error loading doctors:', error)
      }
    },
    
    async loadStatistics() {
      try {
        const response = await axios.get('/api/patients/statistics')
        const stats = response.data
        
        this.totalPatients = stats.total_patients || 0
        this.todayAppointments = stats.today_appointments || 0
        this.waitingPatients = stats.waiting_patients || 0
        this.emergencyPatients = stats.emergency_patients || 0
        
      } catch (error) {
        console.error('Error loading statistics:', error)
      }
    },
    
    // Search and filter methods
    searchPatients() {
      this.options.page = 1
      this.loadPatients()
    },
    
    // Dialog methods
    openAddDialog() {
      this.editMode = false
      this.editedPatient = this.getDefaultPatient()
      this.patientDialog = true
    },
    
    editPatient(patient) {
      this.editMode = true
      this.editedPatient = { ...patient }
      this.viewDialog = false
      this.patientDialog = true
    },
    
    viewPatient(patient) {
      this.selectedPatient = patient
      this.viewDialog = true
    },
    
    closePatientDialog() {
      this.patientDialog = false
      this.editedPatient = this.getDefaultPatient()
      this.$refs.patientForm.resetValidation()
    },
    
    deletePatient(patient) {
      this.patientToDelete = patient
      this.deleteDialog = true
    },
    
    // CRUD operations
    async savePatient() {
      if (!this.$refs.patientForm.validate()) {
        return
      }
      
      this.saveLoading = true
      try {
        const patientData = { ...this.editedPatient }
        
        if (this.editMode) {
          await axios.put(`/api/patients/${patientData.id}`, patientData)
          this.$toast.success(this.$t('patients.updated_successfully'))
        } else {
          await axios.post('/api/patients', patientData)
          this.$toast.success(this.$t('patients.created_successfully'))
        }
        
        this.closePatientDialog()
        this.loadPatients()
        this.loadStatistics()
        
      } catch (error) {
        console.error('Error saving patient:', error)
        this.$toast.error(this.$t('errors.save_patient'))
      } finally {
        this.saveLoading = false
      }
    },
    
    async confirmDelete() {
      if (!this.patientToDelete) return
      
      this.deleteLoading = true
      try {
        await axios.delete(`/api/patients/${this.patientToDelete.id}`)
        this.$toast.success(this.$t('patients.deleted_successfully'))
        
        this.deleteDialog = false
        this.patientToDelete = null
        this.loadPatients()
        this.loadStatistics()
        
      } catch (error) {
        console.error('Error deleting patient:', error)
        this.$toast.error(this.$t('errors.delete_patient'))
      } finally {
        this.deleteLoading = false
      }
    },
    
    // Utility methods
    getDefaultPatient() {
      return {
        name: '',
        phone: '',
        birth_date: '',
        age: null,
        sex: 1,
        address: '',
        email: '',
        emergency_contact: '',
        blood_type: '',
        insurance_type: '',
        allergies: '',
        systemic_conditions: '',
        notes: '',
        doctor_id: null,
        is_scheduled_today: false,
        avatar: null
      }
    },
    
    calculateAge(birthDate) {
      if (!birthDate) return null
      
      const today = new Date()
      const birth = new Date(birthDate)
      let age = today.getFullYear() - birth.getFullYear()
      const monthDiff = today.getMonth() - birth.getMonth()
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--
      }
      
      return age
    },
    
    formatDate(date) {
      if (!date) return this.$t('not_specified')
      
      return new Date(date).toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    
    getStatusColor(status) {
      const colors = {
        'active': 'green',
        'inactive': 'grey',
        'waiting': 'orange',
        'emergency': 'red'
      }
      return colors[status] || 'grey'
    },
    
    getStatusText(status) {
      const texts = {
        'active': this.$t('patients.status.active'),
        'inactive': this.$t('patients.status.inactive'),
        'waiting': this.$t('patients.status.waiting'),
        'emergency': this.$t('patients.status.emergency')
      }
      return texts[status] || status
    },
    
    // File upload
    async uploadPhoto(event) {
      const file = event.target.files[0]
      if (!file) return
      
      // Validate file type
      if (!file.type.startsWith('image/')) {
        this.$toast.error(this.$t('errors.invalid_file_type'))
        return
      }
      
      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        this.$toast.error(this.$t('errors.file_too_large'))
        return
      }
      
      try {
        const formData = new FormData()
        formData.append('photo', file)
        
        const response = await axios.post('/api/upload/patient-photo', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        this.editedPatient.avatar = response.data.url
        this.$toast.success(this.$t('patients.photo_uploaded'))
        
      } catch (error) {
        console.error('Error uploading photo:', error)
        this.$toast.error(this.$t('errors.upload_photo'))
      }
    },
    
    // Navigation methods
    openCaseSheet(patient) {
      this.viewDialog = false
      this.$router.push({ 
        name: 'casesheet', 
        params: { patientId: patient.id } 
      })
    }
  }
}
</script>

<style scoped>
.patient-management {
  font-family: 'Cairo', sans-serif;
}

.v-card {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.v-btn {
  text-transform: none;
}

.v-data-table {
  background-color: #fafafa;
}

.v-data-table >>> th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.v-avatar {
  border: 2px solid #e0e0e0;
}

.v-alert {
  margin: 0;
}

.v-list-item {
  padding: 8px 16px;
}

.v-list-item-icon {
  margin-right: 16px;
}

.v-toolbar-title h3 {
  font-size: 1.2rem;
  font-weight: 500;
}

.v-chip {
  font-size: 0.75rem;
}

.v-text-field--outlined >>> .v-input__control > .v-input__slot {
  min-height: 40px;
}

.v-btn--icon {
  width: 32px;
  height: 32px;
}

.v-btn--icon .v-icon {
  font-size: 16px;
}

@media (max-width: 600px) {
  .v-flex {
    padding: 4px;
  }
  
  .v-card {
    margin: 4px 0;
  }
  
  .v-btn--icon {
    width: 28px;
    height: 28px;
  }
}
</style>
