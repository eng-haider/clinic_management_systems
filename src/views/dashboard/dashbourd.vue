<template>
  <div>
    <v-row>
      <v-col>
        <v-card class="mt-0 mx-auto pa-4" height="420" outlined v-if="showChar">

       



        </v-card>
      </v-col>
    </v-row>

    <br>
    <br>
    <v-simple-table>
      <template v-slot:default>
        <thead>
          <tr>
            <th class="text-right">
              {{$t('datatable.name')}}
            </th>

            <th class="text-right">
              المدفوع
            </th>

            <th class="text-right">
              المتبقي
            </th>

            <th class="text-right">
              مجموع مبلغ الحالات
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in Cases" :key="item">

            <template v-if="BillsSumPaid(item.cases) > 0 || BillsSum(item.cases)-BillsSumPaid(item.cases) > 0">
              <td>{{ item.name }}</td>



              <td>





                {{BillsSumPaid(item.cases)}}







              </td>

              <td>





                {{BillsSum(item.cases)-BillsSumPaid(item.cases)}}







              </td>

              <td>


                {{BillsSum(item.cases)}}


              </td>
            </template>

          </tr>
        </tbody>
      </template>
    </v-simple-table>


    <v-row>

    </v-row>
  </div>
</template>

<script>
  // import {
  //   DxChart,
  //   DxSeries
  // } from "devextreme-vue/chart";

  export default {
    name: "Dashboard",
    components: {
      // DxChart,
      // DxSeries


    },

    data() {
      return {
        dataSource2: [],
        showChar: false,
        Cases: [],
        dataSource: [

        ],
      }
    },
    methods: {
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
        //  alert(bills_amount.length)
        console.log(bills_amount);
        // var totle_coast = [];
        var x = 0
        for (var i = 0; i < bills_amount.length; i++) {

          x += bills_amount[i].price
          //            for (var j = 0; j < bills_amount[i].bills.length; j++) {
          // x+=bills_amount[i].bills[j].price
          //            }  

        }

        return x;
        //return this.caseBillsSum(totle_coast);
      },

      BillsSumPaid(bills_amount) {
        //  alert(bills_amount.length)
        console.log(bills_amount);
        // var totle_coast = [];
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

      
      UserCases() {

        this.axios.get("patients/patientsAccounsts", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          })
          .then(res => {
            // this.loading = false;
            this.Cases = res.data.data;


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
      this.getCase_number_stats();
      this.UserCases()
    }

  }
</script>