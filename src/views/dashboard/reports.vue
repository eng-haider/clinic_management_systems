<template>

 <div>
  <v-form ref="form" v-model="valid">
  <v-layout row wrap pa-5>

   
<v-flex xs4 md3 sm3>

  <v-menu v-model="menu" :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y
    min-width="auto">
    <template v-slot:activator="{ on, attrs }">
      <v-text-field v-model="search.from_date" label="من التاريخ" prepend-icon="mdi-calendar" v-bind="attrs"
        v-on="on"></v-text-field>
    </template>
    <v-date-picker v-model="search.from_date" no-title scrollable>
      <v-spacer></v-spacer>

    </v-date-picker>
  </v-menu>

</v-flex>


<v-flex xs4 md3 sm3>

  <v-menu v-model="menu2" :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y
    min-width="auto">
    <template v-slot:activator="{ on, attrs }">
      <v-text-field v-model="search.to_date" label="الئ التاريخ" prepend-icon="mdi-calendar" v-bind="attrs"
        v-on="on"></v-text-field>
    </template>
    <v-date-picker v-model="search.to_date" no-title scrollable>
      <v-spacer></v-spacer>

    </v-date-picker>
  </v-menu>

</v-flex>


<v-flex xs11 md3 sm3 pr-4 v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors')  && doctorsAll.length>2">
  <v-select :rules="[rules.required]"  
    v-model="search.doctors" :label="$t('doctor')" :items="doctorsAll" item-text="name" item-value="id">
  </v-select>
</v-flex>



<v-flex xs2 md1 sm1 pa-5>
  <v-btn color="green" style="color:#fff" @click="exportCases()">تصــــــدير</v-btn>
</v-flex>





</v-layout>
</v-form>
 </div>
</template>

