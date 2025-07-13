/**
 * Global Loading Utility
 * Provides easy access to the global loading system from any component
 */

export const GlobalLoading = {
  /**
   * Show global loading
   * @param {string} title - Loading title
   * @param {string} message - Loading message
   * @param {boolean} showProgress - Whether to show progress bar
   */
  show(title = 'جاري التحميل', message = 'يرجى الانتظار...', showProgress = true) {
    if (window.globalLoading) {
      window.globalLoading.show(title, message, showProgress);
    }
  },

  /**
   * Hide global loading
   */
  hide() {
    if (window.globalLoading) {
      window.globalLoading.hide();
    }
  },

  /**
   * Update loading progress
   * @param {number} progress - Progress percentage (0-100)
   */
  updateProgress(progress) {
    if (window.globalLoading) {
      window.globalLoading.updateProgress(progress);
    }
  },

  /**
   * Update loading message
   * @param {string} message - New message
   */
  updateMessage(message) {
    if (window.globalLoading) {
      window.globalLoading.updateMessage(message);
    }
  },

  /**
   * Show loading for API calls
   * @param {string} endpoint - API endpoint name
   * @param {string} operation - Operation type (loading, saving, deleting, etc.)
   */
  showForApi(endpoint, operation = 'loading') {
    const messages = {
      loading: 'جاري التحميل...',
      saving: 'جاري الحفظ...',
      deleting: 'جاري الحذف...',
      updating: 'جاري التحديث...',
      creating: 'جاري الإنشاء...',
      uploading: 'جاري الرفع...',
      downloading: 'جاري التنزيل...'
    };

    const endpointMessages = {
      'cases': 'الحالات',
      'patients': 'المرضى',
      'doctors': 'الأطباء',
      'appointments': 'المواعيد',
      'reports': 'التقارير',
      'bills': 'الفواتير',
      'recipes': 'الوصفات',
      'categories': 'الفئات'
    };

    const entityName = endpointMessages[endpoint] || endpoint;
    const message = messages[operation] || messages.loading;
    
    this.show(`${operation === 'loading' ? 'جاري تحميل' : 'جاري معالجة'} ${entityName}`, message);
  },

  /**
   * Show loading with auto-hide after delay
   * @param {string} title - Loading title
   * @param {string} message - Loading message
   * @param {number} delay - Delay in milliseconds
   */
  showWithTimeout(title, message, delay = 2000) {
    this.show(title, message, false);
    setTimeout(() => {
      this.hide();
    }, delay);
  }
};

/**
 * Vue plugin installation
 */
export default {
  install(Vue) {
    Vue.prototype.$loading = GlobalLoading;
    Vue.prototype.$showLoading = GlobalLoading.show;
    Vue.prototype.$hideLoading = GlobalLoading.hide;
  }
};

/**
 * Mixin for easy use in components
 */
export const LoadingMixin = {
  methods: {
    showLoading(title, message, showProgress = true) {
      GlobalLoading.show(title, message, showProgress);
    },
    hideLoading() {
      GlobalLoading.hide();
    },
    updateLoadingProgress(progress) {
      GlobalLoading.updateProgress(progress);
    },
    updateLoadingMessage(message) {
      GlobalLoading.updateMessage(message);
    },
    showApiLoading(endpoint, operation = 'loading') {
      GlobalLoading.showForApi(endpoint, operation);
    }
  }
};
