/**
 * Global Loading Plugin for Vue.js
 * Provides app-wide loading states and performance tracking
 */

import { cacheManager } from '@/utils/cache'
import { performanceManager } from '@/utils/performance'

const GlobalLoadingPlugin = {
  install(Vue, options = {}) {
    // Create a reactive loading state
    const loadingState = Vue.observable({
      isLoading: false,
      loadingMessage: 'Loading...',
      progress: 0,
      routeTransition: false,
      activeOperations: new Set(),
      errors: [],
      statistics: {
        totalOperations: 0,
        activeOperations: 0,
        completedOperations: 0,
        averageLoadTime: 0,
        slowestOperation: null,
        fastestOperation: null,
        totalTime: 0,
        recentOperations: []
      }
    })

    // Global loading manager
    const loadingManager = {
      // Start loading with optional message and operation ID
      start(message = 'Loading...', operationId = null) {
        const id = operationId || `operation_${Date.now()}_${Math.random()}`
        
        loadingState.activeOperations.add(id)
        loadingState.isLoading = true
        loadingState.loadingMessage = message
        loadingState.progress = 0
        
        // Start performance tracking
        performanceManager.startTiming(`loading_${id}`)
        
        return id
      },

      // Update loading progress
      updateProgress(progress, message = null) {
        loadingState.progress = Math.max(0, Math.min(100, progress))
        if (message) loadingState.loadingMessage = message
      },

      // Stop loading for specific operation
      stop(operationId) {
        if (operationId) {
          loadingState.activeOperations.delete(operationId)
          const duration = performanceManager.endTiming(`loading_${operationId}`)
          
          // Update statistics
          this.updateStatistics(operationId, duration)
        }
        
        // Stop global loading only if no active operations
        if (loadingState.activeOperations.size === 0) {
          loadingState.isLoading = false
          loadingState.progress = 100
          
          // Auto-hide after brief delay
          setTimeout(() => {
            if (loadingState.activeOperations.size === 0) {
              loadingState.progress = 0
              loadingState.loadingMessage = 'Loading...'
            }
          }, 500)
        }
      },

      // Update loading statistics
      updateStatistics(operationId, duration) {
        const stats = loadingState.statistics
        const perfStats = performanceManager.getStatistics()
        
        // Update basic counters
        stats.totalOperations = perfStats.totalOperations
        stats.activeOperations = loadingState.activeOperations.size
        stats.completedOperations = perfStats.completedOperations
        stats.averageLoadTime = perfStats.averageLoadTime
        stats.totalTime = perfStats.totalTime
        
        // Update fastest/slowest operations
        if (perfStats.fastestOperation) {
          stats.fastestOperation = {
            name: perfStats.fastestOperation.name,
            duration: perfStats.fastestOperation.duration
          }
        }
        
        if (perfStats.slowestOperation) {
          stats.slowestOperation = {
            name: perfStats.slowestOperation.name,
            duration: perfStats.slowestOperation.duration
          }
        }
        
        // Add to recent operations (keep last 10)
        if (duration && operationId) {
          stats.recentOperations.unshift({
            id: operationId,
            duration: duration,
            timestamp: new Date().toISOString()
          })
          
          // Keep only last 10 operations
          if (stats.recentOperations.length > 10) {
            stats.recentOperations = stats.recentOperations.slice(0, 10)
          }
        }
      },

      // Force stop all loading
      stopAll() {
        loadingState.activeOperations.clear()
        loadingState.isLoading = false
        loadingState.progress = 0
        loadingState.loadingMessage = 'Loading...'
        loadingState.routeTransition = false
      },

      // Add error
      addError(error) {
        loadingState.errors.push({
          message: error.message || error,
          timestamp: new Date(),
          id: Math.random().toString(36).substr(2, 9)
        })
        
        // Auto-remove errors after 5 seconds
        setTimeout(() => {
          loadingState.errors = loadingState.errors.filter(e => e.id !== loadingState.errors[loadingState.errors.length - 1]?.id)
        }, 5000)
      },

      // Clear errors
      clearErrors() {
        loadingState.errors = []
      },

      // Get current statistics
      getStatistics() {
        return { ...loadingState.statistics }
      },

      // Reset statistics
      resetStatistics() {
        loadingState.statistics = {
          totalOperations: 0,
          activeOperations: 0,
          completedOperations: 0,
          averageLoadTime: 0,
          slowestOperation: null,
          fastestOperation: null,
          totalTime: 0,
          recentOperations: []
        }
        performanceManager.metrics = {}
      }
    }

    // Route loading handler
    const handleRouteLoading = (router) => {
      router.beforeEach((to, from, next) => {
        loadingState.routeTransition = true
        const operationId = loadingManager.start(`Loading ${to.name || 'page'}...`, 'route_transition')
        
        // Store operation ID for cleanup
        to.meta = to.meta || {}
        to.meta._loadingId = operationId
        
        next()
      })

      router.afterEach((to) => {
        // Small delay to ensure component is mounted
        setTimeout(() => {
          if (to.meta?._loadingId) {
            loadingManager.stop(to.meta._loadingId)
          }
          loadingState.routeTransition = false
        }, 100)
      })
    }

    // Add to Vue prototype for global access
    Vue.prototype.$loading = loadingManager
    Vue.prototype.$loadingState = loadingState

    // Add global mixin for automatic loading management
    Vue.mixin({
      data() {
        return {
          componentLoadingIds: new Set()
        }
      },

      methods: {
        // Convenient method for loading operations
        async $withLoading(asyncOperation, message = 'Processing...') {
          const operationId = this.$loading.start(message)
          this.componentLoadingIds.add(operationId)
          
          try {
            const result = await asyncOperation()
            return result
          } catch (error) {
            this.$loading.addError(error)
            throw error
          } finally {
            this.$loading.stop(operationId)
            this.componentLoadingIds.delete(operationId)
          }
        },

        // Method for cached API calls with loading
        async $cachedApiCall(key, apiCall, ttl = 300000, message = 'Loading data...') {
          // Check cache first
          const cached = cacheManager.get(key)
          if (cached) {
            return cached
          }

          // If not cached, show loading and fetch
          return await this.$withLoading(async () => {
            const result = await apiCall()
            cacheManager.set(key, result, ttl)
            return result
          }, message)
        }
      },

      beforeDestroy() {
        // Clean up any loading operations when component is destroyed
        this.componentLoadingIds.forEach(id => {
          this.$loading.stop(id)
        })
        this.componentLoadingIds.clear()
      }
    })

    // Auto-setup router if provided
    if (options.router) {
      handleRouteLoading(options.router)
    }

    // Make loading manager available globally
    Vue.prototype.$globalLoading = loadingManager
    
    // Add to window for debugging
    if (process.env.NODE_ENV === 'development') {
      window.__loadingManager = loadingManager
      window.__loadingState = loadingState
    }
  }
}

export default GlobalLoadingPlugin
