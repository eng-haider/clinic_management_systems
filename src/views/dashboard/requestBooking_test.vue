<template>
  <v-container fluid>
    <v-row>
      
      <v-col>
        <!-- <v-calendar ref="calendar" v-model="focus" color="primary" :events="reservations" type="month"
          :event-color="getEventColor"
          
          @click:date="openBookingDialog"      
          
          @click:event="openReservationDialog"
          
          
          > -->


        <v-calendar ref="calendar" v-model="focus"    :start="startDate"
          :end="endDate"   :max="endDate" color="primary" :events="reservations" type="month"
          :event-color="getEventColor" @click:date="openBookingDialog" @click:event="openReservationDialog">
          <!-- This will show only the days of the current month -->
          <template v-slot:day="{ date, outside, events }" >
            <div v-if="!outside" class="calendar-day">
              <!-- !outside ensures the day is from the current month -->
              <div class="day-number"></div>
              <v-chip v-for="event in events" :key="event.start" :color="getEventColor(event)" class="ma-1" label>
                <strong>{{ event }}</strong>
              </v-chip>
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
          <v-toolbar-title>تفاصيل الموعد </v-toolbar-title>
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
                <!-- <h3 class="font-weight-bold mb-3">Event Info</h3> -->
                <v-list dense>

                  <v-list-item>

                    <v-list-item-content>
                      <v-list-item-title><strong> الاســم:</strong> {{ book_details.name}}</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>


                  <v-list-item>

<v-list-item-content>
  <v-list-item-title><strong> رقم الهاتف:</strong> {{ book_details.user_phone}}</v-list-item-title>
