<template>
  <div id='app' v-if="eventSettings.dataSource.length>0">
    <div>
      <OwnerBooking :data="dataxx" :patientFound="patientFound=false" :patients="Patiant" v-if="owner_booking">
      </OwnerBooking>
    </div>
    <ejs-schedule :eventRendered="oneventRendered" :currentView='currentView' height='900px' :popupOpen="onPopupOpen"
      :created="onCreate" :eventClick='onEventClick' :actionComplete="onActionComplete" :eventSettings='eventSettings'>
      <e-resources :save="onChange">
        <e-resource field='DepartmentID' title='date' name='Departments' :dataSource='Patiant' textField='name'
          idField='id' colorField='Color'>
        </e-resource>
      </e-resources>
    </ejs-schedule>
  </div>
</template>
<script>
  import {
    EventBus
  } from './event-bus.js';
  const axios = require('axios');
  import OwnerBooking from './sub_components/OwnerBooking.vue'
  import Vue from "vue";

  import {
    SchedulePlugin,
    Day,
    Week,
    WorkWeek,
    Month,
    Agenda
  } from '@syncfusion/ej2-vue-schedule';
  Vue.use(SchedulePlugin);
  export default Vue.extend({
    data: function () {
      return {
        Patiant: [],
        star_date: '',
        dataxx: {},
        currentView: 'Month',
        owner_booking: false,
        datax: [],
        eventSettings: {
          dataSource: [],

        },
        selectedDate: new Date(2022, 9, 9)
      }
    },
    components: {

      OwnerBooking


    },
    created() {
      EventBus.$on('GetResCancel', (from) => {
        from,
        this.owner_booking = false





      });

      EventBus.$on('GetRes', (from) => {
        from
       window.location.reload();
      });

      this.getPatiant();
      this.getData();
    },
    methods: {



      oneventRendered: function (args) {

        args
      },


      onPopupOpen: function (args) {


        if (args.type == 'DeleteAlert') {
          axios.delete(
              "https://tctate.com/api/api/v2/reservation/delete/" + args.data.Id, {
                headers: {
                  "Content-Type": "application/json",
                  Accept: "application/json",
                  Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
                }
              })
            .then(res => {
                window.location.reload();
              res



            })
            .catch(() => {});
        }

        if ('Subject' in args.data) {
          args
        } else {
          this.owner_booking = true;
          this.dataxx = args.data;

        }

      },


      getData() {
        axios.get(
            "https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1", {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
              }
            })
          .then(res => {

            this.Data = res.data.data;

            for (var i = 0; i < this.Data.length; i++) {

              var objs = {
                Id: this.Data[i]['id'],
                Subject: this.Data[i]['user'].full_name,

                StartTime: this.Data[i]['reservation_end_date'] + 'T' + this.Data[i]['reservation_from_time'],
                EndTime: this.Data[i]['reservation_start_date'] + 'T' + this.Data[i]['reservation_to_time'],
              }

              this.eventSettings.dataSource.push(objs);


            }
            this.$refs.eventSettings.dataSource;
            this.$refs.eventSettings.dataSource;

          })
          .catch(() => {});
      },
      getPatiant() {
        axios.get("patients/getByUserId", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            }
          })
          .then(res => {
            this.loadingData = false;
            this.loading = false;
            this.Patiant = res.data.data;


          })
          .catch(() => {
            this.loading = false;
          });
      }
    },
    provide: {

      schedule: [Day, Week, WorkWeek, Month, Agenda]
    }
  });
</script>
<style>
  #QuickDialog {
    display: none;
  }

  body>div:nth-child(8) {
    display: none !important;
  }
  body > div:nth-child(11){
    display: none !important;
  }

  body > div:nth-child(10){
    display: none !important;
  }
  .e-schedule-dialog-container {
    display: none !important
  }
  .e-quick-popup-wrapper.e-device{
    top: 42px !important;
  }
  @import '../../../node_modules/@syncfusion/ej2-base/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-buttons/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-calendars/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-dropdowns/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-inputs/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-navigations/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-popups/styles/material.css';
  @import '../../../node_modules/@syncfusion/ej2-vue-schedule/styles/material.css';

  .e-quick-popup-wrapper{
    height: 400px !important;;
  }

  body > div:nth-child(9){
    display: none;
  }

  body > div:nth-child(10){
  display:none !important;
}
</style>