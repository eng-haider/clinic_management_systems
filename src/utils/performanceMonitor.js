/**
 * Simple Performance Monitoring Utility
 * Tracks page load times and user interactions
 */

class PerformanceMonitor {
  constructor() {
    this.metrics = {
      pageLoadTimes: {},
      routeTransitions: {},
      apiCalls: {},
      userInteractions: {}
    };
    
    this.startTimes = new Map();
    this.isEnabled = process.env.NODE_ENV === 'development';
  }

  /**
   * Start timing an operation
   * @param {string} operation - Operation name
   */
  startTiming(operation) {
    if (!this.isEnabled) return;
    
    this.startTimes.set(operation, performance.now());
  }

  /**
   * End timing an operation
   * @param {string} operation - Operation name
   * @param {string} category - Category for grouping
   */
  endTiming(operation, category = 'general') {
    if (!this.isEnabled) return;
    
    const startTime = this.startTimes.get(operation);
    if (!startTime) return;
    
    const duration = performance.now() - startTime;
    this.startTimes.delete(operation);
    
    if (!this.metrics[category]) {
      this.metrics[category] = {};
    }
    
    if (!this.metrics[category][operation]) {
      this.metrics[category][operation] = [];
    }
    
    this.metrics[category][operation].push(duration);
    
    // Log slow operations
    if (duration > 1000) {
      console.warn(`Slow operation detected: ${operation} took ${duration.toFixed(2)}ms`);
    }
  }

  /**
   * Track route transition
   * @param {string} from - From route
   * @param {string} to - To route
   * @param {number} duration - Duration in ms
   */
  trackRouteTransition(from, to, duration) {
    if (!this.isEnabled) return;
    
    const key = `${from} -> ${to}`;
    if (!this.metrics.routeTransitions[key]) {
      this.metrics.routeTransitions[key] = [];
    }
    
    this.metrics.routeTransitions[key].push(duration);
  }

  /**
   * Track API call performance
   * @param {string} endpoint - API endpoint
   * @param {number} duration - Duration in ms
   * @param {boolean} success - Whether call was successful
   */
  trackApiCall(endpoint, duration, success = true) {
    if (!this.isEnabled) return;
    
    if (!this.metrics.apiCalls[endpoint]) {
      this.metrics.apiCalls[endpoint] = {
        calls: [],
        successRate: 0,
        averageTime: 0
      };
    }
    
    this.metrics.apiCalls[endpoint].calls.push({
      duration,
      success,
      timestamp: Date.now()
    });
    
    // Calculate success rate and average time
    const calls = this.metrics.apiCalls[endpoint].calls;
    const successfulCalls = calls.filter(call => call.success);
    this.metrics.apiCalls[endpoint].successRate = (successfulCalls.length / calls.length) * 100;
    this.metrics.apiCalls[endpoint].averageTime = calls.reduce((sum, call) => sum + call.duration, 0) / calls.length;
  }

  /**
   * Get performance summary
   */
  getPerformanceSummary() {
    if (!this.isEnabled) return null;
    
    const summary = {
      totalMetrics: Object.keys(this.metrics).length,
      slowestRouteTransitions: this.getSlowOperations(this.metrics.routeTransitions),
      slowestApiCalls: this.getSlowApiCalls(),
      averagePageLoadTime: this.getAveragePageLoadTime(),
      recommendations: this.getRecommendations()
    };
    
    return summary;
  }

  /**
   * Get slow operations
   */
  getSlowOperations(operations) {
    const slow = [];
    
    for (const [operation, times] of Object.entries(operations)) {
      if (times.length > 0) {
        const avgTime = times.reduce((sum, time) => sum + time, 0) / times.length;
        if (avgTime > 500) { // Consider >500ms as slow
          slow.push({ operation, avgTime: avgTime.toFixed(2), count: times.length });
        }
      }
    }
    
    return slow.sort((a, b) => b.avgTime - a.avgTime);
  }

  /**
   * Get slow API calls
   */
  getSlowApiCalls() {
    const slow = [];
    
    for (const [endpoint, data] of Object.entries(this.metrics.apiCalls)) {
      if (data.averageTime > 1000) { // Consider >1s as slow
        slow.push({
          endpoint,
          averageTime: data.averageTime.toFixed(2),
          successRate: data.successRate.toFixed(1),
          totalCalls: data.calls.length
        });
      }
    }
    
    return slow.sort((a, b) => b.averageTime - a.averageTime);
  }

  /**
   * Get average page load time
   */
  getAveragePageLoadTime() {
    const allPageLoads = [];
    
    for (const times of Object.values(this.metrics.pageLoadTimes)) {
      if (Array.isArray(times)) {
        allPageLoads.push(...times);
      }
    }
    
    if (allPageLoads.length === 0) return 0;
    
    return (allPageLoads.reduce((sum, time) => sum + time, 0) / allPageLoads.length).toFixed(2);
  }

  /**
   * Get performance recommendations
   */
  getRecommendations() {
    const recommendations = [];
    
    // Check for slow route transitions
    const slowRoutes = this.getSlowOperations(this.metrics.routeTransitions);
    if (slowRoutes.length > 0) {
      recommendations.push(`Consider optimizing these slow routes: ${slowRoutes.map(r => r.operation).join(', ')}`);
    }
    
    // Check for slow API calls
    const slowApis = this.getSlowApiCalls();
    if (slowApis.length > 0) {
      recommendations.push(`Consider optimizing these slow API calls: ${slowApis.map(a => a.endpoint).join(', ')}`);
    }
    
    // Check average page load time
    const avgLoadTime = parseFloat(this.getAveragePageLoadTime());
    if (avgLoadTime > 2000) {
      recommendations.push('Consider implementing lazy loading or code splitting to improve page load times');
    }
    
    return recommendations;
  }

  /**
   * Clear all metrics
   */
  clearMetrics() {
    this.metrics = {
      pageLoadTimes: {},
      routeTransitions: {},
      apiCalls: {},
      userInteractions: {}
    };
    this.startTimes.clear();
  }

  /**
   * Log performance summary to console
   */
  logSummary() {
    if (!this.isEnabled) return;
    
    const summary = this.getPerformanceSummary();
    console.group('🚀 Performance Summary');
    console.log('Average Page Load Time:', summary.averagePageLoadTime + 'ms');
    
    if (summary.slowestRouteTransitions.length > 0) {
      console.log('Slowest Route Transitions:', summary.slowestRouteTransitions);
    }
    
    if (summary.slowestApiCalls.length > 0) {
      console.log('Slowest API Calls:', summary.slowestApiCalls);
    }
    
    if (summary.recommendations.length > 0) {
      console.log('Recommendations:', summary.recommendations);
    }
    
    console.groupEnd();
  }
}

// Create global instance
const performanceMonitor = new PerformanceMonitor();

// Auto-log summary every 5 minutes in development
if (process.env.NODE_ENV === 'development') {
  setInterval(() => {
    performanceMonitor.logSummary();
  }, 300000); // 5 minutes
}

export default performanceMonitor;
