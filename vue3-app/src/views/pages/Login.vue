<template>
  <div class="login-page">
    <div class="bg-animation">
      <div class="circle c1"></div>
      <div class="circle c2"></div>
      <div class="circle c3"></div>
    </div>
    
    <v-container fluid class="fill-height">
      <v-row class="fill-height" align="center" justify="center">
        <v-col cols="12" sm="8" md="6" lg="4">
          <v-card class="login-card pa-8" rounded="xl" elevation="20">
            <div class="text-center mb-6">
              <v-avatar size="80" color="primary" class="mb-4">
                <v-icon size="50" color="white">mdi-hospital-building</v-icon>
              </v-avatar>
              <h1 class="text-h5 font-weight-bold">العيادة الذكية</h1>
              <p class="text-grey">سجل دخولك للمتابعة</p>
            </div>
            
            <v-alert v-if="error" type="error" variant="tonal" class="mb-4" closable>
              {{ error }}
            </v-alert>
            
            <v-form ref="form" @submit.prevent="login">
              <v-text-field
                v-model="phone"
                label="رقم الهاتف"
                prepend-inner-icon="mdi-phone"
                variant="outlined"
                :rules="[v => !!v || 'مطلوب']"
                class="mb-3"
              ></v-text-field>
              
              <v-text-field
                v-model="password"
                label="كلمة المرور"
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="show ? 'mdi-eye-off' : 'mdi-eye'"
                :type="show ? 'text' : 'password'"
                variant="outlined"
                :rules="[v => !!v || 'مطلوب']"
                @click:append-inner="show = !show"
                class="mb-3"
              ></v-text-field>
              
              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                :loading="loading"
                class="mt-4"
              >
                دخول
              </v-btn>
            </v-form>
            
            <div class="text-center mt-6">
              <span>ليس لديك حساب؟</span>
              <router-link to="/register" class="text-primary font-weight-bold ms-1">
                سجل الآن
              </router-link>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()
const form = ref(null)
const phone = ref('')
const password = ref('')
const show = ref(false)
const loading = ref(false)
const error = ref('')

async function login() {
  const { valid } = await form.value.validate()
  if (!valid) return
  
  loading.value = true
  error.value = ''
  
  try {
    const res = await api.post('/login', {
      phone: '964' + phone.value.replace(/^0+/, ''),
      password: password.value
    })
    
    if (res.data?.token) {
      localStorage.setItem('tokinn', res.data.token)
      authStore.setToken(res.data.token)
      authStore.setUser(res.data.result)
      router.push('/')
    }
  } catch (e) {
    error.value = 'بيانات الدخول غير صحيحة'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (authStore.isAuthenticated) router.push('/')
})
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  direction: rtl;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-animation {
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  animation: float 15s infinite;
}

.c1 { width: 300px; height: 300px; top: -100px; right: -100px; }
.c2 { width: 200px; height: 200px; bottom: 50px; left: 50px; animation-delay: -5s; }
.c3 { width: 150px; height: 150px; top: 50%; left: 30%; animation-delay: -10s; }

@keyframes float {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(30px, -30px); }
}

.login-card {
  background: rgba(255,255,255,0.95) !important;
  backdrop-filter: blur(10px);
}
</style>
