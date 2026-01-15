<template>
  <div>
    <v-dialog v-model="BookingDetails" width="700" max-width="95vw" persistent>
      <v-form ref="form" v-model="valid">
        <v-card>
          <v-toolbar dark color="primary lighten-1" class="mb-5">
            <v-toolbar-title>
              <h3 class="white--text" style="font-family: 'Cairo'">حجز موعد</h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="close" icon>
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>
          <v-container>
            <v-row justify="center" v-if="!patientFound">
              <v-col>
                <v-autocomplete 
                  :items="patients" 
                  item-text="name" 
                  return-object 
                  v-model="patient" 
                  outlined 
                  label="اختار المراجع"
                />
              </v-col>
            </v-row>
            
            <!-- Doctor selector for secretary/accounter - Better UI -->
            <v-row justify="center" v-if="($store.state.role=='secretary'|| $store.state.role=='accounter') && doctors && doctors.length>0">
              <v-col cols="12">
                <v-select 
                  v-model="editedItem.doctors" 
                  :items="doctors" 
                  :label="$t('doctor') || 'الطبيب'"
                  :rules="[rules.required]"
                  item-text="name" 
                  item-value="id"
                  return-object
                  outlined
                  dense
                  prepend-inner-icon="mdi-doctor"
                  clearable
                  hint="اختر الطبيب للموعد"
                  persistent-hint
                  class="mb-2"
                >
                  <template v-slot:selection="{ item }">
                    <v-chip small color="primary" text-color="white">
                      <v-icon small left>mdi-doctor</v-icon>
                      {{ item.name }}
                    </v-chip>
                  </template>
                  <template v-slot:item="{ item }">
                    <v-list-item-avatar color="primary" size="32">
                      <v-icon dark small>mdi-doctor</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title>{{ item.name }}</v-list-item-title>
                    </v-list-item-content>
                  </template>
                </v-select>
              </v-col>
            </v-row>

            <v-row justify="center">
              <v-col cols="12" sm="6">
                <v-date-picker 
                  v-model="editedItem.reservation_start_date" 
                  locale="ar-iq"
                  full-width
                  color="primary"
                />
              </v-col>


             


              <v-col cols="12" sm="6" xs="12"   v-if="owner_item.possib_reserving_period !==null">
                <v-time-picker 
                  v-model="editedItem.reservation_from_time" 
                  full-width 
                  format="ampm"
                  color="primary"
                ></v-time-picker>

              </v-col>

              <v-col cols="12" sm="6"  v-else>
                <v-menu 
                  ref="menu1" 
                  v-model="menu2" 
                  :close-on-content-click="false" 
                  max-width="290px" 
                  min-width="290px"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field 
                      v-model="editedItem.reservation_from_time" 
                      label="من الساعه" 
                      prepend-icon="mdi-clock-time-four-outline" 
                      readonly 
                      :rules="[rules.required]"
                      required 
                      v-bind="attrs" 
                      v-on="on"
                    />
                  </template>
                  <v-time-picker 
                    v-if="menu2" 
                    v-model="editedItem.reservation_from_time" 
                    full-width 
                    @click:minute="$refs.menu1.save(editedItem.reservation_from_time)" 
                  />
                </v-menu>

                <v-menu 
                  ref="menu2" 
                  v-model="menu3" 
                  :close-on-content-click="false" 
                  max-width="290px" 
                  min-width="290px"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field 
                      v-model="editedItem.reservation_to_time" 
                      label="الى الساعه" 
                      prepend-icon="mdi-clock-time-four-outline" 
                      readonly 
                      :rules="[rules.required]"
                      required 
                      v-bind="attrs" 
                      v-on="on"
                    />
                  </template>
                  <v-time-picker 
                    v-if="menu3" 
                    v-model="editedItem.reservation_to_time" 
                    full-width 
                    @click:minute="$refs.menu2.save(editedItem.reservation_to_time)" 
                  />
                </v-menu>
                <div v-if="$store.state.AdminInfo.send_msg ==1">
                <v-checkbox 
                  v-model="send_msg" 
                      :disabled="!editedItem.reservation_from_time || !editedItem.reservation_to_time"
                  style="    text-align: right;"
                  label="ارســـال رسالة تذكير بلموعد للمراجع على الوتساب"
                />

                <v-textarea 
                  v-if="send_msg" 
                  v-model="editedItem.appointmentMessage" 
                  label="Message" 
                  outlined
                />
                </div>
              </v-col>

        
            </v-row>

            <!-- Appointment Type Selection: Examination or Other -->
            <v-row justify="center">
              <v-col cols="12">
                <v-card outlined class="mt-3 pa-3" color="blue lighten-5">
                  <v-radio-group 
                    v-model="editedItem.appointment_type" 
                    row
                    class="mt-0"
                  >
                    <template v-slot:label>
                      <div class="d-flex align-center mb-2">
                        <v-icon color="primary" class="ml-2">mdi-clipboard-text</v-icon>
                        <span style="font-weight: 500; color: #1976d2;">نوع الموعد</span>
                      </div>
                    </template>
                    <v-radio 
                      label="فحص" 
                      value="examination"
                      color="primary"
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon color="primary" small class="ml-1">mdi-stethoscope</v-icon>
                          <span>فحص</span>
                        </div>
                      </template>
                    </v-radio>
                    <v-radio 
                      label="أخرى" 
                      value="other"
                      color="primary"
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon color="primary" small class="ml-1">mdi-text-box</v-icon>
                          <span>أخرى</span>
                        </div>
                      </template>
                    </v-radio>
                  </v-radio-group>

                  <!-- Show textarea when "Other" is selected -->
                  <v-expand-transition>
                    <v-textarea
                      v-if="editedItem.appointment_type === 'other'"
                      v-model="editedItem.notes"
                      label="الملاحظات"
                      placeholder="اكتب سبب الموعد أو ملاحظات إضافية..."
                      outlined
                      dense
                      rows="3"
                      counter
                      class="mt-2"
                    ></v-textarea>
                  </v-expand-transition>
                </v-card>
              </v-col>
            </v-row>

            <br>
            <br>
            <v-card-actions>
              <v-spacer />
              <v-btn color="red darken-1" text @click="close">اغلاق</v-btn>
              <v-btn :loading="loadSave" color="blue darken-1" text @click="save">حجز</v-btn>
            </v-card-actions>
          </v-container>
        </v-card>
      </v-form>
    </v-dialog>
  </div>
