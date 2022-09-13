<template>
    <v-card height="auto">
        <v-container grid-list-xs>
            <v-col>

                <v-row class="mb-3" style="padding-right: 10px;">
                    <h2 style="color:#000">معلومات الحجز</h2>
                </v-row>

                <v-divider class="mb-3"></v-divider>
                <v-row class="mb-2" justify="center">
                    <v-col cols="4">

                        <v-row class="mb-2 mr-2">
                            <v-icon size="20" style="padding-left:5px">fas fa-user</v-icon>
                            <label>اسم المراجع</label>
                        </v-row>

                        <v-row class="mb-2 mr-2">
                            <v-icon size="20" style="padding-left:5px">fas fa-phone</v-icon>
                            <label>رقم هاتف المراجع</label>
                        </v-row>
<!-- 
                        <v-row class="mb-2 mr-2">
                            <v-icon size="20" style="padding-left:5px">fas fa-bookmark</v-icon>
                            <label>اسم الخدمه</label>
                        </v-row> -->

                        <v-row class="mb-2 mr-2">
                            <v-icon size="20" style="padding-left:5px">fas fa-calendar-day</v-icon>
                            <label> تاريخ الحجز</label>
                        </v-row>

                        <v-row class="mb-2 mr-2">
                            <v-icon size="20" style="padding-left:5px">fas fa-clock</v-icon>
                            <label> وقت الحجز</label>
                        </v-row>

                    </v-col>

                    <v-col>
                        <v-row class="mb-2"><label> {{reserverionsInfo.user.full_name}}</label></v-row>
                        <v-row class="mb-2"><label> {{reserverionsInfo.user.user_phone}}</label></v-row>
                        <!-- <v-row class="mb-2"><label> {{reserverionsInfo.item.item_name}}</label></v-row> -->
                        <v-row class="mb-2"><label>



                                {{reserverionsInfo.reservation_start_date}}
                                <!-- <v-chip style="margin-left:5px;margin-right:5px;">></v-chip>
                    {{reserverionsInfo.to_date}} -->
                            </label>

                        </v-row>

                        <v-row class="mb-2"><label>

                                {{formatAMPM(reserverionsInfo.reservation_from_time)}}



                            </label>

                        </v-row>

                    </v-col>
                </v-row>


                <!-- <v-row class="mb-3" style="padding-right: 10px;">
                    <h2 style="color:#000">معلومات الدفع</h2>
                </v-row> -->


                <!-- <v-divider class="mb-3"></v-divider> -->

                <v-row class="mb-2" justify="center">
                    <v-col cols="4">


                        <!-- <v-row class="mb-2 mr-2">
                            <v-icon size="20" style="padding-left:5px;font-size: 29px;">fas fa-dollar-sign</v-icon>
                            <label>طريقه الدفع</label>
                        </v-row> -->
<!-- 
                        <v-row class="mb-2 mr-2" v-if="reserverionsInfo.item.price.payment_when_receiving==0">
            
                            <label>حاله الفاتوره</label>
                        </v-row>

                        <v-row v-else>

                        </v-row> -->


                    </v-col>

