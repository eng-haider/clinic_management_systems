/**
 * Performance monitoring and optimization utilities
 */
class PerformanceManager {
  constructor() {
    this.metrics = {};
    this.observers = [];
  }

  /**
   * Start performance measurement
   * @param {string} name - Metric name
   */
  startMeasure(name) {
    if (window.performance && window.performance.mark) {
      window.performance.mark(`${name}-start`);
    }
    this.metrics[name] = {
      startTime: Date.now(),
      endTime: null,
      duration: null
    };
  }

  /**
   * End performance measurement
   * @param {string} name - Metric name
   * @returns {number} - Duration in milliseconds
   */
  endMeasure(name) {
    const metric = this.metrics[name];
    if (!metric) {
      console.warn(`⚠️ No measurement started for: ${name}`);
      return 0;
    }

    metric.endTime = Date.now();
    metric.duration = metric.endTime - metric.startTime;

    if (window.performance && window.performance.mark && window.performance.measure) {
      window.performance.mark(`${name}-end`);
      window.performance.measure(name, `${name}-start`, `${name}-end`);
    }

    console.log(`⏱️ ${name}: ${metric.duration}ms`);
    return metric.duration;
  }

  /**
   * Debounce function calls
   * @param {Function} func - Function to debounce
   * @param {number} wait - Wait time in milliseconds
   * @param {boolean} immediate - Execute immediately
   * @returns {Function} - Debounced function
   */
  debounce(func, wait = 300, immediate = false) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        timeout = null;
        if (!immediate) func(...args);
      };
      const callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func(...args);
    };
  }

  /**
   * Throttle function calls
   * @param {Function} func - Function to throttle
   * @param {number} limit - Limit in milliseconds
   * @returns {Function} - Throttled function
   */
  throttle(func, limit = 100) {
    let inThrottle;
    return function executedFunction(...args) {
      if (!inThrottle) {
        func.apply(this, args);
        inThrottle = true;
        setTimeout(() => inThrottle = false, limit);
      }
    };
  }

  /**
   * Lazy load images
   * @param {string} selector - Image selector
   */
  lazyLoadImages(selector = 'img[data-src]') {
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            observer.unobserve(img);
          }
        });
      });

      document.querySelectorAll(selector).forEach(img => {
        imageObserver.observe(img);
      });
    }
  }

  /**
   * Check if element is in viewport
   * @param {Element} element - DOM element
   * @returns {boolean}
   */
  isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  /**
   * Get memory usage info
   * @returns {object}
   */
  getMemoryUsage() {
    if (window.performance && window.performance.memory) {
      return {
        usedJSHeapSize: window.performance.memory.usedJSHeapSize,
        totalJSHeapSize: window.performance.memory.totalJSHeapSize,
        jsHeapSizeLimit: window.performance.memory.jsHeapSizeLimit
      };
    }
    return null;
  }

  /**
   * Start timing measurement (alias for startMeasure)
   * @param {string} name - Metric name
   */
  startTiming(name) {
    return this.startMeasure(name);
  }

  /**
   * End timing measurement (alias for endMeasure)
   * @param {string} name - Metric name
   * @returns {number} - Duration in milliseconds
   */
  endTiming(name) {
    return this.endMeasure(name);
  }

  /**
   * Get all metrics as statistics
   * @returns {object}
   */
  getStatistics() {
    const stats = {
      totalOperations: Object.keys(this.metrics).length,
      activeOperations: Object.keys(this.metrics).filter(key => 
        this.metrics[key].duration === null
      ).length,
      completedOperations: Object.keys(this.metrics).filter(key => 
        this.metrics[key].duration !== null
      ).length,
      averageLoadTime: 0,
      slowestOperation: null,
      fastestOperation: null,
      totalTime: 0
    };

    const completedMetrics = Object.entries(this.metrics)
      .filter(([, metric]) => metric.duration !== null)
      .map(([name, metric]) => ({ name, ...metric }));

    if (completedMetrics.length > 0) {
      stats.totalTime = completedMetrics.reduce((sum, metric) => sum + metric.duration, 0);
      stats.averageLoadTime = stats.totalTime / completedMetrics.length;
      
      const sortedByDuration = completedMetrics.sort((a, b) => a.duration - b.duration);
      stats.fastestOperation = sortedByDuration[0];
      stats.slowestOperation = sortedByDuration[sortedByDuration.length - 1];
    }

    return stats;
  }

  /**
   * Log performance metrics
   */
  logMetrics() {
    console.group('📊 Performance Metrics');
    Object.entries(this.metrics).forEach(([name, metric]) => {
      if (metric.duration !== null) {
        console.log(`${name}: ${metric.duration}ms`);
      }
    });
    
    const memory = this.getMemoryUsage();
    if (memory) {
      console.log(`Memory Used: ${(memory.usedJSHeapSize / 1024 / 1024).toFixed(2)} MB`);
    }
    console.groupEnd();
  }
}

// Create singleton instance
const performanceManager = new PerformanceManager();

export default performanceManager;
