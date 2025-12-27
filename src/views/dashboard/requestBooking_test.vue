<template>
  <v-container fluid>
    <!-- App Bar with Doctor Filter for Secretary -->
   
    <v-app-bar v-if="($store.state.role === 'secretary' || $store.state.role === 'adminDoctor' || $store.state.role === 'accounter') && doctors && doctors.length > 1" 
               color="primary" 
               dark 
               dense 
               flat 
               class="mb-4">
      <v-toolbar-title>{{ $t("reservations_calendar") }}</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-select
        v-model="selectedDoctorFilter"
        :items="doctorFilterOptions"
        :label="$t('filter_by_doctor')"
        item-text="name"
        item-value="id"
        outlined
        dense
        hide-details
        clearable
        style="max-width: 300px; border-radius: 4px;"
        @change="onDoctorFilterChange"
      >
        <template v-slot:prepend-inner>
          <v-icon>mdi-doctor</v-icon>
        </template>
      </v-select>
      <v-btn icon @click="forceRefreshData" class="ml-2">
        <v-icon>mdi-refresh</v-icon>
      </v-btn>
    </v-app-bar>

    <v-row>
  <v-col>
    <v-calendar 
      ref="calendar" 
      v-model="focus" 
      :start="startDate" 
      :end="endDate" 
      :max="endDate" 
      :events="reservations" 
      type="month" 
      :event-color="getEventColor" 
      @click:date="openBookingDialog"
      @click:event="openReservationDialog"
      :event-more="false"
      :event-overlap-mode="'stack'"
      :event-height="18"
      :event-margin-bottom="1"
    >
      <!-- Custom event template to ensure all events show -->
      <template v-slot:event="{ event, eventParsed }">
        <div
          class="v-event"
          :style="{
            backgroundColor: getEventColor(event),
            color: 'white',
            height: '22px',
            marginBottom: '2px',
            padding: '3px 6px',
            fontSize: '12px',
            fontWeight: '500',
            borderRadius: '4px',
            cursor: 'pointer',
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
            lineHeight: '1.3'
          }"
          @click="openReservationDialog({ event, eventParsed })"
        >
          <strong>{{ formatTime(event.startTime) }}</strong> {{ event.name }}
        </div>
      </template>
    </v-calendar>
  </v-col>
