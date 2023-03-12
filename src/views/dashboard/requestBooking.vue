<template>
  <div  >

    <v-dialog v-model="dialog" width="700" height="100%">

      <reserverionsInfo  :reserverionsInfo="item_selected"></reserverionsInfo>

    </v-dialog>

     <v-container id="dashboard" fluid tag="section">
         <v-data-table :headers="headers" :items="items" :loading="loading" loading-text="جاري تحميل البيانات" class="elevation-1 request_table">

        <template v-slot:top>
          <v-toolbar flat  >
            <v-toolbar-title>الحجوزات</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>

           



           

          </v-toolbar>

          <!-- <v-toolbar flat   > -->

        <v-layout row wrap justify-center  class="pdretm">

          <v-flex xs12>
             <v-chip-group center-active show-arrows v-model="selection" mandatory 
              active-class=" primary--text">
              <v-chip @click="All_res()">
                الكل
              </v-chip>
              <v-chip v-for="tag in lastdays" :key="tag.name" @click="ResByDate(tag.date)">
                {{ tag.name }}
              </v-chip>
              <v-chip @click="Weekly_Res()">
                حجوزات الاسبوع
              </v-chip>
              <v-chip @click="Monthly_Res()">
                حجوزات الشهر
              </v-chip>
            </v-chip-group>
          </v-flex>
        </v-layout>


            <v-layout row wrap justify-center >

              <v-flex xs12 md4 sm4>
                <v-chip-group center-active show-arrows v-model="selection_state"  mandatory
                  active-class="primary--text">
                  <v-chip  v-for="tag in reser_status" :key="tag.id" @click="reservsion_status(tag.id)">
                    {{ tag.name }}
                  </v-chip>


                </v-chip-group>
              </v-flex>

              <!-- <v-spacer></v-spacer> -->




              <v-flex xs5 md2 sm2 pr-1 style="padding-right: 22px !important;">
                <v-text-field  ref="name" v-model="search.user.phone" 
                  placeholder="رقم هاتف المراجع" required>
                </v-text-field>
              </v-flex>

              <v-flex xs5 md2 sm2 pr-4>
                <v-text-field  ref="name" v-model="search.user.full_name" 
                  placeholder="اسم المراجع" required>
                </v-text-field>
              </v-flex>
              <v-flex md1 sm3>

              </v-flex>
        
              <!-- <v-btn flat icon @click="Search()">
                <v-icon>fas fa-search</v-icon>
              </v-btn>
 -->







              <v-flex xs12 md2 sm2 mr-5>
                <v-btn color="green" @click="owner_booking=true" style="color:#fff;font-weight:bold" pr-3>
                  <v-icon size="15">fas fa-plus</v-icon> <span>اضافه حجز</span>
                </v-btn>
              </v-flex>





            </v-layout>








<!-- 
          </v-toolbar> -->

        </template>
        <template v-slot:item.status="{ item }">
          <v-row wrap justify="center">
            <v-chip class="res_pinding_chip" dark="" :color="item.status.status_color">
              <span style="color:#fff"> {{item.status.status_name_ar}}
                <v-icon left size="12"> {{item.status.status_icon}}</v-icon>
              </span>

            </v-chip>
          </v-row>
        </template>
        <template v-slot:item.reservation_from_time="{ item }">
          {{formatAMPM(item.reservation_from_time)}}
        </template>

        <!-- edit reservation -->
        <template v-slot:item.action="{ item }">
          <v-btn text icon @click="selectrequest(item)">
            <i class="fas fa-edit fa-lg"></i>
          </v-btn>


        </template>
        <template v-slot:no-data>
          <h4>لاتوجد بيانات</h4>
        </template>



      </v-data-table>
     </v-container>

   
    <v-row>

      <!-- <v-pagination v-model="page" prev-icon="mdi-menu-right" next-icon="mdi-menu-left" circle="" :length="pageCount">
      </v-pagination> -->



    </v-row>

    <OwnerBooking :patientFound="patientFound=false" :patients="patients" v-if="owner_booking"></OwnerBooking>
  </div>

</template>


<style >
  .pdretm{
    padding-right: 53px !important;
  }
  @media screen and (min-width:320px) {
      .pdretm{
    padding-right:0px !important;
  }
  }

