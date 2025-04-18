import Vue from 'vue'
import Router from 'vue-router'
import store from './store'

Vue.use(Router)

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
      component: () => import('@/views/pages/Index'),
      children: [
        {
          path: '',
          name: 'Login',
          component: () => import('@/views/pages/Login')
        }
      ]
    },
    {
      path: '/register',
      component: () => import('@/views/pages/register'),
      children: [
        {
          path: '',
          name: 'Register',
          component: () => import('@/views/pages/register')
        }
      ]
    },
    {
      path: '/',
      component: () => import('@/views/dashboard/Index'),
      beforeEnter: (to, from, next) => {
        const isAuthenticated = localStorage.getItem('tokinn') && store?.state?.AdminInfo?.authe === true
        next(isAuthenticated ? true : { path: '/register' })
      },
      children: [
        {
          path: '',
          name: 'statistics',
          component: () => import('@/views/dashboard/Dashboard')
        },
        {
          path: 'patients',
          name: 'casesheet',
          component: () => import('@/views/dashboard/casesheet')
        },
        {
          path: 'accounts',
          name: 'accounts',
          component: () => import('@/views/dashboard/accounts')
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
          component: () => import('@/views/dashboard/showCases')
        },
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
          component: () => import('@/views/dashboard/cases')
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
