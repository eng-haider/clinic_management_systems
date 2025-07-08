<template>
  <div>
    <v-dialog v-model="BookingDetails" width="700" height="700" persistent>
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
            <v-row justify="center">
              <v-col cols="12" sm="6">
                <v-date-picker 
                  v-model="editedItem.reservation_start_date" 
                  locale="ar-iq"
                />
              </v-col>


             

              <v-col cols="12" sm="6" xs="12"   v-if="owner_item.possib_reserving_period !==null">
              <v-time-picker v-model="editedItem.reservation_from_time" full-width format="ampm"></v-time-picker>

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
        this.prepareReservationData();
        

        if(this.doctors.length>1 && this.patientInfo.doctors && this.patientInfo.doctors.length > 0 && this.patientInfo.doctors[0] && this.patientInfo.doctors[0].user){
     
     this.tokx=this.patientInfo.doctors[0].user.tctate_token;
    }else{
     this.tokx=this.$store.state.AdminInfo.tctate_token;
    }


        this.$http.post("https://tctate.com/api/api/reservation/owner/setv2", this.post_data, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${this.tokx}`,
          },
        }).then(response => {
          response
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
        })

        .catch(response => {
          response
          this.BookingDetails = false;
          this.close();
          EventBus.$emit('GetResCancel', true);
          console.error("Failed ", 'error');
          // this.$swal('', "تم الحجز بنجاح", 'success');
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
