<template>
    <v-container fluid>
        <v-layout column>
            <v-card>


                <v-card-text>
                    <v-form ref="form">
                        <v-layout row wrap>
                            <v-flex xs4>

                            </v-flex>

                            <input type="file" style="display: none" ref="image" accept="image/*"
                                @change="onFilePicked">
                            <span style="color:#fff">


                                {{img_name}} </span>

                            <v-row justify="center" class="mb-6">
                                <v-card height="200" width="200" class="card_img">
                                    <v-img :src="images[0]" height="200" width="200" class="card_img"
                                        v-if="images[0]!=='ab'">
                                        <v-btn style="padding-right: 24px;position:relative;left: 46px;"
                                            @click="delete_img(img_id,0)">
                                            xx
                                            <v-icon color="red">fas fa-window-close</v-icon>
                                        </v-btn>

                                    </v-img>



                                    <v-btn icon v-else @click='pickFile()'>
                                        <v-icon style="margin:0px" size="40">fas fa-plus-square</v-icon>
                                    </v-btn>

                                    <v-container fluid="" style="width:100%;height:100%" @>

                                    </v-container>

                                </v-card>

                            </v-row>



                            <v-flex xs4>
                            </v-flex>

                        </v-layout>



                        <v-layout row wrap pt-5>







                            <v-flex xs2>
                            </v-flex>

                            <v-flex xs8>
                                <v-lable>{{ $t("name") }}</v-lable>
                                <v-text-field filled height="40" style="" v-model="owner.name" :placeholder="$t('name')" required>
                                </v-text-field>



                                <v-lable>{{ $t("clinic_name") }}</v-lable>
                                <v-text-field height="40" style="" filled v-model="owner.Clinics.name" :placeholder="$t('clinic_name')" required :rules="nameRules"></v-text-field>

                                <v-lable>{{ $t("email") }}</v-lable>
                                <v-text-field height="40" style="" filled :placeholder="$t('email')" v-model="owner.email"
                                    :rules="emailRules">
                                </v-text-field>






                                <v-lable>{{ $t("clinic_address") }}</v-lable>
                                <v-text-field height="40" style="" filled :placeholder="$t('clinic_address')" v-model="owner.Clinics.address"
                                    :rules="emailRules">
                                </v-text-field>



                                <p>
                                    <v-btn @click="password_Dailog=true" color="red" style="color:#fff" width="200px">
                                        {{ $t("change_password") }}</v-btn>
                                </p>
                                <v-btn pr-4 color="primary" style="width:50%" :loading="loading"
                                    @click="update(),update()">

                                    {{ $t("save_changes") }}

                                </v-btn>
                            </v-flex>
                            <v-flex xs2>
                            </v-flex>
                        </v-layout>
                    </v-form>
                </v-card-text>




            </v-card>
        </v-layout>
        <!-- <avatar-picker v-model="showAvatarPicker" :current-avatar="form.avatar" @selected="selectAvatar">
        </avatar-picker> -->

        <v-dialog v-model="password_Dailog" max-width="550px" height="600px" persistent>
            <v-card pr-5>
                <v-form v-model="password_form" ref="password_form">
                    <v-container>
                        <!-- <span class="mb-4">تم ارسال رقم التفعيل برساله يرجئ التحقق</span> -->

                        <v-flex xs12 md12 sm12 pt-5>
                            <v-alert type="error" v-if="error_msg.length!==0"
                                style="position: relative;right: 0%;width: 84%;">

                                <span v-for="error in error_msg" :key="error">{{error}}</span>
                            </v-alert>


                        </v-flex>
                        <v-flex xs12 md12 sm12>
                            <v-lable>{{ $t("old_password") }}</v-lable>
                            <v-text-field filled v-model="password.old_password" height="50" style=""
                                :type="show1 ? 'text' : 'password'"
                                :append-icon="show1 ? 'fa-sharp fa-solid fa-eye' : 'fa-regular fa-eye-slash'"
                                @click:append="show1 = !show1" :placeholder="$t('old_password')" required
                                :rules="oldpasswordRules"></v-text-field>
                        </v-flex>


                        <v-flex xs12 md12 sm12>
                            <v-lable>{{ $t("new_password") }}</v-lable>
                            <v-text-field filled v-model="password.new_password" height="50" style=""
                                :type="show2 ? 'text' : 'password'"
                                :append-icon="show2 ? 'fa-sharp fa-solid fa-eye' : 'fa-regular fa-eye-slash'"
                                @click:append="show2 = !show2" :placeholder="$t('new_password')" required
                                :rules="newpasswordRules"></v-text-field>
                        </v-flex>

                        <v-flex xs12 md12 sm12>
                            <v-lable>{{ $t("confirm_password") }}</v-lable>
                            <v-text-field filled v-model="password.password_confirmation"
                                :type="show3 ? 'text' : 'password'"
                                :append-icon="show3 ? 'fa-sharp fa-solid fa-eye' : 'fa-regular fa-eye-slash'"
                                @click:append="show3 = !show3" height="50" style="" :placeholder="$t('confirm_password')"
                                required :rules="passwordConfirmationRules"></v-text-field>
                        </v-flex>

                        <v-row justify="center">
                            <v-flex xs4 pl-3>
                                <v-btn color="success" @click="change_password()" style=";color:#fff;width:100%">
                                    {{ $t("change_password") }}
                                </v-btn>



                            </v-flex>

                            <v-flex xs4>

                                <v-btn color="red" @click="password_Dailog=false" style=";color:#fff;width:100%">
                                    {{ $t("back") }}
                                </v-btn>


                            </v-flex>

                        </v-row>

                        <br>
                        <br>
                    </v-container>

                </v-form>
            </v-card>
        </v-dialog>

    </v-container>

