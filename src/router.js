import Vue from 'vue'
import Router from 'vue-router'
import store from './store'
import performanceMonitor from './utils/performanceMonitor'

Vue.use(Router)

// Enhanced route components with better caching and preloading
const routeComponents = {
  // Auth pages - Critical path, smaller chunks
  Login: () => import(/* webpackChunkName: "auth" */ '@/views/pages/Login'),
  Register: () => import(/* webpackChunkName: "auth" */ '@/views/pages/register'),
  
  // Layouts - High priority
  DashboardLayout: () => import(/* webpackChunkName: "layout" */ '@/views/dashboard/Index'),
  PagesLayout: () => import(/* webpackChunkName: "layout" */ '@/views/pages/Index'),
  
  // Core dashboard views - Optimized chunking
  Dashboard: () => import(/* webpackChunkName: "dashboard-core" */ '@/views/dashboard/Dashboard'),
  CaseSheet: () => import(/* webpackChunkName: "dashboard-core" */ '@/views/dashboard/casesheet'),
  Cases: () => import(/* webpackChunkName: "dashboard-core" */ '@/views/dashboard/cases'),
  Patient: () => import(/* webpackChunkName: "patient" */ '@/views/dashboard/patient'),
  
  // Secondary views - Separate chunks
  Calendar: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/calendar'),
  Recipe: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/Recipe'),
  Reports: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/reports'),
  Accounts: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/accounts'),
  BillsReport: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/billsReport'),
  
  // Administrative views - Separate chunk
  Doctors: () => import(/* webpackChunkName: "admin" */ '@/views/dashboard/doctors'),
  Profile: () => import(/* webpackChunkName: "admin" */ '@/views/dashboard/profile'),
  Store: () => import(/* webpackChunkName: "admin" */ '@/views/dashboard/store'),

  
  // Error pages
  Error: () => import(/* webpackChunkName: "error" */ '@/views/pages/Error'),
  // Add this to the routeComponents object
  CaseCategories: () => import(/* webpackChunkName: "admin" */ '@/views/dashboard/CaseCategories')
  
  // Add this route in the dashboard children array

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
          component: () => import(/* webpackChunkName: "case-detail" */ '@/views/dashboard/casex'),
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
          component: () => import(/* webpackChunkName: "features" */ '@/views/dashboard/recipes')
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
          path: 'case-categories',
          name: 'case-categories',
          component: routeComponents.CaseCategories
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
