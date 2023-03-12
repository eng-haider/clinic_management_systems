import Vue from 'vue'
import Router from "vue-router";
import store from './store'
Vue.use(Router)

import {
  USER_ROLE_GUEST,
  ADMIN_ROLE,


} from "constants";

USER_ROLE_GUEST
ADMIN_ROLE

const router = new Router({
  mode: "history",
  scrollBehavior (to, from, savedPosition) {
    to
    from 
    savedPosition
    return new Promise((resolve, reject) => {
      reject
      setTimeout(() => {
        resolve({ x: 0, y: 0 })
      }, 300)
    })
  },
    routes: [
   
    
      {
        path: '/login',
        name:'login',
        component: () => import('@/views/pages/Index'),
        children: [{
          name: 'Login',
          path: '',
          component: () => import('@/views/pages/Login')
        }]
      },
      {
        path: '/',
        name:"admin",
        component: () => import('@/views/dashboard/Index'),
        beforeEnter: (to, from, next) => {
          
          if (localStorage.getItem('tokinn') && store.state.AdminInfo.authe==true) {
            next()
          }
          
          else {
            next({
              path: '/login',
            });
          }
        },   
        children: [
          {
            name: 'statistics',
            path: '',
            component: () => import('@/views/dashboard/Dashboard')
          },
          {
            path: '/patients',
            name:'casesheet',
            component: () => import('@/views/dashboard/casesheet'),
            
          },

          {
            path: '/accounts',
            name:'accounts',
            component: () => import('@/views/dashboard/accounts'),
            
          },

          {
            path: 'case/:id',
            name:'case',
            component: () => import('@/views/dashboard/casex'),
          },
        
          {
            path: 'patient/:id',
            name:'showCases',
            component: () => import('@/views/dashboard/showCases'),
          },

       

          {
            path: 'cases',
            name:'cases',
            component: () => import('@/views/dashboard/cases'),
          },


          {
            path: 'dashbourd',
            name:'dashbourd',
            component: () => import('@/views/dashboard/dashbourd'),
          },

          {
            path:'requestBooking_test',
            name:'requestBooking_test',
            component: () => import('@/views/dashboard/requestBooking_test'),
            
          },
            {
              path:'requestBooking',
              name:'requestBooking',
              component: () => import('@/views/dashboard/requestBooking'),
              
            },
            {
              path:'profile',
              name:'profile',
              component: () => import('@/views/dashboard/profile'),
              
            },
  

            //profile
          

       
       //requestBooking.vue
       
        

       
        ]   
        
      },
    ]
    });
    
    
    
    
    router.beforeEach((to, from, next) => {
     
        window.document.title = to.name ;
   
      next();
    });
    
    export default router;