<script>
  //   import dash_card from '../../components/core/counts_number_card.vue';
  import Axios from "axios";
  import {
    EventBus
  } from "./event-bus.js";
  //   import billsReport from './sub_components/billsReport.vue';
  export default {
    name: "Dashboard",


    data() {
      return {
        dataSource2: [],
        is_Conjugations: false,
        valid: false,
        Conjugations: [],
        data:{
          doctors:''
        },
        doctorsAll: [],
        doctorsIdsAll: [],
        patient: {},

        rules: {
                    minPhon: (v) => v.length == 13 || "رقم الهاتف يجب ان يتكون من 11 رقم",
                    required: value => !!value || "مطلوب",
                    requiredd: value => !!value || "مطلوب",
                    min: (v) => v.length >= 6 || "كلمة المرور يجب ان تتكون من 6 عناصر او اكثر",
                    email: value => {
                        if (value.length > 0) {
                            const pattern =
                                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return pattern.test(value) || 'يجب ان يكون ايميل صحيح';
                        }
                    },
                },

        menu: false,
        menu2: false,
        dialog: false,
        showChar: false,
        Cases: [],
        search: {
          from_date: (new Date(new Date().setDate(new Date().getDate() - 30) - (new Date()).getTimezoneOffset() *
            60000)).toISOString().substr(0, 10),
          to_date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
        },
        page: 1,
        pageCount: 0,
        current_page: 1,
        searchDocorId: 0,
        is_search: false,
        last_page: 0,
        loadingData: true,


        dataSource: [

        ],
      }
    },
    computed: {

      selected: function () {
        return this.getMoreitems();
      }
    },
    watch: {
      selected: 'search by sub_cat_id',

    },

    methods: {


      getclinicDoctor() {
        this.loading = true;
        Axios.get("doctors/clinic", {
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


            this.doctorsAll.push({
              id:  11111111111111111,
              name: ' الكل'
            });
            this.doctors.forEach((item, index) => {
              index
              this.doctorsAll.push(item)
              this.doctorsIdsAll.push(item.id)
            })




          })
          .catch(() => {
            this.loading = false;
          });
      },
      Export() {

        this.axios.post('/patientsAccounsts/export', 
       this.data.doctors,
        
        {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          })
          .then((response) => {
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'file.xlsx');
            document.body.appendChild(fileLink);
            fileLink.click();
          });



      },
      getMoreitems() {

        if (this.current_page <= this.last_page) {
          this.current_page = this.page;
          this.initialize()
        }



      },
      editItem(item) {
        item
        this.dialog = true;
        this.patient = item;
      },

      totalOrders: function (values) {
        return values.reduce((acc, val) => {
          return acc + parseInt(val.price);
        }, 0);
      },
      pushx(x) {
        var r = [];

        r.push(x)
        return r;


      },
      BillsSum(bills_amount) {

        var x = 0
        for (var i = 0; i < bills_amount.length; i++) {

          x += bills_amount[i].price

        }

        return x;

      },

      BillsSumPaid(bills_amount) {

        var x = 0
        for (var i = 0; i < bills_amount.length; i++) {

          //   x+=bills_amount[i].price
          for (var j = 0; j < bills_amount[i].bills.length; j++) {
            x += bills_amount[i].bills[j].price
          }

        }

        return x;
        //return this.caseBillsSum(totle_coast);
      },
      caseBillsSum(bills_amount) {
        var totle_coast = [];

        for (var i = 0; i < bills_amount.length; i++) {
          totle_coast.push(bills_amount[i]);

        }
        return totle_coast;
      },
      searchs() {

      },

      async exportCases() {

        if (this.$refs.form.validate()) {
        this.loadingData = true; // Show loading indicator
        try {
          // Sending a GET request to trigger the export on the server

          if (this.search.doctors == 11111111111111111) {
                    this.search.doctors = this.doctorsIdsAll;
                }else{
                  this.search.doctors=[this.search.doctors];
                }

          const response = await this.axios.get('/cases/export-cases', {
            params: this.search, // Send search filters as query params
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            },
            responseType: 'blob' // To handle file response
          });

          // Create a link element to trigger the file download
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'cases.xlsx'); // Set the file name
          document.body.appendChild(link);
          link.click(); // Trigger the download
          document.body.removeChild(link); // Clean up the link element

          this.loadingData = false; // Hide loading indicator
        } catch (error) {
          console.error('Error exporting cases:', error);

          // Optionally display an error message to the user
          this.$notify({
            type: 'error',
            title: 'Export Failed',
            text: 'An error occurred while exporting cases. Please try again.'
          });

          this.loadingData = false; // Hide loading indicator
        }

      }
      },
      //user1_hospital1
      initialize() {
        if (this.is_search == true) {

          this.axios.post('/patientsAccounsts/search?page=' + this.current_page, this.search, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              this.loadingData = false;

              this.Cases = res.data.data;

              this.Conjugations = res.data.Conjugations;



              this.accounts_statistic.all_sum = res.data.all_sum;
              this.accounts_statistic.case_count = res.data.case_count;

              this.accounts_statistic.paid = res.data.paid;
              this.accounts_statistic.remainingamount = res.data.remainingamount;

              this.accounts_statistic.Conjugationsprice = res.data.Conjugationsprice;

              this.last_page = res.data.meta.last_page;
              this.pageCount = res.data.meta.last_page;







            })
            .catch(() => {
              this.loading = false;
            });
        } else {

          this.axios.get('/patientsAccounsts?page=' + this.current_page, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              this.loadingData = false;
              this.accounts_statistic.all_sum = res.data.all_sum;
              this.accounts_statistic.case_count = res.data.case_count;
              this.accounts_statistic.Conjugationsprice = res.data.Conjugationsprice;


              this.accounts_statistic.paid = res.data.paid;
              this.accounts_statistic.remainingamount = res.data.remainingamount;
              this.Cases = res.data.data;
              this.Conjugations = res.data.Conjugations;


              this.last_page = res.data.meta.last_page;
              this.pageCount = res.data.meta.last_page;







            })
            .catch(() => {
              this.loading = false;
            });
        }
      },


      getCase_number_stats() {

        this.axios
          .get("cases/getCaseCategoriesCounts", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token,
            },
          })
          .then((res) => {
            this.dataSource = res.data.data;
            this.showChar = true


          })
          .catch((err) => {
            err
          })
          .finally(() => {
            this.loading4 = false;
          });
      },
    },
    created() {
      this.getclinicDoctor();
      EventBus.$on("billsReportclose", (tooth) => {

        tooth
        this.dialog = false;

      });

      this.initialize()
    }

  }
</script>