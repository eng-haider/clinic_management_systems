<template>
  <v-container fluid class="patient-detail-page no-horizontal-scroll">
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
              {{ patient.name || $t('loading.loading') }}...
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
            <!-- Credit Balance Display -->
            <div class="patient-credit-info mt-2" v-if="useCreditSystem">
              <v-chip
                small
                :color="patient.credit_balance > 0 ? 'success' : 'grey'"
                text-color="white"
                class="mr-2"
              >
                <v-icon left size="16">mdi-wallet</v-icon>
                {{ $t('patients.credit_balance') }}: {{ formatCreditBalance(patient.credit_balance) }} IQ
              </v-chip>
            </div>
          </div>
        </v-col>

        <!-- Action Buttons (Hidden for secretaries) -->
        <v-col cols="auto" class="pa-2 pa-md-4">
          <!-- Mobile: Show buttons vertically in smaller size -->
          <div class="d-flex d-sm-none flex-column">
            <v-btn 
              small
              class="mb-1" 
              color="primary" 
              rounded 
              @click="editPatient"
            >
              <v-icon left small>mdi-pencil</v-icon>
              {{ $t('edit') }}
            </v-btn>
            
            <v-btn 
              small
              class="mb-1" 
              rounded
              style="background-color: rgb(59, 106, 117); color: white;"
              @click="bookAppointment()"
            >
              <v-icon left small>mdi-calendar-clock</v-icon>
              {{ $t('book_appointment') }}
            </v-btn>
            
            <v-btn 
              small
              class="mb-1" 
              color="success" 
              rounded
              @click="generateBill"
            >
              <v-icon left small>mdi-file-document-outline</v-icon>
              {{ $t('patients.bill') }}
            </v-btn>
            
            <!-- Add Credit Button (Only shown when credit system is enabled) -->
            <v-btn 
              v-if="useCreditSystem"
              small
              class="mb-1" 
              color="orange" 
              rounded
              @click="openAddCreditDialog"
            >
              <v-icon left small>mdi-wallet-plus</v-icon>
              {{ $t('patients.add_credit') }}
            </v-btn>
          </div>

          <!-- Desktop: Show buttons horizontally -->
          <div class="d-none d-sm-flex">
            <v-btn 
              class="mr-2" 
              color="primary" 
              rounded 
              @click="editPatient"
            >
              {{ $t('edit') }}
            </v-btn>
            
            <v-btn 
              class="mr-2" 
              rounded
              style="background-color: rgb(59, 106, 117); color: white;"
              @click="bookAppointment()"
            >
              <v-icon left>mdi-calendar-clock</v-icon>
              {{ $t('book_appointment') }}
            </v-btn>
            
            <v-btn 
              class="mr-2" 
              color="success" 
              rounded
              @click="generateBill"
            >
              <v-icon left>mdi-file-document-outline</v-icon>
              {{ $t('patients.bill') }}
            </v-btn>
            
            <!-- Add Credit Button (Only shown when credit system is enabled) -->
            <v-btn 
              v-if="useCreditSystem"
              class="mr-2" 
              color="orange" 
              rounded
              @click="openAddCreditDialog"
            >
              <v-icon left>mdi-wallet-plus</v-icon>
              {{ $t('patients.add_credit') }}
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-card>

    <!-- Main Content Card -->
    <v-card class="main-content-card">
      <v-card-text class="main-content-text">
        <v-row>
          <v-col cols="12" md="12">
            <!-- Secretary Welcome Message (Only shown for secretaries) -->
           


            <v-card class="mb-4" outlined v-if="!secretaryBillsOnlyMode">
              <v-card-text class="text-center">
                <!-- Context menu and right-click are now handled inside teeth component -->
                <div class="teeth-container">
               
                  <teeth_v2 
                    ref="teethComponent"
                    :categories="dentalOperations"
                    :tooth_num="selectedTeethNumbers" 
                    :id="1"
                    :patientCases="patientCases"
                    :patientData="patient"
                    @case-added="handleCaseAdded"
                  />
                </div>
              </v-card-text>


                <!-- Notes Section -->
        <v-card class="notes-section mt-4">
          <v-card-title class="pb-2">
            <v-icon left>mdi-note-text</v-icon>
            {{ $t('patients.notes') }}
          </v-card-title>
          <v-card-text>
            <v-textarea
              v-model="patient.notes"
              :label="$t('patients.add_notes')"
              :placeholder="$t('patients.notes_placeholder')"
              outlined
              rows="4"
              counter
              maxlength="1000"
              style="direction: rtl; text-align: right;"
            ></v-textarea>
          </v-card-text>
        </v-card>









              
              <!-- Selected Cases Table -->
              <v-card-text>
                <div class="selected-teeth-table">
                  <v-card flat class="teeth-template-card">
                    <v-card-title class="teeth-template-title">
                      <v-icon left>mdi-tooth-outline</v-icon>
                      <span class="case-title">{{ $t('patients.cases') }}</span>
                    </v-card-title>

                    <v-card-text style="padding-top: 20px;">
                      <v-data-table
                        :headers="caseHeaders"
                        :items="patientCases"
                        class="elevation-1 mobile-responsive-table"
                        dense
                        :sort-by="['id']"
                        :sort-desc="[true]"
                        @click="handleDataTableClick"
                        :items-per-page="-1"
                        :footer-props="{
                          'items-per-page-options': [-1, 10, 25, 50, 100],
                          'items-per-page-text': 'عدد العناصر في الصفحة:',
                          'items-per-page-all-text': 'الكل'
                        }"
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

                        <!-- Date Column -->
                        <template v-slot:item.date="{ item }">
                          <span>{{ item.date }}</span>
                        </template>

                        <!-- Price Column -->
                        <template v-slot:item.price="{ item }">
                          <v-text-field
                            v-model="item.displayPrice"
                            type="text"
                            :placeholder="$t('patients.case_amount')"
                            suffix="IQ"
                            dense
                            hide-details
                            class="price-input"
                            @input="formatPriceInput(item, $event)"
                            @click.stop="hideContextMenu()"
                            @focus.stop="hideContextMenu()"
                          />
                        </template>

                        <!-- Status Column -->
                        <template v-slot:item.status="{ item }">
                          <v-switch
                            v-model="item.completed"
                            :label="item.completed ? $t('completed') : $t('not_completed')"
                            color="green"
                            inset
                            @change="updateCaseStatus(item)"
                          />
                        </template>

                        <!-- Bills Column -->
                        <template v-slot:item.bills="{ item }">
                          <div class="bills-for-case">
                            <div v-if="getBillsForCase(item.id).length > 0" class="d-flex flex-wrap" style="font-size: 23px;">
                              <v-chip
                                v-for="bill in getBillsForCase(item.id)"
                                :key="bill.id"
                               
                                :color="bill.is_paid == 1 ? 'success' : 'warning'"
                                text-color="white"
                                class="ma-1"
                                :title="`${$t('patients.bill_number')} ${bill.id} - ${bill.is_paid == 1 ? $t('paid') : $t('not_paid')}`"
                              >
                                {{ bill.price }} IQ
                              </v-chip>
                            </div>
                            <span v-else class="grey--text text--darken-1 caption">
                              {{ $t('patients.no_bills') }}
                            </span>
                          </div>
                        </template>

                        <!-- Notes Column -->
                        <template v-slot:item.notes="{ item }">
                          <div class="notes-container">
                            <!-- Main case notes field -->
                            <div class="mb-2">
                              <v-textarea
                                v-model="item.notes"
                                :placeholder="$t('patients.main_case_notes')"
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
                                  :placeholder="`${$t('patients.session_notes')} ${index + 1}...`"
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
                                  :placeholder="`${$t('patients.new_session_notes')} ${index + 1}...`"
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
                                  {{ $t('patients.new') }}
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
                            {{ $t('patients.add_session') }}
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

            <!-- Case Images Section (Hidden for secretaries) -->
            <v-card class="mb-4" outlined v-if="!secretaryBillsOnlyMode && patientImages.length > 0">
              <v-card-title class="subtitle-1">
                <v-icon left class="primary--text">mdi-image-multiple</v-icon>
                <span style="font-family: Cairo !important;">{{ $t('patients.case_images') }}</span>
              </v-card-title>
              <v-card-text>
                <v-row>
                  <v-col
                    v-for="(image, index) in patientImages"
                    :key="image.id"
                    cols="6"
                    sm="4"
                    md="3"
                    lg="2"
                  >
                    <v-card class="position-relative">
                      <a
                        :href="getImageUrl(image.image_url)"
                        :data-fancybox="'patient-gallery'"
                        :data-caption="`${$t('patients.patient_image')} ${index + 1}`"
                        class="patient-image-link"
                      >
                        <v-img
                          :src="getImageUrl(image.image_url)"
                          :alt="`${$t('patients.patient_image')} ${index + 1}`"
                          aspect-ratio="1"
                          contain
                          class="patient-image"
                          style="cursor: pointer; border-radius: 8px;"
                        />
                      </a>
                      <v-btn
                        icon
                        small
                        color="error"
                        class="delete-image-btn"
                        style="position: absolute; top: 5px; right: 5px;"
                        @click="deleteImage(image)"
                      >
                        <v-icon size="16">mdi-delete</v-icon>
                      </v-btn>
                    </v-card>
                  </v-col>
                </v-row>
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

        <!-- Billing Section - Visible to all but only accountants can add bills -->
        <v-card class="cre_bill mt-4">
          <!-- Section Header -->
          <v-layout row wrap>
            <v-flex md5>
              <hr />
            </v-flex>
            <v-flex md2>
              <p class="se_tit_menu text-center">{{ $t('patients.bill') }}</p>
            </v-flex>
            <v-flex md5>
              <hr />
            </v-flex>
          </v-layout>

          <!-- Role-based message for non-accountants -->
          <v-alert
            v-if="!canAddBills"
            type="info"
            outlined
            class="mx-4 mt-2"
          >
            <v-icon left>mdi-information</v-icon>
            {{ $t('patients.bills_view_only_message') }}
          </v-alert>

          <!-- Total Amount -->
          <v-row>
            <v-col cols="12" md="2" class="d-none d-sm-flex"></v-col>
            <v-col cols="12" md="8">
              <v-text-field
                :value="totalAmount"
                :label="$t('patients.total_cases_amount')"
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
              {{ $t('patients.no_bills_message') }}
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
                  <span class="grey--text text--darken-1 font-weight-medium">{{ bill.user ? bill.user.name : $t('patients.not_specified') }}</span>
                </div>

                <!-- Case Selection -->
                            <div class="mobile-field-container mb-2">
                              <v-select
                                v-model="bill.case_id"
                                :items="availableCases"
                                item-text="case_display"
                                item-value="id"
                                item-disabled="disabled"
                                dense
                                outlined
                                :placeholder="$t('patients.select_case')"
                                :label="$t('patients.case')"
                                class="mobile-responsive-select"
                                :disabled="!canEditBills"
                              >
                              </v-select>
                            </div>

                <!-- Payment Amount -->
                <div class="mobile-field-container mb-2">
                  <v-text-field
                    v-model="bill.price"
                    :label="$t('patients.payment_amount')"
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
                      :label="$t('date')"
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
                      :disabled="!canEditBills"
                      inset
                      dense
                      @change="toggleBillPaymentStatus(bill)"
                    />
                    <div class="mobile-status-label">
                      {{ bill.is_paid == 1 ? $t('paid') : $t('not_paid') }}
                    </div>
                  </div>
                </div>

                <!-- Use Credit Row for Mobile -->
                <div class="mobile-credit-row mb-2" v-if="useCreditSystem">
                  <v-switch
                    v-model="bill.use_credit"
                    :label="$t('patients.use_credit_for_this_bill')"
                    color="orange"
                    inset
                    dense
                    :disabled="!canEditBills || patient.credit_balance <= 0"
                    @change="onBillCreditToggle(bill)"
                  />
                  <div v-if="bill.use_credit && patient.credit_balance > 0" class="text-caption grey--text mt-1">
                    {{ $t('patients.available_credit') }}: {{ formatCreditBalance(patient.credit_balance) }} IQ
                  </div>
                  <div v-if="patient.credit_balance <= 0" class="text-caption red--text mt-1">
                    {{ $t('patients.no_credit_available_message') }}
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
              <v-layout row wrap class="d-none d-sm-flex desktop-bill-layout">
                <v-flex md1 class="d-none d-md-flex"></v-flex>
                
                <!-- Case Selection -->
                <v-flex md3 sm4>
                  <v-select
                    v-model="bill.case_id"
                    :items="availableCases"
                    item-text="case_display"
                    item-value="id"
                    item-disabled="disabled"
                    dense
                    outlined
                    :placeholder="$t('patients.select_case')"
                    class="desktop-responsive-select"
                    :disabled="!canEditBills"
                  >
                  </v-select>
                </v-flex>

                <!-- Payment Amount -->
                <v-flex md2 sm3>
                  <v-text-field
                    v-model="bill.price"
                    :label="$t('patients.payment_amount')"
                    suffix="IQ"
                    type="number"
                    outlined
                    dense
                    :disabled="!canEditBills"
                    @input="updateBillPrice(bill)"
                  />
                  <div class="desktop-user-info">
                    <v-icon size="12" class="mr-1">mdi-account</v-icon>
                    <span class="grey--text text--darken-1">{{ bill.user ? bill.user.name : $t('patients.not_specified') }}</span>
                  </div>
                </v-flex>

                <!-- Payment Date -->
                <v-flex md2 sm3>
                  <v-text-field
                    v-model="bill.PaymentDate"
                    :label="$t('datatable.date')"
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


                     <!-- Use Credit Column -->
                     <v-flex md1 sm1 class="mt-2 text-center credit-column" v-if="useCreditSystem" pr-2>
                  <v-switch
                    v-model="bill.use_credit"
                    :disabled="!canEditBills || patient.credit_balance <= 0"
                    color="orange"
                    inset
                    dense
                    style="position: relative; bottom: 10px;"
                    @change="onBillCreditToggle(bill)"
                  />
                  <div class="caption text-center credit-label" :class="patient.credit_balance <= 0 ? 'red--text' : 'grey--text'">
                    {{ $t('patients.use_credit') }}
                  </div>
                </v-flex>


                
                <!-- Payment Status -->
                <v-flex md1 sm2 class="mt-2 text-center payment-status-column">
                  <v-switch
                    :input-value="bill.is_paid == 1"
                    :disabled="!canEditBills"
                    inset
                    dense
                    style="position: relative; padding-right: 10px;"
                    @change="toggleBillPaymentStatus(bill)"
                  />
                  <div class="caption text-center payment-status-label grey--text">
                    {{ bill.is_paid == 1 ? $t('paid') : $t('patients.awaiting_payment') }}
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

          <!-- Add Payment Button - Only for Accountants -->
          <v-card-actions class="justify-center" v-if="canEditBills">
            <v-btn
              color="primary"
              @click="addPayment"
              class="add-payment-btn mr-3"
            >
              <v-icon left>mdi-plus</v-icon>
              {{ $t('patients.add_new_payment') }}
            </v-btn>
            
       
          </v-card-actions>

          <!-- Credit System Actions for non-bill editors -->
          <v-card-actions class="justify-center" v-else-if="useCreditSystem && !canEditBills">
            <v-btn
              color="orange"
              @click="openAddCreditDialog"
              class="add-credit-btn"
            >
              <v-icon left>mdi-wallet-plus</v-icon>
              {{ $t('patients.add_credit') }}
            </v-btn>
          </v-card-actions>


          <!-- Message for non-accountants -->
          <v-alert
            v-if="!canEditBills"
            type="info"
            outlined
            class="mx-4 mt-2 mb-4"
          >
            <v-icon left>mdi-information</v-icon>
            {{ $t('patients.bills_view_only_message') }}
          </v-alert>

          <!-- Payment Summary -->
          <v-layout row wrap class="pt-5 mt-5">
            <v-flex md xs></v-flex>
            <v-flex md xs>
              <div style="font-weight: bold;">
                {{ $t('patients.amount_paid') }} :
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
                <span style="padding-left: 34px;">{{ $t('patients.remaining') }} :</span>
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
              {{ $t('patients.save_information') }}
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
        <Bill :patient="completePatientData" />
      </v-card>
    </v-dialog>

    <!-- Add Credit Dialog -->
    <v-dialog v-model="addCreditDialog" max-width="500px" persistent>
      <v-card>
        <v-card-title class="headline">
          <v-icon left color="orange">mdi-wallet-plus</v-icon>
          {{ $t('patients.add_credit_to_patient') }}
        </v-card-title>
        
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <div class="text-center mb-4">
                  <h3>{{ patient.name }}</h3>
                  <v-chip small color="primary" class="mt-2">
                    {{ $t('patients.current_balance') }}: {{ formatCreditBalance(patient.credit_balance) }} IQ
                  </v-chip>
                </div>
              </v-col>
              
              <v-col cols="12">
                <v-text-field
                  v-model="creditAmount"
                  :label="$t('patients.credit_amount')"
                  type="number"
                  min="0"
                  step="0.01"
                  outlined
                  suffix="IQ"
                  :rules="creditAmountRules"
                  :error-messages="creditAmountErrors"
                  @input="clearCreditErrors"
                  class="credit-amount-input"
                />
              </v-col>
              
              <v-col cols="12" v-if="creditAmount && parseFloat(creditAmount) > 0">
                <v-alert type="info" outlined class="mb-0">
                  {{ $t('patients.new_balance_will_be') }}: 
                  <strong>{{ formatCreditBalance((patient.credit_balance || 0) + parseFloat(creditAmount)) }} IQ</strong>
                </v-alert>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            text
            @click="closeAddCreditDialog"
            :disabled="addingCredit"
          >
            {{ $t('cancel') }}
          </v-btn>
          <v-btn
            color="orange"
            :loading="addingCredit"
            :disabled="!creditAmount || parseFloat(creditAmount) <= 0"
            @click="addCreditToPatient"
          >
            <v-icon left>mdi-wallet-plus</v-icon>
            {{ $t('patients.add_credit') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Fancybox is handling image viewing now -->
  </v-container>
</template>

<script>
import teeth from '@/components/core/teeth.vue';
import teeth_v2 from '@/components/core/teeth_v2.vue';
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
    teeth_v2,
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
        birth_date: '',
        notes: '',
        credit_balance: 0
      },
      
      // Dental Operations (will be loaded from API)
      dentalOperations: [],
      
      // Context menu state is now managed in teeth component
      selectedTooth: null,
      
      // Patient Cases (will be loaded from API)
      patientCases: [],
      
      // Patient Images (will be loaded from API)
      patientImages: [],
        
      // Patient Bills (will be loaded from API)
      patientBills: [],
      
      // Billing Data
      currentPayment: {
        id: null,
        amount: '', // Empty amount field for user input
        date: new Date().toISOString().substr(0, 10),
        paid: false,
        user_name: ''
      },
      
      // Add cases for bill creation
      availableCases: [],
      
      currentUser: this.$store.state.user?.name || 'User',
      
      // Doctors list for appointment booking
      doctors: [],
      
      // Dialogs
      editDialog: false,
      appointmentDialog: false,
      billDialog: false,
      addCreditDialog: false,
      
      // Credit System
      creditAmount: '',
      addingCredit: false,
      creditAmountErrors: [],
      useCreditForBills: false,

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

      // Uploaded images tracking
      uploadedImages: [],
      
      // Track new uploaded images that need to be saved
      newUploadedImages: [],
      // Fancybox is now handling image viewing
    }
  },
  
  computed: {
     teethComponent() {
      try {
        const teethV2 = this.$store.state.AdminInfo?.clinics_info?.teeth_v2;
        console.log('🦷 Teeth component version check:', { teethV2, clinicsInfo: this.$store.state.AdminInfo?.clinics_info });
   return () => import('@/components/core/teeth_v2.vue');
      } catch (error) {
        console.error('❌ Error determining teeth component:', error);
        // Default to original teeth component
        return () => import('@/components/core/teeth.vue');
      }
    },

    // Get selected teeth numbers for highlighting
    selectedTeethNumbers() {
      return this.patientCases.map(case_item => case_item.tooth_number);
    },
    
    totalAmount() {
      return this.patientCases.reduce((total, case_item) => {
        const price = case_item.actualPrice || parseFloat(this.removeCommas(case_item.price)) || 0;
        return total + price;
      }, 0).toLocaleString();
    },
    
    totalAmountNumber() {
      return this.patientCases.reduce((total, case_item) => {
        const price = case_item.actualPrice || parseFloat(this.removeCommas(case_item.price)) || 0;
        return total + price;
      }, 0);
    },
    
    paidAmount() {
      // Calculate from bills where is_paid = 1
      const paid = this.patientBills
        .filter(bill => bill.is_paid == 1)
        .reduce((total, bill) => {
          const price = parseFloat(this.removeCommas(bill.price)) || 0;
          return total + price;
        }, 0);
      return paid.toLocaleString();
    },
    
    paidAmountNumber() {
      return this.patientBills
        .filter(bill => bill.is_paid == 1)
        .reduce((total, bill) => {
          const price = parseFloat(this.removeCommas(bill.price)) || 0;
          return total + price;
        }, 0);
    },
    
    totalBillsAmount() {
      return this.patientBills
        .reduce((total, bill) => {
          const price = parseFloat(this.removeCommas(bill.price)) || 0;
          return total + price;
        }, 0);
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
        const doctorCanPay = this.$store.state.AdminInfo?.clinics_info?.doctor_can_pay;

        // If doctor_can_pay is enabled (1), allow doctors, adminDoctors, and accounters to change payment status
        if (doctorCanPay == 1 || doctorCanPay === true) {
          // Check paid_at_secretary setting for additional permissions
          if (paidAtSecretary == 1 || paidAtSecretary === true) {
            return role === 'secretary' || role === 'accounter' || role === 'doctor' || role === 'adminDoctor';
          } else {
            return role === 'accounter' || role === 'doctor' || role === 'adminDoctor';
          }
        }
        
        // If doctor_can_pay is not enabled, use original logic
        // If paid_at_secretary is true (1), only secretary/accounter can change payment status
        if (paidAtSecretary == 1 || paidAtSecretary === true) {
          return role === 'secretary' || role === 'accounter';
        }
        // If paid_at_secretary is false (0), only secretary/accounter can change payment status
        else {
          return role === 'secretary' || role === 'accounter';
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
        const doctorCanPay = this.$store.state.AdminInfo?.clinics_info?.doctor_can_pay;
        
        // Always allow accounters to add bills
        if (role === 'accounter') {
          return true;
        }
        
        // If doctor_can_pay is enabled (1), allow doctors and adminDoctors to add bills
        if (doctorCanPay == 1 || doctorCanPay === true) {
          return role === 'adminDoctor' || role === 'doctor' || role === 'accounter';
        }
        
        // Otherwise, only accounters can add bills
        return role === 'accounter';
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
        
        // Block secretary completely (this shouldn't be reached due to route guard)
        if (role === 'secretary') {
          return true;
        }
        
        // Accountant can only see bills section
        if (role === 'accounter') {
          return true;
        }
        
        const paidAtSecretary = this.$store.state.AdminInfo?.clinics_info?.paid_at_secretary;
        
        // Original logic for other roles
        const isSecretaryOnlyMode = (role === 'secretary' || role === 'accounter') && (paidAtSecretary == 1 || paidAtSecretary === true);
        
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
        const doctorCanPay = this.$store.state.AdminInfo?.clinics_info?.doctor_can_pay;
        
        // Always allow accounters to edit bills
        if (role === 'accounter') {
          return true;
        }
        
        // If doctor_can_pay is enabled (1), allow doctors and adminDoctors to edit bills
        if (doctorCanPay == 1 || doctorCanPay === true) {
          return role === 'adminDoctor' || role === 'doctor' || role === 'accounter';
        }
        
        // Otherwise, only accounters can edit bills
        return role === 'accounter';
      } catch (error) {
        console.error('Error checking edit bills permission:', error);
        return false;
      }
    },

    // Check if current user can delete bills
    canDeleteBills() {
      try {
        const role = this.$store.state.role;
        // Only doctors, adminDoctors, and accounters can delete bills
        return role === 'adminDoctor' || role === 'doctor' || role === 'accounter';
      } catch (error) {
        console.error('Error checking delete bills permission:', error);
        return false;
      }
    },

    // Check if current user can access billing section
    canAccessBillingSection() {
      try {
        const role = this.$store.state.role;
        // Only accountants can access the billing section
        return role === 'accounter';
      } catch (error) {
        console.error('Error checking billing section access:', error);
        return false;
      }
    },

    // Table Headers with translations
    caseHeaders() {
      return [
        { text: this.$t('patients.tooth'), value: 'tooth_number', align: 'center', width: '2%' },
        { text: this.$t('datatable.type'), value: 'case_type', align: 'start', width: '5%' },
        { text: this.$t('datatable.date'), value: 'date', align: 'center', width: '10%' },
        { text: this.$t('datatable.price'), value: 'price', align: 'center', width: '12%' },
        { text: this.$t('datatable.status'), value: 'status', align: 'center', width: '12%' },
        { text: this.$t('datatable.paid_bills'), value: 'bills', align: 'center', width: '15%' },
        { text: this.$t('datatable.notes'), value: 'notes', align: 'start', width: '36%' },
        { text: this.$t('datatable.actions'), value: 'actions', align: 'center', width: '8%' }
      ];
    },
    
    billHeaders() {
      return [
        { text: this.$t('datatable.bill_number'), value: 'id', align: 'center', width: '10%' },
        { text: this.$t('datatable.case'), value: 'case_id', align: 'center', width: '15%' },
        { text: this.$t('datatable.price'), value: 'price', align: 'center', width: '15%' },
        { text: this.$t('patients.payment_date'), value: 'PaymentDate', align: 'center', width: '15%' },
        { text: this.$t('patients.payment_status'), value: 'is_paid', align: 'center', width: '15%' },
        { text: this.$t('patients.created_by'), value: 'user_name', align: 'start', width: '15%' },
        { text: this.$t('patients.created_at'), value: 'created_at', align: 'center', width: '10%' },
        { text: this.$t('datatable.actions'), value: 'actions', align: 'center', width: '5%' }
      ];
    },

    dropzoneOptions() {
      return {
        url: "https://smartclinicv5.tctate.com/api/cases/uploude_image",
        thumbnailWidth: 150,
        maxFilesize: 5,
        acceptedFiles: "image/*",
        dictDefaultMessage: `<i class="fas fa-upload"></i> ${this.$t('patients.upload_case_images')}`,
        paramName: "file",
        maxFiles: 10,
        addRemoveLinks: true,
        dictRemoveFile: this.$t('patients.remove_image'),
        dictCancelUpload: this.$t('patients.cancel_upload'),
        autoProcessQueue: true
      };
    },

    // Credit System
    useCreditSystem() {
      return this.$store.getters.useCreditSystem;
    },

    creditAmountRules() {
      return [
        v => !!v || this.$t('patients.credit_amount_required'),
        v => (!isNaN(parseFloat(v)) && parseFloat(v) > 0) || this.$t('patients.credit_amount_must_be_positive')
      ];
    }
  },
  
  methods: {

        generateBill() {
      this.billDialog = true;
    },

    // Credit System Methods
    openAddCreditDialog() {
      this.addCreditDialog = true;
      this.creditAmount = '';
      this.creditAmountErrors = [];
    },

    closeAddCreditDialog() {
      this.addCreditDialog = false;
      this.creditAmount = '';
      this.creditAmountErrors = [];
      this.addingCredit = false;
    },

    clearCreditErrors() {
      this.creditAmountErrors = [];
    },

    formatCreditBalance(balance) {
      if (!balance || isNaN(balance)) return '0';
      return parseFloat(balance).toLocaleString();
    },

    async addCreditToPatient() {
      try {
        // Validate input
        if (!this.creditAmount || parseFloat(this.creditAmount) <= 0) {
          this.creditAmountErrors = [this.$t('patients.credit_amount_must_be_positive')];
          return;
        }

        this.addingCredit = true;
        this.creditAmountErrors = [];

        const token = this.$store.state.AdminInfo?.token;
        if (!token) {
          throw new Error('No authentication token found');
        }

        console.log('🏦 Adding credit to patient:', {
          patientId: this.patient.id,
          amount: this.creditAmount
        });

        const response = await this.$http.post(`/patients/${this.patient.id}/add-credit`, {
          amount: parseFloat(this.creditAmount)
        }, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        console.log('🏦 Credit added successfully:', response.data);

        // Update patient credit balance
        this.patient.credit_balance = response.data.current_balance;

        // Show success message
        this.$swal.fire({
          title: this.$t('success'),
          text: this.$t('patients.credit_added_successfully'),
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });

        // Close dialog
        this.closeAddCreditDialog();

      } catch (error) {
        console.error('❌ Error adding credit:', error);
        
        let errorMessage = this.$t('patients.error_adding_credit');
        
        if (error.response) {
          if (error.response.status === 422 && error.response.data.errors) {
            // Validation errors
            this.creditAmountErrors = Object.values(error.response.data.errors).flat();
            return;
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        }

        this.$swal.fire({
          title: this.$t('error'),
          text: errorMessage,
          icon: 'error',
          confirmButtonText: this.$t('close')
        });
      } finally {
        this.addingCredit = false;
      }
    },

    onUseCreditToggle() {
      console.log('Use credit toggled:', this.useCreditForBills);
      if (this.useCreditForBills && this.patient.credit_balance <= 0) {
        this.useCreditForBills = false;
        this.$swal.fire({
          title: this.$t('patients.no_credit_available_title'),
          text: this.$t('patients.no_credit_available_message'),
          icon: 'warning',
          confirmButtonText: this.$t('close')
        });
      }
    },

    onBillCreditToggle(bill) {
      console.log('Bill credit toggled for bill:', bill.id, 'use_credit:', bill.use_credit);
      if (bill.use_credit && this.patient.credit_balance <= 0) {
        bill.use_credit = false;
        this.$swal.fire({
          title: this.$t('patients.no_credit_available_title'),
          text: this.$t('patients.no_credit_available_message'),
          icon: 'warning',
          confirmButtonText: this.$t('close')
        });
      }
      
      // Mark bill as modified
      bill.modified = true;
    },

    // Clear cache and reload data
    async clearCacheAndReload() {
      try {
        // Clear service worker cache
        if ('caches' in window) {
          const cacheNames = await caches.keys();
          await Promise.all(
            cacheNames.map(name => caches.delete(name))
          );
          console.log('🗑️ All caches cleared');
        }

        // Clear localStorage cache if any
        localStorage.removeItem('dental_operations_cache');
        localStorage.removeItem('case_categories_cache');

        // Force reload dental operations
        this.dentalOperations = [];
        await this.fetchDentalOperations();
        
        // Force reload patient data
        await this.loadPatientData();
        
        this.$swal.fire({
          title: "تم التحديث",
          text: "تم تحديث البيانات بنجاح",
          icon: "success",
          timer: 1500,
          showConfirmButton: false
        });
      } catch (error) {
        console.error('❌ Error clearing cache:', error);
        this.$swal.fire({
          title: "خطأ",
          text: "فشل في تحديث البيانات",
          icon: "error",
          confirmButtonText: "موافق"
        });
      }
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
        displayPrice: '',
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
        this.$http.get('case-categories', {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            "Cache-Control": "no-cache",
            "Pragma": "no-cache"
          },
          params: {
            _t: Date.now() // Add timestamp to prevent caching
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
          { id: 1, name: this.$t('patients.default_doctor'), name_ar: this.$t('patients.default_doctor') }
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
        
        const response = await this.$http.get(`/getPatientById/${patientId}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
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
          rx_id: data.rx_id,
          credit_balance: data.credit_balance || 0,
          tooth_parts: data.tooth_parts  // Add tooth_parts data
        };
        console.log('👤 Patient basic info set:', this.patient.name);
        console.log('🦷 Patient tooth_parts:', this.patient.tooth_parts);

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
            displayPrice: this.formatNumberWithCommas(caseItem.price || ''),
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

        // Format cases for bill selection
        if (data.cases && Array.isArray(data.cases)) {
          this.availableCases = this.formatCasesForSelection(data.cases);
        }

        // Process images data
        console.log('🖼️ Processing images data...');
        this.patientImages = data.images ? data.images.map(image => ({
          id: image.id,
          image_url: image.image_url,
          imageable_id: image.imageable_id,
          imageable_type: image.imageable_type,
          description: image.descrption,
          created_at: image.created_at,
          updated_at: image.updated_at
        })) : [];
        console.log('🖼️ Images processed:', this.patientImages.length);

        // Process bills data
        console.log('💰 Processing bills data...');
        console.log('💰 Raw bills from API:', data.bills);
        this.patientBills = data.bills ? data.bills.map(bill => {
          console.log('💰 Processing bill:', bill.id, 'use_credit:', bill.use_credit);
          return {
            id: bill.id,
            server_id: bill.id,
            billable_id: bill.billable_id,
            patient_id: bill.patient_id,
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
            case_id: bill.billable_id, // Use billable_id as case_id
            billable: bill.billable, // Store the complete billable object
            isNew: false,
            modified: false,
            use_credit: bill.use_credit == 1 || bill.use_credit === true // Convert to boolean from API response
          };
        }) : [];
        console.log('💰 Bills processed:', this.patientBills.length);
        console.log('💰 Bills with credit usage:', this.patientBills.filter(bill => bill.use_credit).length);

        // Create available cases from bills' billable objects and existing cases
        const casesFromBills = data.bills ? data.bills
          .filter(bill => bill.billable) // Only bills with billable data
          .map(bill => bill.billable) : [];
        
        const allCases = [...(data.cases || []), ...casesFromBills];
        
        // Remove duplicates based on case ID
        const uniqueCases = allCases.filter((case_item, index, self) => 
          index === self.findIndex(c => c.id === case_item.id)
        );
        
        this.availableCases = this.formatCasesForSelection(uniqueCases);

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
        console.log('Images:', this.patientImages);
        console.log('Bills:', this.patientBills);
        
        // Initialize Fancybox after data is loaded
        this.$nextTick(() => {
          if (window.$ && window.$.fancybox) {
            window.$('[data-fancybox]').fancybox({
              buttons: [
                "zoom",
                "share",
                "slideShow",
                "fullScreen",
                "download",
                "thumbs",
                "close"
              ],
              animationEffect: "zoom",
              transitionEffect: "slide",
              loop: true,
              protect: true,
              wheel: false
            });
          }
        });
      } catch (error) {
        console.error('❌ Error loading patient data:', error);
        
        // More detailed error handling
        let errorMessage = this.$t('patients.error_loading_patient_data');
        let errorTitle = this.$t('patients.error_loading_data_title');
        
        if (error.message === 'Request timeout') {
          errorMessage = this.$t('patients.connection_timeout');
          errorTitle = this.$t('patients.connection_timeout_title');
        } else if (error.response && error.response.status === 401) {
          errorMessage = this.$t('patients.session_expired');
          errorTitle = this.$t('patients.authentication_error_title');
        } else if (error.response && error.response.status === 404) {
          errorMessage = this.$t('patients.patient_not_found');
          errorTitle = this.$t('patients.patient_not_found_title');
        }
        
        this.$swal.fire({
          title: errorTitle,
          text: errorMessage,
          icon: "error",
          confirmButtonText: this.$t('close')
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

    // Toggle bill payment status
    toggleBillPaymentStatus(bill) {
      // Check if current user can change payment status
      if (!this.canChangePaymentStatus) {
        this.$swal.fire({
          title: "غير مسموح",
          text: "ليس لديك صلاحية لتغيير حالة الدفع",
          icon: "warning",
          confirmButtonText: "موافق"
        });
        return;
      }

      // Find the bill in the array
      const index = this.patientBills.findIndex(b => b.id === bill.id);
      if (index !== -1) {
        // Toggle the payment status
        this.patientBills[index].is_paid = this.patientBills[index].is_paid == 1 ? 0 : 1;
        // Mark as modified for saving (unless it's already marked as new)
        if (!this.patientBills[index].isNew) {
          this.patientBills[index].modified = true;
        }
      }
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
        displayPrice: '',
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
    
    // Format number with commas (e.g., 20000 -> 20,000)
    formatNumberWithCommas(value) {
      if (!value || value === '' || value === null || value === undefined) {
        return '';
      }
      // Remove any existing commas and non-numeric characters except decimal point
      const numericValue = value.toString().replace(/[^\d.]/g, '');
      if (numericValue === '') return '';
      // Add commas for thousands separator
      return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    
    // Remove commas from formatted number for API calls
    removeCommas(value) {
      if (!value) return '';
      return value.toString().replace(/,/g, '');
    },
    
    // Handle input formatting in real-time
    formatPriceInput(item, event) {
      // Get the raw value without commas
      const rawValue = this.removeCommas(event);
      // Update the actual price value (without commas for calculations)
      item.price = rawValue;
      // Update the display price with commas - use Vue.nextTick to ensure proper reactivity
      this.$nextTick(() => {
        item.displayPrice = this.formatNumberWithCommas(rawValue);
      });
      // Update the case price in the array
      this.updateCasePrice(item);
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
          // Make API call to delete case from server
          this.$http.delete(`cases/${caseItem.id}`, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          })
          .then(() => {
            // Find the case in the array and remove it after successful API call
            const index = this.patientCases.findIndex(c => c.id === caseItem.id);
            if (index !== -1) {
              this.patientCases.splice(index, 1);
            }
            
            // Show success message
            this.$swal.fire({
              title: "تم الحذف",
              text: "تم حذف الحالة بنجاح",
              icon: "success",
              confirmButtonText: "موافق"
            });
          })
          .catch((error) => {
            console.error('Delete case error:', error);
            // Show error message
            this.$swal.fire({
              title: "خطأ",
              text: "حدث خطأ أثناء حذف الحالة",
              icon: "error",
              confirmButtonText: "موافق"
            });
          });
        }
      });
    },
    
    // Add a new payment
    addPayment() {
      // Check if user has permission to create bills
      if (!this.canAddBills) {
        this.$swal.fire({
          title: "غير مسموح",
          text: "ليس لديك صلاحية لإنشاء فواتير جديدة",
          icon: "error",
          confirmButtonText: "موافق"
        });
        return;
      }
      
      // Create a new bill object with case selection
      const newBill = {
        id: Date.now(), // Temporary ID
        price: 0,
        PaymentDate: new Date().toISOString().substr(0, 10),
        is_paid: 0,
        case_id: null, // Add case selection
        user_id: this.$store.state.AdminInfo.user_id,
        clinics_id: this.$store.state.AdminInfo.clinics_id,
        isNew: true, // Mark as new bill to be saved
        use_credit: false, // Default to not using credit
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
        
        // Refresh available cases to update disabled state in real-time
        this.refreshAvailableCases();
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
    async deleteBill(bill) {
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
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            // Find the bill in the array
            const index = this.patientBills.findIndex(b => b.id === bill.id);
            if (index !== -1) {
              // If it's a new bill (not saved to server yet), just remove it
              if (bill.isNew) {
                this.patientBills.splice(index, 1);
              } else {
                // If it's an existing bill, call the DELETE API
                const billId = bill.server_id || bill.id;
                await this.$http.delete(`https://smartclinicv5.tctate.com/api/patientsAccounstsv2/bills/${billId}`, {
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                  }
                });
                
                // Remove from UI after successful API call
                this.patientBills.splice(index, 1);
              }
              
              this.$swal.fire({
                title: "تم الحذف",
                text: "تم حذف الفاتورة بنجاح",
                icon: "success",
                confirmButtonText: "موافق"
              });
              
              // Refresh available cases to update disabled state
              this.refreshAvailableCases();
            }
          } catch (error) {
            console.error('Error deleting bill:', error);
            this.$swal.fire({
              title: "خطأ",
              text: "حدث خطأ أثناء حذف الفاتورة",
              icon: "error",
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
      if (this.saving) return; // Prevent multiple saves
      
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
      
        // Save uploaded images if any
        if (this.newUploadedImages.length > 0) {
          await this.saveUploadedImages();
        }
        
        // Save patient notes
        if (this.patient.notes !== undefined && this.patient.notes !== null) {
          await this.savePatientNotes();
        }
        
        // Save tooth colors from teeth_v2 component
        await this.saveToothColors();
        
        // Show success message
        this.$swal.fire({
          title: "نجاح",
          text: "تم حفظ بيانات المراجع بنجاح",
          icon: "success",
          timer: 1500, // Auto close after 1.5 seconds
          showConfirmButton: false // Hide the confirm button
        }).then(async () => {
          // Reload patient data after successful save
          console.log('🔄 Reloading patient data after save...');
          try {
            await this.loadPatientData();
            console.log('✅ Patient data reloaded successfully');
          } catch (error) {
            console.error('❌ Error reloading patient data:', error);
            // Show a toast or small notification instead of another modal
            this.$toast.error('تم حفظ البيانات ولكن فشل في إعادة تحميلها');
          }
        });
      } catch (error) {
        console.error('❌ Error saving patient data:', error);
        
        this.$swal.fire({
          title: this.$t('patients.error_title'),
          text: this.$t('patients.error_saving_patient'),
          icon: "error",
          confirmButtonText: this.$t('close')
        });
      } finally {
        this.saving = false;
      }
    },
    
    // Save uploaded images to the API
    async saveUploadedImages() {

      console.log(this.newUploadedImages)
      try {
        if (this.newUploadedImages.length === 0) {

          return;
        }
        
        const requestBody = {
          images: this.newUploadedImages.map(image => ({
            img: image.fileName,
            descrption: image.originalName || image.fileName,
            patient_id: this.patient.id.toString()
          })),
          patient_id: this.patient.id.toString()
        };
        
        console.log('📸 Saving uploaded images:', requestBody);
        
        const response = await this.$http.post('https://smartclinicv5.tctate.com/api/cases/uploude_images', requestBody, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
          }
        });
        
        console.log('📸 Images saved successfully:', response.data);
        
        // Clear the uploaded images array after successful save
        this.newUploadedImages = [];
        
      } catch (error) {
        console.error('❌ Error saving uploaded images:', error);
        throw error;
      }
    },
    
    // Save new case
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
            price: caseItem.price ? caseItem.price.toString() : "0",
            PaymentDate: caseItem.date,
            patient_id: this.patient.id ? this.patient.id.toString() : ""
          }],
          images: [],
          notes: caseItem.notes || "",
          price: caseItem.price ? caseItem.price.toString() : "0",
          patient_id: this.patient.id ? this.patient.id.toString() : ""
        };
        
        const response = await this.$http.post('https://smartclinicv5.tctate.com/api/cases', requestBody, {
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
        
        const response = await this.$http.patch(`https://smartclinicv5.tctate.com/api/cases_v2/${caseItem.server_id}`, requestBody, {
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
            price: parseFloat(bill.price) || 0,
            PaymentDate: bill.PaymentDate,
            is_paid: bill.is_paid ? 1 :  0,
            billable_id: bill.case_id,
            user_id: bill.user_id,
            use_credit: bill.use_credit || false
          }));
          
          // Check if any bill wants to use credit
          const anyBillUsesCredit = newBills.some(bill => bill.use_credit);
          
          const requestBody = {
            bills: billsData,
            case_id: newBills[0]?.case_id?.toString() || this.patient.id.toString(), // Use selected case_id as patient_id
            use_credit: anyBillUsesCredit
          };
          
          const response = await this.$http.post(`https://smartclinicv5.tctate.com/api/patients/bills/${this.patient.id}`, requestBody, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
          
          // Handle credit usage response
          if (response.data && anyBillUsesCredit) {
            // Update patient credit balance if credit was used
            if (response.data.remaining_credit !== undefined) {
              this.patient.credit_balance = response.data.remaining_credit;
            }
            
            // Show credit usage message
            if (response.data.credit_used > 0) {
              this.$swal.fire({
                title: this.$t('success'),
                html: `
                  <div>
                    <p>${this.$t('patients.bills_added_successfully')}</p>
                    <p><strong>${this.$t('patients.credit_used')}: ${this.formatCreditBalance(response.data.credit_used)} IQ</strong></p>
                    <p>${this.$t('patients.remaining_credit')}: ${this.formatCreditBalance(response.data.remaining_credit)} IQ</p>
                  </div>
                `,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
              });
            }
          }
          
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
          
          await this.$http.put(`https://smartclinicv5.tctate.com/api/bills_v2/${bill.server_id}`, requestBody, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
          
          // Mark existing bill as saved
          bill.modified = false;
        }
        
        // Refresh available cases to update disabled state
        this.refreshAvailableCases();
        
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
     
      // Track the uploaded image for saving later
      if (response) {
        this.newUploadedImages.push({
          fileName: response.data,
          originalName: file.name,
          file: file
        });
        console.log('📸 Added image to upload queue:', response.filename);
      }
    },
    
    // Handle image upload error
    handleImageError(file, message, xhr) {
      console.error('Image upload error:', message);
    },
    
    // Handle image removal
    handleImageRemoved(file, error, xhr) {
      console.log('Image removed:', file);
    },
    
    // Save tooth colors from teeth component
    async saveToothColors() {
      try {
        // Use the ref to access the teeth component directly
        const teethComponent = this.$refs.teethComponent;
        
        if (!teethComponent) {
          console.log('Teeth component ref not found, skipping color save');
          return;
        }
        
        console.log('Found teeth component:', teethComponent);
        
        // Check if the component has the saveColoredParts method
        if (typeof teethComponent.saveColoredParts === 'function') {
          console.log('Saving tooth colors from teeth component...');
          // Pass the patient notes to save both notes and tooth_parts together
          await teethComponent.saveColoredParts(this.patient.notes);
          console.log('Tooth colors and notes saved successfully');
        } else {
          console.log('saveColoredParts method not found in teeth component');
        }
      } catch (error) {
        console.error('Error saving tooth colors:', error);
        // Don't throw error to prevent blocking the main save process
      }
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

    // Get full image URL
    getImageUrl(imageName) {
      if (!imageName) return '';
      // Use the base URL from the example API response
      return `https://smartclinicv5.tctate.com/case_photo/${imageName}`;
    },

    // Refresh available cases to update disabled state
    refreshAvailableCases() {
      // Get current cases from the existing availableCases and reformat them
      const currentCases = this.availableCases.map(item => ({
        id: item.id,
        case_categories: item.case_categories,
        tooth_num: item.tooth_num,
        price: item.price
      }));
      
      // Reformat with updated disabled state
      this.availableCases = this.formatCasesForSelection(currentCases);
    }
,
    // Format cases for selection
    formatCasesForSelection(cases) {
      return cases.map(case_item => {
        // Parse tooth number from JSON array format
        let toothNumber = this.$t('patients.not_specified');
        try {
          if (case_item.tooth_num) {
            const parsed = JSON.parse(case_item.tooth_num);
            toothNumber = Array.isArray(parsed) ? parsed.join(', ') : parsed;
          }
        } catch (e) {
          toothNumber = case_item.tooth_num || this.$t('patients.not_specified');
        }

        // Calculate sum of bills for this case
        const caseBills = this.getBillsForCase(case_item.id);
        const billsSum = caseBills.reduce((sum, bill) => sum + (parseFloat(bill.price) || 0), 0);
        const casePrice = parseFloat(case_item.price) || 0;
        
        // Disable case if bills sum equals or exceeds case price
        const isDisabled = billsSum >= casePrice && casePrice > 0;

        return {
          id: case_item.id,
          case_display: `${case_item.case_categories?.name_ar || this.$t('patients.not_specified')} - ${this.$t('patients.tooth')} ${toothNumber} - ${case_item.price} د.ع`,
          case_categories: case_item.case_categories,
          tooth_num: toothNumber,
          price: case_item.price,
          disabled: isDisabled
        };
      });
    },

    // Get case display name for bills
    getCaseDisplayName(caseId) {
      const case_item = this.availableCases.find(c => c.id === caseId);
      return case_item ? case_item.case_display : this.$t('patients.not_specified');
    },

    // Get bills for a specific case
    getBillsForCase(caseId) {
      if (!caseId || !this.patientBills) {
        return [];
      }
      return this.patientBills.filter(bill => {
        // Match both case_id and billable_id for compatibility
        return bill.case_id === caseId || bill.billable_id === caseId;
      });
    },

    // Delete image from server and local array
    async deleteImage(image) {
      try {
        // Show confirmation dialog
        const result = await this.$swal.fire({
          title: "تأكيد الحذف",
          text: "هل أنت متأكد من حذف هذه الصورة؟",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "نعم، احذف",
          cancelButtonText: "إلغاء",
          confirmButtonColor: "#d33"
        });

        if (result.isConfirmed) {
          // Remove from local array immediately for better UX
          const index = this.patientImages.findIndex(img => img.id === image.id);
          if (index !== -1) {
            this.patientImages.splice(index, 1);
          }

          // Call API to delete from server (if needed)
          // Note: You may need to implement a delete image API endpoint
          
          this.$swal.fire({
            title: "تم الحذف",
            text: "تم حذف الصورة بنجاح",
            icon: "success",
            confirmButtonText: "موافق"
          });
        }
      } catch (error) {
        console.error('Error deleting image:', error);
        this.$swal.fire({
          title: "خطأ",
          text: "تعذر حذف الصورة",
          icon: "error",
          confirmButtonText: "موافق"
        });
      }
    },

    // Save patient notes
    async savePatientNotes() {
      try {
        const response = await this.axios.patch(`patients/${this.patient.id}/notes`, {
          notes: this.patient.notes
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          }
        });
        
        console.log('✅ Patient notes saved successfully');
        return response;
      } catch (error) {
        console.error('❌ Error saving patient notes:', error);
        throw error;
      }
    },

  },

