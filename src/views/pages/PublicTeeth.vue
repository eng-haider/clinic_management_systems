<template>
  <div class="public-teeth-page">
    <div v-if="loading" class="loading-container">
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
      ></v-progress-circular>
      <p class="mt-4">Loading patient teeth data...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <v-icon color="error" size="64">mdi-alert-circle</v-icon>
      <p class="error-message">{{ error }}</p>
    </div>

    <div v-else class="teeth-container">
      <div class="patient-info" v-if="patientInfo">
        <h2>{{ patientInfo.name }}</h2>
        <p>Patient Code: {{ patientInfo.code }}</p>
      </div>
      
      <SimpleTeethDisplay
        :coloredParts="coloredParts"
      />
    </div>
  </div>
</template>

<script>
import SimpleTeethDisplay from '@/components/SimpleTeethDisplay.vue'
import axios from 'axios'

export default {
  name: 'PublicTeeth',
  components: {
    SimpleTeethDisplay
  },
  data() {
    return {
      loading: true,
      error: null,
      coloredParts: [],
      patientInfo: null
    }
  },
  mounted() {
    this.loadTeethData()
  },
  methods: {
    async loadTeethData() {
      try {
        this.loading = true
        this.error = null
        
        // Get patient code from URL parameter
        const code = this.$route.params.code
        
        if (!code) {
          this.error = 'Patient code is required'
          this.loading = false
          return
        }

        // Fetch patient colored parts data from API
        const response = await axios.post('https://smartclinicv5.tctate.com/api/public/patient/colored-parts', {
          code: code
        })
        
        if (response.data && response.data.success) {
          // Set patient info
          this.patientInfo = {
            code: code,
            name: 'Patient' // You can add patient name if available in the response
          }
          
          // Map the data to the format expected by SimpleTeethDisplay component
          this.coloredParts = response.data.colored_parts.map(item => ({
            toothId: item.tooth_id,
            toothNum: item.tooth_number,
            partId: item.part_id,
            color: item.color
          }))
        } else {
          this.error = response.data?.message || 'Patient not found or invalid code'
        }
        
        this.loading = false
      } catch (err) {
        console.error('Error loading teeth data:', err)
        if (err.response) {
          // Server responded with error
          this.error = err.response.data?.message || 'Failed to load patient data'
        } else if (err.request) {
          // Request was made but no response
          this.error = 'Unable to connect to server. Please check your connection.'
        } else {
          // Something else happened
          this.error = 'Failed to load teeth data. Please try again.'
        }
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.public-teeth-page {
  min-height: 100vh;
  background: #f5f5f5;

  display: flex;
  justify-content: center;
  align-items: center;
}

.loading-container,
.error-container {
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error-message {
  margin-top: 16px;
  color: #f44336;
  font-size: 16px;
}

.teeth-container {
  width: 100%;
  max-width: 1400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.patient-info {
  text-align: center;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.patient-info h2 {
  margin: 0 0 10px 0;
  color: #333;
}

.patient-info p {
  margin: 0;
  color: #666;
}

@media (max-width: 768px) {
  .public-teeth-page {
    padding: 10px;
  }
  
  .teeth-container {
    padding: 10px;
  }
}
</style>
