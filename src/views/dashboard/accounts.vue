<template>
  <div>



    <v-dialog v-model="dialog" max-width="900px">

      <v-card>
        <billsReport :patient="patient" />
      </v-card>
    </v-dialog>

    <v-container id="dashboard" fluid tag="section">
      <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" items-per-page="15"
        @page-count="pageCount = $event" :items="Cases" class="elevation-1 request_table">
        <template v-slot:top>
          <v-toolbar flat>
            <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.accounts") }}
            </v-toolbar-title>

            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>

          </v-toolbar>


          <v-layout row wrap>



            <v-flex xs4>

              <v-menu v-model="menu" :close-on-content-click="false" :nudge-right="40" transition="scale-transition"
                offset-y min-width="auto">
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field v-model="search.from_date" label="من التاريخ" prepend-icon="mdi-calendar" v-bind="attrs"
                    v-on="on"></v-text-field>
                </template>
                <v-date-picker v-model="search.from_date" no-title scrollable>
                  <v-spacer></v-spacer>

                </v-date-picker>
              </v-menu>

            </v-flex>


            <v-flex xs4>

              <v-menu v-model="menu2" :close-on-content-click="false" :nudge-right="40" transition="scale-transition"
                offset-y min-width="auto">
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field v-model="search.to_date" label="الئ التاريخ" prepend-icon="mdi-calendar" v-bind="attrs"
                    v-on="on"></v-text-field>
                </template>
                <v-date-picker v-model="search.to_date" no-title scrollable>
                  <v-spacer></v-spacer>

                </v-date-picker>
              </v-menu>

            </v-flex>






            <v-flex xs2 md1 sm1 pa-5>
              <v-btn color="green" style="color:#fff" @click="is_search=true;initialize()">بحــث</v-btn>
            </v-flex>

            <v-flex xs3 md1 sm1 pt-5 pb-5 pr-2 v-if="allItem">
              <v-btn color="blue" style="color:#fff;float: left;" @click="allItem=false;initialize()">الكل</v-btn>
            </v-flex>

            
  <v-flex md1>

            </v-flex>
            <v-flex xs11 md2 sm2 pt-5 style="float: left;">
                            <v-select dense @input="getByDocor()"
                                v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors') &&   doctorsAll.length>2"
                                v-model="searchDocorId" :label="$t('doctor')" :items="doctorsAll" outlined
                                item-text="name" item-value="id">
                            </v-select>
                        </v-flex>


            <!-- <v-flex md4>

            </v-flex> -->

            <!-- <v-flex md1>
              <v-btn color="blue" style="color:#fff" @click="Export() ">تصــــدير</v-btn>
            </v-flex> -->

          </v-layout>



          <v-layout row wrap pt-4>
            <v-flex xs12 md3 sm6 pr-2 pb-4>


              <dash_card name="عدد الحالات" icon="fa-light fa-tooth" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.case_count">

              </dash_card>

            </v-flex>


            <v-flex xs12 md3 sm6 pr-2 pb-4>

              <dash_card name="مبلغ الحالات" icon="fa-solid fa-money-bill" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.all_sum|currency"></dash_card>
            </v-flex>

            <v-flex xs12 md3 sm6 pr-2 pb-4>

              <dash_card name="المدفوع" icon="fa-solid fa-money-bill" text_color="#53D3F8" icon_color="#035aa6" :count="accounts_statistic.paid|currency