</v-list-item-content>
</v-list-item>


                  <v-list-item>
                    <v-list-item-content>
                      <v-list-item-title><strong>الموعد:</strong>   {{ book_details.book_time  }} | {{ book_details.star_date   }} </v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-content>
                      <!-- <v-list-item-title><strong>Start Date:</strong> {{ formatDate(selectedEvent.start) }}</v-list-item-title> -->
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-btn color="success" dark @click="chatWithPatient">
            <v-icon left>mdi-whatsapp</v-icon> مــحادثة وتســاب
          </v-btn>
          <v-btn color="error" dark @click="confirmDelete">
            <v-icon left>mdi-delete</v-icon> حـــذف المـــوعد
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="dialog = false">اغــلاق</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

 

    <!-- Booking Dialog -->
    <v-dialog v-model="BookingDetails" width="700" height="700" persistent>
      <v-form ref="form" v-model="valid">
        <v-card height="auto">
          <v-toolbar dark color="primary lighten-1 mb-5">
            <v-toolbar-title>
              <h3 style="color:#fff;font-family: 'Cairo'">حجز موعد</h3>
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
                  <v-autocomplete :items="patients" outlined required item-text="name" :rules="[rules.required]" return-object v-model="patient"
                    item-value="name" label="اختار المراجع"></v-autocomplete>
                </v-col>
              </v-row>
            </div>
            <v-row justify="center">
              <v-col cols="12" sm="6" xs="12">
                <v-menu ref="menu1" v-model="menu2" :close-on-content-click="false" :nudge-right="40"
                  :return-value.sync="editedItem.reservation_from_time" transition="scale-transition" offset-y
                  max-width="290px" min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field :rules="[rules.required]" v-model="editedItem.reservation_from_time" label="من الساعه"
                      prepend-icon="mdi-clock-time-four-outline" readonly required v-bind="attrs" v-on="on">
                    </v-text-field>
                  </template>
                  <v-time-picker v-if="menu2" v-model="editedItem.reservation_from_time" full-width format="ampm"
                    @click:minute="$refs.menu1.save(editedItem.reservation_from_time)"></v-time-picker>
                </v-menu>

                <v-menu ref="menu" :close-on-content-click="false" v-model="menu3" :nudge-right="40"
                  :return-value.sync="editedItem.reservation_to_time" transition="scale-transition" offset-y
                  max-width="290px" min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field v-model="editedItem.reservation_to_time" label="الى الساعه" :rules="[rules.required]"
                      prepend-icon="mdi-clock-time-four-outline" readonly v-bind="attrs" v-on="on"></v-text-field>
                  </template>
                  <v-time-picker v-if="menu3" v-model="editedItem.reservation_to_time" full-width
                    @click:minute="$refs.menu.save(editedItem.reservation_to_time)"></v-time-picker>
                </v-menu>

                <div v-if="$store.state.AdminInfo.send_msg ==1">
                  <v-checkbox :disabled="!editedItem.reservation_from_time || !editedItem.reservation_to_time"
                    v-model="send_msg" style="    text-align: right;"
                    label="ارســـال رسالة تذكير بلموعد للمراجع على الوتساب" />

                  <v-textarea v-if="send_msg" v-model="editedItem.appointmentMessage" label="Message" outlined />

                </div>



              </v-col>






            </v-row>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}</v-btn>
              <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>
                حجز
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
  export default {
    data() {
      return {
        dialogInfo: false,
        BookingDetails: false,
        selectedEvent: {},
        confirmDialog: false,
        dialog: false,
        book_details: {},
        send_msg: false,
        focus: "2024-12-01", // Focus on December by default
        startDate: "2024-11-30", // Start of December
      endDate: "2024-12-31", // End of December,
    
        valid: true,
        
        patient: null,
        editedItem: {
          reservation_from_time: null,
          reservation_to_time: null,
          appointmentMessage: "",
        },
        menu2: false,
        menu3: false,
        patients: [],
        loadSave: false,
        item_id: '',
        patientFound: false,
        reservations: [],
        loadingData: false,

        rules: {
  required: value => !!value || "هذا الحقل مطلوب",
  phoneNumber: value => /^\d+$/.test(value) || 'Enter a valid phone number',
},


       
      };
    },
    mounted() {
      this.fetchReservations();
      this.getPatient(); // Fetch patients for autocomplete
      this.getOwnerTctateitemsById();
    },

    computed: {
      clinicName() {
        return this.$store.state.AdminInfo.clinics_info?.name || '';
      },
    },
    watch: {
      'editedItem.reservation_from_time'(newValue) {
        this.send_msg(newValue);
      },
      'editedItem.reservation_to_time'(newValue) {
        this.send_msg(newValue);
      },

      send_msg(newValue) {
        this.handleSendMsg(newValue);
      }


    },
    methods: {
     

      showDeleteConfirmation() {
      this.confirmDialog = true;
    },
    async confirmDelete() {
      this.dialog=false;
      const userConfirmed = confirm("Are you sure you want to delete this event? This action cannot be undone.");
      if (!userConfirmed) {
        return; // If user cancels, exit the function
      }

      try {
        const response = await fetch(`https://tctate.com/api/api/v2/reservation/delete/${this.book_details.id}`, {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json",
           Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
          },
        });

        if (!response.ok) {
          this.fetchReservations();
          throw new Error("Failed to delete the event.");
        }

        const data = await response.json();
data
this.fetchReservations();

this.$swal.fire({
                    position: "top-end",
  icon: "success",
  title: "تم الحجز بنجاح",
  showConfirmButton: false,
  timer: 1500
              });
    
        


        this.dialog = false;
      } catch (error) {
        console.error("Error deleting event:", error);
        alert("Failed to delete the event. Please try again.");
      }
    },
    chatWithPatient() {
   
      const phone = '+'+this.book_details.user_phone;
      window.open(`https://wa.me/${phone}`, "_blank");
    },
      openReservationDialog(event) {
console.log(event)

//  alert(event.event.startTime)
        this.book_details.star_date = event.eventParsed.start.date;
        this.book_details.name = event.event.name;
        this.book_details.book_time = event.event.startTime;
        this.book_details.user_phone = event.event.phone;
        this.book_details.id = event.event.id;
        this.selectedEvent = event; // Set the selected reservation
        this.dialog = true;

      },
      handleSendMsg(newValue) { // Rename the method to avoid conflict
        if (newValue && this.editedItem.reservation_from_time && this.editedItem.reservation_to_time) {
          this.editedItem.appointmentMessage =
            `يسرنا إبلاغكم بموعدكم القادم في عيادة ${this.clinicName} بتاريخ ${this.editedItem.reservation_start_date} في الساعة ${this.convertToArabicAmPm(this.editedItem.reservation_from_time)} حتى ${this.convertToArabicAmPm(this.editedItem.reservation_to_time)}. نتمنى لكم دوام الصحة والعافية ونتطلع لرؤيتكم قريبًا.`;
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
          this.item_id = response.data.data[0].id;
        });
      },
      getPatient() {
        this.loadingData = true; // Show loading indicator
        axios.get("patients/getByUserId", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            }
          })
          .then(res => {
            this.loadingData = false;
            this.patients = res.data.data; // Store fetched patients
          })
          .catch(() => {
            this.loadingData = false;
          });
      },
      async save() {


      if (this.$refs.form.validate()) {
        this.loadSave = true; // Show loading indicator

        try {
          const fromTime = this.editedItem.reservation_from_time;
          const toTime = this.editedItem.reservation_to_time;





          // The reservation start date is the date clicked by the user
          const reservationStartDate = this.editedItem.reservation_start_date;

          const reservationData = {
            reservation_start_date: reservationStartDate,
            reservation_end_date: reservationStartDate, // Use the same date if it's a one-day reservation
            reservation_from_time: fromTime,
            reservation_to_time: toTime,
            item_id: this.item_id,
            reservation_number: 1,
            appointmentMessage: this.editedItem.appointmentMessage,

            reservation_date: reservationStartDate,
            ReservationRequirements: [],
            item_features: [],
            phone: '',
            withoutBills: 0,




            deliverable: false,
          };


          if (this.patientFound) {
            reservationData.user = {
              phone: "964" + parseInt(this.patientInfo.phone.replace(/ /g, "")),
              name: this.patientInfo.name,
            };
          } else {
            reservationData.user = {
              phone: "964" + parseInt(this.patient.phone.replace(/ /g, "")),
              name: this.patient.name,
            };
          }


          // Make the API call...
          const response = await axios.post('https://tctate.com/api/api/reservation/owner/setv2', reservationData, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token,
            },
          });
          response
          // Add the new reservation to the local list of reservations
          this.reservations.push({
            name: reservationData.user.name,
            details: 'New Reservation',
            start: `${reservationStartDate} ${fromTime}`,
            startTime: fromTime,
            color: "green",
          });
          this.close();
          this.BookingDetails = false;
          this.$swal.fire({
                    position: "top-end",
  icon: "success",
  title: "تم الحجز بنجاح",
  showConfirmButton: false,
  timer: 1500
              });
          this.resetForm();
this.fetchReservations();


        } catch (error) {


          const fromTime = this.editedItem.reservation_from_time;
          const toTime = this.editedItem.reservation_to_time;





          // The reservation start date is the date clicked by the user
          const reservationStartDate = this.editedItem.reservation_start_date;

          const reservationData = {
            reservation_start_date: reservationStartDate,
            reservation_end_date: reservationStartDate, // Use the same date if it's a one-day reservation
            reservation_from_time: fromTime,
            reservation_to_time: toTime,
            item_id: this.item_id,
            reservation_number: 1,
            appointmentMessage: this.editedItem.appointmentMessage,

            reservation_date: reservationStartDate,
            ReservationRequirements: [],
            item_features: [],
            phone: '',
            withoutBills: 0,




            deliverable: false,
          };


          if (this.patientFound) {
            reservationData.user = {
              phone: "964" + parseInt(this.patientInfo.phone.replace(/ /g, "")),
              name: this.patientInfo.name,
            };
          } else {
            reservationData.user = {
              phone: "964" + parseInt(this.patient.phone.replace(/ /g, "")),
              name: this.patient.name,
            };
          }
          this.reservations.push({
            name: reservationData.user.name,
            details: 'New Reservation',
            start: `${reservationStartDate} ${fromTime}`,
            startTime: fromTime,
            color: "green",
          });
          this.close();
          this.BookingDetails = false;
          this.$swal.fire({
                    position: "top-end",
  icon: "success",
  title: "تم الحجز بنجاح",
  showConfirmButton: false,
  timer: 1500
              });
          this.resetForm();
          // this.BookingDetails = false;
          // console.error('An error occurred:', error);
        } finally {
          this.loadSave = false;
        }

      }
      else{
        // alert('s')
      }
      },
      async fetchReservations() {
  try {
 // Get the current date
const currentDate = new Date();

// Set the start date for December (first day of December)
const startDate = new Date(currentDate.getFullYear(), 11, 1); // December is month 11 (zero-indexed)

// Set the end date for December (last day of December)
const endDate = new Date(currentDate.getFullYear(), 11, 31); // December 31st

// Convert to ISO format (YYYY-MM-DD)
const startDateISO = startDate.toISOString().split('T')[0];
const endDateISO = endDate.toISOString().split('T')[0];

// Fetch reservations for December
const response = await axios.get(
  `https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=${startDateISO}_${endDateISO}&filter[status_id]=%20&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`,
  {
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token,
    }
  }
);

// Now `response` will contain the reservations for December


    // Map response data to reservation objects
    this.reservations = response.data.data.map(reservation => ({
      name: reservation.user.full_name,
      details: reservation.item.item_name,
      phone: reservation.user.user_phone,
      id: reservation.id,
      start: `${reservation.reservation_start_date} ${reservation.reservation_from_time}`,
      startTime: reservation.reservation_from_time,
      color: reservation.status ? reservation.status.status_color : 'lightblue',
    }));
  } catch (error) {
    console.error('Error fetching reservations:', error);
  }
}
,

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
        };
      },
      getEventColor(event) {
        return event.color || "lightblue";
      }
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


  .highlight-day {
  background-color: #ffeb3b;
  border-radius: 50%;
}

</style>