</v-row>

    <!-- Dialog for Reservation Details -->

    <!-- Dialog for Event Details -->
    <v-dialog v-model="dialog" max-width="600">
  <v-card>
    <v-toolbar flat color="primary" dark>
      <v-toolbar-title>{{ $t("appointment_details") }}</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn icon @click="dialog = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>

    <v-card-text>
      <v-container>
        <v-row>
          <!-- Event Info -->
          <v-col cols="12" md="6">
            <v-list dense>
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>
                    <strong>{{ $t("name") }}:</strong> {{ book_details.name }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>

              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>
                    <strong>{{ $t("phone") }}:</strong> {{ book_details.user_phone }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>


              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>
                    <strong>{{ $t("datatable.doctor") }}:</strong> {{ book_details.owner_name || book_details.doctor_name }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>


              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>
                    <strong>{{ $t("appointment") }}:</strong>
                    {{ formatTime(book_details.book_time) }} | {{ book_details.star_date }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>

              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>
                    <v-chip
                      :color="book_details.is_examine == 1 ? 'primary' : 'grey'"
                      small
                      text-color="white"
                    >
                      <v-icon small left>mdi-stethoscope</v-icon>
                      {{ $t("examination") }}
                    </v-chip>
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
      </v-container>
    </v-card-text>

    <v-card-actions>
      <v-btn color="info" dark @click="viewPatientDetails">
        <v-icon left>mdi-account-details</v-icon>{{ $t("view_patient_details") || "عرض تفاصيل المريض" }}
      </v-btn>
      <v-btn color="success" dark @click="chatWithPatient">
        <v-icon left>mdi-whatsapp</v-icon>{{ $t("whatsapp_chat") }}
      </v-btn>
      <v-btn color="error" dark @click="confirmDelete">
        <v-icon left>mdi-delete</v-icon>{{ $t("delete_appointment") }}
      </v-btn>
      <v-spacer></v-spacer>
      <v-btn color="primary" text @click="dialog = false">{{ $t("close") }}</v-btn>
    </v-card-actions>
  </v-card>
</v-dialog>


    <!-- Booking Dialog -->
    <v-dialog v-model="BookingDetails" width="700" height="700" persistent>
      <v-form ref="form" v-model="valid">
        <v-card height="auto">
          <v-toolbar dark color="primary lighten-1 mb-5">
            <v-toolbar-title>
              <h3 style="color:#fff;font-family: 'Cairo'">{{ $t("book_appointment") }}</h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="close()" icon>
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>
          <v-container grid-list-xs>
            <div v-if="!patientFound">
              <v-row justify="center">
                <v-col>
                  <multiselect v-model="patient" style="text-align: right;" dir="rtl" track-by="id"
                    :rules="ArtTypeRules" label="name" :placeholder="$t('select_patient')" open-direction="bottom"
                    :options="patients" :multiple="false" :searchable="true" :loading="isLoading"
                    :internal-search="false" :clear-on-select="true" :close-on-select="true" :options-limit="300"
                    :max-height="600" :show-no-results="false" @search-change="asyncFind">
                  </multiselect>

                </v-col>
              </v-row>
            </div>
            <br>
            <br>
            <v-row justify="center">

            
              <v-col class="py-0" cols="12" sm="12" md="12" v-if="($store.state.role=='secretary'|| $store.state.role=='accounter') && doctors && doctors.length>0">
                <v-select :rules="[rules.required]" v-model="editedItem.doctors" :label="$t('doctor')" return-object
                  :items="doctors" outlined item-text="name" item-value="id">
                </v-select>

              </v-col>

          

              <v-col cols="12" sm="6" xs="12" >
                <v-time-picker v-model="editedItem.reservation_from_time" full-width format="ampm"></v-time-picker>


                <div v-if="$store.state.AdminInfo.send_msg ==1">
                  <v-checkbox 
                    :disabled="!editedItem.reservation_from_time || !editedItem.reservation_start_date" 
                    v-model="send_msg"
                    style="text-align: right;" 
                    :label="$t('send_reminder_message')"
                    @change="handleSendMsg"
                  />

                  <v-textarea 
                    v-if="send_msg" 
                    v-model="editedItem.appointmentMessage" 
                    :label="$t('message')" 
                    outlined 
                    rows="3"
                    auto-grow
                  />
                </div>

                <!-- is_examine checkbox with better UI -->
                <v-card outlined class="mt-3 pa-2" color="blue lighten-5">
                  <v-checkbox 
                    v-model="editedItem.is_examine" 
                    color="primary"
                    class="mt-0"
                  >
                    <template v-slot:label>
                      <div class="d-flex align-center">
                        <v-icon color="primary" class="ml-2">mdi-stethoscope</v-icon>
                        <span style="font-weight: 500; color: #1976d2;">{{ $t('examination') || 'فحص' }}</span>
                      </div>
                    </template>
                  </v-checkbox>
                </v-card>

              </v-col>

            </v-row>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}</v-btn>
              <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>
                {{ $t("book") }}
              </v-btn>
            </v-card-actions>
          </v-container>
        </v-card>
      </v-form>
    </v-dialog>
  </v-container>
</template>

<script>
  import axios from 'axios';
  import Multiselect from 'vue-multiselect'
  import { EventBus } from './event-bus.js';
  // import cacheManager from '@/utils/cache'; // Commented out - cache not working properly

  export default {
    components: {
      Multiselect
    },
    data() {
      return {
        // Cache configuration - COMMENTED OUT (not working properly)
        /*
        cacheConfig: {
          reservations: {
            key: 'reservations_cache',
            ttl: 2 * 60 * 1000, // 2 minutes
          },
          patients: {
            key: 'patients_cache',
            ttl: 5 * 60 * 1000, // 5 minutes
          },
          doctors: {
            key: 'doctors_cache',
            ttl: 30 * 60 * 1000, // 30 minutes
          },
          ownerItems: {
            key: 'owner_items_cache',
            ttl: 60 * 60 * 1000, // 1 hour
          }
        },
        */
        dialogInfo: false,
        owner_item: '',
        BookingDetails: false,
        ArtTypeRules: [
          (v) => !!v || 'يجب ادخال نوع المقاله',

        ],
        selectedEvent: {},
        tokx: '',
        confirmDialog: false,
        doctors: [],
        dialog: false,
        book_details: {},
        send_msg: false,
        focus: "2025-12-01", // Focus on October by default
        startDate: "2025-12-01", // Start of October
        endDate: "2026-01-01", // End of November

        valid: true,

        patient: null,
        editedItem: {
          reservation_from_time: null,
          reservation_to_time: null,
          appointmentMessage: "",
          is_examine: false,
        },
        menu2: false,
        menu3: false,
        patients: [],
        loadSave: false,
        item_id: '',
        patientFound: false,
        reservations: [],
        loadingData: false,

        // New data properties for doctor filter
        selectedDoctorFilter: null, // Selected doctor for filtering
        doctorFilterOptions: [], // Doctor options for filter dropdown

        rules: {
          required: value => !!value || this.$t("field_required"),
          phoneNumber: value => /^\d+$/.test(value) || this.$t('enter_valid_phone_number'),
        },



      };
    },
    mounted() {
      // Auto-detect and show current month
      const now = new Date();
      const currentMonth = now.getMonth() + 1; // +1 because getMonth() is 0-indexed
      const currentYear = now.getFullYear();
      
      console.log('📅 Auto-detected current month dates:', {
        focus: this.focus,
        startDate: this.startDate,
        endDate: this.endDate,
        currentMonth: currentMonth,
        currentYear: currentYear
      });
      
      console.log(`🗓️ Calendar will automatically show ${currentYear}-${currentMonth.toString().padStart(2, '0')} (current month)`);
      
      // Initialize cache check
      console.log('🚀 RequestBooking component mounted - initializing cache system');        // Force calendar to show current month (October)
        this.$nextTick(() => {
          if (this.$refs.calendar) {
            this.$refs.calendar.updateTimes();
          }
        });
        
        // this.fetchReservations(); 
        this.getclinicDoctor();
        this.getPatient(); // Fetch patients for autocomplete
        this.getOwnerTctateitemsById();
     // Ensure reservations are loaded when the page opens
     
      // Listen for status changes to clear cache and refresh colors
      if (EventBus) {
        EventBus.$on('changeStatus', () => {
          console.log('📡 Status change detected - clearing reservations cache');
          // Clear reservations cache to force refresh with updated colors
          // cacheManager.delete(this.currentReservationsCacheKey); // Commented out - cache not working
          // Refresh reservations to get updated colors
          this.fetchReservations();
        });
      }
    },

    // beforeDestroy method - COMMENTED OUT (cache not working properly)
    /*
    beforeDestroy() {
      // Clean up EventBus listener to prevent memory leaks
      if (EventBus) {
        EventBus.$off('changeStatus');
      }
    },
    */

    computed: {
      clinicName() {
        return this.$store.state.AdminInfo.clinics_info?.name || '';
      },
      
      // Cache-related computed properties - COMMENTED OUT (not working properly)
      /*
      cacheStatus() {
        return {
          reservations: cacheManager.has(this.cacheConfig.reservations.key),
          patients: cacheManager.has(this.cacheConfig.patients.key),
          doctors: cacheManager.has(this.cacheConfig.doctors.key),
          ownerItems: cacheManager.has(this.cacheConfig.ownerItems.key)
        };
      },

      // Current cache key based on selected doctor filter
      currentReservationsCacheKey() {
        return this.selectedDoctorFilter 
          ? `reservations_cache_doctor_${this.selectedDoctorFilter}`
          : this.cacheConfig.reservations.key;
      }
      */
    },

    watch: {
      // Watch for changes in reservation time to update message
      'editedItem.reservation_from_time'() {
        if (this.send_msg) {
          this.handleSendMsg(true);
        }
      },
      'editedItem.reservation_to_time'() {
        if (this.send_msg) {
          this.handleSendMsg(true);
        }
      },
      'editedItem.reservation_start_date'() {
        if (this.send_msg) {
          this.handleSendMsg(true);
        }
      }
    },
   
    methods: {
      // Auto-generate current month dates
      getCurrentMonthStart() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth(); // 0-indexed
        return new Date(year, month, 1).toISOString().split('T')[0]; // First day of current month
      },

      getCurrentMonthEnd() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth(); // 0-indexed
        return new Date(year, month + 1, 0).toISOString().split('T')[0]; // Last day of current month
      },

      // Extract first name from doctor's full name
      getDoctorFirstName(doctorName) {
        if (!doctorName) return '';
        // Split by space and return first part
        const nameParts = doctorName.trim().split(' ');
        return nameParts[0] || '';
      },

       formatEventDetails(event) {
    const time = this.formatTime(event.startTime); // Format the time
    return `${time} ${event.name}`; // Combine time and name
  },
      formatTime(time) {
    if (!time) return '';
    const [hours, minutes] = time.split(':').map(Number);
    const period = hours >= 12 ? 'م' : 'ص';
    const formattedHours = hours % 12 || 12; // Convert 0 to 12 for 12-hour format
    return `${formattedHours}:${minutes.toString().padStart(2, '0')} ${period}`;
  },
      asyncFind(query) {
        if (query.length > 2) {
          this.isLoading = true;
          
          // Create a cache key for the search query
          const searchCacheKey = `patient_search_${query}`;
          
          // Check cache first - COMMENTED OUT (cache not working properly)
          /*
          const cached = cacheManager.get(searchCacheKey);
          if (cached) {
            console.log('✅ Using cached patient search results');
            this.patients = cached;
            this.isLoading = false;
            return;
          }
          */

          axios.get("https://mina-api.tctate.com/api/patients/searchv2/" + query, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              this.isLoading = false;
              this.patients = res.data.data; // Search results
              
              // Cache the search results for 1 minute - COMMENTED OUT (not working properly)
              // cacheManager.set(searchCacheKey, res.data.data, 60 * 1000);
              console.log('💾 Cached patient search results');
            })
            .catch(error => {
              this.isLoading = false;
              console.error('Error searching patients:', error);
              
              // Try to use expired cache as fallback
              const fallbackCache = localStorage.getItem('clinic_app_cache_' + searchCacheKey);
              if (fallbackCache) {
                try {
                  const cacheData = JSON.parse(fallbackCache);
                  this.patients = cacheData.data;
                  console.log('📦 Using fallback cache for patient search');
                } catch (e) {
                  console.warn('Failed to parse fallback cache');
                }
              }
            });
        }
      },
      async getclinicDoctor() {
       
        if (this.$store.state.role == 'secretary' || this.$store.state.role == 'accounter'  || this.$store.state.role == 'adminDoctor') {
          console.log('Fetching doctors...');
          
          // Check cache first - COMMENTED OUT (cache not working properly)
          /*
          const cached = cacheManager.get(this.cacheConfig.doctors.key);
          if (cached) {
            console.log('✅ Using cached doctors');
            this.doctors = cached;
            this.setupDoctorFilterOptions();
            this.fetchReservations();
            return;
          }
          */
          
          this.loading = true;

          
          await axios.get("https://mina-api.tctate.com/api/doctors/secretary", {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              this.loadingData = false;
              this.loading = false;
              this.doctors = res.data.data;
              
              // Cache the doctors data - COMMENTED OUT (cache not working properly)
              // cacheManager.set(this.cacheConfig.doctors.key, res.data.data, this.cacheConfig.doctors.ttl);
              console.log('💾 Cached doctors data');
              
              this.setupDoctorFilterOptions();
              this.fetchReservations();
            })
            .catch(error => {
              this.loading = false;
              console.error('Error fetching doctors:', error);
              
              // Try to use expired cache as fallback
              const fallbackCache = localStorage.getItem('clinic_app_cache_' + this.cacheConfig.doctors.key);
              if (fallbackCache) {
                try {
                  const cacheData = JSON.parse(fallbackCache);
                  this.doctors = cacheData.data;
                  this.setupDoctorFilterOptions();
                  console.log('📦 Using fallback cache for doctors');
                } catch (e) {
                  console.warn('Failed to parse fallback cache');
                }
              }
              
              this.fetchReservations();
            });

        } else {
          this.fetchReservations();
        }

      },

      // Setup doctor filter options
      setupDoctorFilterOptions() {
        if (this.doctors && this.doctors.length > 0) {
          this.doctorFilterOptions = [
            { id: null, name: this.$t('all_doctors') || 'جميع الأطباء' },
            ...this.doctors
          ];
        }
      },

      // Handle doctor filter change
      async onDoctorFilterChange(doctorId) {
        console.log('Doctor filter changed to:', doctorId);
        this.selectedDoctorFilter = doctorId;
        
        // Clear current reservations cache - COMMENTED OUT (cache not working)
        // cacheManager.remove(this.currentReservationsCacheKey);
        
        // Fetch reservations for selected doctor
        await this.fetchReservations();
      },

      showDeleteConfirmation() {
        this.confirmDialog = true;
      },
      async confirmDelete() {
        this.dialog = false;
        const userConfirmed = confirm("هل أنت متأكد من حذف هذا الموعد؟ لا يمكن التراجع عن هذا الإجراء.");
        if (!userConfirmed) {
          return; // If user cancels, exit the function
        }

        try {
          // Update to use the new API endpoint for deletion
          await axios.delete(`https://mina-api.tctate.com/api/reservations/${this.book_details.id}`, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });

          // Clear reservations cache to force refresh - COMMENTED OUT (cache not working)
            // cacheManager.remove(this.cacheConfig.reservations.key);
            // console.log('🗑️ Cleared reservations cache after deletion');

          // Refresh the reservations list
          await this.fetchReservations();

          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم حذف الموعد بنجاح",
            showConfirmButton: false,
            timer: 1500
          });

          this.dialog = false;
        } catch (error) {
          console.error("Error deleting reservation:", error);
          
          this.$swal.fire({
            icon: 'error',
            title: 'خطأ في حذف الموعد',
            text: 'حدث خطأ أثناء حذف الموعد. يرجى المحاولة مرة أخرى.',
            confirmButtonText: 'موافق'
          });
        }
      },
      chatWithPatient() {

        const phone = '+' + this.book_details.user_phone;
        window.open(`https://wa.me/${phone}`, "_blank");
      },

     
      // Navigate to patient details page
      viewPatientDetails() {
        if (this.book_details.patient_id) {
          this.$router.push(`/patient/${this.book_details.patient_id}`);
          
        } else {
          this.$swal.fire({
            icon: 'warning',
            title: 'تحذير',
            text: 'معرف المريض غير متوفر',
            confirmButtonText: 'موافق'
          });
        }
      },
      openReservationDialog(event) {
        console.log('Opening reservation dialog:', event);

        // Extract event details with fallbacks for missing data
        this.book_details.star_date = event.eventParsed.start.date;
        this.book_details.name = event.event.name;
        this.book_details.book_time = event.event.startTime;
        this.book_details.user_phone = event.event.phone;
        this.book_details.id = event.event.id;
        this.book_details.patient_id = event.event.patient_id; // Add patient_id
        this.book_details.owner_name = event.event.owner_name || event.event.doctor_name || 'غير محدد'; // Use doctor_name as fallback
        this.book_details.doctor_name = event.event.doctor_name || event.event.owner_name || 'غير محدد'; // Add doctor_name field
        this.book_details.is_examine = event.event.is_examine || 0; // Add is_examine field

        this.selectedEvent = event; // Set the selected reservation
        this.dialog = true;
      },
      handleSendMsg(newValue) {
        if (newValue && this.editedItem.reservation_from_time && this.editedItem.reservation_start_date) {
          // Auto-generate the appointment message when switch is turned on
          const toTime = this.owner_item.possib_reserving_period == null ? 
            this.editedItem.reservation_to_time : 
            this.addtime(this.owner_item.possib_reserving_period);
            
          this.editedItem.appointmentMessage = 
            `يسرنا إبلاغكم بموعدكم القادم في عيادة ${this.clinicName} بتاريخ ${this.editedItem.reservation_start_date} في الساعة ${this.convertToArabicAmPm(this.editedItem.reservation_from_time)}${toTime ? ' حتى ' + this.convertToArabicAmPm(toTime) : ''}. نتمنى لكم دوام الصحة والعافية ونتطلع لرؤيتكم قريبًا.`;
        } else {
          this.editedItem.appointmentMessage = '';
        }
      },
      convertToArabicAmPm(time) {
        const [hours, minutes] = time.split(':').map(Number);
        let period = hours >= 12 ? 'مساءاً' : 'صباحاً';
        let adjustedHours = hours % 12 || 12;
        return `${adjustedHours}:${minutes.toString().padStart(2, '0')} ${period}`;
      },
      getOwnerTctateitemsById() {
        console.log('Fetching owner items...');
        
        // Check cache first - COMMENTED OUT (cache not working)
        // const cached = cacheManager.get(this.cacheConfig.ownerItems.key);
        // if (cached) {
        //   console.log('✅ Using cached owner items');
        //   this.item_id = cached.id;
        //   this.owner_item = cached;
        //   return;
        // }
        
        this.loading = true
        this.$http({
          method: 'get',
          url: "https://tctate.com/api/api/v2/items/owner/get?page=1",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
          }

        }).then(response => {
          this.loading = false;
          this.item_id = response.data.data[0].id;
          this.owner_item = response.data.data[0];
          
          // Cache the owner item data - COMMENTED OUT (cache not working)
          // cacheManager.set(this.cacheConfig.ownerItems.key, response.data.data[0], this.cacheConfig.ownerItems.ttl);
          // console.log('💾 Cached owner items data');
        }).catch(error => {
          this.loading = false;
          console.error('Error fetching owner items:', error);
          
          // Try to use expired cache as fallback
          const fallbackCache = localStorage.getItem('clinic_app_cache_' + this.cacheConfig.ownerItems.key);
          if (fallbackCache) {
            try {
              const cacheData = JSON.parse(fallbackCache);
              this.item_id = cacheData.data.id;
              this.owner_item = cacheData.data;
              console.log('📦 Using fallback cache for owner items');
            } catch (e) {
              console.warn('Failed to parse fallback cache');
            }
          }
        });
      },
      getPatient() {
        console.log('Fetching patients...');
        
        // Check cache first - COMMENTED OUT (cache not working properly)
        /*
        const cached = cacheManager.get(this.cacheConfig.patients.key);
        if (cached) {
          console.log('✅ Using cached patients');
          this.patients = cached;
          return;
        }
        */
        
        this.loadingData = true; // Show loading indicator
        axios.get("https://mina-api.tctate.com/api/patients/getByUserIdv3", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            }
          })
          .then(res => {
            this.loadingData = false;
            this.patients = res.data.data; // Store fetched patients
            
            // Cache the patients data - COMMENTED OUT (cache not working properly)
            // cacheManager.set(this.cacheConfig.patients.key, res.data.data, this.cacheConfig.patients.ttl);
            console.log('💾 Cached patients data');
          })
          .catch(error => {
            this.loadingData = false;
            console.error('Error fetching patients:', error);
            
            // Try to use expired cache as fallback
            const fallbackCache = localStorage.getItem('clinic_app_cache_' + this.cacheConfig.patients.key);
            if (fallbackCache) {
              try {
                const cacheData = JSON.parse(fallbackCache);
                this.patients = cacheData.data;
                console.log('📦 Using fallback cache for patients');
              } catch (e) {
                console.warn('Failed to parse fallback cache');
              }
            }
          });
      },
      addtime(item) {
        let time = this.editedItem.reservation_from_time; // Example: "12:30"
        let [hours, minutes] = time.split(":").map(Number);

        let date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes + item); // Add 60 minutes

        return `${date.getHours()}:${date.getMinutes().toString().padStart(2, "0")}`;
      },
      async save() {
        if (this.$refs.form.validate()) {
          this.loadSave = true; // Show loading indicator

          try {
            const fromTime = this.editedItem.reservation_from_time;
            const toTime = this.owner_item.possib_reserving_period == null ? 
              this.editedItem.reservation_to_time :
              this.addtime(this.owner_item.possib_reserving_period);

            // The reservation start date is the date clicked by the user
            const reservationStartDate = this.editedItem.reservation_start_date;

            // Prepare reservation data for the new API
            const reservationData = {
              patient_id: this.patient ? this.patient.id : null,
              reservation_start_date: reservationStartDate,
              reservation_end_date: reservationStartDate,
              reservation_from_time: fromTime,
              reservation_to_time: toTime,
              item_id: this.item_id,
              reservation_number: 1,
              appointmentMessage: this.editedItem.appointmentMessage || "",
              reservation_date: reservationStartDate,
              ReservationRequirements: [],
              item_features: [],
              phone: "",
              withoutBills: 0,
              deliverable: false,
              is_examine: this.editedItem.is_examine ? 1 : 0,
              doctor_id: this.editedItem.doctors ? this.editedItem.doctors.id : null, // Include doctor ID when selected
              user: {
                phone: this.patient && this.patient.phone ? 
                  "964" + parseInt(this.patient.phone.replace(/ /g, "")) : "9647700281899",
                name: this.patient ? this.patient.name : ""
              }
            };

            console.log('Booking data:', reservationData);

            // Make the API call to create reservation
            const response = await axios.post('https://mina-api.tctate.com/api/reservations', reservationData, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token,
              },
            });

            console.log('Booking response:', response.data);

            // Send WhatsApp message if the switch is enabled and message exists
            if (this.send_msg && this.editedItem.appointmentMessage && this.patient) {
              try {
                const whatsappData = {
                  patient_id: this.patient.id,
                  message: this.editedItem.appointmentMessage,
                  date: `${reservationStartDate} ${fromTime}`
                };

                const whatsappResponse = await axios.post('https://mina-api.tctate.com/api/whatsapp', whatsappData, {
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                  },
                });

                console.log('WhatsApp message sent:', whatsappResponse.data);
              } catch (whatsappError) {
                console.error('Error sending WhatsApp message:', whatsappError);
                // Don't fail the booking if WhatsApp fails
              }
            }

            // Add the new reservation to the local list
            const patientName = reservationData.user.name;
            const doctorName = this.editedItem.doctors ? this.editedItem.doctors.name : '';
            const displayName = (this.$store.state.role === 'secretary' || this.$store.state.role === 'adminDoctor') && doctorName
              ? `${patientName} | ${this.getDoctorFirstName(doctorName)}`
              : patientName;
              
            this.reservations.push({
              name: displayName,
              details: `Appointment with ${patientName}`,
              phone: reservationData.user.phone,
              patient_id: this.patient ? this.patient.id : null,
              owner_name: doctorName,
              doctor_name: doctorName,
              id: response.data.id || Date.now(),
              start: `${reservationStartDate} ${fromTime}`,
              startTime: fromTime,
              color: this.getReservationColor(reservationStartDate, fromTime)
            });

            // Clear reservations cache to force refresh on next load - COMMENTED OUT (cache not working properly)
            // cacheManager.remove(this.cacheConfig.reservations.key);
            console.log('🗑️ Cleared reservations cache after new booking');

            // Close dialog and show success message
            this.close();
            this.BookingDetails = false;
            
            this.$swal.fire({
              position: "top-end",
              icon: "success",
              title: this.send_msg && this.editedItem.appointmentMessage ? 
                "تم الحجز وإرسال الرسالة بنجاح" : "تم الحجز بنجاح",
              showConfirmButton: false,
              timer: 2000
            });

            this.resetForm();
            
            // Force refresh reservations to get complete data from server
            this.fetchReservations(); // Refresh the calendar with server data

          } catch (error) {
            console.error('Error creating reservation:', error);
            
            this.$swal.fire({
              icon: 'error',
              title: 'خطأ في الحجز',
              text: 'حدث خطأ أثناء إنشاء الموعد. يرجى المحاولة مرة أخرى.',
              confirmButtonText: 'موافق'
            });
          } finally {
            this.loadSave = false;
          }
        }
      },
      async fetchReservations() {
        try {
          console.log('Fetching reservations...');
          
          // Check cache first using current cache key - COMMENTED OUT (cache not working properly)
          /*
          const cached = cacheManager.get(this.currentReservationsCacheKey);
          if (cached) {
            console.log('✅ Using cached reservations');
            this.reservations = cached;
            return;
          }
          */
          
          // Determine API endpoint based on doctor filter
          let apiEndpoint = 'https://mina-api.tctate.com/api/reservations/formatted';
          if (this.selectedDoctorFilter && (this.$store.state.role === 'secretary' || this.$store.state.role === 'adminDoctor' || this.$store.state.role === 'accounter')) {
            apiEndpoint =`https://mina-api.tctate.com/api/reservations/formatted/doctor/${this.selectedDoctorFilter}`;
          }
          
          console.log('API Endpoint:', apiEndpoint);
          
          // Use the API endpoint
          const response = await axios.get(apiEndpoint, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            }
          });

          console.log('API Response:', response.data);

          // Check if the response has the expected structure
          if (response.data && response.data.status && response.data.data) {
            // Map the API response to the format expected by the calendar
            const formattedReservations = response.data.data.map(reservation => {
              // Debug: log reservation structure to understand doctor data
              console.log('🔍 Reservation data:', reservation);
              
              // Validate required date/time fields to prevent null timestamp errors
              if (!reservation.reservation_start_date || !reservation.reservation_from_time) {
                console.warn('⚠️ Skipping reservation with missing date/time:', reservation);
                return null; // Skip this reservation
              }
              
              // Try multiple possible doctor field locations
              const doctorName = reservation.doctor?.name || 
                                reservation.owner?.name || 
                                reservation.doctor_name || 
                                reservation.owner_name || 
                                '';
              
              console.log('👨‍⚕️ Found doctor name:', doctorName);
              
              // Format the display name with doctor info for secretary/admin roles
              const patientName = reservation.user?.full_name || reservation.user?.name || 'Unknown Patient';
              const displayName = (this.$store.state.role === 'secretary' || this.$store.state.role === 'adminDoctor') && doctorName
                ? `${patientName}| ${this.getDoctorFirstName(doctorName)}`
                : patientName;
              
              const eventData = {
                name: displayName,
                details: `Appointment with ${patientName}`,
                phone: reservation.user?.user_phone || reservation.user?.phone || '',
                patient_id: reservation.patient_id, // Use patient_id directly from response
                owner_name: doctorName, // Map doctor name
                doctor_name: doctorName, // Add doctor_name field
                id: reservation.id,
                status_id: reservation.status_id, // Add status_id to event data
                is_examine: reservation.is_examine || 0, // Add is_examine field
                start: `${reservation.reservation_start_date} ${reservation.reservation_from_time}`,
                startTime: reservation.reservation_from_time
              };
              
              // Set color based on status_id and date/time
              eventData.color = this.getEventColor(eventData);
              
              return eventData;
            }).filter(event => event !== null); // Remove null entries

            this.reservations = formattedReservations;
            
            // Cache the formatted reservations with current cache key - COMMENTED OUT (cache not working properly)
            // cacheManager.set(this.currentReservationsCacheKey, formattedReservations, this.cacheConfig.reservations.ttl);
            
            console.log('Formatted reservations:', this.reservations);
          } else {
            console.error('Unexpected API response structure:', response.data);
            this.reservations = [];
          }
        } catch (error) {
          console.error('Error fetching reservations:', error);
          console.error('Full error details:', error.response?.data || error.message);
          
          // Try to use expired cache as fallback
          const fallbackCache = localStorage.getItem('clinic_app_cache_' + this.currentReservationsCacheKey);
          if (fallbackCache) {
            try {
              const cacheData = JSON.parse(fallbackCache);
              this.reservations = cacheData.data;
              console.log('📦 Using fallback cache for reservations');
            } catch (e) {
              console.warn('Failed to parse fallback cache');
            }
          }
          
          // Fallback: show user-friendly error message
          // this.$swal.fire({
          //   icon: 'error',
          //   title: 'خطأ في تحميل المواعيد',
          //   text: 'حدث خطأ أثناء تحميل المواعيد. يرجى المحاولة مرة أخرى.',
          //   confirmButtonText: 'موافق'
          // });
          
          if (!this.reservations.length) {
            this.reservations = [];
          }
        }
      },

      openBookingDialog(dateInfo) {
        const date = new Date(dateInfo.date);
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;
        this.editedItem.reservation_start_date = formattedDate;
        this.BookingDetails = true;
      },
      close() {
        this.BookingDetails = false;
        this.send_msg = false;
        this.resetForm();
      },
      resetForm() {
        this.patient = null;
        this.editedItem = {
          reservation_from_time: null,
          reservation_to_time: null,
          appointmentMessage: "",
          is_examine: false,
        };
      },
      // Get event color based on status_id and date/time
      getEventColor(event) {
        const currentDateTime = new Date();
        const eventDateTime = new Date(event.start); // Parse the event's start date and time

        // Check status_id first
        if (event.status_id === 2) {
          return "blue"; // Status 2 gets blue color
        }

        // Check if the event is in the past
        if (eventDateTime < currentDateTime) {
          return "green"; // Event is in the past
        }

        return "#FFA500"; // Default to event color or lightblue
      },
      // Helper method to determine color based on reservation date and time
      getReservationColor(reservationDate, reservationTime) {
        const currentDateTime = new Date();
        const reservationDateTime = new Date(`${reservationDate} ${reservationTime}`);
        
        // Check if the reservation is in the past
        if (reservationDateTime < currentDateTime) {
          return "green"; // Past reservations are green
        }
        
        return "#FFA500"; // Future reservations are orange
      },
      searchPatients(query) {
        console.log(query)
        if (query.length < 3) {
          return;
        }
        this.loadingData = true;
        axios.get(`https://mina-api.tctate.com/api/patients/search?query=${query}`, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            }
          })
          .then(res => {
            this.loadingData = false;
            this.patients = res.data.data;
          })
          .catch(() => {
            this.loadingData = false;
          });
      },
      // Cache management methods - COMMENTED OUT (not working properly)
      /*
      clearCache(key) {
        cacheManager.remove(key);
      },

      clearAllCache() {
        Object.values(this.cacheConfig).forEach(config => {
          cacheManager.remove(config.key);
        });
        
        // Also clear doctor-specific caches
        if (this.doctors && this.doctors.length > 0) {
          this.doctors.forEach(doctor => {
            cacheManager.remove(`reservations_cache_doctor_${doctor.id}`);
          });
        }
        
        console.log('🧹 Cleared all booking cache');
      },

      // Force refresh data by clearing cache and refetching
      forceRefreshData() {
        // Reset doctor filter
        this.selectedDoctorFilter = null;
        
        // Reset calendar to current month
        this.focus = this.getCurrentMonthStart();
        this.startDate = this.getCurrentMonthStart();
        this.endDate = this.getCurrentMonthEnd();
        
        console.log('🔄 Force refreshed to current month:', {
          focus: this.focus,
          startDate: this.startDate,
          endDate: this.endDate,
          currentMonth: new Date().getMonth() + 1
        });
        
        // Refresh data
        this.getclinicDoctor();
        this.getPatient();
        this.getOwnerTctateitemsById();
      },
      // Check cache validity and refresh if needed
      async checkAndRefreshCache() {
        console.log('🔍 Checking cache validity...');
        
        // Check each cache type
        const cacheChecks = [
          { key: this.cacheConfig.reservations.key, method: 'fetchReservations' },
          { key: this.cacheConfig.patients.key, method: 'getPatient' },
          { key: this.cacheConfig.doctors.key, method: 'getclinicDoctor' },
          { key: this.cacheConfig.ownerItems.key, method: 'getOwnerTctateitemsById' }
        ];
        
        for (const check of cacheChecks) {
          // if (!cacheManager.has(check.key)) {
          //   console.log(`⚠️ Cache miss for ${check.key}, refreshing...`);
          //   if (check.method === 'fetchReservations') {
          //     await this.fetchReservations();
          //   } else if (check.method === 'getPatient') {
          //     this.getPatient();
          //   } else if (check.method === 'getclinicDoctor') {
          //     await this.getclinicDoctor();
          //   } else if (check.method === 'getOwnerTctateitemsById') {
          //     this.getOwnerTctateitemsById();
          //   }
          // }
        }
      },

      // Handle cache warming - preload data in background
      async warmupCache() {
        console.log('🔥 Starting cache warmup...');
        
        // Warmup in background without blocking UI
        setTimeout(() => {
          this.getPatient();
          this.getOwnerTctateitemsById();
        }, 100);
      },
      */

    },
    beforeDestroy() {
      // Optional: Clear cache when component is destroyed if needed
      // this.clearAllCache();
      console.log('🧹 RequestBooking component destroyed');
    },
  };