"></dash_card>





            </v-flex>

            <v-flex xs12 md3 sm6 pr-2 pb-4>

              <dash_card name="المتبقي" icon="fa-solid fa-money-bill" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.remainingamount |currency"></dash_card>
            </v-flex>


          </v-layout>

        </template>


        <template v-slot:[`item.case_num`]="{ item }">
          {{ item.cases.length }}
        </template>
        <template v-slot:[`item.case_sum`]="{ item }">
          {{BillsSum(item.cases)| currency}}
        </template>
        <template v-slot:[`item.case_push`]="{ item }">
          <v-chip class="text-right" :color="'green'" outlined>
           

            {{BillsSumPaid(item.cases)| currency}}

          </v-chip>


        </template>


        <template v-slot:[`item.case_rem`]="{ item }">







          <v-chip :color="'red'" outlined class="text-right">
          
            {{(BillsSum(item.cases)-BillsSumPaid(item.cases)) | currency}}
          </v-chip>



        </template>







        <template v-slot:[`item.actions`]="{ item }">






          <v-btn dence color="blue" outlined @click="editItem(item)">التفاصيل
            <i class="fa-solid fa-circle-info"></i>

          </v-btn>
        </template>

      </v-data-table>

      <v-layout row wrap>
        <v-flex xs12>

          <div class="text-center pt-2">
            <v-pagination v-model="page" prev-icon="fas fa-angle-left fa-lg" color="#c7000b"
              next-icon="fas fa-angle-right fa-lg" style="    position: relative;top: 20px;" circle=""
              :length="pageCount">
            </v-pagination>
          </div>
        </v-flex>
      </v-layout>

    </v-container>




    <v-row>

    </v-row>
  </div>
</template>

<script>
  import dash_card from '../../components/core/counts_number_card.vue';

  import {
    EventBus
  } from "./event-bus.js";
  import billsReport from './sub_components/billsReport.vue';
  export default {
    name: "Dashboard",
    components: {
      billsReport,
      dash_card


    },

    data() {
      return {
        dataSource2: [],
        doctorsAll:[],
        patient: {},
        accounts_statistic: {
          all_sum: '',
          case_count: '',
          paid: '',
          remainingamount: ''





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
        searchDocorId:0,
        is_search: false,
        last_page: 0,
        loadingData: true,
        headers: [{
            text: this.$t('datatable.name'),
            align: "start",
            value: "name"
          },









          {
            text: ' عدد الحالات',
            value: "case_num",
            sortable: false
          },


          {
            text: ' مجموع مبلغ الحالات',
            value: "case_sum",
            sortable: false
          },


          {
            text: ' المدفوع',
            value: "case_push",
            sortable: false
          },


          {
            text: ' المتبقي',
            value: "case_rem",
            sortable: false
          },




          {
            text: this.$t('Processes'),
            value: "actions",
            sortable: false
          }
        ],
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
      getByDocor() {

if (this.searchDocorId == 0) {

    return this.initialize()
}
this.axios.get("patientsAccounsts/getByDoctor/" + this.searchDocorId, {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token
        }
    })
    .then(res => {
      this.loadingData = false;

this.Cases = res.data.data;


this.accounts_statistic.all_sum = res.data.all_sum;
this.accounts_statistic.case_count = res.data.case_count;

this.accounts_statistic.paid = res.data.paid;
this.accounts_statistic.remainingamount = res.data.remainingamount;


this.last_page = res.data.meta.last_page;
this.pageCount = res.data.meta.last_page;



    })
    .catch(() => {
        this.loading = false;
    });
},
      Export() {

        // this.axios({
        //   url: 'patientsAccounsts/export',
        //   method: 'GET',
        //   responseType: 'blob',
        // })
        
        

        this.axios.get('/patientsAccounsts/export', {
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


              this.accounts_statistic.all_sum = res.data.all_sum;
              this.accounts_statistic.case_count = res.data.case_count;

              this.accounts_statistic.paid = res.data.paid;
              this.accounts_statistic.remainingamount = res.data.remainingamount;


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


              this.accounts_statistic.paid = res.data.paid;
              this.accounts_statistic.remainingamount = res.data.remainingamount;
              this.Cases = res.data.data;
              this.last_page = res.data.meta.last_page;
              this.pageCount = res.data.meta.last_page;







            })
            .catch(() => {
              this.loading = false;
            });
        }
      },

      getclinicDoctor() {
                this.loading = true;
                this.axios.get("doctors/clinic", {
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
                            id: 0,
                            name: ' الكل'
                        });
                        this.doctors.forEach((item, index) => {
                            index
                            this.doctorsAll.push(item)
                        })




                    })
                    .catch(() => {
                        this.loading = false;
                    });
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