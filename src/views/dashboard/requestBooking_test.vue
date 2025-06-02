<template>
  <v-container fluid>
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
          @click:event="openReservationDialog">
          <template v-slot:day="{ date, outside, events }">
            <div v-if="!outside" class="calendar-day">
              <div class="day-number"></div>
              <v-chip 
                v-for="event in events" 
                :key="event.start" 
                :color="getEventColor(event)" 
                class="ma-1" 
                label>
                <strong>{{ event.name }}</strong>
              </v-chip>
            </div>
          </template>
        </v-calendar>
      </v-col>
    </v-row>

    <!-- Dialog for Reservation Details -->
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
                        <strong>{{ $t("datatable.doctor") }}:</strong> {{ book_details.owner_name }}
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
                </v-list>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
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
    <v-dialog v-model="bookingDialog" width="700" persistent>
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
                  <multiselect 
                    v-model="patient" 
                    style="text-align: right;" 
                    dir="rtl" 
                    track-by="id"
                    :rules="requiredRule" 
                    label="name" 
                    :placeholder="$t('select_patient')" 
                    open-direction="bottom"
                    :options="patients" 
                    :multiple="false" 
                    :searchable="true" 
                    :loading="isLoading"
                    :internal-search="false" 
                    :clear-on-select="true" 
                    :close-on-select="true" 
                    :options-limit="300"
                    :max-height="600" 
                    :show-no-results="false" 
                    @search-change="asyncFind">
                  </multiselect>
                </v-col>
              </v-row>
            </div>
            <br>
            <br>
            <v-row justify="center">
              <v-col cols="12" sm="6" xs="12" v-if="!hasReservingPeriod">
                <v-menu 
                  ref="menu1" 
                  v-model="menu2" 
                  :close-on-content-click="false" 
                  :nudge-right="40"
                  :return-value.sync="appointmentData.reservation_from_time" 
                  transition="scale-transition" 
                  offset-y
                  max-width="290px" 
                  min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field 
                      :rules="[rules.required]" 
                      v-model="appointmentData.reservation_from_time"
                      :label="$t('from_time')" 
                      prepend-icon="mdi-clock-time-four-outline" 
                      readonly 
                      required
                      v-bind="attrs" 
                      v-on="on">
                    </v-text-field>
                  </template>
                  <v-time-picker 
                    v-if="menu2" 
                    v-model="appointmentData.reservation_from_time" 
                    full-width 
                    format="ampm"
                    @click:minute="$refs.menu1.save(appointmentData.reservation_from_time)">
                  </v-time-picker>
                </v-menu>

                <v-menu 
                  ref="menu" 
                  :close-on-content-click="false" 
                  v-model="menu3" 
                  :nudge-right="40"
                  :return-value.sync="appointmentData.reservation_to_time" 
                  transition="scale-transition" 
                  offset-y
                  max-width="290px" 
                  min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field 
                      v-model="appointmentData.reservation_to_time" 
                      :label="$t('to_time')"
                      :rules="[rules.required]" 
                      prepend-icon="mdi-clock-time-four-outline" 
                      readonly 
                      v-bind="attrs"
                      v-on="on">
                    </v-text-field>
                  </template>
                  <v-time-picker 
                    v-if="menu3" 
                    v-model="appointmentData.reservation_to_time" 
                    full-width
                    @click:minute="$refs.menu.save(appointmentData.reservation_to_time)">
                  </v-time-picker>
                </v-menu>

                <div>
                  <v-switch
                    :disabled="!appointmentData.reservation_from_time"
                    v-model="send_msg"
                    style="text-align: right;"
                    :label="$t('send_reminder_message')"
                    @change="generateAppointmentMessage"
                  />

                  <v-textarea 
                    v-if="send_msg" 
                    v-model="appointmentData.appointmentMessage" 
                    :label="$t('message')" 
                    outlined 
                  />
                </div>
              </v-col>

              <v-col cols="12" sm="6" xs="12" v-else>
                <v-time-picker 
                  v-model="appointmentData.reservation_from_time" 
                  full-width 
                  format="ampm">
                </v-time-picker>

                <div>
                  <v-switch 
                    :disabled="!appointmentData.reservation_from_time" 
                    v-model="send_msg"
                    style="text-align: right;" 
                    :label="$t('send_reminder_message')" 
                    @change="generateAppointmentMessage" 
                  />

                  <v-textarea 
                    v-if="send_msg" 
                    v-model="appointmentData.appointmentMessage" 
                    :label="$t('message')"  
                    outlined 
                  />
                </div>
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
import Multiselect from 'vue-multiselect';

