import Vue from 'vue'
import axios from 'axios'
import VueAxios from "vue-axios";
import router from './router'

// Vue Axios setup
Vue.use(VueAxios, axios);
Vue.config.silent = true
Vue.config.productionTip = false

// Base URL configuration - Use environment variable or fallback to production
const baseURL = 'https://smartclinicv5.tctate.com/api/';

// Force set the base URL multiple ways to ensure it takes effect
axios.defaults.baseURL = baseURL;
axios.defaults.headers.common['X-Base-URL'] = baseURL; // Debug header

// Override any existing baseURL
if (axios.defaults.baseURL !== baseURL) {
  console.warn('⚠️ BaseURL was different, forcing update from:', axios.defaults.baseURL, 'to:', baseURL);
  axios.defaults.baseURL = baseURL;
}

console.log('🌐 API Base URL (FROM ENV):', baseURL);
console.log('🌐 Environment Variable VUE_APP_URL:', process.env.VUE_APP_URL);
console.log('🔧 Axios Default Base URL:', axios.defaults.baseURL);
console.log('🔧 Environment:', process.env.VUE_APP_ENVIRONMENT || 'production');

// Additional verification
if (axios.defaults.baseURL !== baseURL) {
  console.error('❌ CRITICAL: Base URL not set correctly!', {
    expected: baseURL,
    actual: axios.defaults.baseURL
  });
}

// Default headers
axios.defaults.headers.get['Accepts'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Request timeout (30 seconds)
axios.defaults.timeout = 30000;
// Request interceptor for authentication and performance monitoring
axios.interceptors.request.use(
  config => {
    // Debug: Log the full URL being called with more details
    console.log('📡 API Request:', config.method?.toUpperCase(), config.url);
    console.log('🔗 Full URL:', (config.baseURL || '') + (config.url || ''));
    console.log('🏠 BaseURL:', config.baseURL);
    console.log('🎯 Endpoint:', config.url);
    
    // If we detect localhost, throw an error to catch it
    const fullUrl = (config.baseURL || '') + (config.url || '');
    if (fullUrl.includes('localhost:8080')) {
      console.error('🚨 LOCALHOST DETECTED IN REQUEST!', {
        fullUrl,
        baseURL: config.baseURL,
        url: config.url,
        stackTrace: new Error().stack
      });
      // Don't throw error, just log it for debugging
    }
    
    // Add authentication token
    const token = localStorage.getItem("tokinn");
    if (token) {
      config.headers.common.Authorization = `Bearer ${token}`;
    }
    
    // Add performance monitoring
    config.metadata = { startTime: performance.now() };
    
    // Add language header if available
    const language = localStorage.getItem('language') || 'ar';
    config.headers.common['Accept-Language'] = language;
    
    return config;
  },
  error => Promise.reject(error)
);
// Response interceptor for error handling and performance monitoring
axios.interceptors.response.use(
  response => {
    // Calculate request duration
    if (response.config.metadata) {
      const duration = performance.now() - response.config.metadata.startTime;
      
      // Log slow requests (>2 seconds)
      if (duration > 2000) {
        console.warn(`🐌 Slow API call: ${response.config.url} took ${duration.toFixed(2)}ms`);
      }
      
      // Track API performance if monitor is available
      if (window.performanceMonitor) {
        window.performanceMonitor.trackApiCall(response.config.url, duration, true);
      }
    }
    
    return response;
  },
  error => {
    // Calculate request duration for failed requests
    if (error.config && error.config.metadata) {
      const duration = performance.now() - error.config.metadata.startTime;
      
      // Track failed API performance if monitor is available
      if (window.performanceMonitor) {
        window.performanceMonitor.trackApiCall(error.config.url, duration, false);
      }
    }
    
    // Handle authentication errors
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      console.warn('🔐 Authentication error, user may need to login again');
      
      // Clear invalid token
      localStorage.removeItem('tokinn');
      
      // Redirect to login if not already there
      if (router.currentRoute.name !== 'Login') {
        router.push({ name: 'Login' });
      }
    }
    
    // Handle server errors
    if (error.response && error.response.status >= 500) {
      console.error('🚨 Server error:', error.response.status, error.response.data);
    }
    
    return Promise.reject(error);
  }
);

// Make axios available globally
window.axios = axios;

// Export axios for direct use if needed
export default axios;