/**
 * API Client - Axios Instance
 * 
 * @author Clinic Management System
 * @version 3.0.0
 */

import axios from 'axios'

const BASE_URL = 'https://mina-api.tctate.com/api'

const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request Interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('tokinn')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    const lang = localStorage.getItem('lang') || 'ar'
    config.headers['Accept-Language'] = lang
    
    return config
  },
  (error) => Promise.reject(error)
)

// Response Interceptor
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('tokinn')
      localStorage.removeItem('tctate_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default apiClient
