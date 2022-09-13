<template>
  <v-container id="login" fill-height tag="section">
    <v-row justify="center">
      <v-slide-y-transition appear>
        <v-card outlined width="400px" class="pt-6">
          <form v-on:submit.prevent="login">
            <v-card-text class="text-center">
              <v-avatar size="150" tile class="">
                <img class="" src="logo.png"
              />
              
              </v-avatar>
<br>

<br>

              <div class="text-center grey--text text--darken-2">
                 نظام ادارة عيادت الاسنان
              </div>
              <v-text-field 
                outlined
                color="primary darken-1 "
                label="اسم المستخدم"
                class="mt-10"
                v-model="username"
                :rules="[rules.required]"
              />

              <v-text-field
                outlined
                class="mb-1"
                color="primary darken-1"
                label="كلمة المرور"
                v-model="password"
                :rules="[rules.required, rules.min]"
                :type="show1 ? 'text' : 'password'"
                :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="show1 = !show1"
              />
            </v-card-text>
            <v-col class="text-center">
              <v-btn
                :loading="loading"
                @click.prevent="login()"
                type="submit"
                large
                rounded
                outlined
                color="primary darken-1"
                ref="sendReply"
                >تسجيل الدخول</v-btn
              >
            </v-col>
          </form>
        </v-card>
      </v-slide-y-transition>
    </v-row>
    <v-row justify="center">
      <span style="color:#7c7c7c"> جميع الحقوق محفوظة | {{ new Date().getFullYear() }} </span>
    </v-row>
  </v-container>
</template>

<script>
export default {
  data: () => ({
    show1: false,
    rules: {
      required: (value) => !!value || "مطلوب",
      min: (v) =>
        v.length >= 6 || "كلمة المرور يجب ان تتكون من 6 عناصر او اكثر",
      minPhon: (v) => v.length == 13 || "رقم الهاتف يجب ان يتكون من 11 رقم",
    },
    username: "",
    password: "",
    loading: false,
  }),

  methods: {
    login() {

      //  this.$store.dispatch("login", {
      //    name:'haider altemimy',
      //    token:'ada',
      //    email:'en. haider1@gmail.com',
      //    photo:null
      //  });
      //this.$router.push("/");
      if (this.username && this.password) {
        this.loading = true;
        const data = {
          email: this.username.replace(/ /g, ""),
          password: this.password,
        };
        this.axios
          .post("/users/login", data)
          .then((res) => {
            
            this.$store.dispatch("login", res.data.result);
            localStorage.setItem('tokinn',res.data.token);
            localStorage.setItem('tctate_token',res.data.tctate_token);
            
            this.loading = false;
          })
          .catch((err) => {
            err
            this.loading = false;

            this.$swal({
              title: "رقم المستخدم او كلمة المرور غير صحيح",
              text: "",
              icon: "error",
              confirmButtonText: "اغلاق",
            });
          });
      } else {
        this.$swal({
          title: "تاكد من ادخال رقم المستخدم وكلمة المرور",
          text: "",
          icon: "error",
          confirmButtonText: "اغلاق",
        });
      }
    },
  },
  created() {
    this.$store.dispatch("logout");
  },
};
</script>
