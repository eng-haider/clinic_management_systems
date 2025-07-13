<template>
  <div v-if="isVisible" class="cases-loading-overlay">
    <div class="loading-backdrop" @click="$emit('close')"></div>
    <div class="loading-content">
      <div class="loading-spinner">
        <div class="spinner-ring"></div>
        <div class="spinner-ring"></div>
        <div class="spinner-ring"></div>
      </div>
      <h3 class="loading-title">{{ title }}</h3>
      <p class="loading-message">{{ message }}</p>
      <div class="loading-progress">
        <div class="progress-bar" :style="{ width: progress + '%' }"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CasesLoadingOverlay',
  props: {
    isVisible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'جاري تحميل الحالات'
    },
    message: {
      type: String,
      default: 'يرجى الانتظار...'
    },
    progress: {
      type: Number,
      default: 0
    }
  },
  emits: ['close'],
  mounted() {
    if (this.isVisible) {
      document.body.style.overflow = 'hidden'
    }
  },
  beforeDestroy() {
    document.body.style.overflow = ''
  },
  watch: {
    isVisible(newVal) {
      if (newVal) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = ''
      }
    }
  }
}
</script>

<style scoped>
.cases-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease-out;
  font-family: 'Cairo', sans-serif;
}

.loading-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: all 0.3s ease;
}

.loading-content {
  position: relative;
  background: rgba(255, 255, 255, 0.95);
  padding: 40px;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  min-width: 300px;
  max-width: 400px;
  animation: slideUp 0.4s ease-out;
}

.loading-spinner {
  position: relative;
  margin: 0 auto 20px;
  width: 60px;
  height: 60px;
}

.spinner-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 3px solid transparent;
  border-top: 3px solid #1976d2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.spinner-ring:nth-child(1) {
  animation-delay: 0s;
}

.spinner-ring:nth-child(2) {
  animation-delay: 0.3s;
  border-top-color: #42a5f5;
  transform: scale(0.8);
}

.spinner-ring:nth-child(3) {
  animation-delay: 0.6s;
  border-top-color: #90caf9;
  transform: scale(0.6);
}

.loading-title {
  margin: 0 0 10px 0;
  font-size: 1.4rem;
  font-weight: 600;
  color: #1976d2;
  direction: rtl;
}

.loading-message {
  margin: 0 0 20px 0;
  font-size: 1rem;
  color: #666;
  direction: rtl;
}

.loading-progress {
  width: 100%;
  height: 4px;
  background: #e0e0e0;
  border-radius: 2px;
  overflow: hidden;
  margin-top: 15px;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #1976d2, #42a5f5);
  border-radius: 2px;
  transition: width 0.3s ease;
  animation: shimmer 1.5s infinite;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@keyframes shimmer {
  0% {
    background-position: -200px 0;
  }
  100% {
    background-position: 200px 0;
  }
}

/* Mobile responsive */
@media (max-width: 768px) {
  .loading-content {
    margin: 20px;
    padding: 30px 20px;
    min-width: unset;
    max-width: unset;
  }
  
  .loading-title {
    font-size: 1.2rem;
  }
  
  .loading-message {
    font-size: 0.9rem;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .cases-loading-overlay,
  .loading-content,
  .spinner-ring,
  .progress-bar {
    animation: none;
  }
  
  .loading-backdrop {
    transition: none;
  }
}
</style>
