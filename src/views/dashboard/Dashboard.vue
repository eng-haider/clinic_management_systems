<template>
  <v-container id="dashboard" fluid tag="section">
    <template>


      <v-layout row wrap pt-4>
        <v-flex xs12 md3 sm3 pr-2 pb-4>


          <dash_card name="عدد المراجعين" icon="fa-solid fa-hospital-user" text_color="#53D3F8" icon_color="#035aa6"
            :count="DashbourdCounts.patients">

          </dash_card>

        </v-flex>


        <v-flex xs12 md3 sm3 pr-2 pb-4 >

          <dash_card name="عدد الحالات" icon="fa-solid fa-hospital-user" text_color="#53D3F8" icon_color="#035aa6"
            :count="DashbourdCounts.Casesall"></dash_card>
        </v-flex>

        <v-flex xs12 md3 sm3 pr-2 pb-4>

          <dash_card name="  اعدد الحالات المنتهيه" icon="fa-solid fa-check" text_color="#53D3F8" icon_color="#035aa6"
            :count="DashbourdCounts.Casescompleted
"></dash_card>





        </v-flex>

        <v-flex xs12 md3 sm3 pr-2 pb-4>

          <dash_card name="  عدد الحالات غير المنتهيه" icon="fas fa-layer-group" text_color="#53D3F8"
            icon_color="#035aa6" :count="DashbourdCounts.Casesactive
"></dash_card>
        </v-flex>


      </v-layout>

      <v-layout row wrap pt-4>

        <v-flex md-6 sm12>

          <v-card pa-3>
            <!--         
          <Doughnut :chart-options="chartOptions" :chart-data="chartData" :chart-id="chartId"
             :plugins="plugins" /> -->

            <DxChart id="chart" :data-source="dataSource" :title="$t('dashboard.Case_number_stats')">
              <DxSeries argument-field="name_ar" value-field="case_count" type="bar" />
              <DxLegend :visible="false" />
              <!-- <DxExport :enabled="true" /> -->
              <DxSeriesTemplate name-field="name_ar" />
              <DxCommonSeriesSettings :ignore-empty-points="true" argument-field="name_ar" value-field="case_count"
                type="bar" />
              <DxTooltip :enabled="true" />
            </DxChart>


         

          </v-card>
        </v-flex>

        <v-flex md-6 xs12 pt-4>
<v-card>
  <DxPieChart
    id="pie"
    :data-source="dataSource"
    palette="Bright"
    title="Olympic Medals in 2008"
  >
    <DxSeries
      argument-field="name_ar"
      value-field="case_count"
    >
      <DxLabel
        :visible="true"
        :customize-text="formatLabel"
        position="columns"
      >
        <DxConnector
          :visible="true"
          :width="0.5"
        />
        <DxFont :size="16"/>
      </DxLabel>
    </DxSeries>
    <DxLegend
      :column-count="4"
      orientation="horizontal"
      item-text-position="right"
      horizontal-alignment="center"
      vertical-alignment="bottom"
    />
    <DxExport :enabled="true"/>
  </DxPieChart>
</v-card>
        </v-flex>



      </v-layout>

    </template>

  </v-container>
</template>

