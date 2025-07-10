import Vue from 'vue'
import Router from 'vue-router'
import store from './store'

Vue.use(Router)

// Route component imports with simpler chunk naming
const routeComponents = {
  // Auth pages
  Login: () => import(/* webpackChunkName: "auth" */ '@/views/pages/Login'),
  Register: () => import(/* webpackChunkName: "auth" */ '@/views/pages/register'),
  
  // Layouts
  DashboardLayout: () => import(/* webpackChunkName: "layout" */ '@/views/dashboard/Index'),
  PagesLayout: () => import(/* webpackChunkName: "layout" */ '@/views/pages/Index'),
  
  // Main dashboard views - using simpler chunk names
  Dashboard: () => import(/* webpackChunkName: "dashboard" */ '@/views/dashboard/Dashboard'),
  CaseSheet: () => import(/* webpackChunkName: "casesheet" */ '@/views/dashboard/casesheet'),
  Cases: () => import(/* webpackChunkName: "cases" */ '@/views/dashboard/cases'),
  Patient: () => import(/* webpackChunkName: "patient" */ '@/views/dashboard/patient'),
  Calendar: () => import(/* webpackChunkName: "calendar" */ '@/views/dashboard/calendar'),
  Recipe: () => import(/* webpackChunkName: "recipe" */ '@/views/dashboard/Recipe'),
  Reports: () => import(/* webpackChunkName: "reports" */ '@/views/dashboard/reports'),
  Accounts: () => import(/* webpackChunkName: "accounts" */ '@/views/dashboard/accounts'),
  BillsReport: () => import(/* webpackChunkName: "bills" */ '@/views/dashboard/billsReport'),
  
  // Error pages
  Error: () => import(/* webpackChunkName: "error" */ '@/views/pages/Error'),
}

const router = new Router({
  mode: 'history',
  scrollBehavior() {
    return new Promise(resolve => {
      setTimeout(() => resolve({ x: 0, y: 0 }), 300)
    })
  },
  routes: [
    {
      path: '/login',
      component: routeComponents.PagesLayout,
      children: [
        {
          path: '',
          name: 'Login',
          component: routeComponents.Login
        }
      ]
    },
    {
      path: '/register',
      component: routeComponents.Register,
      children: [
        {
          path: '',
          name: 'Register',
          component: routeComponents.Register
        }
      ]
    },
    {
      path: '/',
      component: routeComponents.DashboardLayout,
      beforeEnter: (to, from, next) => {
        const isAuthenticated = localStorage.getItem('tokinn') && store?.state?.AdminInfo?.authe === true
        next(isAuthenticated ? true : { path: '/register' })
      },
      children: [
        {
          path: '',
          name: 'statistics',
          component: routeComponents.Dashboard
        },
        {
          path: 'patients',
          name: 'casesheet',
          component: routeComponents.CaseSheet
        },
        {
          path: 'accounts',
          name: 'accounts',
          component: routeComponents.Accounts
        },
        {
          path: 'doctors',
          name: 'doctors',
          component: () => import('@/views/dashboard/doctors')
        },
        {
          path: 'case/:id',
          name: 'case',
          component: () => import('@/views/dashboard/casex')
        },
        {
          path: 'patient/:id',
          name: 'showCases',
          component: () => import('@/views/dashboard/patient')
        },

  // {
  //         path: 'patient',
  //         name: 'patient',
  //         component: () => import('@/views/dashboard/patient')
  //       },
       
        {
          path: 'recipes',
          name: 'recipes',
          component: () => import('@/views/dashboard/recipes')
        },
        {
          path: 'conjugations',
          name: 'conjugations',
          component: () => import('@/views/dashboard/Conjugations')
        },
        {
          path: 'ConjugationsCategories',
          name:'conjugationsCategories',
          component: () => import('@/views/dashboard/ConjugationsCategories'),
        },
        {
          path: 'xx',
          name: 'XX',
          component: () => import('@/views/dashboard/xx')
        },
        {
          path: 'store',
          name: 'store',
          component: () => import('@/views/dashboard/store')
        },
        {
          path: 'cases',
          name: 'cases',
          component: routeComponents.Cases
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('@/views/dashboard/Dashboard')
        },
        {
          path:'requestBooking_test',
          name:'requestBooking_test',
          component: () => import('@/views/dashboard/requestBooking_test'),
          
        },
        
        {
          path: 'profile',
          name: 'Profile',
          component: () => import('@/views/dashboard/profile')
        },
        {
          path: 'birthday',
          name: 'birthday',
          component: () => import('@/views/dashboard/BirthDay')
        },
        {
          path: 'reports',
          name: 'reports',
          component: () => import('@/views/dashboard/reports')
        },
        {
          path: 'waitinglist',
          name: 'showWaitingList',
          component: () => import('@/views/dashboard/waitinglist')
        }
      ]
    }
  ]
})

// Update document title
router.beforeEach((to, from, next) => {
  document.title = to.name || 'Smart Clinic'
  next()
})

export default router