<!-- 
                    <v-col>
                        <v-row style="padding-top:5px" class="mb-2">
                          

                            <div v-if="reserverionsInfo.bill.status_id==32">

                                <v-chip style="color:#fff" color="red" class="ma-2">
                                    لم يتم التسديد
                                </v-chip>
                            </div>


                            <div v-else-if="reserverionsInfo.bill.status_id==31">
                                
                                <div v-if="reserverionsInfo.bill.payment_method_id==2">
                                    <v-chip style="color:#fff" color="green" class="ma-2">
                                        تم التسديد عن طريق فاتوره تسديد
                                    </v-chip>

                                </div>

                                <div v-else-if="reserverionsInfo.bill.payment_method_id==3">
                                    <v-chip style="color:#fff" color="green" class="ma-2">
                                        تم التسديد عن طريق زين كاش
                                    </v-chip>


                                </div>


                            </div>

                            <label v-else>الدفع
                                نقدا</label>

                        </v-row>
                        
                        <v-row class="mb-2" v-if="reserverionsInfo.item.price.payment_when_receiving==0">

                            <div v-if="reserverionsInfo.bill.tasdid_bills">
                                <v-chip style="color:#fff" color="green"
                                    v-if="reserverionsInfo.bill.tasdid_bills.Status==3 && reserverionsInfo.bill.status_id==31"
                                    class="ma-2">
                                    تم التسديد
                                </v-chip>

                                <v-chip style="color:#fff" color="red" v-else class="ma-2">
                                    لم يتم التسديد
                                </v-chip>

                            </div>

                            <div v-else>
                                <v-chip style="color:#fff" color="red" class="ma-2">
                                    لم يتم التسديد
                                </v-chip>
                            </div>




                        </v-row>
                    </v-col> -->
                </v-row>





                <row v-if="reserverionsInfo.ReservationRequirements.length>0">

                    <v-row class="mb-3" style="padding-right: 10px;">
                        <h2 style="color:#000">مرفقات الحجز</h2>
                    </v-row>

                    <!-- <v-divider class="mb-3"></v-divider> -->

                    <v-layout row wrap>
                        <v-flex xs4 v-for="item  in reserverionsInfo.ReservationRequirements" :key="item.id">
                            <div style="width:160px;height:160px">
                                {{item.image.image_url}}
                                <v-img :src="Url+'/images/'+item.image.image_url" style="width:100%;height:100%">

                                </v-img>



                            </div>

                        </v-flex>

                    </v-layout>

                </row>


            </v-col>


            <v-row v-if="reserverionsInfo.item.ItemsReservationRequirements.length>0 && reserverionsInfo.status.id ==4"
                class="mb-3" style="padding-right: 10px;padding-top:20px">
                <h2 style="color:#000">ارفاق صوره {{reserverionsInfo.status_id}}</h2>
                <!-- <v-divider class="mb-3"></v-divider> -->
            </v-row>



            <v-layout v-if="reserverionsInfo.item.ItemsReservationRequirements.length>0 ">

                <input type="file" style="display: none" ref="image" accept="image/*" @change="onFilePicked">
                <span style="color:#fff">


                    {{img_name}} </span>

                <v-row justify="center" class="mb-6">
                    <v-card height="150" width="200" flat>


                        <div v-if="reserverionsInfo.images.length >0 " style="width:200px;height:150px"
                            class="card_img">
                            <h5>الصوره المرسله</h5>
                            <v-img :src="Url+'/images/'+reserverionsInfo.images[0].image_url"
                                style="width:100%;height:100%" class="card_img">
                            </v-img>

                        </div>


                        <div v-if="images[0]!=='ab' && reserverionsInfo.status.id==4" class="card_img">
                            <v-img :src="images[0]" height="150" width="150" class="card_img">
                                <v-btn icon="" style="padding-right: 24px;position:relative;left: 46px;"
                                    @click="delete_img(img_id,0)">

                                    <v-icon color="#fff">fas fa-window-close</v-icon>
                                </v-btn>

                            </v-img>
                        </div>



                        <div v-if="images[0]=='ab'  && reserverionsInfo.status.id==4 " class="card_img">
                            <v-btn icon @click='pickFile()'>


                                <v-icon style="margin:0px" size="40">fas fa-plus-square</v-icon>
                            </v-btn>
                        </div>

                        <v-container fluid="" style="width:100%;height:100%" @>

                        </v-container>

                    </v-card>

                </v-row>





            </v-layout>

            <v-spacer></v-spacer>
            <v-container>
                <!-- <v-divider class="mb-3"></v-divider> -->
                <v-row justify="space-between"
                    v-if="reserverionsInfo.owner_notes!==null || reserverionsInfo.status.id==4">

                    <v-flex xs12>
                        <div v-if="reserverionsInfo.owner_notes!==null && reserverionsInfo.status.id !== 4">
                            <v-textarea background-color="#fff" disabled solo placeholder=" كتابه ملاحظه للزبون "
                                v-model="reserverionsInfo.owner_notes" required>
                            </v-textarea>
                        </div>


                        <v-textarea v-else background-color="#fff" solo placeholder=" كتابه ملاحظه للزبون "
                            v-model="reserverionsInfo.owner_notes" required>
                        </v-textarea>

                    </v-flex>
                </v-row>
                <v-row justify="space-between">

                    <span v-if="reserverionsInfo.status.id==4">




                        <v-btn color="success" @click="change_reservation_status(reserverionsInfo,6)" width="120"
                            style="margin-left:10px">
                            <span v-show="!loding_accept">
                                <v-icon right>fas fa-check</v-icon>
                                قبول الحجز
                            </span>
                            <v-progress-circular indeterminate color="white" v-show="loding_accept">
                            </v-progress-circular>
                        </v-btn>

                        <v-btn color="#f95252" width="120" dark=""
                            @click="change_reservation_status(reserverionsInfo,9)">

                            <span v-show="!loding_cancel">
                                <v-icon right size="18">fas fa-trash-alt</v-icon>
                                رفض الحجز
                            </span>
                            <v-progress-circular indeterminate color="white" v-show="loding_cancel">
                            </v-progress-circular>
                        </v-btn>


                    </span>

                    <span v-if="reserverionsInfo.status.id==6">

                        <v-btn color="success" @click="change_reservation_status(reserverionsInfo,10)"
                            style="margin-left:10px">
                            <!-- {{loding_accept}} -->
                            <span v-show="!loding_accept">
                                <v-icon right>fas fa-check</v-icon>
                                تم اكمال الحجز
                            </span>
                            <v-progress-circular indeterminate color="white" v-show="loding_accept">
                            </v-progress-circular>
                        </v-btn>

                    </span>




                    <v-btn color="#bdc3c7" @click="closeDialog()">
                        الغاء
                    </v-btn>
                </v-row>


            </v-container>


        </v-container>
    </v-card>
