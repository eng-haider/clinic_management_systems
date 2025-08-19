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

    <v-layout row wrap>
      <v-flex  md6 sm6 xs12 pt-4>
        <v-card>
          <canvas id="pie-chart" ref="pieChart"></canvas>
        </v-card>
      </v-flex>

      <!-- <v-flex md-6 sm-6 xs12 pt-4>
        <v-card>
          <canvas id="accounts-chart" ref="accountsChart"></canvas>
        </v-card>
      </v-flex> -->
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
      DashbourdCounts: [],
      dataSource: [],
      loadingData: false,
      pieChart: null,
      barChart: null
    };
  },
  methods: {
    // Override loadInitialData from mixin
    async loadInitialData() {
      // Load all dashboard data concurrently for better performance
      await Promise.all([
        this.loadCaseStats(),
        this.loadDashboardCounts(),
        this.loadBookingData(),
        this.loadAccountsCounts()
      ]);
      
      // Create charts after all data is loaded
      this.$nextTick(() => {
        this.createCharts();
      });
    },

    async loadCaseStats() {
      try {
        const response = await this.axios.get("https://apismartclinicv3.tctate.com/api/cases/getCaseCategoriesCounts", {
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
        const response = await this.axios.get("https://apismartclinicv3.tctate.com/api/cases/getDashbourdCounts", {
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
        const response = await this.axios.get(`https://apismartclinicv3.tctate.com/api/patientsAccounsts/dash?page=1`, {
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
    }
  },
  mounted() {
    // Charts will be created automatically after data loads via mixin
    this.$nextTick(() => {
      this.createCharts();
    });
  },
  methods: {
    createCharts() {
      // Check if canvas elements exist before creating charts
      if (!this.$refs.barChart || !this.$refs.pieChart) {
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
          this.barChart.destroy(); // Destroy the existing bar chart if it exists
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

        // Pie Chart for Accounts
        const ctxPie = this.$refs.pieChart.getContext('2d');
        if (this.pieChart) {
          this.pieChart.destroy(); // Destroy the existing pie chart if it exists
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

      // Another Pie Chart (optional)
      const ctxAccounts = this.$refs.accountsChart.getContext('2d');
      if (this.accountsChart) {
        this.accountsChart.destroy(); // Destroy the existing accounts chart if it exists
      }
      this.accountsChart = new Chart(ctxAccounts, {
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
    },
    getBooking() {
      const today = new Date();
      const date = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
      this.axios.get(`https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=${date}_${date}.&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`, {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${this.$store.state.AdminInfo.tctate_token}`
        }
      })
      .then(res => {
        this.loadingData = false;
        this.booking = res.data.data;
      })
      .catch(() => {
        this.loadingData = false;
      });
    },
    getAccountsCounts() {

      this.axios.get(`https://apismartclinicv3.tctate.com/api/patientsAccounsts/dash?page=${this.current_page}`, {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
        }
      })
      .then(res => {
        this.loadingData = false;
        console.log(res.data); // Log the response data for verification
        this.accounts_statistic = res.data; // Ensure this matches your data structure
        this.$nextTick(() => {
          this.createCharts(); // Create charts after fetching data
        });
      })
      .catch(() => {
        this.loadingData = false;
      });
    },
    getCase_number_stats() {
      this.axios.get("cases/getCaseCategoriesCounts", {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
        }
      })
      .then(res => {
        this.dataSource = res.data.data;
        this.$nextTick(() => {
          this.createCharts(); // Recreate charts after data fetch
        });
      })
      .catch(err => {
        console.error(err);
      });
    },
    getDashbourdCounts() {
      this.axios.get("https://apismartclinicv3.tctate.com/api/cases/getDashbourdCounts", {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
        }
      })
      .then(res => {
        this.DashbourdCounts = res.data;
      })
      .catch(() => {
        this.loadingData = false;
      });
    },
  }
};
</script>

<style scoped>
canvas {
  width: 100% !important; /* Ensure canvas takes full width */
  height: 400px !important; /* Set a fixed height for better visibility */
}
.ma {
  display: flex;
  justify-content: center;
}
.sssaw {
  color: #075aa6 !important;
}
</style>
