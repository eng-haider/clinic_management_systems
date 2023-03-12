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
                                        v-if="images[0]!='ab'">
                                        <v-btn icon="" style="padding-right: 24px;position:relative;left: 46px;"
                                            @click="delete_img(img_id,0)">

                                            <v-icon color="#fff">fas fa-window-close</v-icon>
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
                                <v-lable>الاسم</v-lable>
                                <v-text-field filled background-color="#eeeeee" height="40" style=""
                                    v-model="owner.user.full_name" placeholder="الاسم" required>
                                </v-text-field>


                                <v-lable>اسم المركز</v-lable>
                                <v-text-field background-color="#eeeeee" height="40" style="" filled
                                    v-model="owner.owner_barnd_name" placeholder="اسم الشركة" required
                                    :rules="nameRules"></v-text-field>

                                <v-lable>الايميل</v-lable>
                                <v-text-field background-color="#eeeeee" height="40" style="" filled
                                    placeholder="الايميل" v-model="owner.owner_email" :rules="emailRules">
                                </v-text-field>




                                <!-- <p>
                                    <v-btn @click="password_Dailog=true" color="red" style="color:#fff" width="200px">
                                        تغير الباسورد</v-btn>
                                </p> -->
                                <v-btn pr-4 color="primary" style="width:50%" :loading="loading" @click="update()">

                                    حفظ التعديلات
                                    <v-icon left dark>check</v-icon>
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

        <v-dialog v-model="password_Dailog" max-width="450px" height="600px" persistent>
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
                            <v-lable>الباسورد السابق</v-lable>
                            <v-text-field filled v-model="password.old_password" height="50" style=""
                                :type="show1 ? 'text' : 'password'"
                                :append-icon="show1 ? 'visibility' : 'visibility_off'" @click:append="show1 = !show1"
                                placeholder="الباسورد السابق" required :rules="oldpasswordRules"></v-text-field>
                        </v-flex>


                        <v-flex xs12 md12 sm12>
                            <v-lable>الباسورد الجديد</v-lable>
                            <v-text-field filled v-model="password.new_password" height="50" style=""
                                :type="show2 ? 'text' : 'password'"
                                :append-icon="show2 ? 'visibility' : 'visibility_off'" @click:append="show2 = !show2"
                                placeholder="الباسورد الجديد" required :rules="newpasswordRules"></v-text-field>
                        </v-flex>

                        <v-flex xs12 md12 sm12>
                            <v-lable>اعاده كتابه الباسورد</v-lable>
                            <v-text-field filled v-model="password.password_confirmation"
                                :type="show3 ? 'text' : 'password'"
                                :append-icon="show3 ? 'visibility' : 'visibility_off'" @click:append="show3 = !show3"
                                height="50" style="" placeholder="اعاده كتابه الباسورد" required
                                :rules="passwordConfirmationRules"></v-text-field>
                        </v-flex>

                        <v-row justify="center">
                            <v-flex xs4 pl-3>
                                <v-btn color="success" @click="change_password()" style=";color:#fff;width:100%">
                                    تغير
                                </v-btn>



                            </v-flex>

                            <v-flex xs4>

                                <v-btn color="red" @click="password_Dailog=false" style=";color:#fff;width:100%">
                                    خروج
                                </v-btn>


                            </v-flex>

                        </v-row>
                    </v-container>

                </v-form>
            </v-card>
        </v-dialog>

    </v-container>

</template>