</style>
<script>

  //import Echo from 'laravel-echo';
  import {
    EventBus
  } from './event-bus.js';
  import Axios from "axios";
  import reserverionsInfo from './sub_components/reserverions_info.vue'
  import OwnerBooking from './sub_components/OwnerBooking.vue'
  export default {
    data: () => ({
      items_ByStatus: [],
      selection: 0,
      patients:[],
      selection_state: 0,
      
      owner_booking: false,
      lastdays: [],
      items_ByDate: [],
      days: ["اﻷحد", "اﻷثنين", "الثلاثاء", "اﻷربعاء", "الخميس", "الجمعة", "السبت"],
      today: '',
      today_week: '',
      ReserverationAdded: '',
      filters: {
        first_date: null,
        second_date: null,
        status_id: ""
      },
      events: [],

      res_status: null,
      user_info: {},
      res_filter: null,
      token: "",
      calander_dialog: false,
      err_show: false,
      error: '',
      search: {
        user: {
          phone: '',
          full_name:''
        }
      },
      dialog: false,
      items: [],

      reser_status: [{
          id: 0,
          name: 'الكل'
        }, {
          id: 6,
          name: 'المقبوله'
        }, {
          id: 9,
          name: 'المرفوضه'
        }, {
          id: 4,
          name: 'المعلقه'
        },
        {
          id: 10,
          name: 'المكتمله'
        }

      ],





      filter: [{
        id: 0,
        name: 'الكل'
      }, {
        id: 1,
        name: 'حجوزات اليوم'
      }, {
        id: 2,
        name: 'حجوزات الاسبوع'
      }, {
        id: 3,
        name: 'حجوزات الشهر'
      }],

      item_selected: {
        user: {
          name: ''
        },
        ReservationRequirements: [],
        item: {
          name: '',
          price: ''
        },
        status: {
          id: ''
        },
        bill: {
          tasdid_bills: ''
        }
      },

      loding_accept: false,
      marvelHeroes: [],
      loding_cancel: false,
      headers: [{
          text: '#',
          align: 'center',
          sortable: false,
          value: 'id',
        },
        // {
        //   text: 'اسم الخدمه',
        //   align: 'center',
        //   sortable: false,
        //   value: 'item.item_name',
        // },
        {
          text: 'اسم المراجع',
          align: 'center',
          sortable: false,
          value: 'user.full_name',
        },


        {
          text: 'تاريخ الحجز',
          value: 'reservation_start_date',
          align: 'center',
          sortable: false
        },
        {
          text: 'وقت الحجز',
          value: 'reservation_from_time',
          align: 'center',
          sortable: false
        },

        {
          text: ' ',
          value: 'status',
          align: 'center',
        },
        {
          text: ' ',
          value: 'action',
          align: 'center',
        },
      ],

      editedIndex: -1,
      Patiants:[],
      editedItem: {
        name: '',
        calories: 0,
        fat: 0,
        carbs: 0,
        protein: 0,
      },

      defaultItem: {
        name: '',
        calories: 0,
        fat: 0,
        carbs: 0,
        protein: 0,
      },


      valid: false,
      page: 1,
      pageCount: 0,
      reserverationss: [],

      current_page: 1,
      last_page: 0,
      loading: false


    }),



    methods: {
      getLastDays() {

        var goBackDays = 7;

        var today = new Date();

        var newDate = new Date(today.setDate(today.getDate()));
        this.lastdays.push({
            name: 'اليوم',
            date: newDate.getFullYear() + '-' + (1 + newDate.getMonth()) + '-' + newDate.getDate()
          }


        );


        for (var i = 0; i < goBackDays; i++) {

          newDate = new Date(today.setDate(today.getDate() + 1));

          this.lastdays.push({
              name: this.days[newDate.getDay()],
              date: newDate.getFullYear() + '-' + (1 + newDate.getMonth()) + '-' + newDate.getDate()
            }


          );
        }



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

      selectrequest(item) {

        this.dialog = true;
        this.item_selected = item;
      },

      Monthly_Res() {
        this.reservsion_status(0);
        this.selection_state = 0;
        var date = new Date();
        var first_date = new Date(date);
        first_date.setUTCDate(1);

        var last_date = new Date(first_date); //Make a copy of the calculated first day
        last_date.setUTCMonth(last_date.getUTCMonth() + 1); //Add a month
        last_date.setUTCDate(0); //Set the date to 0, this goes to the last day of the previous month

        this.filters.first_date = first_date.toJSON().substring(0, 10);
        this.filters.second_date = last_date.toJSON().substring(0, 10);
        this.current_page = 1;
        this.Search();
      },


      Daily_Res() {
        this.reservsion_status(0);
        this.selection_state = 0;
        var today = new Date();
        var date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
        this.filters.first_date = date;
        this.filters.second_date = date;
        this.Search();

      },
      Weekly_Res() {
        this.reservsion_status(0);
        this.selection_state = 0;
        var curr = new Date;
        var firstday = new Date(curr.setDate(curr.getDate() - curr.getDay()));
        var lastday = new Date(curr.setDate(curr.getDate() - curr.getDay() + 6));
        this.filters.first_date = firstday.getFullYear() + "-" + (firstday.getMonth() + 1) + "-" + firstday.getDate()
        this.filters.second_date = lastday.getFullYear() + "-" + (lastday.getMonth() + 1) + "-" + lastday.getDate()
        this.current_page = 1;
        this.Search();

      },


      ResByDate(date) {
        this.reservsion_status(0);
        this.selection_state = 0;
        this.filters.first_date = date,
          this.filters.second_date = date,
          this.current_page = 1;
        this.Search();

      },
      get_calander() {
        //this.$refs.calendar.scrollToTime('08:00')
        this.calander_dialog = true;
      },

      


      Search() {

        this.loading = true;

        if (this.filters.first_date != null) {
          var date = this.filters.first_date + '_' + this.filters.second_date;
        } else {
          date = '';
        }
        var url = 'https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=' + date + '&filter[status_id]=' + this.filters
          .status_id + '&filter[user.user_phone]=' + this.search.user.phone.substring(1) + '&filter[user.full_name]='+this.search.user.full_name+'&sort=-id&page=' + this.current_page;
        //this.loading = true;
        //alert(this.loading);
       
           Axios.get(url, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
                        }
                    })

 
       .then(response => {
          this.loading = false;
          this.dialog = false;
          this.items_ByDate = response.data.data;
          this.last_page = response.data.meta.last_page;
          this.pageCount = response.data.meta.last_page;

          this.items = this.items_ByDate;


        }).catch(rr => {
            rr
            //this.$swal('خطاء', "خطاء بالاتصال", 'error')
          }

        ).finally(


        );


      },

      reservsion_status(res_status) {

        if (res_status == 0) {
          this.filters.status_id = ' ';
        } else {
          this.filters.status_id = res_status;
          this.Search();

        }

      },


      All_res() {
//alert('sd');
        this.filters.first_date = null;
        this.filters.second_date = null;
        this.current_page = 1;
        this.Search();
        this.res_status = 0;

      },



      // open(event) {

      // },
      dateClick() {


      },







      deleteItem(item) {
        this.items.indexOf(item)
        this.cancelReservation(item);




      },



      getMoreitems() {

        if (this.current_page <= this.last_page) {
          this.current_page = this.page;
          this.Search();
        }



      },


   getPatiants() {
                this.loading = true;
                Axios.get("patients/getByUserId", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.patients = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },






    },

    created() {
this.getPatiants();
  this.All_res();
  this.getLastDays();
      EventBus.$on('changeStatus', (from) => {
        from,

        this.Search();

      });

      EventBus.$on('GetResCancel', (from) => {
        from,
        this.owner_booking = false

      });


      EventBus.$on('GetRes', (from) => {
        from,
        this.owner_booking = false,
        this.All_res();


      });

      EventBus.$on('closeDialog', (from) => {
        from,
        this.dialog = false;


      });


      this.user_info = this.$cookies.get('admin_info');
      // this.weekly_request();

    },
    mounted() {

      window.Echo.channel('reserverationss')
        .listen('ReserverationAdded', (e) => {
          alert('sds');
          this.item = e.items;
        });


      this.getLastDays();
      this.All_res();
      var today = new Date();
      var date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();

      this.today = date;
      this.user_info = this.$cookies.get('admin_info');



    },
    computed: {

      selected: function () {
        return this.getMoreitems();
      }


    },

    components: {
      reserverionsInfo,
      OwnerBooking


    },


    watch: {
      selected: 'search by sub_cat_id',
    },
  }
</script>