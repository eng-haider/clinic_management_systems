<template>
  <div class="register-page">
    <div class="bg-animation">
      <div class="circle c1"></div>
      <div class="circle c2"></div>
      <div class="circle c3"></div>
      <div class="circle c4"></div>
    </div>
    
    <v-container fluid class="fill-height">
      <v-row class="fill-height" align="center" justify="center">
        <v-col cols="12" sm="10" md="8" lg="5">
          <v-card class="register-card pa-8" rounded="xl" elevation="20">
            <div class="text-center mb-6">
              <v-avatar size="80" color="success" class="mb-4">
                <v-icon size="50" color="white">mdi-account-plus</v-icon>
              </v-avatar>
              <h1 class="text-h5 font-weight-bold">إنشاء حساب جديد</h1>
              <p class="text-grey">انضم إلى منصة العيادة الذكية</p>
            </div>
            
            <v-alert v-if="errors.length" type="error" variant="tonal" class="mb-4">
              <div v-for="(e, i) in errors" :key="i">{{ e }}</div>
            </v-alert>
            
            <v-form ref="form" @submit.prevent="register">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="name"
                    label="اسم الدكتور"
                    prepend-inner-icon="mdi-doctor"
                    variant="outlined"
                    :rules="[v => !!v || 'مطلوب']"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="clinic"
                    label="اسم العيادة"
                    prepend-inner-icon="mdi-hospital-building"
                    variant="outlined"
                    :rules="[v => !!v || 'مطلوب']"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-text-field
                    v-model="phone"
                    label="رقم الهاتف"
                    prepend-inner-icon="mdi-phone"
                    variant="outlined"
                    type="tel"
                    :rules="phoneRules"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="password"
                    label="كلمة المرور"
                    prepend-inner-icon="mdi-lock"
                    :append-inner-icon="show1 ? 'mdi-eye-off' : 'mdi-eye'"
                    :type="show1 ? 'text' : 'password'"
                    variant="outlined"
                    :rules="passRules"
                    @click:append-inner="show1 = !show1"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="confirmPass"
                    label="تأكيد كلمة المرور"
                    prepend-inner-icon="mdi-lock-check"
                    :append-inner-icon="show2 ? 'mdi-eye-off' : 'mdi-eye'"
                    :type="show2 ? 'text' : 'password'"
                    variant="outlined"
                    :rules="confirmRules"
                    @click:append-inner="show2 = !show2"
                  ></v-text-field>
                </v-col>
              </v-row>
              
              <v-btn
                type="submit"
                color="success"
                size="large"
                block
                :loading="loading"
                class="mt-4"
              >
                <v-icon start>mdi-account-check</v-icon>
                تسجيل
              </v-btn>
            </v-form>
            
            <div class="text-center mt-6">
              <span>لديك حساب؟</span>
              <router-link to="/login" class="text-primary font-weight-bold ms-1">
                سجل دخول
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
const name = ref('')
const clinic = ref('')
const phone = ref('')
const password = ref('')
const confirmPass = ref('')
const show1 = ref(false)
const show2 = ref(false)
const loading = ref(false)
const errors = ref([])

const phoneRules = [
  v => !!v || 'مطلوب',
  v => (v && v.length >= 10) || 'رقم غير صحيح'
]

const passRules = [
  v => !!v || 'مطلوب',
  v => (v && v.length >= 6) || 'على الأقل 6 أحرف'
]

const confirmRules = [
  v => !!v || 'مطلوب',
  v => v === password.value || 'غير متطابق'
]

async function register() {
  const { valid } = await form.value.validate()
  if (!valid) return
  
  loading.value = true
  errors.value = []
  
  try {
    const res = await api.post('/users', {
      name: name.value,
      clinic_name: clinic.value,
      phone: '964' + phone.value.replace(/^0+/, ''),
      password: password.value,
      password_confirmation: confirmPass.value
    })
    
    if (res.data?.token) {
      localStorage.setItem('tokinn', res.data.token)
      authStore.setToken(res.data.token)
      authStore.setUser(res.data.result)
      router.push('/')
    }
  } catch (e) {
    if (e.response?.data?.data?.user_phone) {
      errors.value.push('رقم الهاتف مسجل مسبقاً')
    } else {
      errors.value.push('حدث خطأ، حاول مرة أخرى')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (authStore.isAuthenticated) router.push('/')
})
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  direction: rtl;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
  animation: float 18s infinite;
}

.c1 { width: 350px; height: 350px; top: -150px; right: -100px; }
.c2 { width: 200px; height: 200px; bottom: 100px; left: 50px; animation-delay: -4s; }
.c3 { width: 150px; height: 150px; top: 40%; left: 20%; animation-delay: -8s; }
.c4 { width: 100px; height: 100px; bottom: 20%; right: 20%; animation-delay: -12s; }

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(20px, -20px) scale(1.05); }
}

.register-card {
  background: rgba(255,255,255,0.95) !important;
  backdrop-filter: blur(10px);
}
</style>