</script>

<style scoped>
  .calendar-day {
    min-height: 150px;
    padding: 5px;
    border: 1px solid #ddd;
    display: flex;
    flex-direction: column;
  }

  .calendar-day .day-number {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
  }

  .calendar-day .v-chip {
    font-size: 12px;
  }

  .doctor-name {
    font-size: 10px;
    opacity: 0.9;
    font-weight: normal;
    margin-left: 4px;
  }

  .highlight-day {
    background-color: #ffeb3b;
    border-radius: 50%;
  }

  /* Increase calendar day height to show all events */
  :deep(.v-calendar-weekly__day) {
    min-height: 250px !important;
    height: auto !important;
    max-height: none !important;
  }

  /* Ensure past days also have proper height */
  :deep(.v-calendar-weekly__day.v-past) {
    min-height: 250px !important;
    height: auto !important;
    max-height: none !important;
  }

  /* Make sure the calendar container is flexible */
  :deep(.v-calendar-weekly__week) {
    min-height: 250px !important;
  }

  /* Adjust event container within days */
  :deep(.v-calendar-weekly__day-container) {
    min-height: 230px !important;
    height: auto !important;
    max-height: none !important;
    overflow-y: visible !important;
  }

  /* Style for individual events with larger font */
  :deep(.v-event) {
    margin-bottom: 2px !important;
    font-size: 12px !important;
    line-height: 1.3 !important;
    padding: 3px 6px !important;
    border-radius: 4px !important;
    height: auto !important;
    min-height: 22px !important;
  }

  /* Larger font for event text */
  :deep(.v-event-summary) {
    font-size: 12px !important;
    font-weight: 500 !important;
  }

  /* Remove height restrictions on event containers */
  :deep(.v-calendar-weekly__events) {
    max-height: none !important;
    overflow: visible !important;
  }

  /* Increase the overall calendar month view height */
  :deep(.v-calendar-monthly) {
    height: auto !important;
    min-height: 800px !important;
  }

  /* Ensure month view days are tall enough */
  :deep(.v-calendar-monthly__day) {
    min-height: 120px !important;
    height: auto !important;
    max-height: none !important;
  }
</style>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
  .multiselect__tags {
    border: none;
    border-bottom: solid thin rgba(0, 0, 0, 0.42);
    border-radius: 0;
  }
</style>


<style scoped>
  .v-card {
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .v-toolbar-title {
    font-size: 18px;
    font-weight: bold;
  }

  .v-list-item-title {
    font-size: 16px;
    color: #333;
    margin-bottom: 8px;
  }

  .v-list-item-title strong {
    color: #1976d2; /* Primary color for labels */
  }

  .v-btn {
    margin-right: 8px;
  }
</style>