async mounted() {
  try {
    // Set authorization header for dropzone
    if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.token) {
      this.dropzoneOptions.headers = {
        "Authorization": `Bearer ${this.$store.state.AdminInfo.token}`
      };
      
      // Add patient_id parameter to dropzone options
      this.dropzoneOptions.params = {
        patient_id: this.$route.params.id
      };
    }
    
    // Initialize Fancybox with custom options
    if (window.$ && window.$.fancybox) {
      window.$(document).ready(() => {
        window.$('[data-fancybox]').fancybox({
          buttons: [
            "zoom",
            "share",
            "slideShow",
            "fullScreen",
            "download",
            "thumbs",
            "close"
          ],
          animationEffect: "zoom",
          transitionEffect: "slide",
          loop: true,
          protect: true,
          wheel: false
        });
      });
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
      // Remove Fancybox cleanup
      
      // Clean up event listeners
      EventBus.$off('changetooth', this.handleToothChange);
      
      // Listen for tooth right-click events - try multiple event names
      EventBus.$off('toothRightClick', this.handleToothRightClick);
      EventBus.$off('tooth-right-click', this.handleToothRightClick);
      EventBus.$off('tooth_right_click', this.handleToothRightClick);
      EventBus.$off('rightClickTooth', this.handleToothRightClick);
      
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
/* Prevent horizontal scrolling globally */
.no-horizontal-scroll {
  max-width: 100vw;
  overflow-x: hidden;
}

.no-horizontal-scroll * {
  max-width: 100%;
  box-sizing: border-box;
}

/* Styles for the patient detail page */
.patient-detail-page {
  padding: 8px;
  max-width: 100%;
  overflow-x: hidden;
  width: 100%;
}

@media (min-width: 600px) {
  .patient-detail-page {
    padding: 16px;
  }
}

/* Styles for patient info section */
.patient-header-card {
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin: 0 4px 16px 4px;
}

@media (min-width: 600px) {
  .patient-header-card {
    margin: 0 0 16px 0;
  }
}

.patient-info-container {
  padding: 8px 0;
  min-width: 0; /* Prevent flex items from overflowing */
  flex: 1;
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
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fafafa;
  margin-bottom: 16px;
}

.mobile-user-info-header {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  background-color: #f5f5f5;
  border-radius: 4px;
  border-left: 3px solid #1976d2;
  margin-bottom: 16px;
}

.mobile-field-container {
  position: relative;
  margin-bottom: 16px;
  padding: 0 4px;
  width: 100%;
  box-sizing: border-box;
}

/* Responsive select fields */
.mobile-responsive-select,
.desktop-responsive-select {
  width: 100% !important;
  min-width: unset !important;
  max-width: 100% !important;
}

@media (min-width: 960px) {
  .desktop-responsive-select {
    min-width: 200px;
  }
}

/* Prevent horizontal overflow in mobile layout */
.mobile-bill-layout * {
  max-width: 100%;
  box-sizing: border-box;
}

/* Fix for v-flex elements on small screens */
@media (max-width: 599px) {
  .v-layout.row .v-flex {
    flex-basis: auto !important;
    max-width: 100% !important;
  }
}

/* Ensure containers don't overflow */
.v-container {
  max-width: 100%;
  padding-left: 8px;
  padding-right: 8px;
}

@media (min-width: 600px) {
  .v-container {
    padding-left: 12px;
    padding-right: 12px;
  }
}

/* Fix for data table on mobile */
.v-data-table {
  width: 100%;
  overflow-x: auto;
}

.mobile-responsive-table {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.mobile-responsive-table .v-data-table__wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Make table cells more compact on mobile */
@media (max-width: 599px) {
  .mobile-responsive-table .v-data-table td,
  .mobile-responsive-table .v-data-table th {
    padding: 0 8px !important;
    font-size: 0.8rem;
  }
  
  .mobile-responsive-table .v-chip {
    font-size: 0.7rem;
    height: 20px;
  }
  
  .mobile-responsive-table .v-text-field {
    font-size: 0.8rem;
  }
  
  .mobile-responsive-table .v-textarea {
    font-size: 0.8rem;
  }
}

/* Credit column responsive styling */
.credit-column {
  min-width: 80px;
  flex: 0 0 auto;
}

.credit-label {
  position: relative;
  bottom: 15px;
  float: right;
  font-size: 0.75rem;
  line-height: 1.2;
  white-space: nowrap;
}

@media (max-width: 599px) {
  .credit-label {
    font-size: 0.6rem;
    bottom: 10px;
  }
}

/* Payment status column responsive styling */
.payment-status-column {
  min-width: 100px;
  flex: 0 0 auto;
}

.payment-status-label {
  position: relative;
  top: -10px;
  float: right;
  font-size: 0.75rem;
  line-height: 1.2;
  white-space: nowrap;
}

@media (max-width: 599px) {
  .payment-status-label {
    font-size: 0.6rem;
    top: -5px;
  }
}

/* Prevent text overflow in patient name */
.patient-info-container h2 {
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

/* Fix for WhatsApp phone number display */
.patient-phone {
  min-width: 0;
  flex-shrink: 1;
}

.patient-phone .case-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Responsive buttons in header */
@media (max-width: 599px) {
  .patient-header-card .v-btn {
    font-size: 0.75rem;
    padding: 0 8px;
    min-width: auto;
  }
  
  .patient-header-card .v-btn .v-icon {
    font-size: 16px;
  }
}

/* Fix flex layout issues on mobile */
@media (max-width: 959px) {
  .v-layout.row .v-flex {
    flex-wrap: wrap;
  }
}

/* Ensure proper spacing for mobile credit info */
.patient-credit-info .v-chip {
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 599px) {
  .patient-credit-info .v-chip {
    font-size: 0.7rem;
    height: 24px;
  }
  
  .patient-credit-info .v-chip .v-icon {
    font-size: 14px;
  }
}

/* Desktop bill layout improvements */
.desktop-bill-layout {
  width: 100%;
  overflow-x: auto;
}

.desktop-bill-layout .v-flex {
  min-width: 0;
  flex-shrink: 1;
}

/* Improve responsive behavior of bill layout */
@media (max-width: 1263px) and (min-width: 600px) {
  .desktop-bill-layout .v-flex.md3 {
    flex: 0 0 25%;
    max-width: 25%;
  }
  
  .desktop-bill-layout .v-flex.md2 {
    flex: 0 0 20%;
    max-width: 20%;
  }
  
  .desktop-bill-layout .v-flex.md1 {
    flex: 0 0 10%;
    max-width: 10%;
  }
}

/* Improve tablet layout */
@media (max-width: 959px) and (min-width: 600px) {
  .desktop-bill-layout .v-flex.sm4 {
    flex: 0 0 40%;
    max-width: 40%;
  }
  
  .desktop-bill-layout .v-flex.sm3 {
    flex: 0 0 30%;
    max-width: 30%;
  }
  
  .desktop-bill-layout .v-flex.sm2 {
    flex: 0 0 20%;
    max-width: 20%;
  }
  
  .desktop-bill-layout .v-flex.sm1 {
    flex: 0 0 10%;
    max-width: 10%;
  }
}

/* Responsive text sizing */
@media (max-width: 599px) {
  .text-h5 {
    font-size: 1.1rem !important;
    line-height: 1.3 !important;
  }
  
  .patient-phone .case-title {
    font-size: 0.9rem;
  }
}

.mobile-date-status-row {
  gap: 12px;
  margin-bottom: 16px;
}

.mobile-status-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 100px;
  padding: 8px;
}

.mobile-actions {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
}

/* Desktop layout improvements */
.bill-payment-item {
  margin-bottom: 20px;
  padding: 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fafafa;
}

.bill-payment-item .v-flex {
  padding: 0 8px;
}

.bill-payment-item .v-text-field,
.bill-payment-item .v-select {
  margin-bottom: 8px;
}

.desktop-user-info {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e0e0e0;
}

/* Add spacing to the bills container */
.bills-payment-loop {
  padding: 16px 0;
}

/* Improve spacing for the add bill button */
.v-card__actions.justify-center {
  padding: 20px 16px;
  margin-top: 16px;
}

/* Payment summary spacing */
.pt-5.mt-5 {
  padding-top: 24px !important;
  margin-top: 24px !important;
  border-top: 2px solid #e0e0e0;
}

/* Override styles for desktop layout within billing section */
@media (min-width: 600px) {
  .desktop-status-positioning {
    position: relative;
    bottom: 0;
    left: 0;
    margin-top: 8px;
  }
  
  .bill-payment-item .v-layout.row {
    align-items: flex-start;
    padding: 8px 0;
  }
  
  .bill-payment-item .v-flex {
    padding: 0 12px;
  }
}
</style>

<style>
/* Custom styling for fancybox gallery */
.patient-image-link {
  display: block;
  width: 100%;
  height: 100%;
}

/* Fancybox styling overrides */
.fancybox-button {
  background: rgba(30, 30, 30, 0.6);
}

.fancybox-button:hover {
  background: rgba(0, 0, 0, 0.8);
}

.fancybox-caption {
  font-family: 'Cairo', sans-serif;
  font-size: 16px;
}
</style>
