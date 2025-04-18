<template>
  <v-container fluid>
    <v-row>
  <v-col>
    <v-calendar ref="calendar" v-model="focus" :start="startDate" :end="endDate" :max="endDate" 
      :events="reservations" type="month" :event-color="getEventColor" @click:date="openBookingDialog"
      @click:event="openReservationDialog">
      <!-- This will show only the days of the current month -->
      <template v-slot:day="{ date, outside, events }">
        <div v-if="!outside" class="calendar-day">
          <!-- !outside ensures the day is from the current month -->
          <div class="day-number"></div>
          <v-chip v-for="event in events" :key="event.start" :color="getEventColor(event)" class="ma-1" label>
            <strong>{{ event.name }}</strong>
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
                    <strong>{{ $t("datatable.doctor") }}:</strong> {{ book_details.owner_name}}
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

              <!-- <v-col class="py-0" cols="12" sm="12" md="12" v-if="$store.state.role=='secretary'  && doctors.length>1">
                <v-select :rules="[rules.required]" v-model="editedItem.doctors" :label="$t('doctor')" return-object
                  :items="doctors" outlined item-text="name" item-value="id">
                </v-select>

              </v-col> -->

              <v-col cols="12" sm="6" xs="12" v-if="owner_item.possib_reserving_period ==null">
                <v-menu ref="menu1" v-model="menu2" :close-on-content-click="false" :nudge-right="40"
                  :return-value.sync="editedItem.reservation_from_time" transition="scale-transition" offset-y
                  max-width="290px" min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field :rules="[rules.required]" v-model="editedItem.reservation_from_time"
                      :label="$t('from_time')" prepend-icon="mdi-clock-time-four-outline" readonly required
                      v-bind="attrs" v-on="on">
                    </v-text-field>
                  </template>
                  <v-time-picker v-if="menu2" v-model="editedItem.reservation_from_time" full-width format="ampm"
                    @click:minute="$refs.menu1.save(editedItem.reservation_from_time)"></v-time-picker>
                </v-menu>

                <v-menu ref="menu" :close-on-content-click="false" v-model="menu3" :nudge-right="40"
                  :return-value.sync="editedItem.reservation_to_time" transition="scale-transition" offset-y
                  max-width="290px" min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field v-model="editedItem.reservation_to_time" :label="$t('to_time')"
                      :rules="[rules.required]" prepend-icon="mdi-clock-time-four-outline" readonly v-bind="attrs"
                      v-on="on"></v-text-field>
                  </template>
                  <v-time-picker v-if="menu3" v-model="editedItem.reservation_to_time" full-width
                    @click:minute="$refs.menu.save(editedItem.reservation_to_time)"></v-time-picker>
                </v-menu>

                <div v-if="$store.state.AdminInfo.send_msg ==1">
                  <v-checkbox :disabled="!editedItem.reservation_from_time || !editedItem.reservation_to_time"
                    v-model="send_msg" style="    text-align: right;" :label="$t('send_reminder_message')" />

                  <v-textarea v-if="send_msg" v-model="editedItem.appointmentMessage" :label="$t('message')" outlined />

                </div>



              </v-col>

              <v-col cols="12" sm="6" xs="12" v-else>
                <v-time-picker v-model="editedItem.reservation_from_time" full-width format="ampm"></v-time-picker>


                <div v-if="$store.state.AdminInfo.send_msg ==1">
                  <v-checkbox :disabled="!editedItem.reservation_from_time" v-model="send_msg"
                    style="    text-align: right;" :label="$t('send_reminder_message')" />

                  <v-textarea v-if="send_msg" v-model="editedItem.appointmentMessage" :label="$t('message')" outlined />

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
  import Multiselect from 'vue-multiselect'
  export default {
    components: {
      Multiselect
    },
    data() {
      return {
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
        focus: "2025-04-01", // Focus on December by default
        startDate: "2025-04-01", // Start of December
        endDate: "2025-05-30", // End of December,

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
          required: value => !!value || this.$t("field_required"),
          phoneNumber: value => /^\d+$/.test(value) || this.$t('enter_valid_phone_number'),
        },



      };
    },
    mounted() {
      // this.fetchReservations(); 
      this.getclinicDoctor();
      this.getPatient(); // Fetch patients for autocomplete
      this.getOwnerTctateitemsById();
     // Ensure reservations are loaded when the page opens
    },

    computed: {
      clinicName() {
        return this.$store.state.AdminInfo.clinics_info?.name || '';
      },
    },
   
    methods: {
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

          axios.get("patients/searchv2/" + query, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {

              this.isLoading = false;
              this.patients = res.data.data; // Search results

            })
            .catch(() => {
              this.loading = false;
            });



        }
      },
      async getclinicDoctor() {
        if (this.$store.state.role == 'secretary') {
          this.loading = true;

          await axios.get("doctors/secretary", {
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
              this.fetchReservations();



            })
            .catch(() => {
              this.loading = false;
            });

        } else {
          this.fetchReservations();
        }

      },

      showDeleteConfirmation() {
        this.confirmDialog = true;
      },
      async confirmDelete() {
        this.dialog = false;
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

        const phone = '+' + this.book_details.user_phone;
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
        this.book_details.owner_name = event.event.owner_name;

        

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
          this.owner_item = response.data.data[0];
        });
      },
      getPatient() {
        this.loadingData = true; // Show loading indicator
        axios.get("patients/getByUserIdv2", {
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




          if (this.doctors.length > 1) {

            this.tokx = this.patient.doctors[0].user.tctate_token;
          } else {
            this.tokx = this.$store.state.AdminInfo.tctate_token;
          }

          try {
            this.addtime();

            const fromTime = this.editedItem.reservation_from_time;
            const toTime = this.owner_item.possib_reserving_period == null ? this.editedItem.reservation_to_time :
              this.addtime(this.owner_item.possib_reserving_period);


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


            if (this.patient && this.patient.phone) {
              reservationData.user = {
                phone: "964" + parseInt(this.patient.phone.replace(/ /g, "")),
                name: this.patient.name
              };
            } else {
              reservationData.user = {
                phone: "9647700281899",
                name: this.patient ? this.patient.name : ""
              };
            }


            // Make the API call...
            const response = await axios.post('https://tctate.com/api/api/reservation/owner/setv2', reservationData, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.tokx,
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
            console.log(this.patient)

            if (this.patient && this.patient.phone) {
              reservationData.user = {
                phone: "964" + parseInt(this.patient.phone.replace(/ /g, "")),
                name: this.patient.name
              };
            } else {
              reservationData.user = {
                phone: "",
                name: this.patient ? this.patient.name : ""
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

        } else {
          // alert('s')
        }
      },
      async fetchReservations() {
        try {



          // Get the current date
          const currentDate = new Date();
          currentDate;
          // Set the start date for December (first day of December)
          const startDate = new Date(2024, 31, 12); // December is month 11 (zero-indexed)

          // Set the end date for December (last day of December)
          const endDate = new Date(2025, 12, 29); // December 31st

          // Convert to ISO format (YYYY-MM-DD)
          const startDateISO = startDate.toISOString().split('T')[0];
          const endDateISO = endDate.toISOString().split('T')[0];
          startDateISO;
          let allReservations = [];

          if (this.doctors.length > 1 && this.$store.state.role == 'secretary') {
            for (let doctor of this.doctors) {

              console.log(doctor)
              const response = await axios.get(
                `https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=2024-29-12_${endDateISO}&filter[status_id]=%20&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`, {
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: "Bearer " + doctor.user.tctate_token,
                  }
                }
              );

              const reservations = response.data.data.map(reservation => ({
                name: reservation.user.full_name,
                details: '',
                phone: reservation.user.user_phone,
                owner_name: reservation.owner_name,
                id: reservation.id,
                start: `${reservation.reservation_start_date} ${reservation.reservation_from_time}`,
                startTime: reservation.reservation_from_time,
                color: ''
              }));

              allReservations = allReservations.concat(reservations);
              this.reservations = allReservations;
            }
          } else {
            const response = await axios.get(
              `https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=2024-29-12_${endDateISO}&filter[status_id]=%20&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`, {
                headers: {
                  "Content-Type": "application/json",
                  Accept: "application/json",
                  Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token,
                }
              }
            );

            allReservations = response.data.data.map(reservation => ({
              name: reservation.user.full_name,
              details: '',
              phone: reservation.user.user_phone,
              id: reservation.id,
              start: `${reservation.reservation_start_date} ${reservation.reservation_from_time}`,
              startTime: reservation.reservation_from_time,
              color: '',
            }));

            this.reservations = allReservations;
          }
        } catch (error) {
          console.error('Error fetching reservations:', error);
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
        };
      },
      getEventColor(event) {
  const currentDateTime = new Date(); // Get the current date and time
  const eventDateTime = new Date(event.start); // Parse the event's start date and time

  // Check if the event is in the past
  if (eventDateTime < currentDateTime) {
    return "green"; // Event is in the past
  }

  return "#FFA500"; // Default to event color or lightblue
},
      searchPatients(query) {
        console.log(query)
        if (query.length < 3) {
          return;
        }
        this.loadingData = true;
        axios.get(`patients/search?query=${query}`, {
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