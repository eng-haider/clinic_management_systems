<template>
  <div style="height: 100%; direction: rtl;">
    <v-layout row wrap style="height: 100%; background-color: #fff;">
      <v-flex xs12 md7 sm7 class="pdr">
        <v-card style="padding: 45px;" flat>
          <h1 style="text-align: right;">اهلا وسهلا بكم في منصة العياده الذكية</h1>
          <br />
          <div>
            <div style="color: #504a4a; font-size: 18px; font-weight: bold;">
              ادخل معلوماتك لانشاء حساب جديد في منصة العياده الذكية مجانا
            </div>
            <div style="color: #504a4a; font-size: 13px;">
              او قم بتجسيل الدخول اذا كنت تمتللك حساب
              <router-link to="/login" text small color="red" style="font-weight: bold; font-size: 18px;">
                تسجيل الدخول
              </router-link>
            </div>
            <v-form ref="form_signup" v-model="valid">
              <v-layout row wrap>
                <v-flex xs12>
                  <v-flex xs12 md12 sm12 pt-5>
                    <v-alert type="error" v-if="error_msg.length !== 0" style="position: relative; right: 0%; width: 84%;">
                      <span v-for="error in error_msg" :key="error">{{ error }}</span>
                    </v-alert>
                  </v-flex>
                  <v-flex xs12 md10 sm10 pt-5>
                    <v-label>اسم الدكتور</v-label>
                    <v-text-field
                      filled
                      v-model="owners.name"
                      height="50"
                      placeholder="اسم الدكتور"
                      required
                      :rules="nameRules"
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs12 md10 sm10>
                    <v-label>اسم العياده</v-label>
                    <v-text-field
                      filled
                      v-model="owners.company_name"
                      height="50"
                      placeholder="اسم المركز"
                      required
                      :rules="centerRules"
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs12 md10 sm10>
                    <v-label>رقم الهاتف</v-label>
                    <v-text-field
                      filled
                      v-model="owners.phone"
                      height="50"
                      placeholder="رقم الهاتف"
                      type="number"
                      required
                      :rules="phoneRules"
                    ></v-text-field>
                  </v-flex>
                  <v-flex xs12 md10 sm10>
                    <v-label>الباسورد</v-label>
                    <v-text-field
                      filled
                      v-model="owners.password"
                      height="50"
                      :type="show1 ? 'text' : 'password'"
                      :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                      @click:append="show1 = !show1"
                      placeholder="الباسورد"
                      required
                      :rules="passwordRules"
                    ></v-text-field>
                    <v-btn
                      class="reg_owner_btn"
                      @click="submitـsignup()"
                      color="#0a304e"
                      style="color: #fff; font-weight: bold; font-size: 19px;"
                    >
                      دخول
                    </v-btn>
                  </v-flex>
                </v-flex>
              </v-layout>
            </v-form>
          </div>
          <v-row>
            <v-text-field color="primary" loading disabled v-show="loading"></v-text-field>
          </v-row>
          <p style="color: red; text-align: center;">{{ errors }}</p>
        </v-card>
      </v-flex>
      <v-flex xs12 class="hidden-md-and-down" md5 sm5>
        <v-layout column style="height: 100%; background-color: #3dbfef !important;">
          <div class="fff" style="height: 100%; padding-top: 45px; position: fixed; bottom: 0; width: 40%;">
            <v-layout row wrap>
              <v-flex xs5></v-flex>
              <v-flex xs2>
                <v-img src="/assets/logo_black.png" style="height: 100%; width: 100%;"></v-img>
              </v-flex>
              <v-flex xs5></v-flex>
            </v-layout>
            <div class="cvc" style="padding-top: 15px; text-align: center; font-weight: bold; font-size: 30px; color: #fff;">
              منصة العياده الذكيه
            </div>
            <v-row>
              <div
               
                style="text-align: center; color: #000; font-size: 20px; font-weight: bold; padding-top: 10px;"
              >
                سجل ونظم جميع بيانات المريض في منصة رقمية واحدة آمنة دون عناء
              </div>
            </v-row>
            <br />
            <v-layout row wrap style="padding-top: 40px;">
              <v-flex xs2></v-flex>
              <v-flex xs8>
                <v-img  src="https://demo.tctate.com/assets/3899624.png" style="width: 100%; height: 100%;"></v-img>
              </v-flex>
              <v-flex xs2></v-flex>
            </v-layout>
          </div>
        </v-layout>
      </v-flex>
    </v-layout>
  </div>
