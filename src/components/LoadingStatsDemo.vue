<template>
  <div class="loading-demo">
    <h3>Loading Statistics Demo</h3>
    <div class="demo-controls">
      <button @click="simulateQuickLoad" class="demo-btn">Quick Load (0.5s)</button>
      <button @click="simulateSlowLoad" class="demo-btn">Slow Load (2s)</button>
      <button @click="simulateMultipleLoads" class="demo-btn">Multiple Loads</button>
      <button @click="simulateError" class="demo-btn">Simulate Error</button>
    </div>
    
    <div class="demo-info">
      <p>Open the global loading indicator to see statistics in development mode.</p>
      <p>Check the browser console for detailed performance logs.</p>
    </div>
    
    <div class="current-stats" v-if="isDevelopment">
      <h4>Current Statistics</h4>
      <pre>{{ JSON.stringify($loadingState.statistics, null, 2) }}</pre>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingStatsDemo',
  
  computed: {
    isDevelopment() {
      return process.env.NODE_ENV === 'development'
    }
  },
  
  methods: {
    async simulateQuickLoad() {
      await this.$withLoading(async () => {
        return new Promise(resolve => setTimeout(resolve, 500))
      }, 'Quick loading operation...')
    },
    
    async simulateSlowLoad() {
      await this.$withLoading(async () => {
        return new Promise(resolve => setTimeout(resolve, 2000))
      }, 'Slow loading operation...')
    },
    
    async simulateMultipleLoads() {
      const promises = []
      for (let i = 0; i < 3; i++) {
        promises.push(
          this.$withLoading(async () => {
            return new Promise(resolve => setTimeout(resolve, Math.random() * 1500 + 500))
          }, `Operation ${i + 1}...`)
        )
      }
      await Promise.all(promises)
    },
    
    async simulateError() {
      try {
        await this.$withLoading(async () => {
          return new Promise((resolve, reject) => {
            setTimeout(() => reject(new Error('Simulated error occurred')), 1000)
          })
        }, 'Loading with error...')
      } catch (error) {
        console.log('Error caught:', error.message)
      }
    }
  }
}
</script>

<style scoped>
.loading-demo {
  padding: 2rem;
  max-width: 600px;
  margin: 0 auto;
}

.demo-controls {
  display: flex;
  gap: 1rem;
  margin: 1rem 0;
  flex-wrap: wrap;
}

.demo-btn {
  padding: 0.75rem 1rem;
  border: 2px solid #3F51B5;
  border-radius: 4px;
  background: white;
  color: #3F51B5;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.demo-btn:hover {
  background: #3F51B5;
  color: white;
}

.demo-info {
  margin: 1.5rem 0;
  padding: 1rem;
  background: #f5f5f5;
  border-radius: 4px;
  border-left: 4px solid #3F51B5;
}

.current-stats {
  margin-top: 2rem;
  padding: 1rem;
  background: #f9f9f9;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.current-stats h4 {
  margin-top: 0;
  color: #333;
}

.current-stats pre {
  background: white;
  padding: 1rem;
  border-radius: 4px;
  border: 1px solid #ddd;
  overflow-x: auto;
  font-size: 0.8rem;
}
</style>
