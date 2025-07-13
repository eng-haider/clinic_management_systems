/**
 * Global Loading Plugin for Vue.js
 * Simple loading overlay management
 */

const GlobalLoadingPlugin = {
  install(Vue, options = {}) {
    // Create a reactive loading state
    const loadingState = Vue.observable({
      isLoading: false
    })

    // Global loading API
    const globalLoading = {
      show() {
        loadingState.isLoading = true
      },
      hide() {
        loadingState.isLoading = false
      },
      get isLoading() {
        return loadingState.isLoading
      }
    }

    // Add to Vue prototype for component access
    Vue.prototype.$globalLoading = globalLoading

    // Add to window for global access
    if (typeof window !== 'undefined') {
      window.globalLoading = globalLoading
    }
  }
}

export default GlobalLoadingPlugin
