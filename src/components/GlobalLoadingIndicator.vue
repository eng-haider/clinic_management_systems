<template>
  <div class="global-loading-overlay" v-show="$loadingState.isLoading || $loadingState.routeTransition">
    <!-- Main loading overlay -->
    <div class="loading-backdrop">
      <div class="loading-container">
        <!-- Loading spinner -->
        <div class="loading-spinner">
          <div class="spinner-ring"></div>
          <div class="spinner-ring"></div>
          <div class="spinner-ring"></div>
        </div>
        
        <!-- Loading message -->
        <div class="loading-message">
          {{ $loadingState.loadingMessage }}
        </div>
        
        <!-- Progress bar -->
        <div class="progress-container" v-if="$loadingState.progress > 0">
          <div class="progress-bar">
            <div 
              class="progress-fill" 
              :style="{ width: $loadingState.progress + '%' }"
            ></div>
          </div>
          <div class="progress-text">{{ Math.round($loadingState.progress) }}%</div>
        </div>
        
        <!-- Loading Statistics (Development mode only) -->
        <div class="statistics-section" v-if="isDevelopment">
          <div class="statistics-toggle-button" @click="showStatistics = !showStatistics">
            <span class="statistics-toggle-text">{{ showStatistics ? 'Hide' : 'Show' }} Stats</span>
            <span class="statistics-toggle-icon">📊</span>
          </div>
          
          <div class="statistics-panel" v-if="showStatistics">
            <div class="statistics-header" @click="toggleStatistics">
              <span class="statistics-title">📊 Loading Statistics</span>
              <span class="statistics-toggle" :class="{ expanded: statisticsExpanded }">▼</span>
            </div>
            
            <div class="statistics-content" v-show="statisticsExpanded">
              <div class="statistics-grid">
                <div class="stat-item">
                  <div class="stat-label">Total Operations</div>
                  <div class="stat-value">{{ $loadingState.statistics.totalOperations }}</div>
                </div>
                <div class="stat-item">
                  <div class="stat-label">Active</div>
                  <div class="stat-value">{{ $loadingState.statistics.activeOperations }}</div>
                </div>
                <div class="stat-item">
                  <div class="stat-label">Completed</div>
                  <div class="stat-value">{{ $loadingState.statistics.completedOperations }}</div>
                </div>
                <div class="stat-item">
                  <div class="stat-label">Avg Time</div>
                  <div class="stat-value">{{ formatDuration($loadingState.statistics.averageLoadTime) }}</div>
                </div>
              </div>
              
              <div class="statistics-details" v-if="$loadingState.statistics.slowestOperation">
                <div class="stat-detail">
                  <span class="stat-label">Slowest:</span>
                  <span class="stat-value">{{ formatOperationName($loadingState.statistics.slowestOperation.name) }} ({{ formatDuration($loadingState.statistics.slowestOperation.duration) }})</span>
                </div>
                <div class="stat-detail" v-if="$loadingState.statistics.fastestOperation">
                  <span class="stat-label">Fastest:</span>
                  <span class="stat-value">{{ formatOperationName($loadingState.statistics.fastestOperation.name) }} ({{ formatDuration($loadingState.statistics.fastestOperation.duration) }})</span>
                </div>
              </div>
              
              <div class="recent-operations" v-if="$loadingState.statistics.recentOperations.length > 0">
                <div class="recent-header">Recent Operations</div>
                <div class="recent-list">
                  <div 
                    class="recent-item" 
                    v-for="operation in $loadingState.statistics.recentOperations.slice(0, 5)" 
                    :key="operation.id"
                  >
                    <span class="recent-name">{{ formatOperationName(operation.id) }}</span>
                    <span class="recent-duration">{{ formatDuration(operation.duration) }}</span>
                  </div>
                </div>
              </div>
              
              <div class="statistics-actions">
                <button class="stat-button" @click="resetStatistics">Reset Stats</button>
                <button class="stat-button" @click="logStatistics">Log to Console</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
    
    <!-- Error notifications -->
    <div class="error-notifications" v-if="$loadingState.errors.length > 0">
      <div 
        class="error-notification"
        v-for="error in $loadingState.errors"
        :key="error.id"
        @click="removeError(error.id)"
      >
        <div class="error-icon">⚠️</div>
        <div class="error-message">{{ error.message }}</div>
        <div class="error-close">×</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalLoadingIndicator',
  
  data() {
    return {
      statisticsExpanded: false,
      showStatistics: false // Start collapsed for cleaner UI
    }
  },
  
  computed: {
    isDevelopment() {
      return process.env.NODE_ENV === 'development'
    }
  },
  
  methods: {
    removeError(errorId) {
      this.$loadingState.errors = this.$loadingState.errors.filter(e => e.id !== errorId)
    },
    
    toggleStatistics() {
      this.statisticsExpanded = !this.statisticsExpanded
    },
    
    formatDuration(duration) {
      if (!duration || duration === 0) return '0ms'
      if (duration < 1000) return `${Math.round(duration)}ms`
      return `${(duration / 1000).toFixed(1)}s`
    },
    
    formatOperationName(operationId) {
      if (!operationId) return 'Unknown'
      // Remove 'loading_' prefix and clean up operation names
      const cleaned = operationId.replace('loading_', '').replace(/_/g, ' ')
      return cleaned.charAt(0).toUpperCase() + cleaned.slice(1)
    },
    
    resetStatistics() {
      if (this.$loading && this.$loading.resetStatistics) {
        this.$loading.resetStatistics()
      }
    },
    
    logStatistics() {
      if (this.$loading && this.$loading.getStatistics) {
        const stats = this.$loading.getStatistics()
        console.group('📊 Loading Statistics')
        console.table(stats)
        console.groupEnd()
      }
    }
  }
}
</script>

