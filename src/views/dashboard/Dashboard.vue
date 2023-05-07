<template>
  <v-container id="dashboard" fluid tag="section">
    <template>


      <v-layout row wrap pt-4>
        <v-flex xs12 md3 sm6 pr-2 pb-4>


          <dash_card name="عدد المراجعين" icon="fa-solid fa-hospital-user" text_color="#53D3F8" icon_color="#035aa6"
            :count="DashbourdCounts.patients">

          </dash_card>

        </v-flex>


        <v-flex xs12 md3 sm6 pr-2 pb-4 >

          <dash_card name="عدد الحالات" icon="fa-light fa-tooth" text_color="#53D3F8" icon_color="#035aa6"
            :count="DashbourdCounts.Casesall"></dash_card>
        </v-flex>

        <v-flex xs12 md3 sm6 pr-2 pb-4>

          <dash_card name="  اعدد الحالات المنتهيه" icon="fa-solid fa-check" text_color="#53D3F8" icon_color="#035aa6"
            :count="DashbourdCounts.Casescompleted
"></dash_card>





        </v-flex>

        <v-flex xs12 md3 sm6 pr-2 pb-4>

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

       




      </v-layout>



      <v-layout row wrap>
        <v-flex md-6 sm-6 xs12 pt-4>
<v-card>
  <DxPieChart
    id="pie"
    :data-source="dataSource"
    palette="Bright"
    title="احصائيات عدد الحالات"
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



        <v-flex md-6 sm-6 xs12 pt-4>
<v-card>
  <DxPieChart
    id="pie"
    :data-source="accounts_statistic"
    palette="Bright"
    title="احصائيات المبالغ المدفوعه والمتبقيه"
  >
    <DxSeries
      argument-field="name"
      value-field="count"
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
        accounts_statistic:[],
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
      this.getAccountsCounts();
      this.get_stats2();
    },
    methods: {
      getAccountsCounts(){
        setTimeout(() => 
        this.axios.get('/patientsAccounsts/dash?page=' + this.current_page, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: "Bearer " + this.$store.state.AdminInfo.token
              }
            })
            .then(res => {
              this.loadingData = false;
              this.accounts_statistic = res.data;
     
            
            
            })
            .catch(() => {
              this.loading = false;
            })
        
        , 700);
       
      
    },
    
      formatLabel(pointInfo) {
      return `${pointInfo.valueText} (${pointInfo.percentText})`;
    },
      getCase_number_stats() {

        setTimeout(() => this.axios
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

           



          })
          .catch((err) => {
            err
          })
          .finally(() => {
            this.loading4 = false;
          }), 500);
       
      },
      theFormat(number) {
        return number.toFixed(0);
      },
      get_stats() {
    
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