export default {
  components: {
    Multiselect
  },
  data() {
    return {
      // Calendar state
      focus: "2025-06-01",
      startDate: "2025-06-01",
      endDate: "2025-07-30",
      reservations: [],
      
      // Dialog state
      dialog: false,
      bookingDialog: false,
      confirmDialog: false,
      
      // Form state
      valid: true,
      isLoading: false,
      loadSave: false,
      loadingData: false,
      send_msg: false,
      menu2: false,
      menu3: false,
      
      // Selected data
      selectedEvent: {},
      patient: null,
      patientFound: false,
      
      // Data collections
      patients: [],
      doctors: [],
      
      // API data
      owner_item: {},
      item_id: '',
      tokx: '',
      
      // Booking details
      book_details: {},
      appointmentData: {
        reservation_from_time: null,
        reservation_to_time: null,
        reservation_start_date: null,
        appointmentMessage: "",
      },
      
      // Validation rules
      requiredRule: [
        v => !!v || this.$t('field_required'),
      ],
      rules: {
        required: value => !!value || this.$t("field_required"),
        phoneNumber: value => /^\d+$/.test(value) || this.$t('enter_valid_phone_number'),
      }
    };
  },
  
  computed: {
    clinicName() {
      return this.$store.state.AdminInfo.clinics_info?.name || '';
    },
    
    hasReservingPeriod() {
      return this.owner_item.possib_reserving_period !== null;
    }
  },
  
  mounted() {
    this.initializeData();
  },
  
  watch: {
    'appointmentData.reservation_from_time': {
      handler(newValue) {
        if (newValue && this.send_msg) {
          this.generateAppointmentMessage();
        }
      }
    }
  },
  
  methods: {
    // Initialization methods
    initializeData() {
      this.getClinicDoctor();
      this.getPatient();
      this.getOwnerTctateitemsById();
    },
    
    // Display formatting methods
    formatEventDetails(event) {
      const time = this.formatTime(event.startTime);
      return `${time} ${event.name}`;
    },
    
    formatTime(time) {
      if (!time) return '';
      const [hours, minutes] = time.split(':').map(Number);
      const period = hours >= 12 ? 'م' : 'ص';
      const formattedHours = hours % 12 || 12;
      return `${formattedHours}:${minutes.toString().padStart(2, '0')} ${period}`;
    },
    
    convertToArabicAmPm(time) {
      if (!time) return '';
      const [hours, minutes] = time.split(':').map(Number);
      let period = hours >= 12 ? 'مساءاً' : 'صباحاً';
      let adjustedHours = hours % 12 || 12;
      return `${adjustedHours}:${minutes.toString().padStart(2, '0')} ${period}`;
    },
    
    // API methods
    async asyncFind(query) {
      if (query.length > 2) {
        this.isLoading = true;

        try {
          const response = await axios.get(`patients/searchv2/${query}`, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
          
          this.patients = response.data.data;
        } catch (error) {
          console.error("Error searching patients:", error);
        } finally {
          this.isLoading = false;
        }
      }
    },
    
    async getClinicDoctor() {
      if (this.$store.state.role == 'secretary') {
        this.loadingData = true;
        
        try {
          const response = await axios.get("doctors/secretary", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          });
          
          this.doctors = response.data.data;
          this.fetchReservations();
        } catch (error) {
          console.error("Error fetching clinic doctors:", error);
        } finally {
          this.loadingData = false;
        }
      } else {
        this.fetchReservations();
      }
    },
    
    async getOwnerTctateitemsById() {
      try {
        const response = await axios.get("https://tctate.com/api/api/v2/items/owner/get?page=1", {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
          }
        });
        
        this.item_id = response.data.data[0].id;
        this.owner_item = response.data.data[0];
      } catch (error) {
        console.error("Error fetching owner items:", error);
      }
    },
    
    getPatient() {
      this.loadingData = true;
      
      axios.get("patients/getByUserIdv2", {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: "Bearer " + this.$store.state.AdminInfo.token
        }
      })
      .then(res => {
        this.patients = res.data.data;
      })
      .catch(error => {
        console.error("Error fetching patients:", error);
      })
      .finally(() => {
        this.loadingData = false;
      });
    },
    
    // Calendar event handling methods
    openReservationDialog(event) {
      this.book_details = {
        star_date: event.eventParsed.start.date,
        name: event.event.name,
        book_time: event.event.startTime,
        user_phone: event.event.phone,
        id: event.event.id,
        owner_name: event.event.owner_name || 'Unknown'
      };
      
      this.selectedEvent = event;
      this.dialog = true;
    },
    
    openBookingDialog(dateInfo) {
      const date = new Date(dateInfo.date);
      const formattedDate = date.toISOString().split('T')[0];
      this.appointmentData.reservation_start_date = formattedDate;
      this.bookingDialog = true;
    },
    
    // Appointment management methods
    generateAppointmentMessage() {
      if (this.appointmentData.reservation_from_time) {
        const fromTime = this.convertToArabicAmPm(this.appointmentData.reservation_from_time);
        let toTime = '';
        
        if (this.appointmentData.reservation_to_time) {
          toTime = ` حتى ${this.convertToArabicAmPm(this.appointmentData.reservation_to_time)}`;
        }
        
        this.appointmentData.appointmentMessage = 
          `يسرنا إبلاغكم بموعدكم القادم في عيادة ${this.clinicName} بتاريخ ${this.appointmentData.reservation_start_date} في الساعة ${fromTime}${toTime}. نتمنى لكم دوام الصحة والعافية ونتطلع لرؤيتكم قريبًا.`;
      }
    },
    
    addtime(minutes) {
      if (!this.appointmentData.reservation_from_time) return '';
      
      const [hours, mins] = this.appointmentData.reservation_from_time.split(':').map(Number);
      const date = new Date();
      date.setHours(hours);
      date.setMinutes(mins + (minutes || 0));
      
      return `${date.getHours()}:${date.getMinutes().toString().padStart(2, '0')}`;
    },
    
    chatWithPatient() {
      if (this.book_details.user_phone) {
        const phone = '+' + this.book_details.user_phone;
        window.open(`https://wa.me/${phone}`, "_blank");
      }
    },
    
    confirmDelete() {
      this.dialog = false;
      const userConfirmed = confirm(this.$t("confirm_delete_appointment"));
      
      if (!userConfirmed) return;

      this.deleteAppointment(this.book_details.id);
    },
    
    async deleteAppointment(id) {
      try {
        await fetch(`https://tctate.com/api/api/v2/reservation/delete/${id}`, {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
          },
        });
        
        this.fetchReservations();
        this.$swal.fire({
          position: "top-end",
          icon: "success",
          title: this.$t("delete_success"),
          showConfirmButton: false,
          timer: 1500
        });
      } catch (error) {
        console.error("Error deleting appointment:", error);
        this.$swal.fire({
          position: "top-end",
          icon: "error",
          title: this.$t("delete_error"),
          showConfirmButton: false,
          timer: 1500
        });
      }
    },
    
    // Calendar data fetching
    async fetchReservations() {
      try {
        const endDateISO = "2025-12-29";
        let allReservations = [];

        if (this.doctors.length > 1 && this.$store.state.role == 'secretary') {
          for (let doctor of this.doctors) {
            const response = await this.fetchDoctorReservations(doctor.user.tctate_token, endDateISO);
            const reservations = this.formatReservationsData(response.data.data, doctor.user.name);
            allReservations = allReservations.concat(reservations);
          }
        } else {
          const response = await this.fetchDoctorReservations(this.$store.state.AdminInfo.tctate_token, endDateISO);
          allReservations = this.formatReservationsData(response.data.data);
        }
        
        this.reservations = allReservations;
      } catch (error) {
        console.error('Error fetching reservations:', error);
      }
    },
    
    async fetchDoctorReservations(token, endDateISO) {
      return axios.get(
        `https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=2024-29-12_${endDateISO}&filter[status_id]=%20&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`,
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + token
          }
        }
      );
    },
    
    formatReservationsData(data, doctorName) {
      return data.map(reservation => ({
        name: reservation.user.full_name,
        details: '',
        phone: reservation.user.user_phone,
        id: reservation.id,
        owner_name: reservation.owner_name || doctorName || 'Unknown',
        start: `${reservation.reservation_start_date} ${reservation.reservation_from_time}`,
        startTime: reservation.reservation_from_time,
        color: ''
      }));
    },
    
    getEventColor(event) {
      const currentDateTime = new Date();
      const eventDateTime = new Date(event.start);
      
      return eventDateTime < currentDateTime ? "green" : "#FFA500";
    },
    
    // Form management
    async save() {
      if (!this.$refs.form.validate()) return;
      
      this.loadSave = true;
      
      try {
        const reservationData = this.prepareReservationData();
        await this.submitReservation(reservationData);
        
        if (this.send_msg && this.patient) {
          await this.sendWhatsAppMessage();
        }
        
        this.handleSuccessfulReservation(reservationData);
      } catch (error) {
        console.error('Error saving reservation:', error);
        this.handleReservationError();
      } finally {
        this.loadSave = false;
      }
    },
    
    prepareReservationData() {
      const fromTime = this.appointmentData.reservation_from_time;
      const toTime = this.hasReservingPeriod ? 
        this.addtime(this.owner_item.possib_reserving_period) : 
        this.appointmentData.reservation_to_time;
      
      const reservationStartDate = this.appointmentData.reservation_start_date;
      
      const data = {
        reservation_start_date: reservationStartDate,
        reservation_end_date: reservationStartDate,
        reservation_from_time: fromTime,
        reservation_to_time: toTime,
        item_id: this.item_id,
        reservation_number: 1,
        appointmentMessage: this.appointmentData.appointmentMessage,
        reservation_date: reservationStartDate,
        ReservationRequirements: [],
        item_features: [],
        phone: '',
        withoutBills: 0,
        deliverable: false,
        user: this.formatPatientData()
      };
      
      return data;
    },
    
    formatPatientData() {
      if (this.patient && this.patient.phone) {
        return {
          phone: "964" + parseInt(this.patient.phone.replace(/ /g, "")),
          name: this.patient.name
        };
      }
      
      return {
        phone: "9647700281899",
        name: this.patient ? this.patient.name : ""
      };
    },
    
    async submitReservation(reservationData) {
      const token = this.getSubmissionToken();
      
      await axios.post('https://tctate.com/api/api/reservation/owner/setv2', 
        reservationData, 
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + token,
          },
        }
      );
    },
    
    getSubmissionToken() {
      if (this.doctors.length > 1 && this.patient && this.patient.doctors && this.patient.doctors.length) {
        return this.patient.doctors[0].user.tctate_token;
      }
      return this.$store.state.AdminInfo.tctate_token;
    },
    
    async sendWhatsAppMessage() {
      await axios.post('/whatsapp',
        {
          patient_id: this.patient ? this.patient.id : null,
          message: this.appointmentData.appointmentMessage
        },
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token,
          },
        }
      );
    },
    
    handleSuccessfulReservation(reservationData) {
      // Add to local reservations for immediate display
      this.reservations.push({
        name: reservationData.user.name,
        details: 'New Reservation',
        start: `${reservationData.reservation_start_date} ${reservationData.reservation_from_time}`,
        startTime: reservationData.reservation_from_time,
        color: "green",
      });
      
      this.close();
      this.$swal.fire({
        position: "top-end",
        icon: "success",
        title: this.$t("booking_success"),
        showConfirmButton: false,
        timer: 1500
      });
      
      // Refresh reservations from the server
      this.fetchReservations();
    },
    
    handleReservationError() {
      this.$swal.fire({
        position: "top-end",
        icon: "error",
        title: this.$t("booking_error"),
        showConfirmButton: false,
        timer: 1500
      });
    },
    
    close() {
      this.bookingDialog = false;
      this.send_msg = false;
      this.resetForm();
    },
    
    resetForm() {
      this.patient = null;
      this.appointmentData = {
        reservation_from_time: null,
        reservation_to_time: null,
        reservation_start_date: null,
        appointmentMessage: ""
      };
    }
  }
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

.highlight-day {
  background-color: #ffeb3b;
  border-radius: 50%;
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
  color: #1976d2;
}

.v-btn {
  margin-right: 8px;
}
</style>