<style scoped>
.global-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  pointer-events: none;
}

.loading-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease;
}

.loading-container {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  text-align: center;
  min-width: 200px;
  max-width: 400px;
  animation: slideIn 0.3s ease;
}

.loading-spinner {
  position: relative;
  width: 60px;
  height: 60px;
  margin: 0 auto 1rem;
}

.spinner-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 3px solid transparent;
  border-top-color: #3F51B5;
  border-radius: 50%;
  animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.spinner-ring:nth-child(1) {
  animation-delay: -0.45s;
}

.spinner-ring:nth-child(2) {
  animation-delay: -0.3s;
  border-top-color: #673AB7;
}

.spinner-ring:nth-child(3) {
  animation-delay: -0.15s;
  border-top-color: #9C27B0;
}

.loading-message {
  font-size: 1.1rem;
  color: #333;
  margin-bottom: 1rem;
  font-weight: 500;
}

.progress-container {
  margin-top: 1rem;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: #e0e0e0;
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3F51B5, #673AB7);
  border-radius: 2px;
  transition: width 0.3s ease;
  position: relative;
}

.progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  animation: shimmer 1.5s infinite;
}

.progress-text {
  font-size: 0.9rem;
  color: #666;
  font-weight: 500;
}

/* Statistics Section */
.statistics-section {
  margin-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
  padding-top: 1rem;
}

.statistics-toggle-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border: 1px dashed #ccc;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.statistics-toggle-button:hover {
  border-color: #999;
  background-color: rgba(0, 0, 0, 0.05);
}

.statistics-toggle-text {
  font-weight: 500;
}

.statistics-toggle-icon {
  font-size: 1rem;
}

.statistics-panel {
  animation: slideDown 0.3s ease;
}

.statistics-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 0.5rem 0;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.statistics-header:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.statistics-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
}

.statistics-toggle {
  transition: transform 0.2s ease;
  font-size: 0.8rem;
  color: #666;
}

