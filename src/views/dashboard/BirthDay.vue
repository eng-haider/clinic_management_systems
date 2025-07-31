<template>

    <div>



        <v-container id="dashboard" fluid tag="section">
            <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" @page-count="pageCount = $event"
                hide-default-footer :items="desserts" class="elevation-1 request_table" items-per-page="15">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.birthDay") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>

                    </v-toolbar>



                </template>


                <template v-slot:[`item.names`]="{ item }">

                    <span>
                        {{item.name}}
                    </span>



                </template>


                <template v-slot:[`item.phones`]="{ item }">


                    <p style="    direction: ltr;
    text-align: end;"> {{item.phone}}</p>



                </template>
                <template v-slot:[`item.sex`]="{ item }">
                    <span v-if="item.sex==1">{{ $t("male") }}</span>
                    <span v-else>{{ $t("female") }}</span>
                </template>

                <template v-slot:[`item.doctor`]="{ item }">


                    <div v-if="item.doctors.length>1">
                        <span style="display: none;">{{ item }}</span>
                        <v-chip style="margin:2px" color="primary" v-for="item in  item.doctors" :key="item">
                            <v-icon left>
                                mdi-account-circle-outline
                            </v-icon>{{ item.name }}
                        </v-chip>
                    </div>

                </template>













                <template v-slot:[`item.actions`]="{ item }">







                    <v-btn color="green" style="color: #fff" :href="`https://wa.me/${convertPhoneNumber(item.phone)}?text=${encodeURIComponent(`عيد ميلاد سعيد من عيادة ${$store.state.AdminInfo.clinics_info.name}

نتمنى لك عيد ميلاد مليء بالسعادة والصحة الجيدة. نحن في عيادة ${$store.state.AdminInfo.clinics_info.name} نود أن نعبّر لك عن خالص تمنياتنا بعيد ميلاد سعيد ومميز. شكراً لك على ثقتك بنا ونتطلع إلى استمرار تقديم أفضل الرعاية الصحية لك.

استمتع بيومك الخاص، ونتمنى لك عاماً قادماً مليئاً بالنجاحات والأوقات السعيدة.`)}`" target="_blank">
                        <v-icon @click.stop="deleteItem(item)" v-if="!item.isDeleted" v-bind="attrs" v-on="on">
                            mdi-f5a3
                        </v-icon>
                        ارســــال تهنئة
                    </v-btn>

                </template>
                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize">{{ $t("Reloading") }}</v-btn>
                </template>
            </v-data-table>

        </v-container>
        <div class="text-center pt-2">
            <v-pagination v-model="page" @input="goTop()" :length="pageCount"></v-pagination>

        </div>

    </div>
</template>