<script>
  import {
    DxChart,
    DxSeries
  } from "devextreme-vue/chart";

  import dash_card from '../../components/core/dash_card.vue';

  
  // import {
  //   Doughnut
  // } from 'vue-chartjs/legacy'
  // import {
  //   Chart as ChartJS,
  //   Title,

  //   Tooltip,
  //   Legend,
  //   ArcElement,
  //   BarElement,
  //   CategoryScale,
  //   LinearScale
  // } from 'chart.js'
  // ChartJS.register(Title, Tooltip, Legend, BarElement, ArcElement, CategoryScale, LinearScale)
  import DxPieChart, {
    DxLegend,
  DxLabel,
  DxConnector,
  DxFont,
  DxExport,
} from 'devextreme-vue/pie-chart';
  import axios from "axios";
  export default {
    components: {
      // Doughnut,
      dash_card,
      DxChart,
      DxSeries,
      DxPieChart,
   
    DxLegend,
    DxLabel,
    DxConnector,
    DxFont,
    DxExport
    },
    props: {
      chartId: {
        type: String,
        default: 'doughnut-chart'
      },
      chartId2: {
        type: String,
        default: 'bar-chart'
      },
      cssClasses: {
        default: 'ma',
        type: String
      },
      width: {
        type: Number,
        default: 500
      },
      height: {
        type: Number,
        default: 500
      }
    },
    data() {
      return {
        dataSource2: [{
  country: 'USA',
  medals: 110,
}, {
  country: 'China',
  medals: 100,
}, {
  country: 'Russia',
  medals: 72,
}, {
  country: 'Britain',
  medals: 47,
}, {
  country: 'Australia',
  medals: 46,
}, {
  country: 'Germany',
  medals: 41,
}, {
  country: 'France',
  medals: 40,
}, {
  country: 'South Korea',
  medals: 31,
}],
        govs: [],
        DashbourdCounts: [],
        data: [],
       
        chartData: {
          labels: [],
          datasets: [{
            label: 'Ratings by date',
            data: [10],
            backgroundColor: '#f87979'
          }]
        },
        chartData2: {
          labels: [],
          datasets: [{
            label: 'Ratings by date',
            data: [10],
            backgroundColor: '#f87979'
          }],
        },
        chartOptions: {
          responsive: false
        }
      }
    },
    created() {
      this.getCase_number_stats();
      this.getDashbourdCounts();
      this.get_stats2();
    },
    methods: {
      formatLabel(pointInfo) {
      return `${pointInfo.valueText} (${pointInfo.percentText})`;
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

            res

            // res.data.data.forEach(element => {

            //   this.chartData.labels.unshift(element.name_ar)
            //   this.chartData.datasets[0].data.unshift(element.case_count)
            //   this.chartData.datasets[0].backgroundColor.unshift("#" + Math.floor(Math.random() * 16777215)
            //     .toString(7))
            // });




          })
          .catch((err) => {
            err
          })
          .finally(() => {
            this.loading4 = false;
          });
      },
      theFormat(number) {
        return number.toFixed(0);
      },
      get_stats() {
        // axios.get("jops/get_stats", {
        // headers: {
        //     "Content-Type": "application/json",
        //     Accept: "application/json",
        //     Authorizations: "Bearer " + this.$store.state.AdminInfo.token
        // }
        // }).then(res => {
        //   res
        //     // res.data.data.forEach(element => {
        //     //     this.chartData.labels.unshift(element.title)
        //     //     this.chartData.datasets[0].data.unshift(element.count)
        //     //     this.chartData.datasets[0].backgroundColor.unshift("#" + Math.floor(Math.random()*16777215).toString(16))
        //     // });
        // })
      },
      getDashbourdCounts() {
        this.axios.get("cases/getDashbourdCounts", {
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: "Bearer " + this.$store.state.AdminInfo.token
            }
          })
          .then(res => {
            // this.loading = false;
            this.DashbourdCounts = res.data;


          })
          .catch(() => {
            this.loading = false;
          });
      },
      get_stats2() {
        axios.get("cases/getCaseCategoriesCounts", {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: "Bearer " + this.$store.state.AdminInfo.token,
          }
        }).then(res => {
          res
          // res.data.data.forEach(element => {
          //   this.chartData2.labels.unshift(element.name_ar)
          //   this.chartData2.datasets[0].data.unshift(element.case_count)
          //   this.chartData2.datasets[0].backgroundColor.unshift("#" + Math.floor(Math.random() * 16777215)
          //     .toString(8))
          // });
        })
      },
    }
  }
</script>

<style scoped>
  .ma {
    display: flex;
    justify-content: center;
  }
  #pie {
  height: 440px;
}
</style>