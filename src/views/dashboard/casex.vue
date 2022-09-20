<template>
    <div>
        <v-form ref="form" v-model="valid">
            <v-card style="padding: 8px;">
                <v-toolbar dark color="primary lighten-1 mb-5">
                    <v-toolbar-title>
                        <h3 style="color:#fff;font-family: 'Cairo';font-size: 19px;"> {{formTitle}}</h3>
                    </v-toolbar-title>
                    <v-spacer />
                    <v-btn @click="close()" text>
                        <i class="fas fa-chevron-right"></i>     رجــوع  
                    </v-btn>
                </v-toolbar>
           
            
               
                        <div class="patiant_status">
                            <v-chip class="ma-2" color="primary">
                                اختر السن :
                            </v-chip>
                            <div v-if="editedItem.id !== undefined && editedItem.id < 87">
                                <v-layout row wrap dense>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>
                                    <v-flex xs6 md4>
                                        <v-text-field type="number" v-model="editedItem.upper_right" filled
                                            label="العلوي الايمين"></v-text-field>
                                    </v-flex>
                                    <v-flex xs6 md4 style="    padding: 0px !important;padding-right:5px">
                                        <v-text-field v-model="editedItem.upper_left" type="number" filled
                                            label="العلوي الايسر"></v-text-field>
                                    </v-flex>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>

                                </v-layout>



                                <v-layout row wrap dense>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>
                                    <v-flex xs6 md4>
                                        <v-text-field v-model="editedItem.lower_right" type="number" dense filled
                                            label=" الاسفل الايمين ">
                                        </v-text-field>
                                    </v-flex>
                                    <v-flex xs6 md4 style="    padding: 0px !important;padding-right:5px">
                                        <v-text-field v-model="editedItem.lower_left" type="number" dense filled
                                            label="الاسفل الايسر "></v-text-field>
                                    </v-flex>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>

                                </v-layout>
                            </div>

                            <div v-else>
                                <teeth :tooth_num="editedItem.tooth_num" />

                            </div>

                            <v-row row wrap>
                                <v-col cols="12" md="3"></v-col>
                                <v-col cols="12" md="6" sm="6">
                                    <v-select :rules="[rules.required]" dense v-model="editedItem.case_categores_id"
                                        label=" نوع الحاله " :items="CaseCategories" outlined item-text="name_ar"
                                        item-value="id"></v-select>
                                </v-col>
                                <v-col cols="12" md="3"></v-col>

                            </v-row>





                            <v-row row wrap v-for="(note, index) in editedItem.sessions" :key="note">
                                <v-col cols="12" md="2" style="    padding: 0px !important;">


                                    <v-chip class="ma-2" color="primary">
                                        الجلسه رقم : {{index+1}}
                                    </v-chip>

                                </v-col>
                                <v-col cols="12" md="8" sm="8" style="    padding: 0px !important;">

                                    <v-textarea v-model="editedItem.sessions[index].note" outlined name="input-7-1"
                                        label="ملاحظات الجلسه ">
                                    </v-textarea>
                                </v-col>

                                <v-col cols="12" md="2" v-if="editedItem.sessions[index].date==''">

                                    <p :class="$vuetify.breakpoint.xs ? 'onl_ph_datea' : ''">
                                        {{ moment(new Date()).format('DD-MM-YYYY') }}</p>



                                </v-col>

                                <v-col cols="12" md="2" v-else>

                                    <p :class="$vuetify.breakpoint.xs ? 'onl_ph_datea' : ''">
                                        {{ moment(editedItem.sessions[index].date).format('DD-MM-YYYY') }}</p>


                                </v-col>


                            </v-row>

                            <v-card-actions class="justify-center">
                                <v-btn color="green" style="color:#fff" @click="addSession()">
                                    اضافه جلسه
                                </v-btn>
                            </v-card-actions>


                            <v-row>





                            </v-row>



                            <v-row row wrap>
                                <v-col cols="12" md="2" style="    padding: 0px !important;">

                                </v-col>
                                <v-col cols="12" md="8" sm="8" style="    padding: 0px !important;">
                                    <v-radio-group row v-model="editedItem.status_id">
                                        <template v-slot:label>


                                        </template>
                                        <v-radio :value="42">
                                            <template v-slot:label>
                                                <div><strong class="error--text">غير مكتمله</strong></div>
                                            </template>
                                        </v-radio>
                                        <v-radio :value="43">
                                            <template v-slot:label>
                                                <div> <strong class="success--text">مكتمله</strong></div>
                                            </template>
                                        </v-radio>
                                    </v-radio-group>
                                </v-col>
                                <v-col cols="12" md="2" style="    padding: 0px !important;">
                                </v-col>
                            </v-row>




                            <br>
                            <br>


                            <v-row row wrap style="height: auto;">
                                <v-col cols="12" md="12">

                                    <div class="widget-container flex-box" v-for="uu in imgCount" :key="uu"
                                        style="padding-right:10px">

                                        <div id="dropzone-external" class="flex-box" :class="[isDropZoneActive
        ? 'dx-theme-accent-as-border-color dropzone-active'
        : 'dx-theme-border-color']">
                                            <img id="dropzone-image" :src="imageSource" v-if="imageSource" alt="">
                                            <div id="dropzone-text" class="flex-box" v-if="textVisible">
                                                <span>اضغط هنا لرفع صوره الحاله</span>
                                                <span>…or click to browse for a file instead.</span>
                                            </div>
                                            <DxProgressBar id="upload-progress" :min="0" :max="100" width="30%"
                                                :show-status="false" :visible="progressVisible"
                                                :value="progressValue" />
                                        </div>
                                        <DxFileUploader id="file-uploader" dialog-trigger="#dropzone-external"
                                            drop-zone="#dropzone-external" :multiple="false"
                                            :allowed-file-extensions="allowedFileExtensions" upload-mode="instantly"
                                            upload-url="https://js.devexpress.com/Demos/NetCore/FileUploader/Upload"
                                            :visible="false" @drop-zone-enter="onDropZoneEnter"
                                            @drop-zone-leave="onDropZoneLeave" @uploaded="onUploaded"
                                            @progress="onProgress" @upload-started="onUploadStarted" />

                                        <v-text-field style="width:100$;padding-top:20px;" dense
                                            v-model="editedItem.images[0].descrption" label="وصف الصوره " outlined>
                                        </v-text-field>
                                    </div>


                                </v-col>
                            </v-row>
                        </div>
                        <br>



                        <v-layout row wrap>
                            <v-flex md5>
                                <hr>
                            </v-flex>
                            <v-flex md2>
                                <p class="se_tit_menu">

                                    الفاتوره

                                </p>
                            </v-flex>

                            <v-flex md5>
                                <hr>
                            </v-flex>
                        </v-layout>

                        <v-row row wrap>
                            <v-col md="2" class="d-none d-sm-flex"></v-col>
                            <v-col md="8">
                                <v-text-field suffix="IQ" dense v-model="editedItem.price" type="number"
                                    label="مبلغ الحاله" outlined>
                                </v-text-field>

                            </v-col>
                            <v-col md="2" class="d-none d-sm-flex"></v-col>
                        </v-row>





                        <v-layout row wrap v-for="(item, index) in  editedItem.bills" :key="index">

                            <v-col md2 class="d-none d-sm-flex"></v-col>
                            <v-flex md="4" md4 xs6>
                                <v-text-field suffix="IQ" dense v-model="editedItem.bills[index].price" type="number"
                                    label="الملغ المدفوع" outlined>
                                </v-text-field>

                            </v-flex>

                            <v-flex md4 xs5>
                                <v-menu v-model="menu[editedItem.bills.indexOf(item)]" :close-on-content-click="false"
                                    :nudge-right="40" lazy transition="scale-transition" offset-y full-width
                                    max-width="290px" min-width="290px">
                                    <template v-slot:activator="{ on }">
                                        <v-text-field dense outlined v-model="editedItem.bills[index].PaymentDate"
                                            label="Date" prepend-icon="mdi-calendar" readonly v-on="on"></v-text-field>
                                    </template>
                                    <v-date-picker v-model="editedItem.bills[index].PaymentDate" no-title
                                        @input="menu[editedItem.bills.indexOf(item)] = false">
                                    </v-date-picker>
                                </v-menu>




                            </v-flex>
                            <v-flex xs1 md1>
                                <v-btn color="red" v-if="index !== 0" @click="deletePayment(index,item.id)"
                                    style="color:#fff">
                                    حذف الدفعه
                                </v-btn>
                            </v-flex>

                            <v-flex md1 class="d-none d-sm-flex">

                            </v-flex>
                        </v-layout>

                        <v-card-actions class="justify-center">
                            <v-btn color="green" style="color:#fff" @click="addPayment()">
                                اضافه دفعه
                            </v-btn>
                        </v-card-actions>











                        <v-row>
                            <v-col md="2"></v-col>
                            <v-col md="4">
                                <p style="font-weight:bold"> المبلغ المدفوع :
                                    <span>{{sumPay()}}</span> <span style="font-weight:bold;color:red">IQ</span></p>
                                <p style="font-weight:bold"> المتبقي :
                                    {{editedItem.price-sumPay()}} <span style="font-weight:bold;color:red">IQ</span></p>
                                <v-btn color="#2196f3" @click="print()" style="color:#fff">
                                    طبـــــاعه الفاتوره


                                    <v-icon right dark>
                                        fas fa-print
                                    </v-icon>
                                </v-btn>
                            </v-col>
                            <v-col md="2"></v-col>

                        </v-row>

                  

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                    </v-btn>
                    <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>
                        {{ $t("save") }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
    </div>
</template>

<style>
    .onl_ph_datea {
        position: relative;
        bottom: 37px;
        float: left;

    }
</style>

<script>
    import teeth from '../../components/core/teeth.vue';
    import {
        EventBus
    } from "./event-bus.js";
    import moment from 'moment'

    import Swal from "sweetalert2";
    import {
        DxFileUploader
    } from 'devextreme-vue/file-uploader';
    import {
        DxProgressBar
    } from 'devextreme-vue/progress-bar';


    import Axios from "axios";
    export default {

        // props: {
        //     editedItem: Object,

        // },
        components: {
            DxFileUploader,
            teeth,
            DxProgressBar,
        },
        data() {
            return {
                desserts: [],
                editedItem: {
                 
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        lower_right: "",
                        lower_left: "",
                         sessions: [{
                            note: '',
                            date: ''
                        }],

                        case_categories: {
                            name_ar: ''
                        },
                        status_id: 42,
                        bills: [{
                            price: '',
                            PaymentDate: ''
                        }],
                        images: [{
                                img: '',
                                descrption: ''
                            }

                        ],
                        notes: ""
                    
                },
                paymentsCount: 1,
                moment: moment,
                cats: [],
                valid: false,

                RecipeInfo: {},
                status: [{
                        id: '42',
                        name: 'لم تنمنهي'
                    },
                    {
                        id: '43',
                        name: 'مكتمله'
                    }
                ],
                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu: [],

                rules: {
                    minPhon: (v) => v.length == 13 || "رقم الهاتف يجب ان يتكون من 11 رقم",
                    required: value => !!value || "مطلوب",
                    min: (v) => v.length >= 6 || "كلمة المرور يجب ان تتكون من 6 عناصر او اكثر",
                    email: value => {
                        if (value.length > 0) {
                            const pattern =
                                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return pattern.test(value) || 'يجب ان يكون ايميل صحيح';
                        }
                    },
                },
                imgCount: 1,
                images: [],
                selecBill: {},

                dialog: false,
                loadSave: false,
                CaseCategories: [],
                editedIndex: -1,

                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                progressValue: 0,
                allowedFileExtensions: ['.jpg', '.jpeg', '.gif', '.png'],



                // editedItem: {
                //     name: "",
                //     age: "",
                //     user_id: "",
                //     sex: "",
                //     phone: "",
                //     systemic_conditions: "",
                //     case: {
                //         case_categores_id: "",
                //         upper_right: "",
                //         upper_left: "",
                //         lower_right: "",
                //         lower_left: "",
                //         case_categories: {
                //             name_ar: ''
                //         },
                //         status_id: 1,
                //         bills: [{
                //             price: null,
                //             PaymentDate: ''
                //         }],
                //         images: [{
                //                 img: null,
                //                 descrption: ''
                //             }

                //         ],
                //         notes: ""
                //     }
                // },
                items: [

                ],
                doctors: [],
                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "name"
                    }, {
                        text: this.$t('datatable.phone'),
                        align: "start",
                        value: "phone"
                    },

                    {
                        text: this.$t('datatable.age'),
                        align: "start",
                        value: "age"
                    },
                    {
                        text: this.$t('datatable.status_Description'),
                        align: "start",
                        value: "case.notes"
                    },


                    {
                        text: this.$t('Processes'),
                        value: "actions",
                        sortable: false
                    }
                ],
                right: null
            }
        },

        methods: {
            addSession() {
                this.editedItem.sessions.push({

                    note: '',
                    date: ''


                })
            },
            addRecipe() {




                this.Recipe = true;
                this.dialog = false;

            },
            print() {


                this.$htmlToPaper('printMe');
            },
            sumPay() {
                // let sum = 0;
                // if (this.editedItem.bills.length == 1) {

                //     //  return 0;
                // }
                // for (let i = 0; i < this.editedItem.bills.length; i++) {
                //     sum += parseInt(this.editedItem.bills[i].price);
                // }


                // if (isNaN(sum)) {
                //     return 0;
                // }
                // return sum
            },

            deletePayment(index, id) {
                // this.sumPay();

                Swal.fire({
                    title: this.$t('sure_process'),
                    text: "",
                    heightAuto: false,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: this.$t('yes'),
                    cancelButtonText: this.$t('no'),
                }).then(result => {
                    if (result.value) {
                        this.editedItem.bills.splice(index, 1);
                        Axios.delete("bills/" + id, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                                }
                            })
                            .then(() => {
                                this.$swal.fire(this.$t('Successfully'), this.$t('done'), "success");
                                this.initialize();
                            })
                            .catch(() => {
                                this.$swal.fire(this.$t('not_successful'), this.$t('not_done'), "error");
                            });
                    }
                });




            },
            addPayment() {
                this.editedItem.bills.push({

                    price: '',
                    PaymentDate: ''


                })
            },

            //uploude photos
            onDropZoneEnter(e) {
                if (e.dropZoneElement.id === 'dropzone-external') {
                    this.isDropZoneActive = true;
                }
            },



            onDropZoneLeave(e) {
                if (e.dropZoneElement.id === 'dropzone-external') {
                    this.isDropZoneActive = false;
                }
            },
            onUploaded(e) {
                const {
                    file
                } = e;
                const fileReader = new FileReader();
                fileReader.onload = () => {
                    this.isDropZoneActive = false;
                    this.imageSource = fileReader.result;
                    this.editedItem.images = [{
                        'img': [this.imageSource],
                        'descrption': this.editedItem.images[0].descrption
                    }];


                };
                fileReader.readAsDataURL(file);
                this.textVisible = false;
                this.progressVisible = false;
                this.progressValue = 0;
            },
            onProgress(e) {
                this.progressValue = (e.bytesLoaded / e.bytesTotal) * 100;


            },
            onUploadStarted() {
                this.imageSource = '';
                this.progressVisible = true;
            },

            editItem(item) {
                this.editedIndex = this.desserts.indexOf(item);

                this.editedItem = Object.assign({}, item);
                this.selecBill = Object.assign({}, this.editedItem);

                if (this.editedItem == null) {
                    this.editedItem = {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        lower_right: "",
                        lower_left: "",
                        tooth_num: "",
                        status_id: 42,

                        bills: [{
                            price: '',
                            PaymentDate: ''
                        }],
                        sessions: [{
                            note: '',
                            date: ''
                        }],
                        images: [{
                                img: '',
                                descrption: ''
                            }

                        ],
                        notes: ""
                    }

                }
                if (this.editedItem.bills.length == 0) {
                    this.editedItem.bills = [{
                        price: '',
                        PaymentDate: ''
                    }]

                }


                if (this.editedItem.bills.length == 0) {
                    this.editedItem.images = [{
                            img: '',
                            descrption: ''
                        }

                    ]

                }
                if (this.editedItem.images.length > 0) {
                    this.imageSource = '/images/' + this.editedItem.images[0].image_url;

                }


                this.dialog = true;
            },
            close() {
                this.$router.go(-1);
               // this.dialog = false;
                EventBus.$emit("changeStatusCloseCase", false);
                this.selecBill = {
                    name: "",
                    age: "",
                    sex: "",
                    phone: "",
                    systemic_conditions: "",
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        patient_id: "",
                        lower_right: "",
                        lower_left: "",
                        status_id: 42,
                        bills: [{
                            price: '',
                            PaymentDate: ''
                        }],
                        sessions: [{
                            note: '',
                            date: ''
                        }],
                        images: [{
                                img: '',
                                descrption: ''
                            }

                        ],
                        notes: ""
                    }
                };
                this.editedItem = {
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        patient_id: "",
                        lower_right: "",
                        tooth_num: "",
                        lower_left: "",
                        images: [{
                            img: '',
                            descrption: ''

                        }],
                        bills: [{
                            price: '',
                            PaymentDate: ''
                        }],
                        status_id: 42,
                        notes: ""
                    }
                };


            },


            initialize() {
                this.loading = true;
                Axios.get("patients/getByUserId", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.desserts = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            getCaseCategories() {


                Axios.get("cases/CaseCategories", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.CaseCategories = res.data;

                    })
                    .catch(() => {
                        this.loading = false;
                    });

            },



            save() {

                if (this.$refs.form.validate()) {

                    this.editedItem.patient_id=this.$route.params.id;
                    this.loadSave = true;
                    // if (this.editedIndex > -1 && this.editedItem.id !== undefined) {
                    if (this.editedItem.id !== undefined) {

                        if (this.editedItem.bills[0].price == "") {
                            this.editedItem.bills = [];
                        }
                        this.axios
                            .patch("cases/" + this.$route.params.id, this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then(() => {
                                this.loadSave = false;
                                this.close();
                                this.initialize();

                                this.$swal.fire({
                                    title: "تم تعديل ",
                                    text: "",
                                    icon: "success",
                                    confirmButtonText: "اغلاق",
                                });
                            })
                            .catch(() => {
                                this.loadSave = false;

                                this.$swal.fire({
                                    title: "تاكد من ملى المعلومات",
                                    text: "",
                                    icon: "error",
                                    confirmButtonText: "اغلاق",
                                });
                            });
                    } else {


                        this.axios
                            .post("cases", this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then((res) => {

                                res
                                //cases
                                this.loadSave = false;
                                this.initialize();
                                this.editedIndex = -1;
                                this.close();
                                EventBus.$emit("changeStatusCloseCase", false);

                                this.$swal.fire({
                                    title: this.$t('Added'),
                                    text: "",
                                    icon: "success",
                                    confirmButtonText: this.$t('close'),
                                });

                                this.$router.push("/")
                                // this.$router.push({
                                //     name: 'showCases',
                                //     params: {
                                //         id: res.data.data.patient_id
                                //     }
                                // })

                            })
                            .catch((err) => {
                                err

                                this.loadSave = false;

                            });
                    }

                }
            },


            getDectors() {
                this.doctors.push({
                    'id': this.$store.state.AdminInfo.id,
                    'name': this.$store.state.AdminInfo.name

                })

                //              this.doctors.push({
                //     'id': this.$store.state.AdminInfo.id+1,
                //     'name': this.$store.state.AdminInfo.name

                // })
            }

        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'اضافه حاله جديده' : this.$t('update');

            },
        },
        created() {

            EventBus.$on("changetooth", (tooth) => {


                this.editedItem.tooth_num = tooth;

            });

            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                this.dialog = true
            });



            //     this.initialize();
            this.getCaseCategories();
            this.getDectors();

        },

    }
</script>

<style>
    #dropzone-external {
        width: 250px;
        height: 250px;
        background-color: rgba(183, 183, 183, 0.1);
        border-width: 2px;
        border-style: dashed;
        padding: 10px;
    }

    #dropzone-external>* {
        pointer-events: none;
    }

    #dropzone-external.dropzone-active {
        border-style: solid;
    }

    .widget-container>span {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    #dropzone-image {
        max-width: 100%;
        max-height: 100%;
    }

    #dropzone-text>span {
        font-weight: 100;
        opacity: 0.5;
    }

    #upload-progress {
        display: flex;
        margin-top: 10px;
    }

    .flex-box {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
</style>

<style>