<script>
    //Recipe
    // import cases from './case.vue';
    // import billsReport from './billsReport.vue';

    import {
        EventBus
    } from "./event-bus.js";
    // import Recipe from './Recipe.vue';
    // import OwnerBooking from './sub_components/ownerBookinfDed.vue'
    // import Bill from './sub_components/billsReport.vue'
    import Swal from "sweetalert2";



    import {
        mask
    } from "vue-the-mask";
    import Axios from "axios";
    export default {
        directives: {
            mask,
        },
        components: {
            // billsReport,
            // OwnerBooking,
            // cases,
            // Recipe,
            // Bill
        },
        data() {
            return {
                gocase: false,
                desserts: [

                ],
                bill: false,
                paymentsCount: 1,
                booking: false,
                cats: [],
                patientInfo: {},
                loadingData: true,
                allItem: false,
                RecipeInfo: {},


                pageCount: 11,
                page: 1,

                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu: [],
                imgCount: 1,
                images: [],

                selecBill: {},
                dialog: false,
                mask: "07XX XXX XXXXX",
                valid: false,
                loadSave: false,
                casesheet: false,
                CaseCategories: [],
                rules: {
                    minPhon: (v) => (v.length == 13 || v.length == 0) || "رقم الهاتف يجب ان يتكون من 11 رقم",
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
                editedIndex: -1,
                recipes: [],
                doctorsAll: [],
                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                search: '',
                progressValue: 0,
                searchDocorId: '',


                editedItem: {
                    name: "",
                    age: "",
                    birth_date: "",
                    sex: "",
                    address: "",
                    phone: "",
                    doctors: "",
                    systemic_conditions: "",
                    case: {
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
                    }
                },
                items: [

                ],
                doctors: [],
                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "names"
                    }, {
                        text: this.$t('datatable.phone'),
                        align: "start",
                        value: "phones"
                    },

                    {
                        text: this.$t('datatable.age'),
                        align: "start",
                        value: "age"
                    },
                    {
                        text: this.$t('datatable.sex'),
                        align: "start",
                        value: "sex"
                    },

                    // {
                    //     text: this.$t('datatable.doctor'),
                    //     align: "start",
                    //     value: "doctor"
                    // },


                    //status_Description


                    //booking
                    //booking
                    {
                        text: '',
                        value: "actions",
                        sortable: false
                    }
                ],
                right: null
            }
        },

        methods: {

            convertPhoneNumber(phone) {

                if (phone !== null) {
                    // Remove spaces
                    phone = phone.replace(/\s+/g, '');

                    // Check if the phone number starts with '0' and replace it with '+964'
                    if (phone.startsWith('0')) {
                        phone = '+964' + phone.substring(1);
                    }

                    return phone;
                }
            },
            getrecipes() {

                Axios.get("getrecipes", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {

                        this.recipes = res.data;




                    })


            },
            getByDocor() {

                Axios.get("patients/getPatientsBirthDay", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        //  this.search = null;
                        this.allItem = true;
                        this.desserts = [];
                        this.desserts = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            goTop() {
                if (/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

                    window.scrollTo(0, 0);

                }

            },
            openbill(item) {
                item

                this.patientInfo = item;
                this.bill = true;
            },
            addCase(item) {

                // Farce de racines this.patientInfo = item;
                this.patientInfo = {
                    case_categores_id: "",
                    upper_right: "",
                    tooth_num: [],
                    upper_left: "",
                    root_stuffing: {
                        "access_opening": [
                            ['', '', '', '']
                        ],
                        "oburation": [
                            ['', '', '', '']
                        ],
                    },
                    patient_id: item.id,
                    lower_right: "",
                    lower_left: "",
                    case_categories: {
                        name_ar: ''
                    },
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
                this.casesheet = true;
                if (this.patientInfo.images.length > 0) {
                    this.casesheet = true;
                }

                // if (/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                //     this.casesheet = true;
                //  //   this.$router.push("/case/" + item.id)

                // } else {

                //     this.casesheet = true;
                // }

            },
            addbooking(item) {
                this.patientInfo = item;
                this.booking = true;
            },
            addRecipe(item) {


                this.RecipeInfo = item;
                if (item.case == null) {
                    this.RecipeInfo.case = {
                        name_ar: "",
                        id: ""
                    }
                }

                this.Recipe = true;
                this.dialog = false;

            },
            print() {


                this.$htmlToPaper('printMe');
            },
            sumPay() {
                let sum = 0;
                if (this.editedItem.case.bills.length == 1) {

                    //  return 0;
                }
                for (let i = 0; i < this.editedItem.case.bills.length; i++) {
                    sum += parseInt(this.editedItem.case.bills[i].price);
                }


                if (isNaN(sum)) {
                    return 0;
                }
                return sum
            },

            deletePayment(index, id) {


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
                        this.editedItem.case.bills.splice(index, 1);
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
                this.editedItem.case.bills.push({

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

            deleteItem(item) {


                Swal.fire({
                    title: this.$t('sure_process'),
                    text: "",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: this.$t('yes'),
                    cancelButtonText: this.$t('no'),
                }).then(result => {
                    if (result.value) {
                        Axios.delete("patients/" + item.id, {
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
                    this.editedItem.case.images = [{
                        'img': [this.imageSource],
                        'descrption': this.editedItem.case.images[0].descrption
                    }];

                    //      this.imageSource= '',
                    //   this.textVisible= true,
                    //   this.progressVisible=false,
                    //   this.progressValue= 0
                    //   this.imgCount=this.imgCount+1;

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
                console.log(item.doctors)
                var doc = [];
                item.doctors.forEach((item, index) => {
                    index
                    doc.push(item.id)
                })
                item.doctors = doc;

                this.editedItem = Object.assign({}, item);

                this.selecBill = Object.assign({}, this.editedItem);

                if (this.editedItem.case == null) {
                    this.editedItem.case = {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        lower_right: "",
                        lower_left: "",
                        root_stuffing: '',
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
                if (this.editedItem.case.bills.length == 0) {
                    this.editedItem.case.bills = [{
                        price: '',
                        PaymentDate: ''
                    }]

                }


                if (this.editedItem.case.bills.length == 0) {
                    this.editedItem.case.images = [{
                            img: '',
                            descrption: ''
                        }

                    ]

                }
                if (this.editedItem.case.images.length > 0) {
                    this.imageSource = this.Url + this.editedItem.case.images[0].image_url;

                }
                if (this.editedItem.root_stuffing !== undefined) {
                    this.editedItem.root_stuffing = JSON.parse(this.editedItem.root_stuffing);
                }

                // this.editedItem.root_stuffing=JSON.parse(this.editedItem.root_stuffing); 
                this.dialog = true;
            },
            close() {
                this.dialog = false;
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
                        lower_left: "",
                        sessions: [{
                            note: '',
                            date: ''
                        }],
                        images: [{
                            images: '',
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
            seachs() {
                Axios.get("patients/search/" + this.search, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        //  this.search = null;
                        this.allItem = true;
                        this.desserts = [];
                        this.desserts = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },


            getclinicDoctor() {
                this.loading = true;
                Axios.get("doctors/clinic", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loadingData = false;
                        this.loading = false;
                        this.doctors = res.data.data;
                        this.doctorsAll.push({
                            id: 0,
                            name: ' الكل'
                        });
                        this.doctors.forEach((item, index) => {
                            index
                            this.doctorsAll.push(item)
                        })



                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },


            initialize() {
                this.loading = true;
                Axios.get("patients/getPatientsBirthDay", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loadingData = false;
                        this.loading = false;
                        this.search = null;
                        this.desserts = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            getCaseCategories() {


                Axios.get("case-categories", {
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



            SaveCase(id) {


                if (this.editedIndex > -1 && this.editedItem.case.id !== undefined) {


                    this.axios
                        .patch("cases/" + this.editedItem.case.id, this.editedItem.case, {
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

                    this.editedItem.case.patient_id = id;
                    // alert( this.editedItem.case.patient_id);
                    this.axios
                        .post("cases", this.editedItem.case, {
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                            },
                        })
                        .then(() => {


                            //cases
                            this.loadSave = false;
                            this.initialize();
                            this.editedIndex = -1;
                            this.close();
                            this.$swal.fire({
                                title: 'تمت اضافه مراجع جديد',
                                text: "",
                                icon: "success",
                                confirmButtonText: this.$t('close'),
                            });
                        })
                        .catch((err) => {
                            err

                            this.loadSave = false;

                        });
                }


            },

            save() {

                if (this.$refs.form.validate()) {
                    this.loadSave = true;
                    if (this.editedIndex > -1) {

                        this.axios
                            .patch("patients/" + this.editedItem.id, this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then(() => {
                                this.loadSave = false;
                                /// this.casesheet = true;

                                //  this.SaveCase();
                                this.initialize();
                                this.close();
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
                            .post("patients", this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then((res) => {
                                res

                                this.$swal.fire({
                                    title: this.$t('Added'),
                                    text: "",
                                    icon: "success",
                                    confirmButtonText: this.$t('close'),
                                });

                                this.patientInfo = res.data.data;
                                this.dialog = false,
                                    this.initialize();


                                if (!this.$store.getters.isSecretary) {
                                    this.gocase = false;
                                    this.addCase(this.patientInfo);
                                }




                            })
                            .catch((err) => {
                                err

                                this.loadSave = false;

                            });
                    }
                }

            },
            getDectors() {
                // this.doctors.push({
                //     'id': this.$store.state.AdminInfo.id,
                //     'name': this.$store.state.AdminInfo.name

                // })
            }

        },


        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('patients.addnewpatients') : this.$t('update');

            },
        },
        mounted() {
            this.getCaseCategories();
        },
        created() {
            this.getrecipes();
            this.getclinicDoctor();
            this.getCaseCategories();
            EventBus.$on("billsReportclose", (tooth) => {

                tooth
                this.bill = false;

            });
            //changeStatusCloseCase

            EventBus.$on("changeStatusCloseCase", (from) => {

                from

                this.casesheet = false;
                ///  this.dialog = true
            });
            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                //  this.dialog = true
            });

            this.initialize();

            // this.getDectors();

        },

    }
</script>

<style>
    /* #dropzone-external {
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
    } */
</style>

<style>
    #my-strictly-unique-vue-upload-multiple-image {
        font-family: 'Avenir', Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-align: center;
        color: #2c3e50;
        margin-top: 60px;
    }

    h1,
    h2 {
        font-weight: normal;
    }

    ul {
        list-style-type: none;
        padding: 0;
    }

    li {
        display: inline-block;
        margin: 0 10px;
    }

    a {
        color: #42b983;
    }

    .se_tit_menu {
        text-align: center;
        font-size: 22px;
        color: #19537a;
        font-weight: bold;
        font-size: 27px;
        position: relative;
        bottom: 10px;
    }




    @media only screen and (max-width: 600px) {
        .allsee {
            display: none;
        }
    }
</style>