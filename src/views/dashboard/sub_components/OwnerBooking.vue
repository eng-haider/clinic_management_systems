<template>
  <div>
    <v-dialog v-model="BookingDetails" width="700" height="700" persistent>

  <v-form ref="form" v-model="valid">

  <v-card height="auto">
       <v-toolbar dark color="primary lighten-1 mb-5">
                    <v-toolbar-title>
                        <h3 style="color:#fff;font-family: 'Cairo'"> حجز موعد</h3>
                    </v-toolbar-title>
                    <v-spacer />
                    <v-btn @click="close()" icon>
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>

        <v-container grid-list-xs>
            <div v-if="!patientFound">
   
      <v-row  justify="center">
        <v-col>
             <v-autocomplete
          :items="patients"
           outlined
           item-text="name"
           return-object
           v-model="patient"
             item-value="name"
          label="اختار المراجع"
        ></v-autocomplete>
        </v-col>
      </v-row>
    </div>


    <v-row justify="center">
       <v-col
      cols="12"
      sm="6"
         xs="12"
    >

  
    <v-date-picker v-model="editedItem.reservation_start_date" width="300" locale="ar-iq" ></v-date-picker>
    </v-col>

    <v-col
      cols="12"
      sm="6"
      xs="12"
    >

         <v-menu
        ref="menu1"
        v-model="menu2"
        :close-on-content-click="false"
        
        :nudge-right="40"
        :return-value.sync="editedItem.reservation_from_time"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="290px"
      >
      {{editedItem.reservation_from_time}}
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
    :rules="[rules.required]"
            v-model="editedItem.reservation_from_time"
            label="من الساعه"
            prepend-icon="mdi-clock-time-four-outline"
            readonly
            required
            v-bind="attrs"
            v-on="on"
          ></v-text-field>
        </template>
        <v-time-picker
          v-if="menu2"
          v-model="editedItem.reservation_from_time"
          full-width

         
  format="ampm"
          @click:minute="$refs.menu1.save(editedItem.reservation_from_time)"
        ></v-time-picker>
      </v-menu>





        <v-menu
        ref="menu"
       
        :close-on-content-click="false"
         v-model="menu3"
        :nudge-right="40"
        :return-value.sync="editedItem.reservation_to_time"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="290px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="editedItem.reservation_to_time"
            label="الى الساعه"
            :rules="[rules.required]"
            prepend-icon="mdi-clock-time-four-outline"
            readonly
            v-bind="attrs"
            v-on="on"
          ></v-text-field>
        </template>
        <v-time-picker
          v-if="menu3"
          v-model="editedItem.reservation_to_time"
          full-width
          @click:minute="$refs.menu.save(editedItem.reservation_to_time)"
        ></v-time-picker>
      </v-menu>

    </v-col>

       <v-col
      cols="12"
      sm="4"
    >
    </v-col>
    <!-- <v-spacer></v-spacer> -->

 </v-row>

    
    <br>
    <br>
    <br>
      <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                    </v-btn>
                    <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>
                        حجز</v-btn>
                </v-card-actions>
        </v-container>
</v-card>
  </v-form>
    </v-dialog>
  </div>
</template>


