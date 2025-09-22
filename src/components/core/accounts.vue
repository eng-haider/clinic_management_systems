<template>
  <div>



    <v-dialog v-model="dialog" max-width="900px" v-track-dialog>

      <v-card>
        <billsReport :patient="patient" />
      </v-card>
    </v-dialog>

    <v-container id="dashboard" fluid>


      <v-data-table v-if="!is_Conjugations" :headers="headers" :loading="loadingData" :page.sync="page"
        items-per-page="15" @page-count="pageCount = $event" :items="Cases" class="elevation-1 request_table">
        <template v-slot:top>
          <v-toolbar flat>
            <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.accounts") }}
            </v-toolbar-title>

            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>

          </v-toolbar>

          <v-container id="dashboard" fluid>
            <v-layout row wrap>



              <v-flex xs12 pt-2 pl-3 sm3 md3>

                <v-menu v-model="menu" :close-on-content-click="false" :nudge-right="40" transition="scale-transition"
                  offset-y min-width="auto">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field dense outlined v-model="search.from_date" label="من التاريخ"
                      prepend-icon="mdi-calendar" v-bind="attrs" v-on="on"></v-text-field>
                  </template>
                  <v-date-picker v-model="search.from_date" no-title scrollable>
                    <v-spacer></v-spacer>

                  </v-date-picker>
                </v-menu>

              </v-flex>


              <v-flex xs12 pt-2 pl-3 sm3 md3>

                <v-menu v-model="menu2" :close-on-content-click="false" :nudge-right="40" transition="scale-transition"
                  offset-y min-width="auto">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field dense outlined v-model="search.to_date" label="الئ التاريخ"
                      prepend-icon="mdi-calendar" v-bind="attrs" v-on="on"></v-text-field>
                  </template>
                  <v-date-picker v-model="search.to_date" no-title scrollable>
                    <v-spacer></v-spacer>

                  </v-date-picker>
                </v-menu>

              </v-flex>








              <v-flex xs12 md3 sm3 pt-2 style="float: left;" pl-3
                v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors') &&   doctorsAll.length>2">
                <v-select dense v-model="search.DocorId" :label="$t('doctor')" :items="doctorsAll" outlined
                  item-text="name" item-value="id">
                </v-select>
              </v-flex>



              <v-flex xs2 md1 sm1 pt-2>
                <v-btn color="green" dense style="color:#fff" @click="is_search=true;initialize()">بحــث</v-btn>
              </v-flex>


              <v-flex xs3 md1 sm1 pt-2 pr-4 v-if="allItem">
                <v-btn color="blue" style="color:#fff;" @click="allItem=false;is_search=false;initialize()">الكل</v-btn>
              </v-flex>





              <!-- <v-flex md4>

            </v-flex> -->

              <!-- <v-flex md1>
              <v-btn color="blue" style="color:#fff" @click="Export() ">تصــــدير</v-btn>
            </v-flex> -->

            </v-layout>

          </v-container>
          <v-layout row wrap pt-4 pb-4>
            <!-- <v-flex xs12 md3 sm6 pr-2 pb-4>


              <dash_card name="عدد الحالات" icon="fa-light fa-tooth" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.case_count">

              </dash_card>

            </v-flex> -->


            <v-flex xs6 md2 sm4 pr-2 pb-4 pt-4>
              <v-card class="mx-auto" outlined>
                <v-card-text>
                  <div class="text-center">
                    <v-icon large color="#035aa6">fa-solid fa-money-bill</v-icon>
                    <div class="text-h6 font-weight-bold mt-2" style="color: #53D3F8;">مبلغ الحالات</div>
                    <div class="text-h5 font-weight-bold mt-1">
                      {{ accounts_statistic.all_sum | currency }}
                    </div>
                    <div v-if="showDoctorPercentage" class="mt-2">
                      <v-divider class="my-2"></v-divider>
                      <div class="text-caption text--secondary">نسبة الطبيب ({{ doctorPercentage }}%)</div>
                      <div class="text-h6 font-weight-medium" style="color: #4CAF50;">
                        {{ (accounts_statistic.all_sum-accounts_statistic.item_cost_sum * doctorPercentage / 100) | currency }}
                      </div>
                    </div>

                     <div  class="mt-2">
                      <v-divider class="my-2"></v-divider>
                      <div class="text-caption text--secondary">المواد المستعملة</div>
                      <div class="text-h6 font-weight-medium" style="color: #4CAF50;">
                        {{ accounts_statistic.item_cost_sum | currency }}
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-flex>

            <v-flex xs6 md2 sm4 pr-2 pb-4 pt-4>
              <v-card class="mx-auto" outlined>
                <v-card-text>
                  <div class="text-center">
                    <v-icon large color="#035aa6">fa-solid fa-money-bill</v-icon>
                    <div class="text-h6 font-weight-bold mt-2" style="color: #53D3F8;">المدفوع</div>
                    <div class="text-h5 font-weight-bold mt-1">
                      {{ accounts_statistic.paid | currency }}
                    </div>
                    <div v-if="showDoctorPercentage" class="mt-2">
                      <v-divider class="my-2"></v-divider>
                      <div class="text-caption text--secondary">نسبة الطبيب ({{ doctorPercentage }}%)</div>
                      <div class="text-h6 font-weight-medium" style="color: #4CAF50;">
                        {{ doctorPaidAmount | currency }}
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-flex>

            <v-flex xs6 md2 sm4 pr-2 pb-4 pt-4>
              <v-card class="mx-auto" outlined>
                <v-card-text>
                  <div class="text-center">
                    <v-icon large color="#035aa6">fa-solid fa-money-bill</v-icon>
                    <div class="text-h6 font-weight-bold mt-2" style="color: #53D3F8;">المتبقي</div>
                    <div class="text-h5 font-weight-bold mt-1">
                      {{ (accounts_statistic.all_sum - accounts_statistic.paid) | currency }}
                    </div>
                    <div v-if="showDoctorPercentage" class="mt-2">
                      <v-divider class="my-2"></v-divider>
                      <div class="text-caption text--secondary">نسبة الطبيب ({{ doctorPercentage }}%)</div>
                      <div class="text-h6 font-weight-medium" style="color: #FF9800;">
                        {{ doctorRemainingAmount | currency }}
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-flex>
            
            <v-flex xs6 md2 sm4 pr-2 pb-4 pt-4 @click="is_Conjugations=true">
              <v-card class="mx-auto" outlined>
                <v-card-text>
                  <div class="text-center">
                    <v-icon large color="#035aa6">fa-solid fa-money-bill</v-icon>
                    <div class="text-h6 font-weight-bold mt-2" style="color: #53D3F8;">الصرفيات</div>
                    <div class="text-h5 font-weight-bold mt-1">
                      {{ accounts_statistic.Conjugationsprice|currency}}
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-flex>

            <!-- Credit Balance Card - only show if credit system is enabled -->
            <v-flex xs6 md2 sm4 pr-2 pb-4 pt-4 v-if="$store.getters.useCreditSystem">
              <v-card class="mx-auto" outlined>
                <v-card-text>
                  <div class="text-center">
                    <v-icon large color="#4CAF50">fa-solid fa-wallet</v-icon>
                    <div class="text-h6 font-weight-bold mt-2" style="color: #4CAF50;">رصيد المرضى</div>
                    <div class="text-h5 font-weight-bold mt-1" style="color: #4CAF50;">
                      {{ accounts_statistic.total_credit_balance | currency }}
                    </div>
                    <div class="text-caption text--secondary mt-1">الرصيد المتاح</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-flex>




          </v-layout>

          <v-tabs>
            <v-tab @click="is_Conjugations=false">دفعات الحالات </v-tab>


            <v-tab @click="is_Conjugations=true">حسابات الصرفيات</v-tab>

          </v-tabs>
        </template>

        <!-- 
        <template v-slot:[`item.case_num`]="{ item }">
          {{ item.cases.length }}
        </template> -->




        <template v-slot:[`item.price`]="{ item }">
          <v-chip class="text-right" :color="'green'" outlined>
            {{ item.price | currency}}
          </v-chip>
        </template>

        <template v-slot:[`item.is_paid`]="{ item }">
          <v-chip :color="item.is_paid ? 'green' : 'red'" outlined>
            {{ item.is_paid ? 'مدفوع' : 'غير مدفوع' }}
          </v-chip>
        </template>

        <template v-slot:[`item.PaymentDate`]="{ item }">
          {{ new Date(item.PaymentDate).toLocaleDateString('ar-IQ') }}
        </template>







        <!-- <template v-slot:[`item.actions`]="{ item }">






          <v-btn dence color="blue" outlined @click="editItem(item)">التفاصيل
            <i class="fa-solid fa-circle-info"></i>

          </v-btn>
        </template> -->

      </v-data-table>
















      <v-data-table v-if="is_Conjugations" :headers="headers_Conjugations" :loading="loadingData" :page.sync="page"
        items-per-page="15" @page-count="pageCount = $event" :items="Conjugations" class="elevation-1 request_table">
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
                v-model="searchDocorId" :label="$t('doctor')" :items="doctorsAll" outlined item-text="name"
                item-value="id">
              </v-select>
            </v-flex>


            <!-- <v-flex md4>

            </v-flex> -->

            <!-- <v-flex md1>
              <v-btn color="blue" style="color:#fff" @click="Export() ">تصــــدير</v-btn>
            </v-flex> -->

          </v-layout>



          <v-layout row wrap pt-4 mb-4 style="margin-bottom: 20px ;">
            <!-- <v-flex xs12 md3 sm6 pr-2 pb-4>


              <dash_card name="عدد الحالات" icon="fa-light fa-tooth" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.case_count">

              </dash_card>

            </v-flex> -->




            <v-flex xs12 md3 sm6 pr-2 pb-4>

              <dash_card name="المدفوع" icon="fa-solid fa-money-bill" text_color="#53D3F8" icon_color="#035aa6" :count="accounts_statistic.paid|currency
