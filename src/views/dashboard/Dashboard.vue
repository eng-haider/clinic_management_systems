<template>
  <v-container id="dashboard" fluid tag="section">
    <v-layout row wrap pt-4 style="padding-top: 20px !important;">
      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card name=" المراجعين" icon="fa-solid fa-hospital-user" text_color="#53D3F8" icon_color="#035aa6" :count="DashbourdCounts.patients" />
      </v-flex>

      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card name=" الحالات" icon="fa-light fa-tooth" text_color="#53D3F8" icon_color="#035aa6" :count="DashbourdCounts.Casesall" />
      </v-flex>

      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card name="  المنتهيه" icon="fa-solid fa-check" text_color="#53D3F8" icon_color="#035aa6" :count="DashbourdCounts.Casescompleted" />
      </v-flex>

      <v-flex xs6 md3 sm6 pr-2 pb-4 pt-4>
        <dash_card name="  غير المنتهيه" icon="fas fa-layer-group" text_color="#53D3F8" icon_color="#035aa6" :count="DashbourdCounts.Casesactive" />
      </v-flex>
    </v-layout>

    <v-layout row wrap pt-4>
      <v-flex md-6 sm12>
        <v-card pa-3>
          <canvas id="bar-chart" ref="barChart"></canvas>
        </v-card>
      </v-flex>

      <v-flex md-6 sm-6 xs12 pt-4>
        <v-card>
          <h2 style="text-align: center; font-family: 'Cairo' !important;" class="sssaw">مواعيد المراجعين اليوم</h2>
          <v-data-table :headers="headers" disable-filtering disable-sort :loading="loadingData" :page.sync="page" @page-count="pageCount = $event" hide-default-footer :items="booking"></v-data-table>
        </v-card>
      </v-flex>
    </v-layout>

    <v-layout row wrap pt-4>
      <v-flex md-3 sm6 xs12 pt-4>
        <v-card pa-3>
          <h2 style="text-align: center; font-family: 'Cairo' !important;" class="sssaw">مصادر المراجعين</h2>
          <canvas id="sources-chart" ref="sourcesChart"></canvas>
        </v-card>
      </v-flex>
    </v-layout>

    <!-- Loading Test Component for Testing -->
    <v-layout row wrap v-if="isDevelopment">
      <v-flex xs12>
        <LoadingTestComponent />
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import { Chart, registerables } from 'chart.js';
import dash_card from '../../components/core/dash_card.vue';

Chart.register(...registerables);

