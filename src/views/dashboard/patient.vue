<template>
  <v-container fluid class="patient-detail-page">
    <!-- Global Loading Overlay -->
    <v-overlay :value="loading || initialLoading" absolute z-index="1000">
      <div class="text-center">
        <v-progress-circular 
          indeterminate 
          size="64" 
          color="primary"
          width="4"
        ></v-progress-circular>
        <div class="mt-4 white--text">
          <div class="text-h6">{{ loadingMessage }}</div>
          <div class="caption">{{ loadingSubMessage }}</div>
        </div>
      </div>
    </v-overlay>

    <!-- Patient Header Card -->
    <v-card class="mb-4 patient-header-card" outlined>
      <v-row no-gutters align="center">
        <!-- Patient Avatar -->
        <v-col cols="auto" class="pa-4">
          <v-avatar size="60" class="mr-3">
            <v-icon v-if="!patient.avatar" size="70" color="grey lighten-1">
              mdi-account-circle
            </v-icon>
            <v-img v-else :src="patient.avatar" />
          </v-avatar>
        </v-col>

        <!-- Patient Info -->
        <v-col>
          <div class="d-flex flex-column patient-info-container">
            <h2 class="text-h5 font-weight-medium mb-1" style="font-family: Cairo, sans-serif !important;">
              {{ patient.name }}
            </h2>
            <div class="patient-sub-info">
               <div class="patient-phone d-flex align-center">
                <v-icon size="16" class="mr-1">mdi-whatsapp</v-icon>
                <a 
                  :href="getWhatsAppLink(patient.phone)" 
                  target="_blank" 
                  class="whatsapp-link text-decoration-none"
                >
                  <div class="case-title" style="direction: ltr; margin-right: 5px;">
                    {{ formatPhone(patient.phone) }}
                  </div>
                </a>
              </div>
            </div>
          </div>
        </v-col>

        <!-- Action Buttons (Hidden for secretaries) -->
        <v-col cols="auto" class="pa-4 text-right" v-if="!secretaryBillsOnlyMode">
          <v-btn 
            class="mr-2" 
            color="primary" 
            rounded 
            @click="editPatient"
          >
            تعديل
          </v-btn>
          
          <v-btn 
            class="mr-2" 
            rounded
            style="background-color: rgb(59, 106, 117); color: white;"
            @click="bookAppointment"
            :disabled="appointmentDialog"
          >
            <v-icon left>mdi-calendar-clock</v-icon>
            حجز موعد
          </v-btn>
          
          <v-btn 
            class="mr-2" 
            color="success" 
            rounded
            @click="generateBill"
          >
            <v-icon left>mdi-file-document-outline</v-icon>
            الفاتورة
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Main Content Card -->
    <v-card>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="12">
            <!-- Secretary Welcome Message (Only shown for secretaries) -->
           

            <!-- Dental Chart Card (Hidden for secretaries) -->
            <v-card class="mb-4" outlined v-if="!secretaryBillsOnlyMode">
              <v-card-text class="text-center">
                <!-- Context Menu for Tooth Operations -->
                <div 
                  ref="contextMenu"
                  class="tooth-context-menu" 
                  :style="contextMenuStyle"
                  v-show="showContextMenu"
                >
                  <div class="context-menu-header">
                    <strong v-if="selectedTooth">السن {{ selectedTooth }}</strong>
                    <strong v-else>اختر عملية</strong>
                  </div>
                  <ul>
                    <li 
                      v-for="operation in dentalOperations" 
                      :key="operation.id"
                      @click="selectOperation(operation)"
                      class="context-menu-item"
                    >
                      {{ operation.name || operation.name_ar }}
                    </li>
                  </ul>
                  <div class="context-menu-footer" v-if="!selectedTooth">
                    <small style="padding: 4px 8px; color: #666; display: block;">
                      انقر بزر الماوس الأيمن على سن محدد
                    </small>
                  </div>
                </div>

                <!-- Reusable Teeth Component with right-click handling -->
                <div class="teeth-container" @click="hideContextMenu($event)" @contextmenu="handleGlobalRightClick">
                  <teeth 
                    :tooth_num="selectedTeethNumbers" 
                    :id="1"
                    @tooth-right-clicked="handleToothRightClick"
                    @contextmenu.native="handleTeethContextMenu"
                  />
                </div>
              </v-card-text>

              <!-- Selected Cases Table -->
              <v-card-text>
                <div class="selected-teeth-table">
                  <v-card flat class="teeth-template-card">
                    <v-card-title class="teeth-template-title">
                      <v-icon left>mdi-tooth-outline</v-icon>
                      <span class="case-title">حالات المراجع</span>
                    </v-card-title>

                    <v-card-text style="padding-top: 20px;">
                      <v-data-table
                        :headers="caseHeaders"
                        :items="patientCases"
                        class="elevation-1"
                        dense
                        :items-per-page="5"
                      >
                        <!-- Tooth Number Column -->
                        <template v-slot:item.tooth_number="{ item }">
                          <div class="tooth-number-cell">
                            <v-chip small color="primary" text-color="white">
                              {{ item.tooth_number }}
                            </v-chip>
                          </div>
                        </template>

                        <!-- Case Type Column -->
                        <template v-slot:item.case_type="{ item }">
                          <v-chip small color="purple" text-color="white">
                            {{ item.case_type }}
                          </v-chip>
                        </template>

                        <!-- Price Column -->
                        <template v-slot:item.price="{ item }">
                          <v-text-field
                            v-model="item.price"
                            type="number"
                            placeholder="0"
                            suffix="IQ"
                            dense
                            hide-details
                            class="price-input"
                            @input="updateCasePrice(item)"
                          />
                        </template>

                        <!-- Status Column -->
                        <template v-slot:item.status="{ item }">
                          <v-switch
                            v-model="item.completed"
                            :label="item.completed ? 'مكتمله' : 'غير مكتمله'"
                            color="green"
                            inset
                            @change="updateCaseStatus(item)"
                          />
                        </template>

                        <!-- Notes Column -->
                        <template v-slot:item.notes="{ item }">
                          <div class="notes-container">
                            <!-- Main case notes field -->
                            <div class="mb-2">
                              <v-textarea
                                v-model="item.notes"
                                placeholder="ملاحضات الحالة الرئيسية ..."
                                rows="1"
                                auto-grow
                                no-resize
                                dense
                                outlined
                                hide-details
                                class="notes-textarea main-note"
                                @input="updateCaseNotes(item)"
                              />
                            </div>
                            
                            <!-- Existing sessions from server -->
                            <div 
                              v-for="(session, index) in item.sessions" 
                              :key="`server-session-${session.id || index}`"
                              class="mb-2"
                            >
                              <div class="d-flex align-center">
                                <v-textarea
                                  v-model="session.note"
                                  :placeholder="`ملاحظات الجلسة ${index + 1}...`"
                                  rows="1"
                                  auto-grow
                                  no-resize
                                  dense
                                  outlined
                                  hide-details
                                  class="notes-textarea session-note flex-grow-1"
                                  @input="updateExistingSessionNote(item)"
                                />
                                <v-chip
                                  small
                                  class="ml-2 session-date-chip"
                                  color="blue-grey lighten-3"
                                >
                                  {{ formatSessionDate(session.date) }}
                                </v-chip>
                              </div>
                            </div>
                            
                            <!-- New additional session notes -->
                            <div 
                              v-for="(session, index) in item.additionalSessions" 
                              :key="`new-session-${index}`"
                              class="mb-2"
                            >
                              <div class="d-flex align-center">
                                <v-textarea
                                  v-model="session.note"
                                  :placeholder="`ملاحظات الجلسة الجديدة ${index + 1}...`"
                                  rows="1"
                                  auto-grow
                                  no-resize
                                  dense
                                  outlined
                                  hide-details
                                  class="notes-textarea session-note new-session flex-grow-1"
                                  @input="updateSessionNote(item)"
                                />
                                <v-chip
                                  small
                                  class="ml-2 session-date-chip"
                                  color="green lighten-3"
                                >
                                  جديد
                                </v-chip>
                              </div>
                            </div>
                          </div>
                          
                          <v-btn
                            text
                            small
                            color="primary"
                            class="mt-1"
                            @click="addNote(item)"
                          >
                            <v-icon left size="16">mdi-plus</v-icon>
                            إضافة جلسة
                          </v-btn>
                        </template>

                        <!-- Actions Column -->
                        <template v-slot:item.actions="{ item }">
                          <v-icon
                            color="error"
                            @click="deleteCase(item)"
                          >
                            mdi-delete
                          </v-icon>
                        </template>
                      </v-data-table>
                    </v-card-text>
                  </v-card>
                </div>
              </v-card-text>
            </v-card>

            <!-- Bills History Card (Always shown) -->
       
          </v-col>
        </v-row>

        <!-- Image Upload Section (Hidden for secretaries) -->
        <v-row style="height: auto;" v-if="!secretaryBillsOnlyMode">
          <v-col cols="12" md="12">
            <vue2-dropzone 
              ref="patientDropzone" 
              id="dropzone" 
              :options="dropzoneOptions"
              @vdropzone-success="handleImageSuccess"
              @vdropzone-error="handleImageError"
              @vdropzone-removed-file="handleImageRemoved"
              class="dropzone-container"
            />
          </v-col>
        </v-row>

        <!-- Billing Section (Shown to secretaries with limited permissions) -->
        <v-card class="cre_bill mt-4">
          <!-- Section Header -->
          <v-layout row wrap>
            <v-flex md5>
              <hr />
            </v-flex>
            <v-flex md2>
              <p class="se_tit_menu text-center">الفاتوره</p>
            </v-flex>
            <v-flex md5>
              <hr />
            </v-flex>
          </v-layout>

       

          <!-- Total Amount -->
          <v-row>
            <v-col cols="12" md="2" class="d-none d-sm-flex"></v-col>
            <v-col cols="12" md="8">
              <v-text-field
                :value="totalAmount"
                label="اجمالي مبلغ الحالات"
                suffix="IQ"
                readonly
                outlined
                dense
              />
            </v-col>
            <v-col cols="12" md="2" class="d-none d-sm-flex"></v-col>
          </v-row>

          <!-- Payment Details - Loop through all bills -->
          <div class="bills-payment-loop">
            <!-- Debug: Show bills count -->
            <div v-if="patientBills.length === 0" class="text-center pa-4 grey--text">
              لا توجد فواتير حاليا. انقر على "اضافه دفعه" لإضافة فاتورة جديدة.
            </div>
            
            <div 
              v-for="bill in patientBills" 
              :key="bill.id"
              class="bill-payment-item"
              :class="{ 
                'paid': bill.is_paid == 1, 
                'unpaid': bill.is_paid == 0,
                'new-bill-highlight': bill.isNew
              }"
            >
              <v-layout row wrap>
                <v-flex md2 class="d-none d-sm-flex"></v-flex>
                
                <!-- Payment Amount -->
                <v-flex md4 xs4>
                  <v-text-field
                    v-if="bill.isNew"
                    v-model="bill.price"
                    label="مبلغ الدفعة"
                    suffix="IQ"
                    type="number"
                    outlined
                    dense
                    @input="updateBillPrice(bill)"
                  />
                  <v-text-field
                    v-else
                    v-model="bill.price"
                    label="مبلغ الدفعة"
                    suffix="IQ"
                    type="number"
                    outlined
                    dense
                    :disabled="!canEditBill(bill)"
                    :readonly="!canEditBill(bill)"
                    @input="updateBillPrice(bill)"
                  />
                  <div class="caption text-right pr-1 user-created">
                    <v-icon size="12" class="mr-1 grey--text text--darken-1">
                      mdi-account
                    </v-icon>
                    <span class="grey--text text--darken-1">{{ bill.user ? bill.user.name : 'غير محدد' }}</span>
                  </div>
                </v-flex>

                <!-- Payment Date -->
                <v-flex md3 xs4>
                  <v-text-field
                    v-if="bill.isNew"
                    v-model="bill.PaymentDate"
                    label="التاريخ"
                    type="date"
                    outlined
                    dense
                    @input="updateBillDate(bill)"
                  >
                    <template v-slot:prepend>
                      <v-icon>mdi-calendar</v-icon>
                    </template>
                  </v-text-field>
                  <v-text-field
                    v-else
                    v-model="bill.PaymentDate"
                    label="التاريخ"
                    type="date"
                    outlined
                    dense
                    :disabled="!canEditBill(bill)"
                    :readonly="!canEditBill(bill)"
                    @input="updateBillDate(bill)"
                  >
                    <template v-slot:prepend>
                      <v-icon>mdi-calendar</v-icon>
                    </template>
                  </v-text-field>
                </v-flex>

                <!-- Payment Status -->
                <v-flex md2 xs2 class="mt-2 text-center">
                  <v-switch
                    :input-value="bill.is_paid == 1"
                    :disabled="!canChangePaymentStatus"
                    inset
                    style="position: relative; padding-right: 10px; bottom: 21px;"
                    @change="toggleBillPaymentStatus(bill)"
                  />
                  <div class="caption text-center payment-status-label grey--text">
                    {{ bill.is_paid == 1 ? 'مدفوع' : 'بانتظار التسديد' }}
                  </div>
                </v-flex>

             
                
                <v-flex md1 class="d-none d-sm-flex"></v-flex>
              </v-layout>
            </div>
          </div>

        

          <!-- Add Bill Button -->
          <div class="v-card__actions justify-center" v-if="canAddBills">
            <v-btn 
              small 
              color="primary" 
              @click="addPayment()"
            >
              <i class="fas fa-plus"></i>
              اضافه دفعه
            </v-btn>
          </div>

          <!-- Payment Summary -->
          <v-layout row wrap class="pt-5 mt-5">
            <v-flex md xs></v-flex>
            <v-flex md xs>
              <div style="font-weight: bold;">
                المبلغ المدفوع :
                <v-chip
                  label
                  outlined
                  color="success"
                  class="ma-2"
                >
                  {{ paidAmount }} IQ
                </v-chip>
              </div>
              <div style="font-weight: bold;">
                <span style="padding-left: 34px;">المتبقي :</span>
                <v-chip
                  label
                  outlined
                  color="success"
                  class="ma-2"
                >
                  {{ remainingAmount }} IQ
                </v-chip>
              </div>
            </v-flex>
            <v-flex md xs></v-flex>
          </v-layout>
        </v-card>

        <!-- Save Button (Hidden for secretaries) -->
        <v-card class="cre_bill mt-4" >
          <v-card-actions class="justify-center">
            <v-btn
              large
              color="primary"
              :loading="saving"
              @click="savePatientData"
            >
              حفظ المعلومات
            </v-btn>
          </v-card-actions>
        </v-card>

    
      </v-card-text>
    </v-card>

    <!-- Dialogs -->
    <!-- Patient Edit Dialog -->
    <PatientEditDialog
      v-model="editDialog"
      :patient="patient"
      :doctors="doctors"
      :loading="saving"
      @save="savePatientEdit"
      @close="closePatientEditDialog"
      :hide-activator="true"
    />

      <div v-if="appointmentDialog && doctors.length > 0">
            <OwnerBooking 
              :patientFound="true" 
              :patientInfo="patientInfo" 
              :doctors="doctors" 
            />
      </div>

      <!-- Bill Report Dialog -->
      <v-dialog v-model="billDialog" max-width="900px">
        <v-card>
          <Bill :patient="patient" />
        </v-card>
      </v-dialog>
  </v-container>
