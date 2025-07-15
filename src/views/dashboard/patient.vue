<template>
  <v-container fluid class="patient-detail-page">
    <!-- Patient Header Card -->
    <v-card class="mb-4 patient-header-card" outlined>
      <v-row no-gutters align="center">
        <!-- Patient Avatar -->
        <v-col cols="auto" class="pa-4">
          <v-avatar size="60" class="mr-3">
            <v-icon  size="70" color="grey lighten-1">
              mdi-account-circle
            </v-icon>
           
          </v-avatar>
        </v-col>

        <!-- Patient Info -->
        <v-col>
          <div class="d-flex flex-column patient-info-container">
            <h2 class="text-h5 font-weight-medium mb-1" style="font-family: Cairo, sans-serif !important;">
              {{ patient.name || 'جاري التحميل...' }}
            </h2>
            <div class="patient-sub-info" v-if="patient.phone">
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
            @click="bookAppointment()"
   
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
            <div v-if="secretaryBillsOnlyMode" class="pa-4 text-center grey--text">
              <h3>وضع السكرتارية: الأسنان مخفية</h3>
              <p>Role: {{ $store.state.role }}, PaidAtSecretary: {{ $store.state.AdminInfo?.clinics_info?.paid_at_secretary }}</p>
              <p>SecretaryBillsOnlyMode: {{ secretaryBillsOnlyMode }}</p>
            </div>

            <v-card class="mb-4" outlined v-if="!secretaryBillsOnlyMode">
              <v-card-text class="text-center">
                <!-- Context menu and right-click are now handled inside teeth component -->
                <div class="teeth-container">
               
                  <teeth 
                    :categories="dentalOperations"
                    :tooth_num="selectedTeethNumbers" 
                    :id="1"
                    @case-added="handleCaseAdded"
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
                        hide-default-footer
                        disable-sort
                        @click="handleDataTableClick"
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
                            placeholder="مبلغ الحالة"
                            suffix="IQ"
                            dense
                            hide-details
                            class="price-input"
                            @input="updateCasePrice(item)"
                            @click.stop="hideContextMenu()"
                            @focus.stop="hideContextMenu()"
                            :value="item.price || ''"
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
              class="bill-payment-item mb-3"
              :class="{ 
                'paid': bill.is_paid == 1, 
                'unpaid': bill.is_paid == 0,
                'new-bill-highlight': bill.isNew
              }"
            >
              <!-- Mobile Layout -->
              <div class="d-flex d-sm-none flex-column mobile-bill-layout">
                <!-- User Info at the top -->
                <div class="mobile-user-info-header mb-3">
                  <v-icon size="14" class="mr-1">mdi-account</v-icon>
                  <span class="grey--text text--darken-1 font-weight-medium">{{ bill.user ? bill.user.name : 'غير محدد' }}</span>
                </div>

                <!-- Payment Amount -->
                <div class="mobile-field-container mb-2">
                  <v-text-field
                    v-model="bill.price"
                    label="مبلغ الدفعة"
                    suffix="IQ"
                    type="number"
                    outlined
                    dense
                    :disabled="!canEditBills"
                    @input="updateBillPrice(bill)"
                  />
                </div>

                <!-- Payment Date and Status Row -->
                <div class="d-flex mobile-date-status-row mb-2">
                  <div class="flex-grow-1 mr-2">
                    <v-text-field
                      v-model="bill.PaymentDate"
                      label="التاريخ"
                      type="date"
                      outlined
                      dense
                      :disabled="!canEditBills"
                      @input="updateBillDate(bill)"
                    >
                      <template v-slot:prepend>
                        <v-icon>mdi-calendar</v-icon>
                      </template>
                    </v-text-field>
                  </div>
                  
                  <div class="mobile-status-container">
                    <v-switch
                      :input-value="bill.is_paid == 1"
                      :disabled="!canChangePaymentStatus"
                      inset
                      dense
                      @change="toggleBillPaymentStatus(bill)"
                    />
                    <div class="mobile-status-label">
                      {{ bill.is_paid == 1 ? 'مدفوع' : 'غير مدفوع' }}
                    </div>
                  </div>
                </div>

                <!-- Delete Button -->
                <div class="mobile-actions text-center" v-if="canDeleteBills">
                  <v-btn
                    icon
                    small
                    color="error"
                    @click="deleteBill(bill)"
                  >
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </div>
              </div>

              <!-- Desktop Layout -->
              <v-layout row wrap class="d-none d-sm-flex">
                <v-flex md2 class="d-none d-md-flex"></v-flex>
                
                <!-- Payment Amount -->
                <v-flex md3 sm4>
                  <v-text-field
                    v-model="bill.price"
                    label="مبلغ الدفعة"
                    suffix="IQ"
                    type="number"
                    outlined
                    dense
                    :disabled="!canEditBills"
                    @input="updateBillPrice(bill)"
                  />
                  <div class="desktop-user-info">
                    <v-icon size="12" class="mr-1">mdi-account</v-icon>
                    <span class="grey--text text--darken-1">{{ bill.user ? bill.user.name : 'غير محدد' }}</span>
                  </div>
                </v-flex>

                <!-- Payment Date -->
                <v-flex md3 sm4>
                  <v-text-field
                    v-model="bill.PaymentDate"
                    label="التاريخ"
                    type="date"
                    outlined
                    dense
                    :disabled="!canEditBills"
                    @input="updateBillDate(bill)"
                  >
                    <template v-slot:prepend>
                      <v-icon>mdi-calendar</v-icon>
                    </template>
                  </v-text-field>
                </v-flex>

                <!-- Payment Status -->
                <v-flex md2 sm3 class="mt-2 text-center">
                  <v-switch
                    :input-value="bill.is_paid == 1"
                    :disabled="!canChangePaymentStatus"
                    inset
                    style="position: relative; padding-right: 10px; bottom: 21px;"
                    @change="toggleBillPaymentStatus(bill)"
                  />
                  <div class="caption text-center payment-status-label grey--text desktop-status-positioning">
                    {{ bill.is_paid == 1 ? 'مدفوع' : 'بانتظار التسديد' }}
                  </div>
                </v-flex>

                <!-- Delete Button -->
                <v-flex md1 sm1 class="text-center" v-if="canDeleteBills">
                  <v-btn
                    icon
                    color="error"
                    @click="deleteBill(bill)"
                    class="delete-bill-btn"
                  >
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </v-flex>
                
                <v-flex md1 class="d-none d-md-flex"></v-flex>
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

        <!-- Save Button (Hidden for secretaries when they can't create bills) -->
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

    <!-- Appointment Booking Dialog -->
    <OwnerBooking 
  
              :patientFound="true" 
              :patientInfo="patientInfo" 
              :doctors="doctors"
              v-if="appointmentDialog"
            />

    <!-- Bill Report Dialog -->
    <v-dialog v-model="billDialog" max-width="900px" v-track-dialog>
      <v-card>
        <Bill :patient="{ ...patient, bills: patientBills }" />
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
      saving: false,
      loadingDoctors: false,
      
      // Patient Data (will be loaded from API)
      patient: {
        id: null,
        name: '',
        phone: '',
        age: null,
        address: '',
        email: '',
        sex: '',
        systemic_conditions: '',
birth_date: ''
      },
      
      // Dental Operations (will be loaded from API)
      dentalOperations: [],
      
      // Context menu state is now managed in teeth component
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
        url: "https://apismartclinicv3.tctate.com/api/cases/uploude_image",
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
        // If paid_at_secretary is false (0), only secretary can change payment status
        else {
          return role === 'secretary';
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
        // Only doctors and adminDoctors can create bills, secretaries cannot
        return role === 'adminDoctor' || role === 'doctor';
      } catch (error) {
        console.error('Error checking add bills permission:', error);
        return false;
      }
    },



    // Patient info formatted for OwnerBooking component
    patientInfo() {

      console.log('📋 Formatting patient info for booking component:', this.patient);
      return {
        id: this.patient.id,
        name: this.patient.name,
        phone: this.patient.phone,
        age: this.patient.age,
        address: this.patient.address,
        email: this.patient.email,
        sex: this.patient.sex,
        birth_date: this.patient.birth_date,
        systemic_conditions: this.patient.systemic_conditions,
        bills: this.patient.bills
      };
    },

    // Check if current user is a secretary with limited permissions
    secretaryBillsOnlyMode() {
      try {
        const role = this.$store.state.role;
        const paidAtSecretary = this.$store.state.AdminInfo?.clinics_info?.paid_at_secretary;
        
        console.log('🔍 Debug secretaryBillsOnlyMode:', { 
          role, 
          paidAtSecretary, 
          storeState: this.$store.state,
          adminInfo: this.$store.state.AdminInfo 
        });
        
        // If paid_at_secretary is true (1) and user is secretary, show limited mode
        const isSecretaryOnlyMode = role === 'secretary' && (paidAtSecretary == 1 || paidAtSecretary === true);
        console.log('🔍 Secretary bills only mode result:', isSecretaryOnlyMode);
        console.log('🔍 Should show teeth component:', !isSecretaryOnlyMode);
        
        return isSecretaryOnlyMode;
      } catch (error) {
        console.error('Error checking secretary mode:', error);
        return false;
      }
    },

    // Check if current user can edit bills (price and date)
    canEditBills() {
      try {
        const role = this.$store.state.role;
        // Only doctors and adminDoctors can edit bills, secretaries cannot
        return role === 'adminDoctor' || role === 'doctor';
      } catch (error) {
        console.error('Error checking edit bills permission:', error);
        return false;
      }
    },

    // Check if current user can delete bills
    canDeleteBills() {
      try {
        const role = this.$store.state.role;
        // Only doctors and adminDoctors can delete bills, secretaries cannot
        return role === 'adminDoctor' || role === 'doctor';
      } catch (error) {
        console.error('Error checking delete bills permission:', error);
        return false;
      }
    }
  },
  
  methods: {
       generateBill() {
      this.billDialog = true;
    },

    // Handle case added from teeth component
    handleCaseAdded(caseData) {
      console.log('Case added from teeth component:', caseData);
      
      if (!caseData || !caseData.toothNumber || !caseData.operation) {
        console.error('Invalid case data received:', caseData);
        return;
      }
      
      // Get operation name
      const operationName = caseData.operation.name || caseData.operation.name_ar;
      
      // Create new case object (allowing multiple categories for same tooth)
      const newCase = {
        id: Date.now() + Math.floor(Math.random() * 10000), // Unique temporary ID
        server_id: null, // Will be set after saving to server
        tooth_number: caseData.toothNumber,
        case_type: operationName,
        date: new Date().toISOString().substr(0, 10),
        price: null,
        completed: false,
        notes: '',
        operation_id: caseData.operation.id,
        status_id: 42, // Default status (not completed)
        sessions: [],
        additionalSessions: [],
        modified: true // Mark as new/modified for save
      };
      
      // Add to the beginning of the cases array
      this.patientCases.unshift(newCase);
      
      // Force UI update
      this.$nextTick(() => {
        this.$forceUpdate();
      });
      
      console.log('New case added:', newCase);
      
     
    },

    // Fetch dental operations from API
  async fetchDentalOperations() {
    try {
      console.log('🦷 Fetching dental operations...');
      
      // Add timeout to prevent hanging
      const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Dental operations request timeout')), 10000); // 10 seconds timeout
      });

      const response = await Promise.race([
        this.$http.get('cases/CaseCategories', {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        }),
        timeoutPromise
      ]);
      
      console.log('🦷 Raw API response:', response.data);
      
      this.dentalOperations = response.data.map(category => ({
        id: category.id,
        name: category.name_ar,
        name_en: category.name_en,
        order: category.order
      }));
      
      console.log('🦷 Dental operations loaded:', this.dentalOperations.length, this.dentalOperations);
      
      // Force reactivity update
      this.$forceUpdate();
      
    } catch (error) {
      console.error('❌ Error fetching dental operations, using fallback:', error);
      // Fallback to default operations if API fails
      this.dentalOperations = [
        { id: 1, name: 'قلع' },
        { id: 2, name: 'حشوة' },
        { id: 3, name: 'فحص' }
      ];
      console.log('🦷 Using fallback dental operations:', this.dentalOperations);
      
      // Force reactivity update
      this.$forceUpdate();
    }
  },

    // Fetch doctors from API
    async fetchDoctors() {
      try {
        this.loadingDoctors = true;
        console.log('👨‍⚕️ Fetching doctors...');
        
        const response = await this.$http.get('doctors/clinic', {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });
        
        this.doctors = response.data.data.map(doctor => ({
          id: doctor.id,
          name: doctor.name,
          name_ar: doctor.name_ar || doctor.name,
          specialty: doctor.specialty,
          phone: doctor.phone,
          email: doctor.email
        }));
        
        console.log('👨‍⚕️ Doctors loaded:', this.doctors.length);
        this.loadingDoctors = false;
      } catch (error) {
        console.error('❌ Error fetching doctors:', error);
        this.loadingDoctors = false;
        
        // Fallback to default doctor if API fails
        this.doctors = [
          { id: 1, name: 'طبيب افتراضي', name_ar: 'طبيب افتراضي' }
        ];
      }
    },

    // Load patient data from API
    // Override loadInitialData from mixin
    async loadInitialData() {
      await this.loadPatientData();
    },

    async loadPatientData() {
      try {
        console.log('🔄 Starting loadPatientData...');

        // Get patient ID from route
        const patientId = this.$route.params.id;
        console.log('🆔 Patient ID from route:', patientId);
        
        if (!patientId) {
          throw new Error('Patient ID not found in route');
        }

        // Check if we have token
        const token = this.$store.state.AdminInfo?.token;
        console.log('🔑 Token available:', !!token);
        
        if (!token) {
          throw new Error('No authentication token found');
        }

        // Load patient data using the new API endpoint
        console.log('📡 Making API request to get patient data...');
        
        const response = await this.$http.get(`https://apismartclinicv4.tctate.com/api/getPatientById/${patientId}`, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + token
          }
        });

        console.log('📡 API response received:', response.status);
        const data = response.data;
        console.log('📊 Patient data structure:', Object.keys(data));
        
        // Set patient basic info
        console.log('👤 Processing patient basic info...');
        this.patient = {
          id: data.id,
          name: data.name,
          phone: data.phone,
          age: data.age,
          address: data.address,
          email: data.email,
          sex: data.sex,
          systemic_conditions: data.systemic_conditions,
          birth_date: data.birth_date,
          notes: data.notes,
          rx_id: data.rx_id
        };
        console.log('👤 Patient basic info set:', this.patient.name);

        // Process cases data
        console.log('📋 Processing cases data...');
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
        console.log('📋 Cases processed:', this.patientCases.length);

        // Process bills data
        console.log('💰 Processing bills data...');
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
        console.log('💰 Bills processed:', this.patientBills.length);

        // Load dental operations and doctors with individual error handling
        try {
          await this.fetchDentalOperations();
        } catch (error) {
          console.error('❌ Failed to load dental operations:', error);
        }

        // Load doctors for appointment booking
        try {
          await this.fetchDoctors();
        } catch (error) {
          console.error('❌ Failed to load doctors:', error);
        }

        console.log('✅ Patient data loaded successfully');
        console.log('Patient:', this.patient);
        console.log('Cases:', this.patientCases);
        console.log('Bills:', this.patientBills);
      } catch (error) {
        console.error('❌ Error loading patient data:', error);
        
        // More detailed error handling
        let errorMessage = "تعذر تحميل بيانات المريض. يرجى المحاولة مرة أخرى.";
        let errorTitle = "خطأ في تحميل البيانات";
        
        if (error.message === 'Request timeout') {
          errorMessage = "انتهت مهلة الاتصال. يرجى التحقق من الإنترنت والمحاولة مرة أخرى.";
          errorTitle = "انتهت مهلة الاتصال";
        } else if (error.response && error.response.status === 401) {
          errorMessage = "انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى.";
          errorTitle = "خطأ في التحقق من الهوية";
        } else if (error.response && error.response.status === 404) {
          errorMessage = "لم يتم العثور على بيانات المريض. يرجى التحقق من الرابط.";
          errorTitle = "المريض غير موجود";
        }
        
        this.$swal.fire({
          title: errorTitle,
          text: errorMessage,
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
      
      // Calculate position based on tooth number and viewport size
      const toothNum = parseInt(toothNumber);
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;
      
      // Context menu approximate dimensions (based on actual CSS)
      const menuWidth = 180; // min-width: 150px + padding
      const menuHeight = 350; // max-height: 300px + header + padding
      
      // Calculate base position (center of viewport as fallback)
      let baseX = viewportWidth / 2;
      let baseY = viewportHeight / 2;
      
      // Adjust position based on tooth quadrant
      if (toothNum >= 11 && toothNum <= 18) {
        // Upper right quadrant
        baseX = viewportWidth * 0.75;
        baseY = viewportHeight * 0.3;
      } else if (toothNum >= 21 && toothNum <= 28) {
        // Upper left quadrant
        baseX = viewportWidth * 0.25;
        baseY = viewportHeight * 0.3;
      } else if (toothNum >= 31 && toothNum <= 38) {
        // Lower left quadrant
        baseX = viewportWidth * 0.25;
        baseY = viewportHeight * 0.7;
      } else if (toothNum >= 41 && toothNum <= 48) {
        // Lower right quadrant
        baseX = viewportWidth * 0.75;
        baseY = viewportHeight * 0.7;
      }
      
      // Ensure menu doesn't go off-screen
      baseX = Math.max(10, Math.min(baseX, viewportWidth - menuWidth - 10));
      baseY = Math.max(10, Math.min(baseY, viewportHeight - menuHeight - 10));
      
      this.contextMenuStyle = {
        position: 'fixed',
        top: baseY + 'px',
        left: baseX + 'px',
        zIndex: 1000,
        display: 'block'
      };
      this.showContextMenu = true;
      
      // Hide menu when clicking elsewhere
      document.addEventListener('click', this.hideContextMenu);
    },
    
    // Handle right-click events from teeth component
    handleToothRightClick(data) {
      console.log('Tooth right-clicked received in patient component:', data);
      
      // CRITICAL: Double-check that no form element is currently active
      const activeElement = document.activeElement;
      if (activeElement && this.isFormElement(activeElement)) {
        console.log('Form element is active, blocking context menu in patient component');
        return;
      }
      
      // CRITICAL: Check if the event originated from a form element
      if (data && data.event && data.event.target && this.isFormElement(data.event.target)) {
        console.log('Event originated from form element, blocking context menu');
        return;
      }
      
      if (data && data.toothId && data.event) {
        console.log('Processing right-click for tooth:', data.toothId);
        this.selectedTooth = data.toothId;
        
        // Get the viewport coordinates
        let x = data.event.clientX;
        let y = data.event.clientY;
        
        // Calculate better positioning based on tooth number and viewport size
        const toothNum = parseInt(data.toothId);
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Context menu approximate dimensions (based on actual CSS)
        const menuWidth = 180; // min-width: 150px + padding
        const menuHeight = 350; // max-height: 300px + header + padding
        
        // Adjust horizontal position based on tooth location
        if (toothNum >= 11 && toothNum <= 18) {
          // Upper right quadrant - position menu to the left of click
          x = Math.max(10, x - menuWidth - 10);
        } else if (toothNum >= 21 && toothNum <= 28) {
          // Upper left quadrant - position menu to the right of click
          x = Math.min(viewportWidth - menuWidth - 10, x + 10);
        } else if (toothNum >= 31 && toothNum <= 38) {
          // Lower left quadrant - position menu to the right of click
          x = Math.min(viewportWidth - menuWidth - 10, x + 10);
        } else if (toothNum >= 41 && toothNum <= 48) {
          // Lower right quadrant - position menu to the left of click
          x = Math.max(10, x - menuWidth - 10);
        } else {
          // Default positioning - try to keep menu in viewport
          if (x + menuWidth > viewportWidth) {
            x = viewportWidth - menuWidth - 10;
          }
        }
        
        // Adjust vertical position to keep menu in viewport
        if (y + menuHeight > viewportHeight) {
          y = Math.max(10, y - menuHeight);
        }
        
        // Ensure menu doesn't go off-screen
        x = Math.max(10, Math.min(x, viewportWidth - menuWidth - 10));
        y = Math.max(10, Math.min(y, viewportHeight - menuHeight - 10));
        
        console.log('Context menu position:', { x, y, toothNum });
        
        // Position context menu at calculated location (fixed positioning relative to viewport)
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
      // This method is disabled now - we only want to show menu on direct tooth right-click
      console.log('Global right-click disabled');
      event.preventDefault();
      return;
    },
    
    // Handle right-click events specifically from teeth component
    handleTeethContextMenu(event) {
      // This method is no longer used for general teeth container context menu
      // All context menu handling is done through handleToothRightClick
      event.preventDefault();
      return;
    },
    
    // Show general context menu when no specific tooth is detected
    showGeneralContextMenu(event) {
      // Don't show general context menu, we only want to show menu when clicking on a specific tooth
      console.log('Ignoring general right-click event');
      event.preventDefault();
      return;
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
    
    hideContextMenu(event) {
      // If no event is passed, force hide the menu (called programmatically)
      if (!event) {
        this.showContextMenu = false;
        this.selectedTooth = null;
        document.removeEventListener('click', this.hideContextMenu);
        return;
      }
      
      // Don't hide if clicking on the context menu itself
      if (event && event.target && event.target.closest('.tooth-context-menu')) {
        return;
      }
      
      // ALWAYS hide the context menu when interacting with form elements
      if (event && event.target && this.isFormElement(event.target)) {
        console.log('Hiding context menu due to form element interaction');
        this.showContextMenu = false;
        this.selectedTooth = null;
        event.stopPropagation();
        document.removeEventListener('click', this.hideContextMenu);
        return;
      }
      
      // Hide context menu for any other clicks
      this.showContextMenu = false;
      this.selectedTooth = null;
      document.removeEventListener('click', this.hideContextMenu);
    },

    // Add this new method to handle clicks on the data table
    handleDataTableClick() {
      // When interacting with the data table, always hide the context menu
      this.showContextMenu = false;
      this.selectedTooth = null;
      
      // Remove context menu event listener
      document.removeEventListener('click', this.hideContextMenu);
    },

    // Select dental operation from context menu
    selectOperation(operation) {

   
      // alert('تم اختيار العملية: ' + (operation.name || operation.name_ar));
      if (this.selectedTooth && operation) {
        // Defensive: ensure patientCases is always reactive and not null
        if (!Array.isArray(this.patientCases)) {
          this.$set(this, 'patientCases', []);
        }
        // Always add a new object (force new reference)
        const operationName = operation.name || operation.name_ar;
        const newCase = {
          id: Date.now() + Math.floor(Math.random() * 10000), // Unique id
          server_id: null,
          tooth_number: this.selectedTooth,
          case_type: operationName,
          date: new Date().toISOString().substr(0, 10),
          price: null,
          completed: false,
          notes: '',
          operation_id: operation.id,
          status_id: 42,
          sessions: [],
          additionalSessions: [],
          modified: true
        };
        this.patientCases.unshift(newCase);
        // Force update after DOM update
        this.$nextTick(() => this.$forceUpdate());
        this.selectedTooth = null;
      }
      this.hideContextMenu();
    },

    // Add new case for the patient
   addCase(toothNumber, operation) {
      // Check if case already exists for this tooth and operation
      const operationName = operation.name || operation.name_ar;
      const existingCase = this.patientCases.find(
        case_item => case_item.tooth_number == toothNumber && case_item.case_type === operationName
      );
      
      if (existingCase) {
        return;
      }

      const newCase = {
        id: Date.now(), // Temporary client-side ID
        server_id: null, // Will be set after saving to server
        tooth_number: toothNumber,
        case_type: operationName,
        date: new Date().toISOString().substr(0, 10),
        price: null,
        completed: false,
        notes: '',
        operation_id: operation.id,
        status_id: 42, // Default status
        sessions: [],
        additionalSessions: [],
        modified: true // Mark as new/modified for save
      };
      
      // Only add to local array - no API call
      this.patientCases.unshift(newCase);
      this.selectedTooth = null;
    },
    
    // Update case price
    updateCasePrice(caseItem) {
      // Find the case in the array
      const index = this.patientCases.findIndex(c => c.id === caseItem.id);
      if (index !== -1) {
        // Update the price locally
        this.patientCases[index].price = caseItem.price;
        this.patientCases[index].modified = true; // Mark as modified for save
      }
    },
    
    // Update case status
    updateCaseStatus(caseItem) {
      // Find the case in the array
      const index = this.patientCases.findIndex(c => c.id === caseItem.id);
      if (index !== -1) {
        // Update the status locally
        this.patientCases[index].completed = caseItem.completed;
        this.patientCases[index].modified = true; // Mark as modified for save
      }
    },
    
    // Update case notes
    updateCaseNotes(caseItem) {
      // Find the case in the array
      const index = this.patientCases.findIndex(c => c.id === caseItem.id);
      if (index !== -1) {
        // Update the notes locally
        this.patientCases[index].notes = caseItem.notes;
        this.patientCases[index].modified = true; // Mark as modified for save
      }
    },
    
    // Update existing session note
    updateExistingSessionNote(caseItem) {
      // Find the case in the array
      const index = this.patientCases.findIndex(c => c.id === caseItem.id);
      if (index !== -1) {
        // Update the sessions array
        this.patientCases[index].sessions = caseItem.sessions;
      }
    },
    
    // Update new session note
    updateSessionNote(caseItem) {
      // Find the case in the array
      const index = this.patientCases.findIndex(c => c.id === caseItem.id);
      if (index !== -1) {
        // Update the additionalSessions array
        this.patientCases[index].additionalSessions = caseItem.additionalSessions;
      }
    },
    
    // Add a new note (session) to the case
    addNote(caseItem) {
      // Find the case in the array
      const index = this.patientCases.findIndex(c => c.id === caseItem.id);
      if (index !== -1) {
        // Create a new session object
        const newSession = {
          id: Date.now(), // Temporary ID
          note: '',
          date: new Date().toISOString().substr(0, 10)
        };
        
        // Add the new session to the case
        this.patientCases[index].additionalSessions.push(newSession);
        
        // Optimistically update the UI
        this.$forceUpdate();
      }
    },
    
    // Delete a case
    deleteCase(caseItem) {
      // Confirm deletion
      this.$swal.fire({
        title: "تأكيد الحذف",
        text: "هل أنت متأكد أنك تريد حذف هذه الحالة؟",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "نعم، احذفها",
        cancelButtonText: "لا، تراجع"
      }).then((result) => {
        if (result.isConfirmed) {
          // Find the case in the array
          const index = this.patientCases.findIndex(c => c.id === caseItem.id);
          if (index !== -1) {
            // Remove the case from the array
            this.patientCases.splice(index, 1);
            
            // Show success message
            this.$swal.fire({
              title: "تم الحذف",
              text: "تم حذف الحالة بنجاح",
              icon: "success",
              confirmButtonText: "موافق"
            });
          }
        }
      });
    },
    
    // Add a new payment
    addPayment() {
      // Check if user has permission to create bills
      const role = this.$store.state.role;
      if (role === 'secretary') {
        this.$swal.fire({
          title: "غير مسموح",
          text: "ليس لديك صلاحية لإنشاء فواتير جديدة",
          icon: "error",
          confirmButtonText: "موافق"
        });
        return;
      }
      
      // Create a new bill object
      const newBill = {
        id: Date.now(), // Temporary ID
        price: 0,
        PaymentDate: new Date().toISOString().substr(0, 10),
        is_paid: 0,
        user_id: this.$store.state.AdminInfo.user_id,
        clinics_id: this.$store.state.AdminInfo.clinics_id,
        isNew: true, // Mark as new bill to be saved
        user: {
          id: this.$store.state.AdminInfo.user_id,
          name: this.$store.state.AdminInfo.name
        }
      };
      
      // Add the new bill to the patientBills array
      this.patientBills.push(newBill);
      
      // Optimistically update the UI
      this.$forceUpdate();
      

    },
    
    // Update bill price
    updateBillPrice(bill) {
      // Check if current user can edit bills
      if (!this.canEditBills) {
        this.$swal.fire({
          title: "غير مسموح",
          text: "لا يمكن للسكرتارية تعديل مبلغ الفاتورة",
          icon: "warning",
          confirmButtonText: "موافق"
        });
        return;
      }

      // Find the bill in the array
      const index = this.patientBills.findIndex(b => b.id === bill.id);
      if (index !== -1) {
        // Update the price locally
        this.patientBills[index].price = bill.price;
        // Mark as modified for saving (unless it's already marked as new)
        if (!this.patientBills[index].isNew) {
          this.patientBills[index].modified = true;
        }
      }
    },
    
    // Update bill date
    updateBillDate(bill) {
      // Check if current user can edit bills
      if (!this.canEditBills) {
        this.$swal.fire({
          title: "غير مسموح",
          text: "لا يمكن للسكرتارية تعديل تاريخ الفاتورة",
          icon: "warning",
          confirmButtonText: "موافق"
        });
        return;
      }

      // Find the bill in the array
      const index = this.patientBills.findIndex(b => b.id === bill.id);
      if (index !== -1) {
        // Update the date locally
        this.patientBills[index].PaymentDate = bill.PaymentDate;
        // Mark as modified for saving (unless it's already marked as new)
        if (!this.patientBills[index].isNew) {
          this.patientBills[index].modified = true;
        }
      }
    },
    
    // Delete bill
    deleteBill(bill) {
      // Check if current user can delete bills
      if (!this.canDeleteBills) {
        this.$swal.fire({
          title: "غير مسموح",
          text: "لا يمكن للسكرتارية حذف الفاتورة",
          icon: "warning",
          confirmButtonText: "موافق"
        });
        return;
      }

      // Show confirmation dialog
      this.$swal.fire({
        title: "تأكيد الحذف",
        text: "هل أنت متأكد من حذف هذه الفاتورة؟",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "نعم، احذف",
        cancelButtonText: "إلغاء",
        confirmButtonColor: "#d33"
      }).then((result) => {
        if (result.isConfirmed) {
          // Find the bill in the array
          const index = this.patientBills.findIndex(b => b.id === bill.id);
          if (index !== -1) {
            // If it's a new bill (not saved to server yet), just remove it
            if (bill.isNew) {
              this.patientBills.splice(index, 1);
            } else {
              // If it's an existing bill, mark it for deletion
              this.patientBills[index].deleted = true;
              this.patientBills[index].modified = true;
              // Hide it from UI
              this.patientBills.splice(index, 1);
            }
            
            this.$swal.fire({
              title: "تم الحذف",
              text: "تم حذف الفاتورة بنجاح",
              icon: "success",
              confirmButtonText: "موافق"
            });
          }
        }
      });
    },
    
    // Close bill report dialog
    closeBillDialog() {
      this.billDialog = false;
    },
    
    // Save patient data
    async savePatientData() {
      this.saving = true;
      
      try {
        // Process new cases (cases without server_id)
        const newCases = this.patientCases.filter(caseItem => !caseItem.server_id);
        
        // Process existing cases that need updates (have server_id and are modified)
        const modifiedCases = this.patientCases.filter(caseItem => caseItem.server_id && caseItem.modified);
        
        // Save new cases
        for (const newCase of newCases) {
          await this.saveNewCase(newCase);
        }
        
        // Update modified cases
        for (const modifiedCase of modifiedCases) {
          await this.updateExistingCase(modifiedCase);
        }
        
        // Save bills if any are new or modified
        const modifiedBills = this.patientBills.filter(bill => bill.isNew || bill.modified);
        if (modifiedBills.length > 0) {
          await this.saveBills(modifiedBills);
        }
        
        // Show success without reloading
        this.$swal.fire({
          title: "نجاح",
          text: "تم حفظ بيانات المريض بنجاح",
          icon: "success",
          confirmButtonText: "موافق"
        });
        
      } catch (error) {
        console.error('❌ Error saving patient data:', error);
        
        this.$swal.fire({
          title: "خطأ",
          text: "تعذر حفظ بيانات المريض. يرجى المحاولة مرة أخرى.",
          icon: "error",
          confirmButtonText: "اغلاق"
        });
      } finally {
        this.saving = false;
      }
    },
    
    // Save new case to API
    async saveNewCase(caseItem) {
      try {
        const requestBody = {
          case_categores_id: caseItem.operation_id,
          tooth_num: [parseInt(caseItem.tooth_number)],
          status_id: caseItem.completed ? 43 : 42,
          sessions: [{
            note: caseItem.notes || "",
            date: caseItem.date
          }],
          bills: [{
            price: caseItem.price.toString(),
            PaymentDate: caseItem.date,
            patient_id: this.patient.id.toString()
          }],
          images: [],
          notes: caseItem.notes || "",
          price: caseItem.price.toString(),
          patient_id: this.patient.id.toString()
        };
        
        const response = await this.$http.post('https://apismartclinicv4.tctate.com/api/cases', requestBody, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });
        
        // Update case with server ID
        caseItem.server_id = response.data.id;
        caseItem.modified = false;
        
      } catch (error) {
        console.error('Error saving new case:', error);
        throw error;
      }
    },
    
    // Update existing case
    async updateExistingCase(caseItem) {
      try {
        const requestBody = {
          case_categores_id: caseItem.operation_id,
          status_id: caseItem.completed ? 43 : 42,
          images: [],
          tooth_num: `[${caseItem.tooth_number}]`,
          notes: caseItem.notes || "",
          price: caseItem.price.toString(),
          sessions: caseItem.sessions || []
        };
        
        const response = await this.$http.patch(`https://apismartclinicv4.tctate.com/api/cases_v2/${caseItem.server_id}`, requestBody, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });
        
        // Clear modified flag
        caseItem.modified = false;
        
      } catch (error) {
        console.error('Error updating case:', error);
        throw error;
      }
    },
    
    // Save bills
    async saveBills(bills) {
      try {
        // Separate new bills from existing bills
        const newBills = bills.filter(bill => !bill.server_id);
        const existingBills = bills.filter(bill => bill.server_id);
        
        // Save new bills using POST to create them
        if (newBills.length > 0) {
          const billsData = newBills.map(bill => ({
            price: bill.price.toString(),
            PaymentDate: bill.PaymentDate,
            patient_id: this.patient.id.toString(),
            is_paid: bill.is_paid || 0
          }));
          
          const requestBody = {
            bills: billsData,
            patient_id: this.patient.id.toString()
          };
          
          const response = await this.$http.post(`https://apismartclinicv4.tctate.com/api/patients/bills/${this.patient.id}`, requestBody, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
          
          // Mark new bills as saved
          newBills.forEach(bill => {
            bill.isNew = false;
            bill.modified = false;
          });
        }
        
        // Update existing bills using PUT to update them individually
        for (const bill of existingBills) {
          const requestBody = {
            price: bill.price,
            PaymentDate: bill.PaymentDate,
            is_paid: bill.is_paid || 0
          };
          
          await this.$http.put(`https://apismartclinicv4.tctate.com/api/bills_v2/${bill.server_id}`, requestBody, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
          
          // Mark existing bill as saved
          bill.modified = false;
        }
        
      } catch (error) {
        console.error('Error saving bills:', error);
        throw error;
      }
    },
    
    // Edit patient
    editPatient() {
      this.editDialog = true;
    },
    
    // Book appointment  
    bookAppointment() {
     
        this.appointmentDialog = true;
    },

    // Close appointment dialog
    closeAppointmentDialog() {
      this.appointmentDialog = false;
    },

    // Save patient edit
    savePatientEdit(data) {
      console.log('Saving patient edit:', data);
      this.editDialog = false;
    },
    
    // Close patient edit dialog
    closePatientEditDialog() {
      this.editDialog = false;
    },
    
    // Handle image upload success
    handleImageSuccess(file, response) {
      console.log('Image uploaded successfully:', response);
    },
    
    // Handle image upload error
    handleImageError(file, message, xhr) {
      console.error('Image upload error:', message);
    },
    
    // Handle image removal
    handleImageRemoved(file, error, xhr) {
      console.log('Image removed:', file);
    },
    
    // Format session date
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

    // Helper method to check if an element is a form element
    isFormElement(element) {
      if (!element) return false;
      
      // Check the element itself
      if (element.tagName === 'INPUT' || 
          element.tagName === 'TEXTAREA' || 
          element.tagName === 'SELECT' || 
          element.tagName === 'BUTTON' ||
          element.tagName === 'FORM') {
        return true;
      }
      
      // Check classes
      if (element.classList && (
          element.classList.contains('v-input') ||
          element.classList.contains('v-text-field') ||
          element.classList.contains('v-select') ||
          element.classList.contains('v-data-table') ||
          element.classList.contains('price-input') ||
          element.classList.contains('v-btn') ||
          element.classList.contains('v-form')
      )) {
        return true;
      }
      
      // Check if it's inside a form context
      if (element.closest && (
          element.closest('.v-input') ||
          element.closest('.v-text-field') ||
          element.closest('.v-select') ||
          element.closest('.v-data-table') ||
          element.closest('.price-input') ||
          element.closest('.v-form') ||
          element.closest('form')
      )) {
        return true;
      }
      
      return false;
    },
  },
  
  async mounted() {
    try {
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
      
      // Listen for appointment booking events
      EventBus.$on('appointmentBooked', () => {
        this.appointmentDialog = false;
        this.$swal.fire({
          title: "نجاح",
          text: "تم حجز الموعد بنجاح",
          icon: "success",
          confirmButtonText: "موافق"
        });
      });

      EventBus.$on('appointmentCancelled', () => {
        this.appointmentDialog = false;
      });
      
      // Load patient data and dental operations
      await this.loadPatientData();
      
      // Add global handler for input fields to ensure context menu is hidden
      document.addEventListener('click', (e) => {
        if (e.target.closest('.price-input') || 
            e.target.closest('.v-text-field__slot') || 
            e.target.closest('.v-input') || 
            e.target.tagName === 'INPUT' ||
            e.target.tagName === 'TEXTAREA') {
          this.showContextMenu = false;
          this.selectedTooth = null;
        }
      });
      
      // Add global context menu prevention for form elements
      document.addEventListener('contextmenu', (e) => {
        // Always prevent context menu when interacting with form elements
        if (this.isFormElement(e.target)) {
          this.showContextMenu = false;
          this.selectedTooth = null;
          console.log('Prevented context menu on form element');
          return; // Allow the default context menu for form elements
        }
        
        // Only allow context menu on tooth elements
        if (!e.target.classList.contains('comon')) {
          this.showContextMenu = false;
          this.selectedTooth = null;
        }
      });
      
      // Add focus event listener to hide context menu when focusing on form elements
      document.addEventListener('focus', (e) => {
        if (this.isFormElement(e.target)) {
          this.showContextMenu = false;
          this.selectedTooth = null;
          console.log('Hiding context menu due to form element focus');
        }
      }, true); // Use capture phase to catch focus events early
      
      console.log('✅ Component mounted successfully');
      
    } catch (error) {
      console.error('❌ Error during component mount:', error);
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
      
      // Clean up appointment booking event listeners
      EventBus.$off('appointmentBooked');
      EventBus.$off('appointmentCancelled');

      // Remove global document event listeners
      document.removeEventListener('click', this.hideContextMenu);
      
      // Remove the additional event listeners we added
      const clickHandler = (e) => {
        if (e.target.closest('.price-input') || 
            e.target.closest('.v-text-field__slot') || 
            e.target.closest('.v-input') || 
            e.target.tagName === 'INPUT' ||
            e.target.tagName === 'TEXTAREA') {
          this.showContextMenu = false;
          this.selectedTooth = null;
        }
      };
      
      const contextMenuHandler = (e) => {
        if (this.isFormElement(e.target)) {
          this.showContextMenu = false;
          this.selectedTooth = null;
          return;
        }
        if (!e.target.classList.contains('comon')) {
          this.showContextMenu = false;
          this.selectedTooth = null;
        }
      };
      
      const focusHandler = (e) => {
        if (this.isFormElement(e.target)) {
          this.showContextMenu = false;
          this.selectedTooth = null;
        }
      };
      
      document.removeEventListener('click', clickHandler);
      document.removeEventListener('contextmenu', contextMenuHandler);
      document.removeEventListener('focus', focusHandler, true);
      
      console.log('✅ Component destroyed and cleaned up');
      
    } catch (error) {
      console.error('❌ Error during component destroy:', error);
    }
  }
}
</script>

<style scoped>
/* Styles for the patient detail page */
.patient-detail-page {
  padding: 16px;
}

/* Styles for patient info section */
.patient-header-card {
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.patient-info-container {
  padding: 8px 0;
}

/* Styles for WhatsApp link */
.whatsapp-link {
  color: #25D366 !important;
  text-decoration: none;
}

.whatsapp-link:hover {
  text-decoration: underline;
}

/* Context menu styles */
.tooth-context-menu {
  position: fixed;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  z-index: 1000;
  min-width: 150px;
  max-height: 300px;
  overflow-y: auto;
}

.context-menu-header {
  padding: 8px 12px;
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
  font-weight: bold;
}

.tooth-context-menu ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.context-menu-item {
  padding: 8px 12px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
  list-style: none;
  display: block;
  width: 100%;
}

.context-menu-item:hover {
  background: #f0f0f0;
}

.context-menu-item:last-child {
  border-bottom: none;
}

/* Session chip styling */
.session-date-chip {
  font-size: 10px !important;
  height: 20px !important;
}

/* Other component-specific styles */
.teeth-container {
  position: relative;
}

.case-title {
  font-family: Cairo, sans-serif !important;
}

/* Mobile-specific styles for billing section */
.mobile-bill-layout {
  padding: 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fafafa;
}

.mobile-user-info-header {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  background-color: #f5f5f5;
  border-radius: 4px;
  border-left: 3px solid #1976d2;
}

.mobile-field-container {
  position: relative;
}

.mobile-user-info {
  position: absolute;
  bottom: -18px;
  right: 0;
  font-size: 12px;
  display: flex;
  align-items: center;
}

.mobile-actions {
  margin-top: 8px;
}

/* Override styles for desktop layout within billing section */
@media (min-width: 600px) {
  .desktop-status-positioning {
    position: relative;
    bottom: 0;
    left: 0;
  }
}
</style>