export default {
  components: {
    dash_card
  },
  data() {
    return {
      accounts_statistic: [],
      booking: [],
      headers: [
        { text: '#id', align: "start", value: "id" },
        { text: 'الاسم', align: "start", value: "user.full_name" },
        { text: 'رقم الهاتف', align: "start", value: "user.user_phone" },
        { text: 'الوقت', align: "start", value: "reservation_from_time" }
      ],
      DashbourdCounts: {
        patients: 0,
        Casesall: 0,
        Casescompleted: 0,
        Casesactive: 0
      },
      dataSource: [],
      loadingData: false,
      pieChart: null,
      barChart: null,
      sourcesChart: null,
      page: 1,
      pageCount: 0,
      isLoading: false, // Add loading state
      sourcesStatistics: []
    };
  },
  computed: {
    isDevelopment() {
      return process.env.NODE_ENV === 'development';
    }
  },
  mounted() {
    // Load initial data when component mounts
    this.loadInitialData();
  },
  methods: {
    // Override loadInitialData from mixin
    async loadInitialData() {
      if (this.isLoading) return; // Prevent multiple calls
      
      this.isLoading = true;
      try {
        // Load all dashboard data concurrently for better performance
        await Promise.all([
          this.loadCaseStats(),
          this.loadDashboardCounts(),
          this.loadBookingData(),
          this.loadAccountsCounts(),
          this.loadSourcesStatistics()
        ]);
        
        // Create charts after all data is loaded
        this.$nextTick(() => {
          this.createCharts();
        });
      } catch (error) {
        console.error('Error loading initial data:', error);
      } finally {
        this.isLoading = false;
      }
    },

    async loadCaseStats() {
      try {
        const response = await this.axios.get("https://hasan.tctate.com/api/cases/getCaseCategoriesCounts", {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
          }
        });
        this.dataSource = response.data.data;
      } catch (error) {
        console.error('Error loading case stats:', error);
      }
    },

    async loadDashboardCounts() {
      try {
        const response = await this.axios.get("https://hasan.tctate.com/api/cases/getDashbourdCounts", {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
          }
        });
        this.DashbourdCounts = response.data;
      } catch (error) {
        console.error('Error loading dashboard counts:', error);
      }
    },

    async loadBookingData() {
      try {
        const today = new Date();
        const date = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
        
        const response = await this.axios.get(`https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=${date}_${date}.&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.state.AdminInfo.tctate_token}`
          }
        });
        this.booking = response.data.data;
      } catch (error) {
        console.error('Error loading booking data:', error);
      }
    },

    async loadAccountsCounts() {
      try {
        const response = await this.axios.get(`/patientsAccounsts/dash?page=1`, {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
          }
        });
        this.accounts_statistic = response.data;
      } catch (error) {
        console.error('Error loading accounts data:', error);
      }
    },

    async loadSourcesStatistics() {
      try {
        const response = await this.axios.get("https://hasan.tctate.com/api/from-where-come/statistics", {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
          }
        });
        this.sourcesStatistics = response.data.data.sources_statistics;
      } catch (error) {
        console.error('Error loading sources statistics:', error);
      }
    },

    createCharts() {
      // Check if canvas elements exist before creating charts
      if (!this.$refs.barChart) {
        console.warn('Canvas elements not found, skipping chart creation');
        return;
      }

      // Check if data exists
      if (!this.dataSource || this.dataSource.length === 0) {
        console.warn('No data available for bar chart');
        return;
      }

      if (!this.accounts_statistic || this.accounts_statistic.length === 0) {
        console.warn('No data available for pie chart');
        return;
      }

      try {
        // Bar Chart
        const ctxBar = this.$refs.barChart.getContext('2d');
        if (this.barChart) {
          this.barChart.destroy();
        }
        this.barChart = new Chart(ctxBar, {
          type: 'bar',
          data: {
            labels: this.dataSource.map(item => item.name_ar),
            datasets: [{
              label: 'عدد الحالات',
              data: this.dataSource.map(item => item.case_count),
              backgroundColor: 'rgba(53, 113, 255, 0.6)',
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              }
            }
          }
        });

        // Sources Statistics Chart
        if (this.$refs.sourcesChart && this.sourcesStatistics && this.sourcesStatistics.length > 0) {
          const ctxSources = this.$refs.sourcesChart.getContext('2d');
          if (this.sourcesChart) {
            this.sourcesChart.destroy();
          }
          this.sourcesChart = new Chart(ctxSources, {
            type: 'doughnut',
            data: {
              labels: this.sourcesStatistics.map(item => item.name),
              datasets: [{
                label: 'عدد المراجعين',
                data: this.sourcesStatistics.map(item => item.patients_count),
                backgroundColor: [
                  'rgba(53, 113, 255, 0.8)',
                  'rgba(255, 99, 132, 0.8)',
                  'rgba(54, 162, 235, 0.8)',
                  'rgba(255, 206, 86, 0.8)',
                  'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                  'rgba(53, 113, 255, 1)',
                  'rgba(255, 99, 132, 1)',
                  'rgba(54, 162, 235, 1)',
                  'rgba(255, 206, 86, 1)',
                  'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 2
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  display: true,
                  position: 'bottom'
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      return context.label + ': ' + context.raw + ' مراجع';
                    }
                  }
                }
              }
            }
          });
        }

        // Pie Chart for Accounts
        const ctxPie = this.$refs.pieChart.getContext('2d');
        if (this.pieChart) {
          this.pieChart.destroy();
        }
        this.pieChart = new Chart(ctxPie, {
          type: 'pie',
          data: {
            labels: this.accounts_statistic.map(item => item.name),
            datasets: [{
              data: this.accounts_statistic.map(item => item.count),
              backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56']
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              }
            }
          }
        });
      } catch (error) {
        console.error('Error creating charts:', error);
      }
    }
  }
};
</script>

<style scoped>
canvas {
  width: 100% !important;
  height: 400px !important;
}
.ma {
  display: flex;
  justify-content: center;
}
.sssaw {
  color: #075aa6 !important;
}
</style>
.sssaw {
  color: #075aa6 !important;
}
</style>
