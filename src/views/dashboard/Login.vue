<template>
  <div style="height:100%;direction: rtl;">
    <v-layout row wrap style="height:100%;background-color: #fff;">
      <v-flex xs12 md7 sm7 class="pdr">
        <v-card style="padding:45px;" flat>
          <h1 style="text-align:right;">اهلا وسهلا بكم في منصة العياده الذكية</h1>
          <br><br><br>
          <div>
            <p style="color:#504a4a;font-size:18px;font-weight:bold">
              لانشاء حساب جديد في منصة العياده الذكية
              <router-link to="/register" text small color="red" style="font-weight:bold;font-size:18px">
                اضغط هنا
              </router-link>
            </p>
            <v-form ref="form_signin" v-model="valid">
              <v-layout row wrap>
                <v-flex xs12 md8 sm8>
                  <v-text-field filled v-model="user.phone" placeholder="رقم الهاتف" type="number" 
                    :rules="phoneRules" required></v-text-field>
                </v-flex>
                <v-flex xs12 md8 sm8>
                  <v-text-field filled v-model="user.password" placeholder="كلمة المرور" 
                    :type="show1 ? 'text' : 'password'" 
                    :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" 
                    @click:append="show1 = !show1" required>
                  </v-text-field>
                </v-flex>
                <v-flex xs12 md8 sm8>
                  <v-btn class="reg_owner_btn" @click="submitSignup" color="#0a304e" :loading="loading"
                    style="color:#fff;font-weight:bold;font-size:19px;" 
                    v-bind:class="{btn_active:signin}" large>
                    دخول
                  </v-btn>
                </v-flex>
              </v-layout>
            </v-form>
          </div>
          <v-row>
            <v-text-field color="primary" loading disabled v-show="loading"></v-text-field>
          </v-row>
          <p style="color:red;text-align:center">{{ errors }}</p>
        </v-card>
      </v-flex>
      <v-flex xs12 class="hidden-md-and-down" md5 sm5>
        <v-layout column style="height:100%;background-color: #3dbfef !important;">
          <div style="height: 100%; padding-top: 45px; position: fixed; bottom: 0; float: left; width: 40%;">
            <v-layout row wrap>
              <v-flex xs5></v-flex>
              <v-flex xs2>
                <v-img src="/assets/logo_black.png" style="height: 100%; width: 100%;"></v-img>
              </v-flex>
              <v-flex xs5></v-flex>
            </v-layout>
            <div class="cvc" style="padding-top: 15px;width: 100%;text-align:center;font-weight:bold;font-size:30px;padding-right:15px;color:#fff">
              منصة العياده الذكيه
            </div>
            <v-row>
              <div class="cvc" style="width: 100%;text-align:center;color:#000;font-size:20px;font-weight:bold;padding-top:10px;">
                سجل ونظم جميع بيانات المريض في منصة رقمية واحدة آمنة دون عناء
              </div>
            </v-row>
            <br>
            <v-layout row wrap style="padding-top: 40px;">
              <v-flex xs2></v-flex>
              <v-flex xs8>
                <v-img class="cvc" src="https://demo.tctate.com/assets/3899624.png" style="width:100%;height:100%"></v-img>
              </v-flex>
              <v-flex xs2></v-flex>
            </v-layout>
          </div>
        </v-layout>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data: () => ({
    valid: true,
    show1: false,
    loading: false,
    signin: false,
    errors: '',
    user: {
      phone: '',
      password: '',
    },
    phoneRules: [
      v => !!v || 'يجب ادخال رقم الهاتف',
      v => v.length > 10 && v.length < 12 || 'يجب ادخال رقم هاتف صحيح',
      v => /^\d+$/.test(v) || 'يجب ادخال رقم هاتف صحيح',
    ],
  }),
  methods: {
    submitSignup() {
      if (this.$refs.form_signin.validate()) {
        const data = {
          phone: "964" + parseInt(this.user.phone),
          password: this.user.password,
        };
        this.loading = true;
        this.axios.post("https://titaniumapi.tctate.com/api/usershttps://titaniumapi.tctate.com", data)
          .then(res => {
            const result = res.data.result;
            result.clinic_info = res.data.clinic_info;
            localStorage.setItem('tokinn', res.data.token);
            localStorage.setItem('tctate_token', res.data.tctate_token);
            this.$store.dispatch("login", res.data);
            this.loading = false;
          })
          .catch(error => {
            this.$swal({
              title: "رقم المستخدم او كلمة المرور غير صحيح",
              icon: "error",
              confirmButtonText: "اغلاق",
            });
            this.errors = error.response?.data?.data || '';
          })
          .finally(() => {
            this.loading = false;
          });
      }
    },
  },
  created() {
    if (localStorage.getItem('tokinn') && this.$store.state.AdminInfo.authe) {
      this.$router.push("/");
    }
  },
};
</script>

<style scoped>
.v-content {
  padding: 0 !important;
}
@media screen and (max-width: 600px) {
  .cvc {
    display: none;
  }
}
p {
  text-align: right;
}
</style>