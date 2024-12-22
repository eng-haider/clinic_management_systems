<template>

  <div style="height:100%;direction: rtl;" >


    <v-layout row wrap style="height:100%;background-color: #fff;">



      <v-flex xs12 md7 sm7 class="pdr">

        <v-card style="padding:45px;" flat>
          <h1 style="text-align:right;f">اهلا وسهلا بكم في منصة  العياده الذكية</h1>

          <br>
          <br>
          <br>
          

     







          <div>
            <p style="color:#504a4a;font-size:18px;font-weight:bold">
              لانشاء حساب جديد في منصة  العياده الذكية

              <router-link to="/register" text small color="red" style="font-weight:bold;font-size:18px">
                اضغط هنا

              </router-link>



            </p>



            <v-form ref="form_signin" v-model="valid">
              <v-layout row wrap>

                <v-flex xs12>
                
                </v-flex>


                <v-flex xs12 md8 sm8>
                  <!-- <v-lable>رقم الهاتف</v-lable> -->
                  <v-text-field filled v-model="user.phone" placeholder="رقم الهاتف" data-vv-name="name" height="50"
                    type="number" :rules="phoneRules" required></v-text-field>



                </v-flex>


                <v-flex xs12 md8 sm8>
                  <!-- <v-lable>كلمة المرور</v-lable> -->
                  <v-text-field filled v-model="user.password" 
                    placeholder="كلمة المرور" height="50" 
                    
                    :type="show1 ? 'text' : 'password'"
                    :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" @click:append="show1 = !show1"
                    
                    
                     data-vv-name="password" required>
                  </v-text-field>
                  <!-- <div style="float:left;    position: relative;
    bottom: 23px;">


                    هل نسيت كلمه السر
                    <router-link to="/forgetpassword" text small color="red" style="font-weight:bold;font-size:18px">
                      اضغط هنا

                    </router-link>

                  </div> -->


                </v-flex>


                <v-flex xs12 md8 sm8>
                  <v-btn class="reg_owner_btn" @click="submitـsignup()" color="#0a304e" :loading="loading"
                    style="color:#fff;font-weight:bold;font-size:19px;" v-bind:class="{btn_active:signin}"  large>
                    دخول</v-btn>

                </v-flex>

                <!-- </v-flex> -->

              </v-layout>
            </v-form>


          </div>





          <v-row>
            <v-text-field color="primary" loading disabled v-show="loading"></v-text-field>
          </v-row>

          <p style="color:red;text-align:center">{{this.errors}}</p>


        </v-card>

      </v-flex>


      <v-flex xs12 class="hidden-md-and-down" md5 sm5>
        <v-layout column  style="height:100%;background-color: #3dbfef !important;">

          <div style="height: 100%;
    padding-top: 45px;

    position: fixed;
    bottom: 0;

    float: left;
    width: 40%;">


            <v-layout row wrap style="">

              <v-flex xs5></v-flex>

              <v-flex xs2>


                <v-img src="/assets/logo_black.png" style="      height: 100%;
    width: 100%;"></v-img>




              </v-flex>


              <v-flex xs5></v-flex>

            </v-layout>
            <div
              class="cvc"
                style="padding-top: 15px;width: 100%;text-align:center;font-weight:bold;font-size:30px;padding-right:15px;color:#fff">
                منصة العياده الذكيه </div>
              <v-row>
                <div class="cvc"
                  style="width: 100%;text-align:center;color:#000;font-size:20px;font-weight:bold;padding-top:10px;text-align: center;">
                  سجل ونظم جميع بيانات المريض في منصة رقمية واحدة آمنة دون عناء
                </div>
              
  
              </v-row>
            <br>







            <v-layout row wrap style="padding-top: 40px;">

              <v-flex xs2></v-flex>

              <v-flex xs8>


                <v-img class="cvc" src="https://demo.tctate.com/assets/3899624.png" style="with:100%;height:100%"></v-img>




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
  .v-content {
    padding: 0px 0px 0px 0px !important;
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
    
    active_form:false,
    owners: {
      name: "",
      password_confirmation:"",
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
      v => (v && v.length > 4) || 'يجب ان لايقل عن ٣ احرف',
    ],
    centerRules: [
      v => !!v || 'يجب ادخال اسم المركز',
      v => (v && v.length > 4) || 'يجب ان لايقل عن ٣ احرف',
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
      //(v) => v.length > 10 && v.length < 12 || 'يجب ادخال رقم هاتف صحيح',

    ],



    passwordRules: [
      v => !!v || 'يجب ادخال الباسورد',
      (v) => v.length >= 8 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
      (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام'
    ],
    passwordConfirmationRules: [
      v => !!v || 'يجب ادخال الباسورد',
      (v) => v.length >= 8 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
      (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام',

    ],


    emailRules: [
      //  v => !!v || 'E-mail is required',
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
     //  alert(this.images[0].image_url);
    
        if (this.$refs.form_signin.validate()) {

        var data = {

          // name: this.owners.name,
          password: this.user.password,
        //  clinic_name: this.owners.company_name,
        //  password_confirmation: this.owners.password_confirmation,
          phone: "964" + parseInt(this.user.phone),
         

          
        };
        this.loading = true;

        this.axios({
            method: 'post',
            url: "/users/login",
            data: data,
            headers: {

            }
          })
          .then((res) => {
         var xx =res.data.result;
         xx.clinic_info=res.data.clinic_info;
         localStorage.setItem('tokinn', res.data.token);
          localStorage.setItem('tctate_token', res.data.tctate_token);
          this.$store.dispatch("login",res.data);
        
          this.loading = false;
          // this.$router.push("/")
        })
          .catch(error => {

            this.$swal({
              title: "رقم المستخدم او كلمة المرور غير صحيح",
              text: "",
              icon: "error",
              confirmButtonText: "اغلاق",
            });

            if (error.response) {
              
             this.error_msg=[];
              if (error.response.data.data.password ==
                "The password confirmation does not match.") {
                  this.scrollToTop();
                this.error_msg.push('الباسوردان غير متاطبقان');

              } 
            
              if (error.response.data.data.user_phone ==
                "The user phone has already been taken.") {
                   this.scrollToTop();
                this.error_msg.push('رقم الهاتف مسجل');

              } 
              
             

            }


          }).finally(() => {

            this.loading = false;
          });

      

  }

    },

   
 


  },

  created() {
  if (localStorage.getItem('tokinn') && this.$store.state.AdminInfo.authe == true) {
   
   this.$router.push("/")
 }
 else{
  //  this.$store.dispatch("logout");
 }
},
  components: {

  }
}
</script>

<style>
@media screen and (max-width: 600px) {
.cvc {

display: none;
}
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}



p{
  text-align: right;
}
</style>