<script>
  const axios = require('axios');
  import moment from 'moment/src/moment'

  import {
    EventBus
  } from '../event-bus.js';
  export default {

  props: {

           
            'patientInfo':Object,
            'patientFound':Boolean,
            'patients':Array

        },


    data: () => ({
      BookingDetailsInfo: {},
      model: 0,
      patient_id:'',
      valid_SelectItem: false,
      time_av: false,
         editedItem: {
           user:{
             phone:""
           },
           reservation_start_date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
           reservation_end_date:"",
           reservation_from_time:"",
           reservation_to_time:"",
           },
       picker: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
      withoutBills:false,
       menu2: false,
        menu3: false,
      day_to_open: [{
        every_day: '',
      }],
      moreItem: true,
      // nameRules: [
      //   v => !!v || 'يجب ادخال الاسم',
      // ],
      xx: true,
      phoneRules: [
        (v) => !!v || 'يجب ادخال رقم الهاتف',
        (v) => v.length > 10 && v.length < 12 || 'يجب ادخال رقم هاتف صحيح',
        (v) => /^\d+$/.test(v) || 'يجب ادخال رقم هاتف صحيح',

      ],
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
      ItemsReservationRequirements: [],
      pricepayment_when_receiving: '',
      possib_reserving_period: '',
      e1: '',
      items: [],
      BookingDetails: true,
      rating: 4,
      valid:false,
      fawater_res_Dailog: false,
      rating_dailog: false,
      reservations_number: null,
      valid_day: true,
      valid_pay: true,
      date: '',
      date_to: '',
      patient:{},
      sub_time_id: '',
      owner_itemId: '',
      img_click: '',
      ItemFeauter: [],
      ItemFeauterAvailable: false,
      itemid: '',
      errors: [],
      images: ['ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab', 'ab'],
      img_name: '',
      miniDate: new Date().toISOString().substr(0, 10),
      time1:null,
      time3:null,
      dayselectid: 0,
      dayoffer: [],
      date_dialog: false,
      date_to_dialog: false,
      time_dialog: false,
      booking_dialog: false,
      subs_time: {

      },
      address: '',
      ItemFeauterSelected: [],
      aa: [],
      allowedHour: [],
      fatwra_dialog: false,
      phoneReq: false,
      loading: false,
      bill: true,
      reserv_count: '',
      available_times: [],
      req_id: '',
      ReservationRequirements: [],
      color: 'color:green',



      numRules: [

        (v) => !!v || 'يجب ادخال رقم الهاتف',
        (v) => v.length > 10 && v.length < 12 || 'يجب ادخال رقم هاتف صحيح',
      ],
      DatenameRules: [
        v => !!v || 'هذا الحقل مطلوب  ',
      ],
      UsernameRules: [
        v => !!v || 'هذا الحقل مطلوب  ',
      ],
      ItemnameRules: [
        v => !!v || 'هذا الحقل مطلوب  ',
      ],



      TimeRules: [
        v => !!v || 'هذا الحقل مطلوب  ',
      ],


      id: '',
      Owner_items: [],

      reserv_date: [],
      post_data: {

        item_id: "",
        user: {
          name: '',
          phone: ''
        },
        reservation_start_date: "",
        reservation_date: "",
        item_features: [],
        withoutBills:0,
        reservation_end_date: "",
        reservation_from_time: "",
        reservation_to_time: "",
        reservation_number: "1",
        deliverable: false,
        phone: "",
        ReservationRequirements: [],
        sub_time_id: ""



      },


      ave_data: {
        item_id: '',
        date: ''
      }

    }),
    mounted() {
      this.getitems();

      this.getOwnerItems();



    },

    created() {
      /// EventBus.$emit('getUserReservationsCount', true);



    },


    methods: {
      pay_prev() {
        if (this.ItemFeauterAvailable) {
          this.e1 = 4
        } else if (this.day_to_open[0].every_day == 0) {
          this.e1 = 2;

        } else if (this.day_to_open[0].every_day == 1) {
          this.e1 = 1;

        }

      },

      step1() {
        this.moreItem = false;

        if (this.$refs.valid_UserInfo.validate()) {

          if (this.day_to_open[0].every_day == 1) {
            if (this.ItemFeauterAvailable) {
              this.e1 = 4
            } else if (!this.ItemFeauterAvailable) {
              this.e1 = 5


            }

          } else {
            this.e1 = 2
          }

        }
      },

      getOwnerItems() {

        //var url = "/v2/items/search?filter[owner.user_id]=" + this.$cookies.get('owner_info').id;
        var url= "https://tctate.com/api/v2/items/owner/get?page=1";
        this.loading = true
        this.$http({
          method: 'get',
          url: url,
          headers: {

          }

        }).then(response => {
          this.Owner_items = response.data.data;
          this.last_page = response.data.meta.last_page;
          this.pageCount = response.data.meta.last_page;


        }).catch(error => {

          error


        }).finally(d => {
          d,
          this.loading = false;
        });

      },






//var url = "/v2/items/search?filter[owner.user_id]=" + this.$cookies.get('owner_info').id;
      getitems() {


        this.loading = true
        this.$http({
          method: 'get',
          url: "/v2/items/owner/get?page=" + this.current_page,
          headers: {

          }

        }).then(response => {

          this.items = response.data.data;


          if (this.items.length > 1) {
            this.e1 = 0;
            this.moreItem = true;

          } else

          {
            this.e1 = 1;

            this.itemid = this.items[0].id;
            this.day_to_open = this.items[0].day_to_open;
            this.ItemFeauter = this.items[0].ItemFeatures;
            this.ItemsReservationRequirements = this.items[0].ItemsReservationRequirements;
            this.pricepayment_when_receiving = this.items[0].price.payment_when_receiving;
            this.possib_reserving_period = this.items[0].possib_reserving_period;


            if (this.items[0].ItemFeatures.length > 0) {
              this.ItemFeauterAvailable = true;

            }
          }




        }).catch(error => {
          error

        }).finally(d => {
          d,
          this.loading = false;
        });

      },


      getitemsById() {


        this.loading = true
        this.$http({
          method: 'get',
          url: "/v2/items/show/" + this.owner_itemId,
          headers: {

          }

        }).then(response => {


          this.items = response.data.data;

          this.itemid = this.items.id;
          this.day_to_open = this.items.day_to_open;
          //console.log(this.day_to_open);

          this.ItemFeauter = this.items.ItemFeatures;

          this.ItemsReservationRequirements = this.items.ItemsReservationRequirements;
          //this.moreItem=false;

          this.pricepayment_when_receiving = this.items.price.payment_when_receiving;
          this.possib_reserving_period = this.items.possib_reserving_period;


          if (this.items.ItemFeatures.length > 0) {
            this.ItemFeauterAvailable = true;

          }






        }).catch(error => {
          if (error.response.status == 402) {
            this.$swal(" انتهت الجلسة", "يجب تسجيل الدخول", 'error')
            this.logout();

          } else {
            // this.$swal('خطاء', "خطاء بالاتصال", 'error')
          }

        }).finally(d => {
          d,
          this.loading = false;
        });

      },


      check_re() {
        if (this.ItemsReservationRequirements.length !== this.ReservationRequirements.length) {
          alert('يجب رفع جميع الصور المرفقه');
          this.e1 = 2;
        }
      },

      Select_Items() {
        if (this.$refs.valid_SelectItem.validate()) {
          this.e1 = 1;

        }

      },
      save() {


 if (this.$refs.form.validate()) {

this.post_data.reservation_start_date=this.editedItem.reservation_start_date;
this.post_data.reservation_end_date=this.editedItem.reservation_start_date;
this.post_data.reservation_from_time=this.editedItem.reservation_from_time;
this.post_data.reservation_to_time=this.editedItem.reservation_to_time;

if(this.patientFound){
this.post_data.user.phone="964"+parseInt(this.patientInfo.phone.replace(/ /g, ""));
this.post_data.user.name=this.patientInfo.name;
}

else{
this.post_data.user.phone="964"+parseInt(this.patient.phone.replace(/ /g, ""));
this.post_data.user.name=this.patient.name;
}



this.post_data.item_id=225;


this.post_data.reservation_number=1;
this.post_data.reservation_date=this.editedItem.reservation_start_date;
          this.$http({
              method: 'post',
              url: "https://tctate.com/api/api/reservation/owner/set",
              data: this.post_data,
             headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
                        }
            })
            .then(response => {
              this.BookingDetails = false;
             
              this.$swal('', "    تم  الحجز بنجاح", 'success')
               this.$router.push({ name: 'requestBooking' })
              EventBus.$emit('GetRes', true)
              this.$refs.form.reset()
              //    this.e1 = 1;
              if ("payId" in response.data) {
                this.fatwra_num = response.data.data.payId;
                this.$refs.form_fa.reset()
                this.fawater_res_Dailog = true;
              }
              // this.rating_dailog = true;



              //bookink item succesful

            })

         
            
              .catch(error => {
                                this.booking_dialog = false;
                                    if (error.response) {
                                        if(error.response.data.data=='SubscriptionsPackages Expire')
                                        {
                                             this.$swal('', "حزمه الاشتراك الحاليه منتهيه يرجئ شراء حزمه جديده ", 'error')

                                        }
                                       

                                    }
                                }

                            )
            
            .finally(d => {
              d;
              this.booking_dialog = false;
              this.loading = false;
            });


       // }


        // if (this.info.item_price.book_without_credit_card == 0 && this.post_data.phone.length < 11) {
        //   //this.$swal('', "    تم  الحجز بنجاح", 'success')
        //   this.fatwra_dialog = true;
        //   return;
        // }


}
      },

      date_today() {
        var x = new Date();
        return moment(String(x)).format('YYYY-MM-DD');

      },
      ava_sub(subs_time) {
        for (var i = 0; i < subs_time.length; i++) {
          if (subs_time[i].reservation.length == 0) {

            return subs_time[i];
          }
        }
      },


      count_availble_per(subs_time) {
        subs_time
        // var x;

        var peroid_time = 0;

        for (var i = 0; i < subs_time.length; i++) {

          if (subs_time[i].reservation.length == 0) {
            peroid_time = peroid_time + 1;


          }

          //x = peroid_time;
        }
        return peroid_time;

      },
      get_times() {

        this.ave_data.item_id = this.itemid;
        this.ave_data.date = this.date;


        this.$http({
            method: 'post',
            url: "https://tctate.com/api/api/v2/reservation/GetAvilableReservationPeriods",
            data: this.ave_data

          })


          .then(response => {
            this.available_times = response.data.data;
            if (this.available_times.length == 0) {
              // alert('لاتوجد اوقات متاحه في هاذه الفتره');
            }



          })

      },

      formatAMPM(date) {
        if (typeof date === "string") {
          let [hours, minutes] = date.split(":");
          let ampm = "ص";

          if (Number(hours) > 12) {
            hours = Number(hours) - 12;
            ampm = "م";
          }

          return `${hours}:${minutes} ${ampm}`;

        } else if (date instanceof Date) {
          let hours = date.getHours();
          let minutes = date.getMinutes();

          let ampm = hours >= 12 ? 'م' : "AM";

          hours = hours % 12;
          hours = hours ? hours : 12; // the hour '0' should be '12'
          minutes = minutes < 10 ? "0" + minutes : minutes;

          let strTime = hours + ":" + minutes + " " + ampm;

          return strTime;
        }

        return date;
      },
      close() {
        this.BookingDetails = false;
        EventBus.$emit('GetResCancel', true)
        this.date_dialog = false;
        this.$refs.form_fa.reset()
        this.e1 = 1;
        this.$emit("changeStatus", false);



      },

      step2() {



        this.errors = [];
        if (!this.date) {
          this.$refs.form.validate();

        } else if (this.subs_time.id === undefined) {
          this.$swal('  خطاء', "يرجئ اختيار وقت الحجز", 'error')

        } else {
          // if (this.ItemsReservationRequirements.length > 0) {
          //   this.e1 = 3;
          // } 

          if (this.ItemFeauterAvailable) {
            this.e1 = 4;
          } else if (this.pricepayment_when_receiving == 0) {
            this.e1 = 5;
          }

        }


      },

      stepForItemReq() {


        if (this.ItemsReservationRequirements.length !== this.ReservationRequirements.length) {
          this.e1 = 2;
        } else {
          this.e1 = this.pricepayment_when_receiving == 0 ? 4 : 2;
        }






      },




      delete_img(img_id, index) {





        const Swal = require('sweetalert2');



        Swal.fire({
          title: "هل انت متاكد من الحذف ؟",

          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'

        }).then((result) => {
          if (result.value) {
            this.img_cliced = index;


            this.images[index] = 'ab';
            this.img_name = 'ghjk'


          }
        })




      },


      cancelImg() {
        this.images[0] = 'ab';
        this.img_name = 'ghjk'

      },
      pickFile(id, index) {
        this.req_id = id;
        this.img_click = index;
        this.$refs.image.click();
      },

      onFilePicked(e) {


        const files = e.target.files
        if (files[0] !== undefined) {
          this.imageName = files[0].name
          if (this.imageName.lastIndexOf('.') <= 0) {
            return
          }
          const fr = new FileReader()
          fr.readAsDataURL(files[0])
          fr.addEventListener('load', () => {
            this.imageUrl = fr.result
            this.imageFile = files[0]


            // this.images = [];
            var req = {
              item_reservation_requirements_id: this.req_id,
              images: [fr.result]

            }

            this.ReservationRequirements.push(req);
            this.images[this.img_click] = fr.result;

            // this.imgname = files[0].name;
            var a = this.img_click + 1;
            this.img_name = "   صورة رقم " + a



          })

        } else {
          this.imageName = ''
          this.imageFile = ''
          this.imageUrl = ''
        }
      },
      // selectAvatar(avatar) {
      //     this.form.avatar = avatar
      // }
    },


    validate_fawater() {
      if (this.$refs.form_fa.validate()) {
        this.fatwra_dialog = false;
      }
    },





    getDate_andTime(id) {

      var url = this.urL + "/getDaysTime/" + id;
      axios.get(url).then(response => {
        this.day_to_open = response.data.Data;

        for (var day = 0; day < 7; day++) {

          var offer = '';
          for (var i = 0; i < this.dayoffer.length; i++) {

            if (this.dayoffer[i] == day) {

              offer = 1;

            }



          }

          this.sechdual_info.push({
            day_name: this.getdays(day + 1),
            time_duration: ['غير متوفر'],
            status: false,
            offer: offer



          });
          for (i = 0; i < this.day_to_open.length; i++) {
            if (day >= this.day_to_open[i].from_day && day <= this.day_to_open[i].to_day) {

              var t = [];

              for (var time = 0; time < this.day_to_open[i].time_to_open.length; time++) {


                var from_time = this.day_to_open[i].time_to_open[time].from_time.split(":");
                var totime = this.day_to_open[i].time_to_open[time].to_time.split(":");



                t.push(from_time[0] + ":" + from_time[1] + " - " +
                  totime[0] + ":" + totime[1])

              }

              this.sechdual_info[day].time_duration = t
              this.sechdual_info[day].status = true



              this.timeline_status[day] = true;

            }
          }



        }



      })

    },
    getReserDate(from, to, time) {


      var url = 'https://tctate.com/api/api/reservation/SearchAvilableReservation?filter[item_id]=' + this.itemid +
        '&filter[BetweenTime]=' + time + '&&filter[BetweenDate]=' + from +
        '_' + to + '&page=' + 1;


      this.$http({
          method: 'get',
          url: url,
          data: this.post_data,
          headers: {

          }
        })

        .then(response => {

          this.reserv_date = response.data.data;
          this.reserv_count = response.data.meta.count;
          for (var i = 0; i < this.reserv_date.length; i++) {
            // var hours = this.reserv_date[i].reservation_from_time.split(":");
            //var hour = parseInt(hours[0]);
            time = this.time.split(":");


            time = parseInt(time);



            const index = this.allowedHour.indexOf(time);

            if (index > -1) {
              this.allowedHour.splice(index, 1);
            }






            if (this.reservations_number !== null) {

              if (this.reserv_count > this.reservations_number) {
                this.$swal('', " عذرا لاتوجد اوقات حجز متاحه في هاذا اليوم العدد ممتلئ", 'error')
                this.time = '';


              }


            } else if (!this.allowedHour.includes(time)) {
              this.$swal('', " عذرا لاتوجد اوقات حجز متاحه في هاذا الوقت", 'error')
              this.time = '';

            } else if (this.allowedHour.length == 0) {


              this.$swal('', " عذرا لاتوجد اوقات حجز متاحه في هاذا اليوم", 'error')
              this.time = '';

            } else if (this.allowedHour == null) {
              this.$swal('', " عذرا لاتوجد اوقات حجز متاحه في هاذا اليوم", 'error')
              this.time = '';

            }


          }

        });

    },
    allowedDates(val) {

      var d = new Date(val);

      for (var i = 0; i <= 6; i++) {
        if (d.getDay() == this.day_to_open[i].work_day) {

          if (this.day_to_open[i].status.id == 23) {
            return true;
          } else {
            return false;
          }
        }
      }

    },




    allowedHours(v) {
      v
    },

    allowedMinutes(v) {
      v
      return true;

    },

    select_to_date() {
      this.date_to_dialog = false;
      //http://109.224.27.9:81/api/v2/reservation/GetAvilableReservationPeriods
      // if (this.possib_reserving_period == 1) {

      //   this.post_data.reservation_end_date = this.date_to;
      // } else {
      //   this.post_data.reservation_end_date = this.date;

      // }

    },

    select_date() {
      // alert('xx');
      // alert(this.date);
      //this.date_dialog = false;
      this.post_data.reservation_start_date = this.date;

      this.post_data.reservation_date = this.date;
      if (this.possib_reserving_period == 1) {

        this.post_data.reservation_end_date = this.date_to;
      } else {
        this.post_data.reservation_end_date = this.date;

      }


      var d = new Date(this.date);

      this.allowedHour = [];
      for (var i = 0; i < 6; i++) {


        if (d.getDay() == this.day_to_open[i].work_day) {
          if (this.day_to_open[i].status.id == 23) {
            for (var j = 0; j < this.day_to_open[i].time_to_open.length; j++) {
              var hours1 = this.day_to_open[i].time_to_open[j].from_time.split(":");
              hours1 = parseInt(hours1[0]);
              var hours2 = this.day_to_open[i].time_to_open[j].to_time.split(":");
              hours2 = parseInt(hours2[0]);

              for (var k = hours1; k < hours2; k++) {
                this.allowedHour.push(k);
              }

            }
          }
        }



      }

    },



    select_time() {


      this.time_dialog = false;

      var d = new Date(this.date);
      for (var i = 0; i < 6; i++) {


        if (d.getDay() == this.day_to_open[i].work_day) {
          if (this.day_to_open[i].status.id == 23) {
            for (var j = 0; j < this.day_to_open[i].time_to_open.length; j++) {
              //  if(this.time>)

              var time = this.time.split(":");


              time = parseInt(time);

              var hours1 = this.day_to_open[i].time_to_open[j].from_time.split(":");
              hours1 = parseInt(hours1[0]);

              var hours2 = this.day_to_open[i].time_to_open[j].to_time.split(":");
              hours2 = parseInt(hours2[0]);

              if (time >= hours1 && time <= hours2) {
                if (this.day_to_open[i].time_to_open[j].reservation_type !== null)


                {
                  if (this.day_to_open[i].time_to_open[j].reservation_type.id == 2) {
                    this.reservations_number = this.day_to_open[i].time_to_open[j].reservations_number;
                    // alert(this.day_to_open[i].time_to_open[j].reservations_number);
                  } else {
                    this.reservations_number == null;

                  }
                } else {
                  this.reservations_number == null;

                }


              }
            }

          }
        }
      }

      this.post_data.reservation_from_time = this.time
      this.post_data.reservation_to_time = this.time

      this.getReserDate(this.date, this.date, this.time);



    },



    getPath(img_path) {

      return this.urL + "/" + img_path;

    },


















    getdays(index) {



      var days = ["اﻷحد", "اﻷثنين", "الثلاثاء", "اﻷربعاء", "الخميس", "الجمعة", "السبت"];

      return days[index];

    },

    components: {
      // BookingDetails,
    }



  }
</script>