<script>
    const axios = require('axios');
    // import {
    //     EventBus
    // } from '../event-bus.js';


    export default {
        data() {
            return {
                //   owner:[],
                error_msg: [],

                oldpasswordRules: [
                    v => !!v || 'يجب ادخال الباسورد',
                    (v) => v.length >= 8 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
                    (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام'
                ],



                newpasswordRules: [
                    v => !!v || 'يجب ادخال الباسورد',
                    (v) => v.length >= 8 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
                    (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام'
                ],

                passwordConfirmationRules: [
                    v => !!v || 'يجب ادخال الباسورد',
                    (v) => v.length >= 8 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
                    (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام',

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
                    (v) => !!v || 'يجب ادخال الاسم',

                ],
                emailRules: [



                    // (v) => ( /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,5})+$/.test(v)) ||
                    // 'ألايميل غير صحيح',


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
                            url: "/owner/v2/UpdatePassword",
                            data: data,
                            headers: {

                            }
                        })
                        .then(response => {
                            response
                            this.password_Dailog = false;
                            this.$swal('', "تم تغير الباسورد بنجاح ", 'success')



                        })
                        .catch(error => {
                            if (error.response) {

                                this.error_msg = [];
                                if (error.response.data.data.password ==
                                    "The password confirmation does not match.") {
                                    // this.scrollToTop();
                                    this.error_msg.push('الباسوردان غير متاطبقان');

                                }

                                if (error.response.data.data ==
                                    "old password false") {
                                    //  this.scrollToTop();
                                    this.error_msg.push('الباسورد السابق غير صحيح');

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
                        url: 'https://tctate.com/api/api/owner/v2/OwnerInfo',
                       headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
                        }

                    }).then(response => {

                        this.owner = response.data.data;
                        this.images = [];

                        if (this.owner.user.image !== null) {


                            this.img_id = this.owner.user.image.id;
                            this.owner.user.image !== null && this.owner.user.image !== null ? this.images[0] ="https://tctate.com/api/images/" + this.owner.user.image.image_url :

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

                if (this.images[0].includes(this.http)) {
                    this.images[0] = "data:image/jpeg;base64";



                }



                if (this.images[0] == "ab") {

                    this.images = []

                }



                if (this.$refs.form.validate() && !this.loading) {



                    var data = {

                        full_name: this.owner.user.full_name,
                        owner_email: this.owner.owner_email,
                        owner_barnd_name: this.owner.owner_barnd_name,
                        images: this.images,

                    };
                    this.loading = true;
                    // this.description=JSON.stringify(data);
                    this.$http({
                            method: 'post',
                            url: "https://tctate.com/api/api/owner/v2/UpdateInfo",
                            data: data,
                          headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token
                        }
                        })
                        .then(response => {
                            response
                            this.$swal('', "   تم تعديل الملعومات بنجاح  ", 'success')
                        //    EventBus.$emit('changeUserInfo', true);


                            this.GetOwnerInfo();



                            // this.signup_user_id = response.data.id;

                            // this.dialog = false;
                            // this.verfy_Dailog = true;
                            // this.Search();

                            // //   this.$swal('', "   تم اضافة تاجر", 'success')



                        })
                        .catch(error => {
                            error
                             this.$swal('', "   تم تعديل الملعومات بنجاح  ", 'success')
                            //this.verfy_Dailog = true;

                            if (error.response) {
                                if (error.response.data.data.owner_email ==
                                    "The owner email has already been taken.") {
                                    this.$swal('', " الايميل مستخدم", 'error')
                                } else {
                                    this.$swal('', "  لم تم اضافة تاجر ", 'error')
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
                    title: "هل انت متاكد من الحذف ؟",

                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'

                }).then((result) => {
                    if (result.value) {
                        this.img_cliced = index;


                        this.images[0] = 'ab';
                        this.img_name = 'ghjk'


                        var url = "/owner/v2/DeleteImage/" + img_id;
                        axios({
                            method: 'delete',
                            url: url,
                            headers: {

                            }

                        }).then(response => {

                            response,

                            this.editedItem.images[index].image_url = 'a';
                            //not important
                            this.img_name = index;
                            this.images[index] = 'a';

                            this.GetOwnerInfo();

                            Swal.fire(
                                'تم الحذف بنجاح',
                                '',
                                'success'
                            )

                        }).catch(error => {
                            error
                            //     this.$swal('خطاء', "خطاء بالاتصال", 'error')
                        }).finally(d => {
                            d
                           location.reload();
                            ///  this.update();
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