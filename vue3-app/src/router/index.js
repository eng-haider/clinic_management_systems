/**
 * Vue Router - Vue 3
 * إعدادات الراوتر
 * 
 * @author Clinic Management System
 * @version 3.0.0
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// ==================== Route Components ====================
const routes = [
  // Login Page
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/pages/Login.vue'),
    meta: { guest: true }
  },
  
  // Register Page
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/pages/Register.vue'),
    meta: { guest: true }
  },
  
  // Dashboard Layout
  {
    path: '/',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/Dashboard.vue')
      },
      {
        path: 'dashboard',
        name: 'DashboardAlt',
        component: () => import('@/views/dashboard/Dashboard.vue')
      }
    ]
  },
  
  // Catch all - Redirect to login
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
]

// ==================== Router Instance ====================
const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  }
})

// ==================== Navigation Guards ====================
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isAuthenticated
  
  // Update document title
  document.title = to.meta?.title || to.name || 'Clinic Management'
  
  // Check if route requires authentication
  if (to.meta?.requiresAuth && !isAuthenticated) {
    next({ name: 'Login' })
    return
  }
  
  // Redirect authenticated users away from guest pages
  if (to.meta?.guest && isAuthenticated) {
    next({ name: 'Dashboard' })
    return
  }
  
  next()
})

export default router
