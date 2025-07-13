<template>
  <div 
    v-if="isLoading" 
    class="loading-bar"
    :class="{ 'loading-bar--error': hasError }"
    :style="{ width: progress + '%' }"
  ></div>
</template>

<script>
export default {
  name: 'LoadingBar',
  data() {
    return {
      isLoading: false,
      progress: 0,
      hasError: false,
      timer: null
    }
  },
  mounted() {
    // Make this component globally accessible
    window.loadingBar = this
  },
  beforeDestroy() {
    if (this.timer) {
      clearInterval(this.timer)
    }
  },
  methods: {
    start() {
      this.isLoading = true
      this.hasError = false
      this.progress = 0
      
      this.timer = setInterval(() => {
        if (this.progress < 90) {
          this.progress += Math.random() * 10
        }
      }, 100)
    },
    
    finish() {
      this.progress = 100
      
      setTimeout(() => {
        this.isLoading = false
        this.progress = 0
        if (this.timer) {
          clearInterval(this.timer)
          this.timer = null
        }
      }, 200)
    },
    
    error() {
      this.hasError = true
      this.finish()
    }
  }
}
</script>

<style scoped>
.loading-bar {
  position: fixed;
  top: 0;
  left: 0;
  height: 3px;
  background: linear-gradient(90deg, #1976d2, #42a5f5);
  z-index: 9999;
  transition: width 0.2s ease;
  box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3);
}

.loading-bar--error {
  background: linear-gradient(90deg, #f44336, #ef5350);
  box-shadow: 0 2px 4px rgba(244, 67, 54, 0.3);
}

@media (prefers-reduced-motion: reduce) {
  .loading-bar {
    transition: none;
  }
}
</style>