</template>

<style scoped>
@media (max-width: 767px) {
  .fff {
    position: relative !important;
    color: red;
  }
}
.v-content {
  padding: 0 !important;
}
.cvc {
  display: none;
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}
</style>

<script>
export default {
  $_veeValidate: {
    validator: 'new',
  },
  name: 'app',
  data: () => ({
    active_status: [],
    valid: true,
    login_info: [],
    show1: false,
    show2: false,
    img_name: '',
    index: '',
    img_cliced: -1,
    signup: true,
    imageUrl: '',
    imageFile: '',
    categories: [],
    token: '',
    active_form: false,
    owners: {
      name: "",
      password_confirmation: "",
      email: "",
      password: "",
      phone: "",
      company_name: "",
      category_id: ""
    },
    error_msg: [],
    signin: false,
    owner_info: {
      id: '',
      name: '',
      session: '',
      email: '',
      role: '',
      photo: '',
      password_confirmation: ''
    },
    loading: false,
    nameRules: [
      v => !!v || 'يجب ادخال الاسم',
    ],
    centerRules: [
      v => !!v || 'يجب ادخال اسم المركز',
    ],
    phoneRules: [
      (v) => !!v || 'يجب ادخال رقم الهاتف',
      (v) => v.length > 10 && v.length < 12 || 'يجب ادخال رقم هاتف صحيح',
      (v) => /^\d+$/.test(v) || 'يجب ادخال رقم هاتف صحيح',
    ],
    verfyRules: [
      (v) => !!v || 'يجب ادخال كود التفعيل',
      (v) => (v.length == 6) || ' يجب ان يكون كود التفعيل 6 ارقام',
      (v) => /^\d+$/.test(v) || 'ارقام فقط'
    ],
    CatsRules: [
      (v) => !!v || 'يجب ادخال القسم ',
    ],
    passwordRules: [
      v => !!v || 'يجب ادخال الباسورد',
      (v) => v.length >= 6 || 'يجب ان لايقل الباسورد عن ٦ احرف او ارقام',
    ],
    passwordConfirmationRules: [
      v => !!v || 'يجب ادخال الباسورد',
      (v) => v.length >= 8 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
      (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام',
    ],
    emailRules: [
      v => /.+@.+\..+/.test(v) || 'الايميل غير صحيح',
    ],
    errors: '',
    verfy_code: '',
    user: {
      phone: '',
      reg_dialog: false,
      password: '',
    },
    options: {
      isLoggingIn: true,
      shouldStayLoggedIn: true,
    },
  }),
  methods: {
    scrollToTop() {
      window.scrollTo(0, 0);
    },
    submitـsignup() {
      if (this.$refs.form_signup.validate()) {
        const data = {
          name: this.owners.name,
          password: this.owners.password,
          clinic_name: this.owners.company_name,
          password_confirmation: this.owners.password_confirmation,
          phone: "964" + parseInt(this.owners.phone),
        };

        this.loading = true;

        this.axios({
          method: 'post',
          url: "/users",
          data: data,
        })
          .then((res) => {
            const result = res.data.result;
            result.clinic_info = res.data.clinic_info;
            localStorage.setItem('tokinn', res.data.token);
            localStorage.setItem('tctate_token', res.data.tctate_token);
            this.$store.dispatch("login", res.data);
            this.loading = false;
          })
          .catch((error) => {
            this.error_msg = [];
            if (error.response && error.response.data.data) {
              const errorData = error.response.data.data;

              if (errorData.password === "The password confirmation does not match.") {
                this.scrollToTop();
                this.error_msg.push('الباسوردان غير متاطبقان');
              }

              if (errorData.user_phone === "The user phone has already been taken.") {
                this.scrollToTop();
                this.error_msg.push('رقم الهاتف مسجل');
              }
            }
          })
          .finally(() => {
            this.loading = false;
          });
      }
    },
  },
  created() {
    if (localStorage.getItem('tokinn') && this.$store.state.AdminInfo.authe === true) {
      this.$router.push("/");
    }
  },
};
</script>