</template>

<script>
import { EventBus } from '../event-bus.js';
import axios from 'axios';

export default {
  props: {
    patientInfo: Object,
    patientFound: Boolean,
    start_date: String,
    patients: Array,
    doctors:Array
  },
  data() {
    return {
      rules: {
        minPhon: (v) => v.length == 13 || "رقم الهاتف يجب ان يتكون من 11 رقم",
        required: value => !!value || "مطلوب",
        min: (v) => v.length >= 6 || "كلمة المرور يجب ان تتكون من 6 عناصر او اكثر",
        email: value => {
          if (value.length > 0) {
            const pattern =
              /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return pattern.test(value) || 'يجب ان يكون ايميل صحيح';
          }
        },
      },
      BookingDetails: true,
      valid: false,
      menu2: false,
      menu3: false,
      owner_item:'',
      send_msg: false,
      editedItem: {
        reservation_start_date: this.getCurrentDate(),
        reservation_from_time: "",
        reservation_to_time: "",
        appointmentMessage: "",
        doctors: null,
        appointment_type: "examination", // Default to examination
        notes: "",
      },
      patient: {},
      loadSave: false,
    };
  },
  computed: {
    clinicName() {
      return this.$store.state.AdminInfo.clinics_info?.name || '';
    },
  },
  watch: {
    send_msg(newValue) {
    if (newValue) {
      this.updateAppointmentMessage();
    } else {
      this.editedItem.appointmentMessage = '';
    }
  },
  'editedItem.reservation_start_date'(newValue) {
    newValue
    if (this.send_msg) {
      this.updateAppointmentMessage();
    }
  },
  'editedItem.reservation_from_time'(newValue) {
    newValue
    if (this.send_msg) {
      this.updateAppointmentMessage();
    }
  },
  'editedItem.reservation_to_time'(newValue) {
    newValue
    if (this.send_msg) {
      this.updateAppointmentMessage();
    }
  },
  },
  mounted() {
    
    this.getOwnerTctateitemsById();
  },
  methods: {
    addtime(item){
        let time = this.editedItem.reservation_from_time; // Example: "12:30"
let [hours, minutes] = time.split(":").map(Number);

let date = new Date();
date.setHours(hours);
date.setMinutes(minutes + item); // Add 60 minutes

return `${date.getHours()}:${date.getMinutes().toString().padStart(2, "0")}`;
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

    updateAppointmentMessage() {
    const fromTime = this.convertToArabicAmPm(this.editedItem.reservation_from_time);
    const toTime = this.convertToArabicAmPm(this.editedItem.reservation_to_time);
    this.editedItem.appointmentMessage = `يسرنا إبلاغكم بموعدكم القادم في عيادة ${this.clinicName} بتاريخ ${this.editedItem.reservation_start_date} من الساعة ${fromTime} إلى الساعة ${toTime}. نتمنى لكم دوام الصحة والعافية ونتطلع لرؤيتكم قريبًا.`;
  },
    getCurrentDate() {
      return new Date().toISOString().substr(0, 10);
    },
    convertToArabicAmPm(time) {
      const [hours, minutes] = time.split(':').map(Number);
      let period = hours >= 12 ? 'مساءاً' :'صباحاً';
      let adjustedHours = hours % 12 || 12;
      return `${adjustedHours}:${minutes.toString().padStart(2, '0')} ${period}`;
    },
    close() {
      this.send_msg=false;
      this.BookingDetails = false;
      EventBus.$emit('GetResCancel', true);
    },

  

    save() {
      if (this.$refs.form.validate()) {
        this.loadSave = true;
        
        // Use the exact API body structure that works
        const reservationData = {
          patient_id: this.patientFound && this.patientInfo ? this.patientInfo.id : (this.patient ? this.patient.id : null),
          reservation_start_date: this.editedItem.reservation_start_date,
          reservation_end_date: this.editedItem.reservation_start_date,
          reservation_from_time: this.editedItem.reservation_from_time,
          reservation_to_time: this.owner_item.possib_reserving_period == null ? this.editedItem.reservation_to_time : this.addtime(this.owner_item.possib_reserving_period),
          item_id: this.item_id,
          reservation_number: 1,
          appointmentMessage: this.editedItem.appointmentMessage || "",
          reservation_date: this.editedItem.reservation_start_date,
          ReservationRequirements: [],
          item_features: [],
          phone: "",
          withoutBills: 0,
          deliverable: false,
          is_examine: this.editedItem.appointment_type === 'examination' ? 1 : 0,
          notes: this.editedItem.appointment_type === 'other' ? this.editedItem.notes : null,
          // doctor_id: prefer selected doctor in dialog (editedItem.doctors), otherwise fall back to patientInfo
          doctor_id: this.editedItem && this.editedItem.doctors ? (this.editedItem.doctors.id || this.editedItem.doctors) : (this.patientInfo && this.patientInfo.doctors ? this.patientInfo.doctors.id : null),
          user: {
            phone: `964${this.patientFound && this.patientInfo.phone ? this.patientInfo.phone.replace(/ /g, "") : (this.patient.phone ? this.patient.phone.replace(/ /g, "") : "")}`,
            name: this.patientFound && this.patientInfo ? this.patientInfo.name : (this.patient ? this.patient.name : ""),
          }
        };

        // Use axios instead of this.$http to match requestBooking_test.vue
        axios.post("https://mina-api.tctate.com/api/reservations", reservationData, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token,
          },
        }).then(() => {
          this.BookingDetails = false;
          this.close();
          EventBus.$emit('GetResCancel', true);
        
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم الحجز بنجاح",
            showConfirmButton: false,
            timer: 1500
          });

          // Send WhatsApp message if enabled
          if (this.send_msg && this.editedItem.appointmentMessage && (this.patientFound ? this.patientInfo : this.patient)) {
            try {
              const whatsappData = {
                patient_id: this.patientFound ? this.patientInfo.id : this.patient.id,
                message: this.editedItem.appointmentMessage,
                date: `${reservationData.reservation_start_date} ${reservationData.reservation_from_time}`
              };

              axios.post('https://mina-api.tctate.com/api/whatsapp', whatsappData, {
                headers: {
                  "Content-Type": "application/json",
                  Accept: "application/json",
                  Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                },
              });
            } catch (whatsappError) {
              console.error('Error sending WhatsApp message:', whatsappError);
            }
          }
        })
        .catch(error => {
          console.error("Booking failed:", error);
          let errorMessage = "حدث خطأ أثناء الحجز";
          
          if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
          }
          
          this.$swal.fire({
            title: "فشل الحجز",
            text: errorMessage,
            icon: "error",
            confirmButtonText: "اغلاق",
          });
        })
        .finally(() => {
          this.loadSave = false;
        });
      }
    },
    prepareReservationData() {
      this.post_data = {

        
        item_id: this.item_id,
        reservation_start_date: this.editedItem.reservation_start_date,
        reservation_end_date: this.editedItem.reservation_start_date,
        reservation_from_time: this.editedItem.reservation_from_time,
        
        reservation_to_time:this.owner_item.possib_reserving_period ==null?this.editedItem.reservation_to_time:this.addtime(this.owner_item.possib_reserving_period),
        appointmentMessage: this.editedItem.appointmentMessage,
        user: {
          phone: `964${this.patientFound && this.patientInfo.phone ? this.patientInfo.phone.replace(/ /g, "") : (this.patient.phone ? this.patient.phone.replace(/ /g, "") : "")}`,
          name: this.patientFound && this.patientInfo ? this.patientInfo.name : (this.patient ? this.patient.name : ""),
        },
        reservation_number: 1,
      };
      this.post_data.item_id = this.item_id;


this.post_data.reservation_number = 1;
      this.post_data.reservation_date = this.editedItem.reservation_start_date;
    },
  },
};
</script>

<style scoped>
.white--text {
  color: #fff;
}
</style>