.statistics-toggle.expanded {
  transform: rotate(180deg);
}

.statistics-content {
  animation: slideDown 0.3s ease;
}

.statistics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  margin: 0.75rem 0;
}

.stat-item {
  background: rgba(63, 81, 181, 0.1);
  padding: 0.5rem;
  border-radius: 4px;
  text-align: center;
}

.stat-label {
  font-size: 0.7rem;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
}

.statistics-details {
  margin: 0.75rem 0;
  padding: 0.5rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}

.stat-detail {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
  font-size: 0.8rem;
}

.stat-detail:last-child {
  margin-bottom: 0;
}

.recent-operations {
  margin-top: 0.75rem;
}

.recent-header {
  font-size: 0.8rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.recent-list {
  max-height: 100px;
  overflow-y: auto;
}

.recent-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 3px;
  margin-bottom: 0.25rem;
  background: rgba(0, 0, 0, 0.03);
  font-size: 0.75rem;
}

.recent-name {
  flex: 1;
  color: #333;
}

.recent-duration {
  color: #666;
  font-weight: 500;
}

.statistics-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e0e0e0;
}

.stat-button {
  flex: 1;
  padding: 0.4rem 0.8rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
  color: #333;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.stat-button:hover {
  background: #f5f5f5;
  border-color: #bbb;
}

.stat-button:active {
  transform: translateY(1px);
}



.error-notifications {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  pointer-events: auto;
}

.error-notification {
  background: #f44336;
  color: white;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  min-width: 300px;
  max-width: 500px;
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
  cursor: pointer;
  animation: slideInRight 0.3s ease;
}

.error-notification:hover {
  background: #d32f2f;
}

.error-icon {
  margin-right: 0.75rem;
  font-size: 1.2rem;
}

.error-message {
  flex: 1;
  font-size: 0.9rem;
  line-height: 1.4;
}

.error-close {
  margin-left: 0.75rem;
  font-size: 1.5rem;
  font-weight: bold;
  opacity: 0.7;
}

.error-close:hover {
  opacity: 1;
}

/* Animations */
@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    max-height: 400px;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Responsive design */
@media (max-width: 768px) {
  .loading-container {
    padding: 1.5rem;
    min-width: 150px;
    margin: 0 1rem;
  }
  
  .loading-spinner {
    width: 50px;
    height: 50px;
  }
  
  .loading-message {
    font-size: 1rem;
  }
  
  .error-notification {
    min-width: 250px;
    margin: 0 1rem 0.5rem;
  }
}

/* Dark theme support */
@media (prefers-color-scheme: dark) {
  .loading-container {
    background: rgba(33, 33, 33, 0.95);
    color: #fff;
  }
  
  .loading-message {
    color: #fff;
  }
  
  .progress-text {
    color: #ccc;
  }
  
  .statistics-section {
    border-top-color: #555;
  }
  
  .statistics-toggle-button {
    border-color: #555;
    color: #ccc;
  }
  
  .statistics-toggle-button:hover {
    border-color: #777;
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .statistics-header:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .statistics-title {
    color: #fff;
  }
  
  .statistics-toggle {
    color: #ccc;
  }
  
  .stat-item {
    background: rgba(63, 81, 181, 0.2);
  }
  
  .stat-label {
    color: #ccc;
  }
  
  .stat-value {
    color: #fff;
  }
  
  .statistics-details {
    background: rgba(255, 255, 255, 0.1);
  }
  
  .recent-header {
    color: #fff;
  }
  
  .recent-item {
    background: rgba(255, 255, 255, 0.05);
  }
  
  .recent-name {
    color: #fff;
  }
  
  .recent-duration {
    color: #ccc;
  }
  
  .statistics-actions {
    border-top-color: #555;
  }
  
  .stat-button {
    background: rgba(255, 255, 255, 0.1);
    border-color: #555;
    color: #fff;
  }
  
  .stat-button:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: #777;
  }
}
</style>
