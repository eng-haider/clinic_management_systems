import Vue from 'vue'
import Router from 'vue-router'
import store from './store'
import performanceMonitor from './utils/performanceMonitor'

Vue.use(Router)

// Static imports - no chunking
import Login from '@/views/pages/Login'
import Register from '@/views/pages/register'
import DashboardLayout from '@/views/dashboard/Index'
import PagesLayout from '@/views/pages/Index'
import Dashboard from '@/views/dashboard/Dashboard'
import CaseSheet from '@/views/dashboard/casesheet'
import Cases from '@/views/dashboard/cases'
import Patient from '@/views/dashboard/patient'
import Calendar from '@/views/dashboard/calendar'
import Recipe from '@/views/dashboard/Recipe'
import Reports from '@/views/dashboard/reports'
import Accounts from '@/views/dashboard/accounts'
import BillsReport from '@/views/dashboard/billsReport'
import CaseLap from '@/views/dashboard/CaseLap'
import Doctors from '@/views/dashboard/doctors'
import Profile from '@/views/dashboard/profile'
import Store from '@/views/dashboard/store'
import Error from '@/views/pages/Error'
import Recipes from '@/views/dashboard/recipes'
import Conjugations from '@/views/dashboard/Conjugations'
import ConjugationsCategories from '@/views/dashboard/ConjugationsCategories'
import XX from '@/views/dashboard/xx'
import RequestBookingTest from '@/views/dashboard/requestBooking_test'
import BirthDay from '@/views/dashboard/BirthDay'
import WaitingList from '@/views/dashboard/waitinglist'

const routeComponents = {
  Login,
  Register,
  DashboardLayout,
  PagesLayout,
  Dashboard,
  CaseSheet,
  Cases,
  Patient,
  Calendar,
  Recipe,
  Reports,
  Accounts,
  BillsReport,
  CaseLap,
  Doctors,
  Profile,
  Store,
  Error,
}

const router = new Router({
  mode: 'history',
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return new Promise(resolve => {
        setTimeout(() => resolve({ x: 0, y: 0 }), 150)
      })
    }
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
          component: routeComponents.Doctors
        },
        {
          path: 'case/:id',
          name: 'case',
          component: routeComponents.Casex,
          meta: { keepAlive: true }
        },
        {
          path: 'patient/:id',
          name: 'showCases',
          component: routeComponents.Patient,
          meta: { keepAlive: true }
        },

  // {
  //         path: 'patient',
  //         name: 'patient',
  //         component: () => import('@/views/dashboard/patient')
  //       },
       
        {
          path: 'recipes',
          name: 'recipes',
          component: Recipes
        },
        {
          path: 'conjugations',
          name: 'conjugations',
          component: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/Conjugations')
        },
        {
          path: 'ConjugationsCategories',
          name:'conjugationsCategories',
          component: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/ConjugationsCategories'),
        },
        {
          path: 'xx',
          name: 'XX',
          component: () => import(/* webpackChunkName: "admin" */ '@/views/dashboard/xx')
        },
        {
          path: 'store',
          name: 'store',
          component: routeComponents.Store
        },
        {
          path: 'cases',
          name: 'cases',
          component: routeComponents.Cases
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: routeComponents.Dashboard
        },
        {
          path:'requestBooking_test',
          name:'requestBooking_test',
          component: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/requestBooking_test'),
        },
        {
          path: 'profile',
          name: 'Profile',
          component: routeComponents.Profile
        },
        {
          path: 'birthday',
          name: 'birthday',
          component: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/BirthDay')
        },
        {
          path: 'reports',
          name: 'reports',
          component: routeComponents.Reports
        },
        {
          path: 'waitinglist',
          name: 'showWaitingList',
          component: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/waitinglist')
        },
        {
          path: 'case-lap',
          name: 'caseLap',
          component: routeComponents.CaseLap,
          meta: { 
            requiresPermission: 'show_lap_cases',
            title: 'حالات المختبر'
          }
        }
      ]
    }
  ]
})

// Enhanced route guards with global loading
let routeStartTime = null;

router.beforeEach(async (to, from, next) => {
  // Start timing route transition
  routeStartTime = performance.now();
  performanceMonitor.startTiming(`route-${to.name || to.path}`);
  
  // Show global loading for all routes
  if (window.globalLoading) {
    window.globalLoading.show();
  }
  
  // Check permissions if route requires them
  if (to.meta?.requiresPermission) {
    const userPermissions = store.getters.userPermissions || [];
    const hasPermission = userPermissions.some(permission => 
      permission === to.meta.requiresPermission
    );
    
    // Special case for show_lap_cases - also check doctor show_lap field
    if (to.meta.requiresPermission === 'show_lap_cases') {
      const canShowLapCases = store.getters.canShowLapCases;
      if (!canShowLapCases) {
        next({ name: 'statistics' });
        return;
      }
    } else if (!hasPermission) {
      // Redirect to dashboard if user doesn't have permission
      next({ name: 'statistics' });
      return;
    }
  }
  
  // Update document title
  document.title = to.meta?.title || to.name || 'Smart Clinic';
  
  next();
});

router.afterEach((to, from) => {
  // End timing route transition
  if (routeStartTime) {
    const duration = performance.now() - routeStartTime;
    performanceMonitor.endTiming(`route-${to.name || to.path}`, 'routeTransitions');
    performanceMonitor.trackRouteTransition(from.name || from.path, to.name || to.path, duration);
  }
  
  // Hide global loading after a short delay to show completion
  setTimeout(() => {
    if (window.globalLoading) {
      window.globalLoading.hide();
    }
  }, 800);
  
  // Optimize for mobile devices with smooth scrolling
  if (window.innerWidth <= 768) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

export default router