</template>

<script>
    import {
        EventBus
    } from '../event-bus.js';

    const swal = require('sweetalert2')

    export default {
        props: {

            'reserverionsInfo': Array
            
           

        },
        data() {
            return {

                images: ['ab'],
                img_name: '',
                owner_notes: '',
                loding_cancel: false,
                loding_accept: false,
            }
        },

        mounted() {


        },

        methods: {






            pickFile() {

                this.$refs.image.click()
            },



            formatAMPM(date) {

                //    this.images[0] = 'ab';
                //                 this.img_name = 'ghjk'

                if (typeof date === "string") {
                    let [hours, minutes] = date.split(":");
                    let ampm = "ص";

                    if (Number(hours) > 12) {
                        hours = Number(hours) - 12;
                        ampm = "م";
                    }

                    return `${hours}:${minutes} ${ampm}`;

                } else if (date instanceof Date) {
                    let hours = date.getHours();
                    let minutes = date.getMinutes();

                    let ampm = hours >= 12 ? 'م' : "AM";

                    hours = hours % 12;
                    hours = hours ? hours : 12; // the hour '0' should be '12'
                    minutes = minutes < 10 ? "0" + minutes : minutes;

                    let strTime = hours + ":" + minutes + " " + ampm;

                    return strTime;
                }

                return date;
            },
            closeDialog() {
                this.images[0] = 'ab';
                this.img_name = 'ghjk';
                EventBus.$emit('closeDialog', false);
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


                        // var url = "/v2/items/delete_image/" + img_id;
                        // this.$http({
                        //     method: 'post',
                        //     url: url,
                        //     headers: {

                        //     }

                        // }).then(response => {

                        //     response,


                        //     this.editedItem.images[index].image_url = 'a';
                        //     //not important
                        //     this.img_name = index;
                        //     this.images[index] = 'a';

                        //     this.GetOwnerInfo();

                        //     Swal.fire(
                        //         'تم الحذف بنجاح',
                        //         '',
                        //         'success'
                        //     )

                        // }).catch(error => {
                        //     error
                        //     //     this.$swal('خطاء', "خطاء بالاتصال", 'error')
                        // }).finally(d => {
                        //     d
                        //     location.reload();
                        //     ///  this.update();
                        // });



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







            change_reservation_status(item, item_status) {


                if (this.images.length > 0) {
                    if (this.images[0].includes(this.http)) {
                        this.images[0] = "data:image/jpeg;base64";


                    }


                    if (this.images[0] == "ab") {

                        this.images = []

                    }


                }

                var data = {
                    reservation_id: item.id,
                    status_id: item_status,
                    user_id: item.user.id,
                    images: this.images,
                  owner_notes: this.reserverionsInfo.owner_notes
                };
                const Swal = require('sweetalert2')


                Swal.fire({
                    title: "هل انت متاكد ",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم',
                    cancelButtonText: 'خروج',

                }).then((result) => {
                    if (result.value) {


                        this.loding_accept = true;

                        this.$http({
                                method: 'post',
                                url: "https://tctate.com/api/api/reservation/owner/changeStatus",
                                data: data,
                                headers: {

                                }
                            })
                            .then(response => {
                                response
                                this.loding_accept = false;
                                EventBus.$emit("changeStatus", false),
                                    swal("تمت الموافقة", {
                                        icon: "success",
                                    })



                            })


                            .catch(error => {
                                    this.loding_accept = false;
                                    if (error.response) {
                                        if (error.response.data.data == 'SubscriptionsPackages Expire') {
                                            this.$swal('', "حزمه الاشتراك الحاليه منتهيه يرجئ شراء حزمه جديده ",
                                                'error')

                                        }


                                    }
                                }

                            ).finally(v => {

                                    v


                                }


                            );



                    }
                })






            },

        }


    }
</script>