</template>

<script>
    import {
        EventBus
    } from "./event-bus.js";
    const axios = require('axios');

    export default {
        data() {
            return {
                //   owner:[],
                error_msg: [],

                oldpasswordRules: [
                    v => !!v || this.$t('password_required'),
                    (v) => v.length >= 8 || this.$t('password_min_length'),
                    // (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام'
                ],



                newpasswordRules: [
                    v => !!v || this.$t('password_required'),
                    (v) => v.length >= 8 || this.$t('password_min_length'),
                    // (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام'
                ],

                passwordConfirmationRules: [
                    v => !!v || this.$t('password_required'),
                    (v) => v.length >= 8 || this.$t('password_min_length'),
                    // (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام',

                ],


                loading: false,

                images: [],
                img_name: '',

                show1: false,
                show2: false,
                show3: false,
                password_Dailog: false,
                password: {
                    old_password: '',
                    new_password: '',
                    password_confirmation: ''

                },
                owner: {
                    name: "",
                    user: {
                        full_name: ''

                    },
                    email: "",
                    password: "",
                    phone: "",
                    company_name: "",
                    category_id: "",
                    owner_barnd_name: ""


                },

                nameRules: [
                    (v) => !!v || this.$t('name_required'),

                ],
                emailRules: [



                    // (v) => ( /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,5})+$/.test(v)) ||
                    // '��لايميل غير صحيح',


                ],
            }
        },
        methods: {
            change_password() {



                if (this.$refs.password_form.validate()) {

                    var data = {


                        old_password: this.password.old_password,
                        password: this.password.new_password,
                        password_confirmation: this.password.password_confirmation,



                    };
                    this.loading = true;

                    this.$http({
                            method: 'post',
                            url: "https://tctate.com/api/api/owner/v2/UpdatePassword",
                            data: data,
                            headers: {
                                "Content-Type": "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
                            },
                        })
                        .then(response => {
                            response
                            this.password_Dailog = false;
                            // this.$swal('', "تم تغير الباسورد بنجاح ", 'success')







                            //change password of smart clinic
                            this.$http({
                            method: 'post',
                            url: "/users/UpdatePassword",
                            data: data,
                            headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                        },
                        })
                        .then(response => {
                            response
                            this.password_Dailog = false;
                            this.$swal('', this.$t('password_changed_successfully'), 'success')









                            

                        })
                        .catch(error => {
                            if (error.response) {

                                this.error_msg = [];
                                if (error.response.data.data.password ==
                                    "The password confirmation does not match.") {
                                    // this.scrollToTop();
                                    this.error_msg.push(this.$t('passwords_do_not_match'));

                                }

                                if (error.response.data.data ==
                                    "old password false") {
                                    //  this.scrollToTop();
                                    this.error_msg.push(this.$t('incorrect_old_password'));

                                }

                            }


                        }).finally(() => {

                            this.loading = false;
                        });









                        })
                        .catch(error => {
                            if (error.response) {

                                this.error_msg = [];
                                if (error.response.data.data.password ==
                                    "The password confirmation does not match.") {
                                    // this.scrollToTop();
                                    this.error_msg.push(this.$t('passwords_do_not_match'));

                                }

                                if (error.response.data.data ==
                                    "old password false") {
                                    //  this.scrollToTop();
                                    this.error_msg.push(this.$t('incorrect_old_password'));

                                }

                            }


                        }).finally(() => {

                            this.loading = false;
                        });

                }

            },
            GetOwnerInfo() {

                this.$http({
                        method: 'get',
                        url: '/users/getInfo',
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }

                    }).then(response => {

                        this.owner = response.data.data;
                        this.$store.dispatch("updateInfo", this.owner);

                        this.images = [];

                        if (this.owner.img_file !== null) {


                            this.img_id = this.owner.id;
                            this.owner.img_file !== null && this.owner.img_file !== null ? this.images[0] =
                                this.Url + "/users/" + this.owner.img_file :

                                this
                                .images[0] = "ab";



                        } else {
                            this
                                .images[0] = "ab";

                        }



                    })

                    .catch(error => {
                        error

                    }).finally(s => {
                        s

                    })


            },


            update() {



                // if (this.images[0] == "ab") {

                //     this.images = []

                // }
                // else  if (this.images[0].includes(this.https)) {
                //     // this.images[0] = "data:image/jpeg;base64";

                //    // this.images = [];

                // }



                if (this.$refs.form.validate() && !this.loading) {

                    var data = {}
                    if (this.images[0].includes(this.http)  || this.images[0]=='ab') {

                        data = {

                            name: this.owner.name,
                            email: this.owner.email,
                            clinics_name: this.owner.Clinics.name,
                            clinics_address: this.owner.Clinics.address,
                            images: [],

                        };
                    } else {
                        data = {

                            name: this.owner.name,
                            email: this.owner.email,
                            clinics_name: this.owner.Clinics.name,
                            clinics_address: this.owner.Clinics.address,
                            images: this.images,

                        };

                    }
                    
                    this.loading = true;
                    // this.description=JSON.stringify(data);
                    this.$http({
                            method: 'post',
                            url: "users/UpdateInfo",
                            data: data,
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token
                            }
                        })
                        .then(response => {
                            response
                            //  this.$swal('', "   تم تعديل الملعومات بنجاح  ", 'success')
                            EventBus.$emit('changeUserInfo', true);
                            // this.$store.dispatch("updateInfo", response.data.data);
                            // this.$store.dispatch("updateInfo", response.data.data);

                            // this.$store.dispatch("updateInfo", response.data.data);

                            // this.$store.dispatch("updateInfo", response.data.data);

                            this.GetOwnerInfo();

                            // window.location.reload()
                            // window.location.reload()
                            // this.signup_user_id = response.data.id;

                            // this.dialog = false;
                            // this.verfy_Dailog = true;
                            // this.Search();

                            // //   this.$swal('', "   تم اضافة تاجر", 'success')



                        })
                        .catch(error => {
                            error
                            this.$swal('', this.$t('update_successful'), 'success')
                            //this.verfy_Dailog = true;

                            if (error.response) {
                                if (error.response.data.data.owner_email ==
                                    "The owner email has already been taken.") {
                                    this.$swal('', this.$t('email_taken'), 'error')
                                } else {
                                    this.$swal('', this.$t('update_failed'), 'error')
                                }

                            }


                        }).finally(() => {

                            this.loading = false;
                        });

                }

            },


            pickFile() {

                this.$refs.image.click()
            },

            delete_img(img_id, index) {





                const Swal = require('sweetalert2');



                Swal.fire({
                    title: this.$t('sure_process'),

                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: this.$t('yes')

                }).then((result) => {
                    if (result.value) {
                        this.img_cliced = index;


                        this.images[0] = 'ab';
                        this.img_name = ' '


                        var url = "/users/DeleteImage";
                        axios({
                            method: 'post',
                            url: url,
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token
                            }

                        }).then(response => {

                            response,
                            this.$store.dispatch("updateInfo", response.data.data);
                            this.editedItem.images[index].image_url = 'a';


                            this.img_name = index;
                            this.images[index] = 'a';




                            this.GetOwnerInfo();

                            Swal.fire(
                                this.$t('delete_successful'),
                                '',
                                'success'
                            )

                        }).catch(error => {
                            error
                            //   this.$swal('خطاء', "خطاء بالاتصال", 'error')
                        }).finally(d => {
                            d

                            //   location.reload();
                            //\ this.update();
                        });



                    }
                })




            },


            cancelImg() {
                this.images[0] = 'ab';
                this.img_name = 'ghjk'

            },
            onFilePicked(e) {

                const files = e.target.files
                if (files[0] !== undefined) {
                    this.imageName = files[0].name
                    if (this.imageName.lastIndexOf('.') <= 0) {
                        return
                    }
                    const fr = new FileReader()
                    fr.readAsDataURL(files[0])
                    fr.addEventListener('load', () => {
                        this.imageUrl = fr.result
                        this.imageFile = files[0]
                        this.images = [];
                        this.images.push(fr.result);
                        this.imgname = files[0].name;


                    })

                } else {
                    this.imageName = ''
                    this.imageFile = ''
                    this.imageUrl = ''
                }
            },
            // selectAvatar(avatar) {
            //     this.form.avatar = avatar
            // }
        },


        mounted() {

            this.GetOwnerInfo();


        },
    }
</script>