"></dash_card>





            </v-flex>

            <v-flex xs12 md3 sm6 pr-2 pb-4>

              <dash_card name="الديون" icon="fa-solid fa-money-bill" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.remainingamount"></dash_card>
            </v-flex>
            <v-flex xs12 md3 sm6 pr-2 pb-4>

              <dash_card name="الصرفيات" icon="fa-solid fa-money-bill" text_color="#53D3F8" icon_color="#035aa6"
                :count="accounts_statistic.Conjugationsprice"></dash_card>
            </v-flex>





          </v-layout>

          <v-tabs>
            <v-tab @click="is_Conjugations=false">حسابات المارجعين</v-tab>
            <v-tab @click="is_Conjugations=true">حسابات الصرفيات</v-tab>

          </v-tabs>

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
        allItem: false,
        dataSource2: [],
        is_Conjugations: false,
        Conjugations: [],
        doctorsAll: [],
        selectedDoctorData: null, // Add this to store selected doctor data

        patient: {},
        accounts_statistic: {
          all_sum: '',
          case_count: '',
          paid: '',
          remainingamount: '',
          Conjugationsprice: '',
          total_credit_balance: 0




        },


        menu: false,
        menu2: false,
        dialog: false,
        showChar: false,
        Cases: [],
        search: {
          DocorId: 0,
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

        headers_Conjugations: [{
            text: this.$t('datatable.name'),
            align: "start",
            value: "name"
          },

          {
            text: this.$t('datatable.price'),
            align: "start",
            value: "price"
          },


          {
            text: 'الكمية',
            align: "start",
            value: "price"
          },








          {
            text: 'التاريخ',
            value: "date",
            sortable: false
          }
        ],




        headers: [{
            text: this.$t('datatable.name'),
            align: "start",
            value: "billable.name"
          },

          {
            text: 'رقم الهاتف',
            value: "billable.phone",
            sortable: false
          },

          {
            text: 'تاريخ الدفع',
            value: "PaymentDate",
            sortable: false
          },

          {
            text: '  مبلغ الدفعة',
            value: "price",
            sortable: false
          },

          {
            text: 'حالة الدفع',
            value: "is_paid",
            sortable: false
          },

          {
            text: 'الطبيب',
            value: "user.name",
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
      },
      
      doctorPercentage() {
        // If a doctor is selected, use their profit_percentage
        if (this.search.DocorId && this.search.DocorId !== 0 && this.selectedDoctorData) {
          const percentage = this.selectedDoctorData.profit_percentage;
          return percentage && !isNaN(percentage) ? parseFloat(percentage) : null;
        }
        
        // Fallback to clinic default (keep existing logic for backward compatibility)
        const doctorMoney = this.$store.state.AdminInfo.clinics_info?.doctor_mony;
        return doctorMoney && !isNaN(doctorMoney) ? parseFloat(doctorMoney) : null;
      },
      
      showDoctorPercentage() {
        return this.doctorPercentage !== null;
      },
      
      doctorPaidAmount() {
        if (!this.showDoctorPercentage) return 0;
        return (this.accounts_statistic.paid * this.doctorPercentage) / 100;
      },
      
      doctorRemainingAmount() {
        if (!this.showDoctorPercentage) return 0;
        const remaining = this.accounts_statistic.all_sum - this.accounts_statistic.paid;
        return (remaining * this.doctorPercentage) / 100;
      }
    },
    watch: {
      selected: 'search by sub_cat_id',
      // Add watcher for doctor selection
      'search.DocorId': function(newVal) {
        if (newVal && newVal !== 0) {
          this.selectedDoctorData = this.doctorsAll.find(doctor => doctor.id === newVal);
        } else {
          this.selectedDoctorData = null;
        }
      }
    },

    methods: {
      getByDocor() {

        if (this.searchDocorId == 0) {

          return this.initialize()
        }
        this.axios.get("patientsAccounstsv2/getByDoctor/" + this.searchDocorId, {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          })
          .then(res => {
            this.loadingData = false;

            // Handle the new response structure
            if (res.data.data && res.data.data.data) {
              this.Cases = res.data.data.data;
              this.last_page = res.data.data.last_page;
              this.pageCount = res.data.data.last_page;
            } else {
              this.Cases = res.data.data || [];
            }

            // Get statistics from report endpoint
            this.getAccountsReport();
          })
          .catch((error) => {
            console.error('getByDocor API error:', error);
            this.loadingData = false;
          });
      },
      Export() {
        this.axios.get('https://smartclinicv5.tctate.com/api/patientsAccounstsv2/export', {
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
            fileLink.setAttribute('download', 'accounts_export.xlsx');
            document.body.appendChild(fileLink);

            fileLink.click();
          })
          .catch((error) => {
            console.error('Export error:', error);
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

      BillsSumPaid(cases) {

        var x = 0



        //   x+=bills_amount[i].price
        for (var j = 0; j < cases.bills.length; j++) {

          x += cases.bills[j].price
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
        console.log('Initialize called with is_search:', this.is_search);
        console.log('Search parameters:', this.search);
        console.log('Current page:', this.current_page);

        // First get the statistics/report data
        this.getAccountsReport();

        if (this.is_search == true) {
          this.allItem = true;

          console.log('Making search API call with params:', this.search);
          this.axios.post('/patientsAccounstsv2?page=' + this.current_page, this.search, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              console.log('Search API response:', res.data);
              this.loadingData = false;

              this.Cases = res.data.data.data;
              this.last_page = res.data.data.last_page;
              this.pageCount = res.data.data.last_page;
            })
            .catch((error) => {
              console.error('Search API error:', error);
              this.loadingData = false;
            });
        } else {
          console.log('Making general API call without body...');
          this.axios.post('/patientsAccounstsv2?page=' + this.current_page, {}, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              console.log('General API response:', res.data);
              this.loadingData = false;

              this.Cases = res.data.data.data;
              this.last_page = res.data.data.last_page;
              this.pageCount = res.data.data.last_page;
            })
            .catch((error) => {
              console.error('General API error:', error);
              this.loadingData = false;
            });
        }
      },

      getAccountsReport() {
        console.log('Getting accounts report...');

        if (this.is_search) {
          // Send search parameters when searching
          this.axios.post('/patientsAccounstsv2/patientsAccounstsReportv2?page=1', this
              .search, {
                headers: {
                  "Content-Type": "application/json",
                  Accept: "application/json",
                  Authorization: "Bearer " + this.$store.state.AdminInfo.token
                }
              })
            .then(res => {
              console.log('Search Report API response:', res.data);

              this.accounts_statistic.all_sum = res.data.all_case_sum;
              this.accounts_statistic.item_cost_sum = res.data.item_cost_sum;
              this.accounts_statistic.paid = res.data.paid;


              this.accounts_statistic.remainingamount = res.data.remainingamount;
              this.accounts_statistic.Conjugationsprice = res.data.Conjugationsprice;
              this.accounts_statistic.total_credit_balance = res.data.total_credit_balance || 0;
              this.Conjugations = res.data.Conjugations;
            })
            .catch((error) => {
              console.error('Search Report API error:', error);
            });
        } else {
          // Send empty body when not searching
          this.axios.post('/patientsAccounstsv2/patientsAccounstsReportv2?page=1', {}, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              console.log('General Report API response:', res.data);

              this.accounts_statistic.all_sum = res.data.all_case_sum;
               this.accounts_statistic.item_cost_sum = res.data.item_cost_sum;
              this.accounts_statistic.paid = res.data.paid;
              this.accounts_statistic.remainingamount = res.data.remainingamount;
              this.accounts_statistic.Conjugationsprice = res.data.Conjugationsprice;
              this.accounts_statistic.total_credit_balance = res.data.total_credit_balance || 0;
              this.Conjugations = res.data.Conjugations;
            })
            .catch((error) => {
              console.error('General Report API error:', error);
            });
        }
      },

      getclinicDoctor() {
        this.loadingData = true;
        this.axios.get("https://smartclinicv5.tctate.com/api/doctors/clinic", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          })
          .then(res => {
            this.loadingData = false;

            // Handle the response structure - check if data is nested
            const doctors = res.data.data || res.data;

            this.doctorsAll = [{
              id: 0,
              name: ' الكل',
              profit_percentage: null
            }];

            if (Array.isArray(doctors)) {
              doctors.forEach((item) => {
                this.doctorsAll.push({
                  id: item.id,
                  name: item.name,
                  profit_percentage: item.profit_percentage,
                  user_id: item.user_id,
                  clinics_id: item.clinics_id
                });
              });
            }
          })
          .catch((error) => {
            console.error('getclinicDoctor error:', error);
            this.loadingData = false;
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