</template>

<script>
import teeth from '@/components/core/teeth.vue';
import OwnerBooking from './sub_components/ownerBookinfDed.vue';
import Bill from './sub_components/billsReport.vue';
import PatientEditDialog from '@/components/PatientEditDialog.vue';
import { EventBus } from './event-bus.js';
// Import the configured axios instance that includes authentication
import '@/axios.js';
import LazyLoadingMixin from '@/mixins/lazyLoadingMixin';
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";

export default {
  name: 'PatientDetail',
  mixins: [LazyLoadingMixin],
  components: {
    teeth,
    OwnerBooking,
    Bill,
    PatientEditDialog,
    vue2Dropzone
  },
  
  data() {
    return {
      loading: false,
      saving: false,
      initialLoading: true,
      loadingMessage: 'جاري تحميل البيانات...',
      loadingSubMessage: 'يرجى الانتظار',
      
      // Patient Data (will be loaded from API)
      patient: {
        id: null,
        name: '',
        phone: '',
        avatar: null,
        age: null,
        address: '',
        email: '',
        sex: '',
        systemic_conditions: '',
        birth_date: ''
      },
      
      // Dental Operations (will be loaded from API)
      dentalOperations: [],
      
      // Context Menu
      showContextMenu: false,
      contextMenuStyle: {
        top: '0px',
        left: '0px',
        display: 'none'
      },
      selectedTooth: null,
      
      // Patient Cases (will be loaded from API)
      patientCases: [],
      
      // Patient Bills (will be loaded from API)  
      patientBills: [],
      
      // Table Headers
      caseHeaders: [
        { text: 'السن', value: 'tooth_number', align: 'center', width: '2%' },
        { text: 'النوع', value: 'case_type', align: 'start', width: '5%' },
        { text: 'التاريخ', value: 'date', align: 'center', width: '10%' },
        { text: 'السعر', value: 'price', align: 'center', width: '15%' },
        { text: 'الحالة', value: 'status', align: 'center', width: '15%' },
        { text: 'ملاحظات', value: 'notes', align: 'start', width: '45%' },
        { text: 'الإجراءات', value: 'actions', align: 'center', width: '8%' }
      ],
      
      billHeaders: [
        { text: 'رقم الفاتورة', value: 'id', align: 'center', width: '10%' },
        { text: 'المبلغ', value: 'price', align: 'center', width: '15%' },
        { text: 'تاريخ الدفع', value: 'PaymentDate', align: 'center', width: '15%' },
        { text: 'حالة الدفع', value: 'is_paid', align: 'center', width: '15%' },
        { text: 'أنشأ بواسطة', value: 'user_name', align: 'start', width: '20%' },
        { text: 'تاريخ الإنشاء', value: 'created_at', align: 'center', width: '15%' },
        { text: 'الإجراءات', value: 'actions', align: 'center', width: '10%' }
      ],
      
      // Billing Data
      currentPayment: {
        id: null,
        amount: '', // Empty amount field for user input
        date: new Date().toISOString().substr(0, 10),
        paid: false,
        user_name: ''
      },
      
      currentUser: 'حيدر عبد عون',
      
      // Doctors list for appointment booking
      doctors: [],
      
      // Dialogs
      editDialog: false,
      appointmentDialog: false,
      billDialog: false,

      // Pagination and search
      options: {
        page: 1,
        itemsPerPage: 5,
        sortBy: 'date',
        sortDesc: true
      },
      totalItems: 0,
      loadingData: false,
      
      // API endpoint for patient data
      apiEndpoint: '/api/patients',
      
      // Search fields
      search: {
        name: '',
        phone: '',
        case_type: '',
        date_from: '',
        date_to: ''
      },

      // Dropzone configuration
      dropzoneOptions: {
        url: "https://apismartclinicv2.tctate.com/api/cases/uploude_image",
        thumbnailWidth: 150,
        maxFilesize: 5,
        acceptedFiles: "image/*",
        dictDefaultMessage: '<i class="fas fa-upload"></i> اضغط هنا لرفع صور الحاله',
        paramName: "file",
        maxFiles: 10,
        addRemoveLinks: true,
        dictRemoveFile: "حذف الصورة",
        dictCancelUpload: "إلغاء الرفع",
        autoProcessQueue: true
      },

      // Uploaded images tracking
      uploadedImages: []
    }
  },
  
  computed: {
    // Get selected teeth numbers for highlighting
    selectedTeethNumbers() {
      return this.patientCases.map(case_item => case_item.tooth_number);
    },
    
    totalAmount() {
      return this.patientCases.reduce((total, case_item) => {
        return total + (parseFloat(case_item.price) || 0);
      }, 0).toLocaleString();
    },
    
    totalAmountNumber() {
      return this.patientCases.reduce((total, case_item) => {
        return total + (parseFloat(case_item.price) || 0);
      }, 0);
    },
    
    paidAmount() {
      // Calculate from bills where is_paid = 1
      const paid = this.patientBills
        .filter(bill => bill.is_paid == 1)
        .reduce((total, bill) => total + (parseFloat(bill.price) || 0), 0);
      return paid.toLocaleString();
    },
    
    paidAmountNumber() {
      return this.patientBills
        .filter(bill => bill.is_paid == 1)
        .reduce((total, bill) => total + (parseFloat(bill.price) || 0), 0);
    },
    
    totalBillsAmount() {
      return this.patientBills
        .reduce((total, bill) => total + (parseFloat(bill.price) || 0), 0);
    },
    
    shouldShowSaveButton() {
      // Show save button only when total cases amount equals total bills amount
      return Math.abs(this.totalAmountNumber - this.totalBillsAmount) < 0.01;
    },
    
    remainingAmount() {
      const total = this.totalAmountNumber;
      const paid = this.paidAmountNumber;
      
      return (total - paid).toLocaleString();
    },
    
    // Check if user can change payment status based on clinic settings and user role
    canChangePaymentStatus() {
      try {
        const role = this.$store.state.role;
        const paidAtSecretary = this.$store.state.AdminInfo?.clinics_info?.paid_at_secretary;
        
        // If paid_at_secretary is true (1), only secretary can change payment status
        if (paidAtSecretary == 1 || paidAtSecretary === true) {
          return role === 'secretary';
        }
        // If paid_at_secretary is false (0), both secretary and doctor can change payment status
        else {
          return role === 'secretary' || role === 'adminDoctor' || role === 'doctor';
        }
      } catch (error) {
        console.error('Error checking payment permission:', error);
        return true; // Default to allowing if error occurs
      }
    },

    // Check if current user can add new bills
    canAddBills() {
      try {
        const role = this.$store.state.role;
        return role === 'secretary' || role === 'adminDoctor' || role === 'doctor';
      } catch (error) {
        console.error('Error checking add bills permission:', error);
        return false;
      }
    },

    // Patient info formatted for OwnerBooking component
    patientInfo() {
      return {
        id: this.patient.id,
        name: this.patient.name,
        phone: this.patient.phone,
        age: this.patient.age,
        address: this.patient.address,
        email: this.patient.email,
        sex: this.patient.sex,
        birth_date: this.patient.birth_date,
        systemic_conditions: this.patient.systemic_conditions
      };
    },

    // Check if current user is a secretary with limited permissions
    secretaryBillsOnlyMode() {
      try {
        const role = this.$store.state.role;
        const paidAtSecretary = this.$store.state.AdminInfo?.clinics_info?.paid_at_secretary;
        
        // If paid_at_secretary is true (1) and user is secretary, show limited mode
        return role === 'secretary' && (paidAtSecretary == 1 || paidAtSecretary === true);
      } catch (error) {
        console.error('Error checking secretary mode:', error);
        return false;
      }
    }
  },
  
  methods: {
    // Fetch dental operations from API
    async fetchDentalOperations() {
      try {
        this.loadingSubMessage = 'تحميل العمليات السنية...';
        const response = await this.$http.get('cases/CaseCategories', {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });
        
        this.dentalOperations = response.data.map(category => ({
          id: category.id,
          name: category.name_ar,
          name_en: category.name_en,
          order: category.order
        }));
        
        console.log('📥 Fetched dental operations:', this.dentalOperations.length);
      } catch (error) {
        console.error('❌ Error fetching dental operations:', error);
        
        // Fallback to default operations if API fails
        this.dentalOperations = [
          { id: 1, name: 'قلع' },
          { id: 2, name: 'حشوة' },
          { id: 3, name: 'فحص' }
        ];
      }
    },

    // Fetch doctors from API
    async fetchDoctors() {
      try {
        this.loadingSubMessage = 'تحميل قائمة الأطباء...';
        const response = await this.$http.get('doctors', {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });
        
        this.doctors = response.data.map(doctor => ({
          id: doctor.id,
          name: doctor.name,
          name_ar: doctor.name_ar || doctor.name,
          specialty: doctor.specialty,
          phone: doctor.phone,
          email: doctor.email
        }));
        
        console.log('📥 Fetched doctors:', this.doctors.length);
      } catch (error) {
        console.error('❌ Error fetching doctors:', error);
        
        // Fallback to default doctor if API fails
        this.doctors = [
          { id: 1, name: 'طبيب افتراضي', name_ar: 'طبيب افتراضي' }
        ];
      }
    },

    // Load patient data from API
    async loadPatientData() {
      try {
        this.initialLoading = true;
        this.loadingMessage = 'جاري تحميل بيانات المريض...';

        // Get patient ID from route
        const patientId = this.$route.params.id;
        if (!patientId) {
          throw new Error('Patient ID not found in route');
        }

        // Load patient data using the new API endpoint
        this.loadingSubMessage = 'تحميل بيانات المريض...';
        const response = await this.$http.get(`https://apismartclinicv3.tctate.com/api/getPatientById/${patientId}`, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });

        const data = response.data;
        
        // Set patient basic info
        this.patient = {
          id: data.id,
          name: data.name,
          phone: data.phone,
          avatar: null,
          age: data.age,
          address: data.address,
          email: data.email,
          sex: data.sex,
          systemic_conditions: data.systemic_conditions,
          birth_date: data.birth_date,
          notes: data.notes,
          rx_id: data.rx_id
        };

        // Process cases data
        this.patientCases = data.cases ? data.cases.map(caseItem => {
          // Parse tooth number from JSON array format
          let toothNumber = null;
          try {
            if (caseItem.tooth_num) {
              const parsed = JSON.parse(caseItem.tooth_num);
              toothNumber = Array.isArray(parsed) ? parsed[0] : parsed;
            }
          } catch (e) {
            toothNumber = caseItem.tooth_num;
          }

          return {
            id: caseItem.id,
            server_id: caseItem.id,
            tooth_number: toothNumber,
            case_type: caseItem.case_categories ? caseItem.case_categories.name_ar : '',
            date: caseItem.created_at ? caseItem.created_at.split('T')[0] : '',
            price: caseItem.price,
            completed: caseItem.status_id === 43, // 43 = completed, 42 = not completed
            notes: caseItem.notes || '',
            operation_id: caseItem.case_categores_id,
            status_id: caseItem.status_id,
            sessions: caseItem.sessions || [],
            additionalSessions: [],
            doctor_id: caseItem.doctor_id,
            user_id: caseItem.user_id,
            is_paid: caseItem.is_paid,
            case_categories: caseItem.case_categories
          };
        }) : [];

        // Process bills data
        this.patientBills = data.bills ? data.bills.map(bill => ({
          id: bill.id,
          server_id: bill.id,
          billable_id: bill.billable_id,
          patient_id: bill.billable_id,
          price: bill.price,
          PaymentDate: bill.PaymentDate ? bill.PaymentDate.split(' ')[0] : '',
          is_paid: bill.is_paid,
          billable_type: bill.billable_type,
          user_id: bill.user_id,
          clinics_id: bill.clinics_id,
          user: bill.user || {},
          user_name: bill.user ? bill.user.name : '',
          created_at: bill.created_at,
          updated_at: bill.updated_at,
          isNew: false,
          modified: false
        })) : [];

        // Load dental operations and doctors
        await Promise.all([
          this.fetchDentalOperations(),
          this.fetchDoctors()
        ]);

        this.initialLoading = false;
        console.log('✅ Patient data loaded successfully');
        console.log('Patient:', this.patient);
        console.log('Cases:', this.patientCases);
        console.log('Bills:', this.patientBills);
      } catch (error) {
        console.error('❌ Error loading patient data:', error);
        this.initialLoading = false;
        this.$swal.fire({
          title: "خطأ في تحميل البيانات",
          text: "تعذر تحميل بيانات المريض. يرجى المحاولة مرة أخرى.",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      }
    },

    // Handle tooth selection from teeth component
    handleToothChange(selectedTeeth) {
      console.log('Teeth changed:', selectedTeeth);
      // The teeth component handles its own state, we just need to respond to changes
      // When a tooth is clicked, we can trigger the context menu
      if (selectedTeeth && selectedTeeth.length > 0) {
        const lastSelectedTooth = selectedTeeth[selectedTeeth.length - 1];
        this.selectedTooth = lastSelectedTooth;
        // Show context menu at a default position since we don't have mouse event
        this.showToothMenuAtPosition(lastSelectedTooth);
      }
    },
    
    // Show tooth menu at a calculated position
    showToothMenuAtPosition(toothNumber) {
      this.selectedTooth = toothNumber;
      
      // Calculate position based on tooth number (simplified positioning)
      const baseX = 100;
      const baseY = 200;
      
      this.contextMenuStyle = {
        top: baseY + 'px',
        left: baseX + 'px',
        display: 'block'
      };
      this.showContextMenu = true;
      
      // Hide menu when clicking elsewhere
      document.addEventListener('click', this.hideContextMenu);
    },
    
    // Handle right-click events from teeth component
    handleToothRightClick(data) {
      console.log('Tooth right-clicked received in patient component:', data);
      
      if (data && data.toothId && data.event) {
        console.log('Processing right-click for tooth:', data.toothId);
        this.selectedTooth = data.toothId;
        
        // Get the viewport coordinates
        const x = data.event.clientX;
        const y = data.event.clientY;
        
        console.log('Context menu position:', { x, y });
        
        // Position context menu at click location (fixed positioning relative to viewport)
        this.contextMenuStyle = {
          position: 'fixed',
          top: y + 'px',
          left: x + 'px',
          zIndex: 1000,
          display: 'block'
        };
        
        this.showContextMenu = true;
        
        // Prevent the event from bubbling to avoid immediate hiding
        data.event.stopPropagation();
        data.event.preventDefault();
        
        console.log('Context menu should be visible now');
        
        // Hide menu when clicking elsewhere (with a small delay to avoid immediate hiding)
        setTimeout(() => {
          document.addEventListener('click', this.hideContextMenu);
        }, 10);
      } else {
        console.error('Invalid right-click data received:', data);
      }
    },
    
    // Handle global right-click on teeth area as fallback
    handleGlobalRightClick(event) {
      console.log('Global right-click detected on teeth area:', event);
      event.preventDefault();
      
      // Try to determine which tooth was clicked by analyzing the target
      const target = event.target;
      
      // Look for tooth number in various ways
      let toothNumber = null;
      
      // Check if target has a data attribute for tooth number
      if (target.dataset && target.dataset.toothNumber) {
        toothNumber = target.dataset.toothNumber;
      }
      // Check if target has an id that contains tooth number
      else if (target.id && target.id.includes('tooth')) {
        const match = target.id.match(/tooth[_-]?(\d+)/);
        if (match) {
          toothNumber = match[1];
        }
      }
      // Check if target has a class that contains tooth number
      else if (target.className && typeof target.className === 'string') {
        const match = target.className.match(/tooth[_-]?(\d+)/);
        if (match) {
          toothNumber = match[1];
        }
      }
      // Check parent elements for tooth information
      else {
        let parent = target.parentElement;
        while (parent && !toothNumber) {
          if (parent.dataset && parent.dataset.toothNumber) {
            toothNumber = parent.dataset.toothNumber;
            break;
          }
          if (parent.id && parent.id.includes('tooth')) {
            const match = parent.id.match(/tooth[_-]?(\d+)/);
            if (match) {
              toothNumber = match[1];
              break;
            }
          }
          parent = parent.parentElement;
        }
      }
      
      console.log('Detected tooth number from global right-click:', toothNumber);
      
      if (toothNumber) {
        // Trigger the context menu for this tooth
        this.handleToothRightClick({
          toothId: toothNumber,
          event: event
        });
      } else {
        // Show a general context menu if no specific tooth detected
        this.showGeneralContextMenu(event);
      }
    },
    
    // Handle right-click events specifically from teeth component
    handleTeethContextMenu(event) {
      console.log('Native context menu event from teeth component:', event);
      event.preventDefault();
      
      // This is a fallback for when the teeth component doesn't emit the custom event
      this.handleGlobalRightClick(event);
    },
    
    // Show general context menu when no specific tooth is detected
    showGeneralContextMenu(event) {
      console.log('Showing general context menu');
      
      this.selectedTooth = null;
      
      this.contextMenuStyle = {
        position: 'fixed',
        top: event.clientY + 'px',
        left: event.clientX + 'px',
        zIndex: 1000,
        display: 'block'
      };
      
      this.showContextMenu = true;
      
      event.stopPropagation();
      event.preventDefault();
      
      setTimeout(() => {
        document.addEventListener('click', this.hideContextMenu);
      }, 10);
    },

    // Phone formatting
    formatPhone(phone) {
      if (!phone) return '';
      // Remove any non-digit characters
      const cleanPhone = phone.replace(/\D/g, '');
      
      // Handle different phone number formats
      if (cleanPhone.startsWith('964')) {
        // Remove country code for display
        const nationalNumber = cleanPhone.substring(3);
        // Format as XXXX XXX XXXX
        if (nationalNumber.length === 10) {
          return nationalNumber.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
        }
        return nationalNumber;
      } else if (cleanPhone.length === 10) {
        // Format as XXXX XXX XXX
        return cleanPhone.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
      } else if (cleanPhone.length === 11) {
        // Format as XXXXX XXX XXX
        return cleanPhone.replace(/(\d{5})(\d{3})(\d{3})/, '$1 $2 $3');
      }
      
      return cleanPhone;
    },
    
    // Generate WhatsApp link
    getWhatsAppLink(phone) {
      if (!phone) return '#';
      // Remove any non-digit characters
      const cleanPhone = phone.replace(/\D/g, '');
      
      // Add 964 prefix if not already present
      let fullPhone = cleanPhone;
      if (!cleanPhone.startsWith('964')) {
        // Remove leading zero if present
        const nationalNumber = cleanPhone.startsWith('0') ? cleanPhone.substring(1) : cleanPhone;
        fullPhone = `964${nationalNumber}`;
      }
      
      return `https://wa.me/${fullPhone}`;
    },
    
    // Tooth operations
    selectTooth(toothNumber) {
      this.selectedTooth = toothNumber;
      console.log('Selected tooth:', toothNumber);
    },
    
    showToothMenu(event, toothNumber = null) {
      if (toothNumber) {
        this.selectedTooth = toothNumber;
      }
      
      this.contextMenuStyle = {
        top: event.clientY + 'px',
        left: event.clientX + 'px',
        display: 'block'
      };
      this.showContextMenu = true;
      
      // Hide menu when clicking elsewhere
      document.addEventListener('click', this.hideContextMenu);
    },
    
    hideContextMenu(event) {
      // Don't hide if clicking on the context menu itself
      if (event && event.target && event.target.closest('.tooth-context-menu')) {
        return;
      }
      
      this.showContextMenu = false;
      this.selectedTooth = null;
      document.removeEventListener('click', this.hideContextMenu);
    },
    
    selectOperation(operation) {
      if (this.selectedTooth) {
        this.addCase(this.selectedTooth, operation);
      }
      this.hideContextMenu();
    },
    
    addCase(toothNumber, operation) {
      // Check if case already exists for this tooth and operation
      const operationName = operation.name || operation.name_ar;
      const existingCase = this.patientCases.find(
        case_item => case_item.tooth_number == toothNumber && case_item.case_type === operationName
      );
      
      if (existingCase) {
        this.$swal.fire({
          title: "تنبيه",
          text: "هذه الحالة موجودة بالفعل لهذا السن",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      const newCase = {
        id: Date.now(), // Temporary client-side ID
        server_id: null, // Will be set after saving to server
        tooth_number: toothNumber,
        case_type: operationName,
        date: new Date().toISOString().substr(0, 10),
        price: 0,
        completed: false,
        notes: '',
        operation_id: operation.id,
        status_id: 42, // Default status
        sessions: [],
        additionalSessions: [] // Initialize empty array for additional session notes
      };
      
      this.patientCases.unshift(newCase);
      
      // Show success message
      this.$swal.fire({
        position: "top-end",
        icon: "success",
        title: `تم إضافة ${operationName} للسن ${toothNumber}`,
        showConfirmButton: false,
        timer: 1500
      });
    },
    
    // Case management
    updateCasePrice(case_item) {
      console.log('Price updated for case:', case_item.id, 'New price:', case_item.price);
      // Mark case as modified for next save
      case_item.modified = true;
    },
    
    updateCaseStatus(case_item) {
      console.log('Status updated for case:', case_item.id, 'Completed:', case_item.completed);
      // Mark case as modified for next save
      case_item.modified = true;
    },
    
    updateCaseNotes(case_item) {
      console.log('Notes updated for case:', case_item.id, 'New notes:', case_item.notes);
      // Mark case as modified for next save
      case_item.modified = true;
    },
    
    addNote(case_item) {
      console.log('Adding note for case:', case_item.id);
      
      // Initialize additionalSessions array if it doesn't exist
      if (!case_item.additionalSessions) {
        case_item.additionalSessions = [];
      }
      
      // Create a new empty session note that will be edited inline
      const newSession = {
        note: '',
        date: new Date().toISOString().substr(0, 10),
        case_id: case_item.server_id
      };
      
      // Add the new session to the additionalSessions array
      case_item.additionalSessions.push(newSession);
      
      // Mark case as modified for next save
      case_item.modified = true;
      
      console.log('New session note field added');
    },
    
    updateSessionNote(case_item) {
      // Mark case as modified when session note is updated
      case_item.modified = true;
      console.log('Session note updated for case:', case_item.id);
    },
    
    updateExistingSessionNote(case_item) {
      // Mark case as modified when existing session note is updated
      case_item.modified = true;
      console.log('Existing session note updated for case:', case_item.id);
    },
    
    formatSessionDate(dateString) {
      if (!dateString) return '';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-IQ', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        });
      } catch (error) {
        return dateString.split(' ')[0] || '';
      }
    },
    
    formatBillDate(dateString) {
      if (!dateString) return '';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-IQ', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        });
      } catch (error) {
        // If it's already in a good format, return the date part only
        return dateString.split(' ')[0] || dateString;
      }
    },
    
    async deleteCase(case_item) {
      // Show confirmation dialog
      const result = await this.$swal.fire({
        title: 'تأكيد الحذف',
        text: `هل أنت متأكد من حذف ${case_item.case_type} للسن ${case_item.tooth_number}؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        // If case has server_id, delete from server
        if (case_item.server_id) {
          await this.$http.delete("cases/" + case_item.server_id, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
        }

        // Remove from local array
        const index = this.patientCases.findIndex(c => c.id === case_item.id);
        if (index > -1) {
          this.patientCases.splice(index, 1);
        }

        this.$swal.fire({
          position: "top-end",
          icon: "success",
          title: "تم حذف الحالة بنجاح",
          showConfirmButton: false,
          timer: 1500
        });
      } catch (error) {
        console.error('Error deleting case:', error);
        this.$swal.fire({
          title: "حدث خطأ في حذف الحالة",
          text: "تاكد من الاتصال بالإنترنت وحاول مرة أخرى",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      }
    },

    // Bill management methods
    editBill(bill) {
      console.log('Editing bill:', bill.id);
      // TODO: Implement bill editing logic
    },

    async deleteBill(bill) {
      // Show confirmation dialog
      const result = await this.$swal.fire({
        title: 'تأكيد الحذف',
        text: `هل أنت متأكد من حذف الفاتورة رقم ${bill.id}؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        // If bill has server_id, delete from server
        if (bill.server_id) {
          await this.$http.delete("bills/" + bill.server_id, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
        }

        // Remove from local array
        const index = this.patientBills.findIndex(b => b.id === bill.id);
        if (index > -1) {
          this.patientBills.splice(index, 1);
        }

        this.$swal.fire({
          position: "top-end",
          icon: "success",
          title: "تم حذف الفاتورة بنجاح",
          showConfirmButton: false,
          timer: 1500
        });
      } catch (error) {
        console.error('Error deleting bill:', error);
        this.$swal.fire({
          title: "حدث خطأ في حذف الفاتورة",
          text: "تاكد من الاتصال بالإنترنت وحاول مرة أخرى",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      }
    },

    async toggleBillPaymentStatus(bill) {
      if (!this.canChangePaymentStatus) {
        this.$swal.fire({
          title: "تنبيه",
          text: "ليس لديك صلاحية لتغيير حالة الدفع",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }
      
      try {
        const token = localStorage.getItem('tokinn');
        if (!token) {
          this.$swal.fire({
            title: "خطأ في التحقق من الهوية",
            text: "يرجى تسجيل الدخول مرة أخرى",
            icon: "error",
            confirmButtonText: "اغلاق"
          });
          return;
        }
        
        const newStatus = bill.is_paid == 1 ? 0 : 1;
        
        // Call API to update payment status
        const response = await this.$http.put(`bills/${bill.id}`, {
          is_paid: newStatus,
          PaymentDate: newStatus == 1 ? new Date().toISOString().split('T')[0] : null
        }, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.status === 200) {
          // Update local state
          const billIndex = this.patientBills.findIndex(b => b.id === bill.id);
          if (billIndex > -1) {
            this.patientBills[billIndex].is_paid = newStatus;
            if (newStatus == 1) {
              this.patientBills[billIndex].PaymentDate = new Date().toISOString().split('T')[0];
            } else {
              this.patientBills[billIndex].PaymentDate = null;
            }
          }
          
          const successMessage = newStatus == 1 ? 'تم تحديد الفاتورة كمدفوعة' : 'تم تحديد الفاتورة كغير مدفوعة';
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: successMessage,
            showConfirmButton: false,
            timer: 1500
          });
        }
      } catch (error) {
        console.error('Error toggling payment status:', error);
        this.$swal.fire({
          title: "حدث خطأ في تحديث حالة الدفع",
          text: "تاكد من الاتصال بالإنترنت وحاول مرة أخرى",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      }
    },

    async addNewBill() {
      if (!this.currentPayment.amount || this.currentPayment.amount === '' || parseFloat(this.currentPayment.amount) <= 0) {
        this.$swal.fire({
          title: "تنبيه",
          text: "يرجى إدخال مبلغ صحيح",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      if (!this.currentPayment.date) {
        this.$swal.fire({
          title: "تنبيه",
          text: "يرجى إدخال تاريخ الدفعة",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      try {
        const token = localStorage.getItem('tokinn');
        if (!token) {
          this.$swal.fire({
            title: "خطأ في التحقق من الهوية",
            text: "يرجى تسجيل الدخول مرة أخرى",
            icon: "error",
            confirmButtonText: "اغلاق"
          });
          return;
        }

        // Create new bill
        const newBillData = {
          patient_id: this.patient.id,
          price: parseFloat(this.currentPayment.amount),
          PaymentDate: this.currentPayment.date,
          is_paid: 0, // Default to unpaid
          description: 'فاتورة جديدة'
        };

        const response = await this.$http.post('bills', newBillData, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.status === 201 || response.status === 200) {
          // Add the new bill to local state
          const newBill = response.data.bill || {
            ...newBillData,
            id: Date.now(), // Temporary ID if not returned from server
            user: {
              name: this.currentUser
            },
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
          };

          this.patientBills.push(newBill);
          
          // Reset form
          this.currentPayment = {
            amount: '',
            date: new Date().toISOString().split('T')[0]
          };

          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم إضافة الفاتورة بنجاح",
            showConfirmButton: false,
            timer: 1500
          });
        }
      } catch (error) {
        console.error('Error adding new bill:', error);
        this.$swal.fire({
          title: "حدث خطأ في إضافة الفاتورة",
          text: "تاكد من الاتصال بالإنترنت وحاول مرة أخرى",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      }
    },

    canEditBill(bill) {
      // Check if user can edit this bill based on role and bill ownership
      try {
        const role = this.$store.state.role;
        const currentUserId = this.$store.state.AdminInfo?.user_id;
        
        // Admin doctors can edit all bills
        if (role === 'adminDoctor') {
          return true;
        }
        
        // Doctors and secretaries can edit their own bills
        if (role === 'doctor' || role === 'secretary') {
          return bill.user_id === currentUserId;
        }
        
        return false;
      } catch (error) {
        console.error('Error checking bill edit permission:', error);
        return false;
      }
    },

    updateBillPrice(bill) {
      // Check if user can edit this bill
      if (!bill.isNew && !this.canEditBill(bill)) {
        this.$swal.fire({
          title: "تنبيه",
          text: "ليس لديك صلاحية لتعديل هذه الفاتورة",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      // Mark bill as modified for next save
      bill.modified = true;
      console.log('Bill price updated:', bill.id, 'New price:', bill.price);
      
      // Auto-save for new bills
      if (bill.isNew && bill.price && parseFloat(bill.price) > 0) {
        setTimeout(() => {
          this.saveNewBillRow(bill);
        }, 2000);
      }
    },

    updateBillDate(bill) {
      // Check if user can edit this bill
      if (!bill.isNew && !this.canEditBill(bill)) {
        this.$swal.fire({
          title: "تنبيه",
          text: "ليس لديك صلاحية لتعديل هذه الفاتورة",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      // Mark bill as modified for next save
      bill.modified = true;
      console.log('Bill date updated:', bill.id, 'New date:', bill.PaymentDate);
    },

    addPayment() {
      console.log('addPayment() method called - Add Bill button clicked!');
      
      // Check if user has permission to add bills
      const role = this.$store.state.role;
      if (role !== 'secretary' && role !== 'adminDoctor' && role !== 'doctor') {
        this.$swal.fire({
          title: "تنبيه",
          text: "ليس لديك صلاحية لإضافة فواتير",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      // Check if there's already a new bill being created
      const existingNewBill = this.patientBills.find(bill => bill.isNew === true);
      if (existingNewBill) {
        this.$swal.fire({
          title: "تنبيه",
          text: "يرجى حفظ الفاتورة الحالية أولاً",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      console.log('Current patient:', this.patient);
      console.log('Current bills before adding:', this.patientBills.length);
      
      const date = new Date();
      let day = date.getDate();
      let month = date.getMonth() + 1;
      let year = date.getFullYear();

      // Format month and day with leading zeros if needed
      if (month < 10) month = `0${month}`;
      if (day < 10) day = `0${day}`;

      // Create a new empty bill object
      const newBill = {
        id: Date.now(), // Temporary client-side ID
        server_id: null, // Will be set after saving to server
        billable_id: this.patient.id,
        patient_id: this.patient.id,
        price: '', // Empty for user input
        PaymentDate: `${year}-${month}-${day}`, // Today's date in YYYY-MM-DD format
        is_paid: 0, // Default to unpaid
        billable_type: "App\\Models\\Patients",
        user_id: this.$store.state.AdminInfo?.user_id, // Current user ID
        clinics_id: this.$store.state.AdminInfo?.clinics_id,
        user: {
          id: this.$store.state.AdminInfo?.user_id,
          name: this.$store.state.AdminInfo?.name || 'المستخدم الحالي'
        },
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        isNew: true, // Flag to identify new bills for editing
        modified: false
      };

      console.log('New bill created:', newBill);

      // Add the new bill to the end of the array so it appears at the bottom
      this.patientBills.push(newBill);
      
      console.log('Bills after adding:', this.patientBills.length);
      console.log('New bill added successfully!');
      
      // Show a success message to confirm the bill was added
      this.$swal.fire({
        position: "top-end",
        icon: "success",
        title: "تم إضافة فاتورة جديدة",
        text: "يمكنك الآن تعديل المبلغ والتاريخ",
        showConfirmButton: false,
        timer: 2000
      });
    },

    async saveNewBillRow(bill) {
      if (!bill.price || bill.price <= 0) {
        this.$swal.fire({
          title: "تنبيه",
          text: "يرجى إدخال مبلغ صحيح",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      if (!bill.PaymentDate) {
        this.$swal.fire({
          title: "تنبيه",
          text: "يرجى إدخال تاريخ الدفعة",
          icon: "warning",
          confirmButtonText: "اغلاق"
        });
        return;
      }

      try {
        const token = localStorage.getItem('tokinn');
        if (!token) {
          this.$swal.fire({
            title: "خطأ في التحقق من الهوية",
            text: "يرجى تسجيل الدخول مرة أخرى",
            icon: "error",
            confirmButtonText: "اغلاق"
          });
          return;
        }

        // Create bill data for API
        const billData = {
          patient_id: this.patient.id,
          price: parseFloat(bill.price),
          PaymentDate: bill.PaymentDate,
          is_paid: bill.is_paid || 0,
          description: bill.description || 'فاتورة جديدة'
        };

        const response = await this.$http.post('bills', billData, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.status === 201 || response.status === 200) {
          // Update the bill with server data
          const savedBill = response.data.bill || response.data;
          Object.assign(bill, {
            id: savedBill.id,
            server_id: savedBill.id,
            isNew: false, // No longer a new bill
            modified: false
          });

          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم حفظ الفاتورة بنجاح",
            showConfirmButton: false,
            timer: 1500
          });
        }
      } catch (error) {
        console.error('Error saving new bill row:', error);
       
      }
    },
    
    // Action methods
    editPatient() {
      this.editDialog = true;
    },
    
    async bookAppointment() {
      console.log('bookAppointment called, current dialog state:', this.appointmentDialog);
      
      // Prevent multiple dialogs from opening
      if (this.appointmentDialog) {
        console.log('Dialog already open, returning');
        return;
      }
      
      try {
        // Ensure doctors are loaded before opening appointment dialog
        if (this.doctors.length === 0) {
          console.log('Loading doctors...');
          await this.fetchDoctors();
        }
        
        console.log('Opening appointment dialog');
        this.appointmentDialog = true;
      } catch (error) {
        console.error('Error opening appointment dialog:', error);
      }
    },
    
    generateBill() {
      console.log('Opening bill dialog...');
      this.billDialog = true;
    },
    
    async savePatientEdit(eventData) {
      try {
        this.saving = true;
        
        const { patient: patientData, isEditing } = eventData;
        
        if (isEditing) {
          // Update existing patient
          const response = await this.$http.patch(`patients/${this.patient.id}`, patientData);
          
          // Update local patient data
          Object.assign(this.patient, response.data);
          
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم تحديث بيانات المراجع بنجاح",
            showConfirmButton: false,
            timer: 1500
          });
        } else {
          // This shouldn't happen in patient detail page, but handle it anyway
          const response = await this.$http.post('patients', patientData);
          console.log('New patient created:', response.data);
          
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم إضافة المراجع بنجاح",
            showConfirmButton: false,
            timer: 1500
          });
        }
        
        this.editDialog = false;
      } catch (error) {
        console.error('Error saving patient:', error);
        this.$swal.fire({
          title: "حدث خطأ في حفظ البيانات",
          text: "تاكد من ملء المعلومات بشكل صحيح",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      } finally {
        this.saving = false;
      }
    },
    
    closePatientEditDialog() {
      this.editDialog = false;
    },
    
    closeAppointmentDialog() {
      console.log('Closing appointment dialog');
      this.appointmentDialog = false;
    },
    
    saveAppointment() {
      this.appointmentDialog = false;
      // Save appointment logic here
    },
    
    deletePayment() {
      console.log('Deleting payment...');
    },
    
    async savePatientData() {
      this.saving = true;
      
      try {
        // Process new cases (cases without server ID)
        const newCases = this.patientCases.filter(caseItem => !caseItem.server_id && caseItem.id > 1000000);
        
        // Process existing cases that need updates (have server_id and are modified)
        const existingCases = this.patientCases.filter(caseItem => caseItem.server_id && caseItem.modified);
        
        // Save new cases
        for (const newCase of newCases) {
          await this.saveNewCase(newCase);
        }
        
        // Update existing cases
        for (const existingCase of existingCases) {
          await this.updateExistingCase(existingCase);
        }
        
        // Save bills using the specified API
        await this.saveBillsToAPI();
        
        // Clear patient cache since data has changed (cache removed)
        
        this.$swal.fire({
          position: "top-end",
          icon: "success",
          title: "تم حفظ البيانات بنجاح",
          showConfirmButton: false,
          timer: 1500
        });
        
        // Reload patient data to get updated information
        await this.loadPatientData();
        
      } catch (error) {
        console.error('Error saving patient data:', error);
        
        this.$swal.fire({
          title: "خطأ في حفظ البيانات",
          text: "تاكد من الاتصال بالإنترنت وحاول مرة أخرى",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      } finally {
        this.saving = false;
      }
    },

    // Save bills using the specified API format
    async saveBillsToAPI() {
      try {
        // Get bills that need to be saved (new bills or modified bills)
        const billsToSave = this.patientBills.filter(bill => 
          bill.isNew || bill.modified
        );

        if (billsToSave.length === 0) {
          console.log('No bills to save');
          return;
        }

        // Format bills according to the specified API structure
        const billsData = billsToSave.map(bill => ({
          price: bill.price.toString(),
          PaymentDate: bill.PaymentDate || new Date().toISOString().substr(0, 10),
          patient_id: this.patient.id.toString(),
          is_paid: bill.is_paid || 0
        }));

        const requestBody = {
          bills: billsData,
          patient_id: this.patient.id.toString()
        };

        console.log('Saving bills to API:', requestBody);

        const response = await this.$http.post(
          `https://apismartclinicv3.tctate.com/api/patients/bills/${this.patient.id}`, 
          requestBody,
          {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          }
        );

        console.log('Bills saved successfully:', response.data);

        // Mark bills as saved
        billsToSave.forEach(bill => {
          bill.isNew = false;
          bill.modified = false;
        });

      } catch (error) {
        console.error('Error saving bills to API:', error);
        throw error;
      }
    },

    // Clear patient cache data (cache functionality removed)
    clearPatientCache() {
      // Cache functionality has been removed - this is a placeholder
      console.log('Cache clearing functionality removed for patient ID:', this.patient.id);
    },

    async saveNewCase(caseItem) {
      try {
        // Find the operation details
        const operation = this.dentalOperations.find(op => 
          (op.name === caseItem.case_type || op.name_ar === caseItem.case_type)
        );
        
        // Prepare sessions array - include main note and additional sessions
        const sessionsArray = [
          {
            note: caseItem.notes || "",
            date: caseItem.date || new Date().toISOString().substr(0, 10)
          }
        ];
        
        // Add additional sessions if they exist
        if (caseItem.additionalSessions && caseItem.additionalSessions.length > 0) {
          caseItem.additionalSessions.forEach(session => {
            if (session.note && session.note.trim()) {
              sessionsArray.push({
                note: session.note.trim(),
                date: session.date || new Date().toISOString().substr(0, 10)
              });
            }
          });
        }
        
        const requestBody = {
          case_categores_id: operation ? operation.id : 11, // Default to 11 if not found
          tooth_num: [parseInt(caseItem.tooth_number)],
          status_id: caseItem.completed ? 43 : 42, // 43 = complete, 42 = not complete
          sessions: sessionsArray,
          bills: [
            {
              price: caseItem.price.toString(),
              PaymentDate: caseItem.date || new Date().toISOString().substr(0, 10),
              patient_id: this.patient.id.toString()
            }
          ],
          images: [],
          notes: caseItem.notes || "",
          price: caseItem.price.toString(),
          patient_id: this.patient.id.toString()
        };
        
        console.log('Saving new case:', requestBody);
        
        const response = await this.$http.post('cases', requestBody);
        console.log('Case saved successfully:', response.data);
        
        // Update the case with server ID
        caseItem.server_id = response.data.id;
        
      } catch (error) {
        console.error('Error saving new case:', error);
        throw error;
      }
    },

    async updateExistingCase(caseItem) {
      try {
        // Find the operation details
        const operation = this.dentalOperations.find(op => 
          (op.name === caseItem.case_type || op.name_ar === caseItem.case_type)
        );
        
        // Prepare sessions array with proper format
        let sessions = [];
        
        // Start with existing sessions
        if (caseItem.sessions && caseItem.sessions.length > 0) {
          sessions = caseItem.sessions
            .map(session => ({
              id: session.id,
              note: session.note || "",
              date: session.date || caseItem.date || new Date().toISOString().substr(0, 10),
              case_id: caseItem.server_id
            }));
        }
        
        // Add additional sessions
        if (caseItem.additionalSessions && caseItem.additionalSessions.length > 0) {
          caseItem.additionalSessions.forEach(session => {
            if (session.note && session.note.trim()) {
              sessions.push({
                note: session.note.trim(),
                date: session.date || new Date().toISOString().substr(0, 10),
                case_id: caseItem.server_id
              });
            }
          });
        }
        
        // If no sessions exist, create one with the main notes
        if (sessions.length === 0 && caseItem.notes) {
          sessions = [{
            note: caseItem.notes,
            date: caseItem.date || new Date().toISOString().substr(0, 10),
            case_id: caseItem.server_id
          }];
        }
        
        const requestBody = {
          case_categores_id: operation ? operation.id : caseItem.operation_id || 3,
          status_id: caseItem.completed ? 43 : 42, // 43 = complete, 42 = not complete
          images: [],
          tooth_num: `[${caseItem.tooth_number}]`, // String format for update
          notes: caseItem.notes || "",
          price: caseItem.price.toString(),
          sessions: sessions
        };
        
        console.log('Updating existing case:', caseItem.server_id, requestBody);
        
        const response = await this.$http.patch(`cases_v2/${caseItem.server_id}`, requestBody);
        console.log('Case updated successfully:', response.data);
        
        // Clear the modified flag after successful update
        caseItem.modified = false;
        
      } catch (error) {
        console.error('Error updating existing case:', error);
        throw error;
      }
    },

    async saveNewBill() {
      try {
        const requestBody = {
          bills: [
            {
              price: this.currentPayment.amount.toString(),
              PaymentDate: this.currentPayment.date,
              patient_id: this.patient.id.toString(),
              is_paid: this.currentPayment.paid ? 1 : 0
            }
          ],
          patient_id: this.patient.id.toString()
        };
        
        console.log('Saving new bill:', requestBody);
        
        const response = await this.$http.post(`patients/bills/${this.patient.id}`, requestBody);
        console.log('Bill saved successfully:', response.data);
        
        // Reset current payment
        this.currentPayment = {
          id: null,
          amount: 0,
          date: new Date().toISOString().substr(0, 10),
          paid: false,
          user_name: ''
        };
        
      } catch (error) {
        console.error('Error saving new bill:', error);
        throw error;
      }
    }
  },
  
  async mounted() {
    try {
      // Start loading immediately
      this.initialLoading = true;
      this.loadingMessage = 'تهيئة التطبيق...';

      // Set authorization header for dropzone
      if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.token) {
        this.dropzoneOptions.headers = {
          "Authorization": `Bearer ${this.$store.state.AdminInfo.token}`
        };
      }
      
      // Listen for tooth change events from the teeth component
      EventBus.$on('changetooth', this.handleToothChange);
      
      // Listen for tooth right-click events - try multiple event names
      EventBus.$on('toothRightClick', this.handleToothRightClick);
      EventBus.$on('tooth-right-click', this.handleToothRightClick);
      EventBus.$on('tooth_right_click', this.handleToothRightClick);
      EventBus.$on('rightClickTooth', this.handleToothRightClick);
      
      // Listen for bill report close event
      EventBus.$on('billsReportclose', () => {
        this.billDialog = false;
      });
      
      // Debug: Log all events on EventBus (only in development)
      if (process.env.NODE_ENV === 'development') {
        const originalEmit = EventBus.$emit;
        EventBus.$emit = function(event, ...args) {
          console.log('EventBus event emitted:', event, args);
          return originalEmit.call(this, event, ...args);
        };
      }
      
      // Load patient data and dental operations
      await this.loadPatientData();
      
      console.log('✅ Component mounted successfully');
      
    } catch (error) {
      console.error('❌ Error during component mount:', error);
      this.initialLoading = false;
    }
  },
  
  beforeDestroy() {
    try {
      // Clean up event listeners
      EventBus.$off('changetooth', this.handleToothChange);
      EventBus.$off('toothRightClick', this.handleToothRightClick);
      EventBus.$off('tooth-right-click', this.handleToothRightClick);
      EventBus.$off('tooth_right_click', this.handleToothRightClick);
      EventBus.$off('rightClickTooth', this.handleToothRightClick);
      EventBus.$off('billsReportclose');
      document.removeEventListener('click', this.hideContextMenu);
      
      // Clear any pending debounced functions
      if (this.debouncedAutoSave) {
        this.debouncedAutoSave = null;
      }
      
      console.log('✅ Component destroyed and cleaned up');
      
    } catch (error) {
      console.error('❌ Error during component destroy:', error);
    }
  }
}
</script>

<style scoped>
/* Performance optimizations */
.patient-detail-page {
  font-family: 'Cairo', sans-serif;
  contain: layout style paint;
  will-change: transform;
}

/* Lazy loading for images */
.lazy {
  opacity: 0;
  transition: opacity 0.3s;
}

.lazy.loaded {
  opacity: 1;
}

/* GPU acceleration for smooth animations */
.patient-header-card {
  border-radius: 8px;
  transform: translateZ(0);
  backface-visibility: hidden;
}

.patient-info-container h2 {
  color: #1976d2;
}

.whatsapp-link {
  color: #25d366;
  text-decoration: none;
}

.whatsapp-link:hover {
  color: #128c7e;
}

/* Dental Chart Styles */
.teeth-svg {
  position: relative;
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
}

.toomain {
  width: 100%;
  height: auto;
  cursor: pointer;
}

.tooth-context-menu {
  position: fixed;
  background: white;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  z-index: 1000;
  min-width: 150px;
  max-height: 300px;
  overflow-y: auto;
}

.context-menu-header {
  padding: 8px 16px;
  background-color: #f5f5f5;
  border-bottom: 1px solid #e0e0e0;
  font-weight: bold;
  color: #1976d2;
}

.tooth-context-menu ul {
  list-style: none;
  margin: 0;
  padding: 4px 0;
}

.tooth-context-menu li {
  padding: 8px 16px;
  cursor: pointer;
  font-size: 14px;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
}

.tooth-context-menu li:last-child {
  border-bottom: none;
}

.tooth-context-menu li:hover {
  background-color: #f5f5f5;
  color: #1976d2;
}

.teeth-container {
  position: relative;
  width: 100%;
  height: auto;
  overflow: visible;
}

.tooth-normal {
  fill: #e3f2fd;
  stroke: #1976d2;
  stroke-width: 1;
  cursor: pointer;
}

.tooth-normal:hover {
  fill: #bbdefb;
}

.tooth-with-case {
  fill: #ffecb3;
  stroke: #f57c00;
  stroke-width: 2;
  cursor: pointer;
}

.tooth-with-case:hover {
  fill: #ffe082;
}

.tooth-number-text {
  font-family: Arial, sans-serif;
  font-size: 12px;
  fill: #424242;
  text-anchor: middle;
  pointer-events: none;
}

/* Table Styles */
.teeth-template-title,
.bills-template-title {
  background-color: #f5f5f5;
  border-bottom: 1px solid #e0e0e0;
}

.teeth-template-card,
.bills-template-card {
  margin-bottom: 20px;
}

.tooth-number-cell {
  display: flex;
  justify-content: center;
}

.price-input {
  max-width: 120px;
}

.notes-textarea {
  font-size: 14px;
}

.notes-container {
  width: 100%;
}

.main-note {
  border-left: 3px solid #2196f3;
}

.session-note {
  background-color: #fafafa;
  border-left: 3px solid #e0e0e0;
}

.new-session {
  border-left: 3px solid #4caf50;
  background-color: #f1f8e9;
}

.session-date-chip {
  min-width: 60px;
  font-size: 10px;
}

.session-note .v-text-field__details {
  display: none;
}

.bills-table .v-data-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #333;
}

.bills-table .v-data-table tbody tr:hover {
  background-color: #f5f5f5;
}

.bill-id-cell {
  display: flex;
  justify-content: center;
}

.price-cell {
  text-align: center;
  font-weight: 600;
  color: #2e7d32;
}

.date-cell {
  text-align: center;
  font-size: 14px;
  color: #424242;
}

.user-name-cell {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #424242;
}

.bills-history-table {
  margin-top: 20px;
}

/* Billing Styles */
.cre_bill {
  background-color: #fafafa;
  border-radius: 8px;
}

.se_tit_menu {
  font-weight: bold;
  font-size: 18px;
  color: #1976d2;
  margin: 0;
  padding: 8px 0;
}

.user-created {
  font-size: 12px;
}

.payment-status-label {
  font-size: 12px;
  margin-top: 5px;
}

/* Bills Payment Loop Styles - Optimized for performance */
.bills-payment-loop {
  max-height: 400px;
  overflow-y: auto;
  padding: 8px;
  contain: layout style paint;
  transform: translateZ(0);
}

.bill-payment-item {
  background-color: #f9f9f9;
  border-radius: 6px;
  padding: 12px;
  margin-bottom: 12px;
  border-left: 4px solid #1976d2;
  transition: all 0.2s ease;
  contain: layout style paint;
  will-change: transform, box-shadow;
}

.bill-payment-item:hover {
  background-color: #f0f8ff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.bill-payment-item.paid {
  border-left-color: #4caf50;
  background-color: #f1f8e9;
}

.bill-payment-item.new-bill-highlight {
  border-left-color: #ff9800;
  background-color: #fff3e0;
  border: 2px solid #ff9800;
}

.new-bill-section {
  background-color: #e8f5e8;
  border-radius: 8px;
  padding: 16px;
  margin-top: 16px;
  border: 2px dashed #4caf50;
}

.bill-divider {
  margin: 16px 0;
  opacity: 0.5;
}

/* Dropzone Styles */
.dropzone-container {
  border: 2px dashed #ccc;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  background-color: #fafafa;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dropzone-container:hover {
  border-color: #1976d2;
  background-color: #f0f8ff;
}

.dz-message {
  color: #666;
  font-size: 16px;
}

.dz-message i {
  font-size: 24px;
  margin-right: 8px;
  color: #1976d2;
}

/* Responsive Design */
@media (max-width: 768px) {
  .patient-header-card .v-btn {
    margin: 2px;
    font-size: 12px;
  }
  
  .teeth-svg {
    transform: scale(0.8);
  }
  
  .tooth-context-menu {
    min-width: 120px;
  }
  
  .tooth-context-menu li {
    padding: 6px 12px;
    font-size: 12px;
  }
}

/* Animation */
.v-card {
  transition: all 0.3s ease;
}

.v-btn {
  transition: all 0.2s ease;
}

.v-chip {
  transition: all 0.2s ease;
}

/* Dropzone Styles */
.dropzone-container {
  border: 2px dashed #007bff !important;
  border-radius: 8px !important;
  background: white !important;
  min-height: 150px !important;
  padding: 20px !important;
}

.dropzone-container .dz-message {
  font-size: 16px !important;
  color: #007bff !important;
  font-family: 'Cairo', sans-serif !important;
  text-align: center !important;
}

.dropzone-container .dz-message i {
  display: block !important;
  margin-bottom: 10px !important;
  font-size: 24px !important;
}

.dropzone-container:hover {
  border-color: #0056b3 !important;
  background-color: #f8f9fa !important;
}

.dropzone-container .dz-preview {
  margin: 10px !important;
}

.dropzone-container .dz-preview .dz-image {
  border-radius: 5px !important;
}

.dropzone-container .dz-preview .dz-remove {
  color: #dc3545 !important;
  font-size: 12px !important;
}

.dropzone-container .dz-preview .dz-remove:hover {
  text-decoration: underline !important;
}
</style>
