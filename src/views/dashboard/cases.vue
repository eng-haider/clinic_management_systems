<template>

    <div>

        <div id="printMe" style="display:none">
            <billsReport :info="selecBill"></billsReport>
        </div>

        <div>
            <OwnerBooking :patientFound="patientFound=true" :patientInfo="patientInfo" v-if="booking" />
        </div>

        <v-dialog v-model="casesheet" max-width="900px">

            <cases :doctors="doctors" :editedItem="editedItem" :CaseCategories="CaseCategoriess"></cases>
        </v-dialog>
        <v-dialog v-model="Recipe" max-width="900px">
            <Recipe :RecipeInfo="RecipeInfo" :CaseCategories="CaseCategoriess"></Recipe>
        </v-dialog>
        <v-container id="dashboard" fluid tag="section">
            <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" items-per-page="15"
                @page-count="pageCount = $event" :items="desserts" class="elevation-1 request_table">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.showCases") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>

                    </v-toolbar>


                    <v-layout row wrap pa-5>
                        <v-flex xs12 md3 sm3>

                            <v-select dense v-model="search.case_categores_id" label=" نوع الحاله "
                                :items="CaseCategories" outlined item-text="name_ar" item-value="id"></v-select>
                        </v-flex>

                        <v-flex xs12 md3 sm3>
                            <v-radio-group row v-model="search.status_id">
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
                        </v-flex>


                        <v-flex xs3 md1 sm1 pa-5>
                            <v-btn color="green" style="color:#fff" @click="seachs()">بحــث</v-btn>
                        </v-flex>

                        <v-flex xs3 md1 sm1 pt-5 pb-5 pr-2>
                            <v-btn color="blue" v-if="allItem" style="color:#fff;float: left;"
                                @click="initialize();allItem=false">الكل</v-btn>
                        </v-flex>


                        <v-flex xs1 md1 sm1></v-flex>
                        <v-flex xs11 md3 sm3 pt-5 style="float: left;">
                            <v-select dense @input="getByDocor()"
                                v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors')  && doctorsAll.length>2"
                                v-model="searchDocorId" :label="$t('doctor')" :items="doctorsAll" outlined
                                item-text="name" item-value="id">
                            </v-select>
                        </v-flex>


                    </v-layout>
                </template>


                <template v-slot:[`item.names`]="{ item }">

                    <span>
                        {{item.name}}
                    </span>



                </template>

                <template v-slot:[`item.doctor`]="{ item }"
                   >

                    <div   v-if="item.doctors.length>0">
                    <span style="display: none;">{{ item }}</span>
                    <v-chip style="margin:2px" color="primary" v-for="item in  item.doctors" :key="item">
                        <v-icon left>
                            mdi-account-circle-outline
                        </v-icon>{{ item.name }}
                    </v-chip>
                    </div>

                </template>


                <template v-slot:[`item.phones`]="{ item }">


                    <p style="    direction: ltr;
    text-align: end;"> {{item.phone}}</p>



                </template>
                <template v-slot:[`item.sex`]="{ item }">
                    <span v-if="item.sex==1">{{ $t("male") }}</span>
                    <span v-else>{{ $t("female") }}</span>
                </template>



                <template v-slot:[`item.cases`]="{ item }">


                    <span v-if="item.case==null">
                        لاتوجد
                    </span>

                    <v-btn v-else dense @click="$router.push('/admin/case/'+item.id)" color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">الحالات</v-btn>




                </template>




                <template v-slot:[`item.addCase`]="{ item }">



                    <v-btn @click="addCase(item)" dense color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-plus" style="position: relative;left:5px"></i>
                        اضافه حاله


                    </v-btn>




                </template>


                <template v-slot:[`item.Recipe`]="{ item }">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click="addRecipe(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-prescription " style="position: relative;left:5px"></i>
                        راجيته


                    </v-btn>

                </template>

                <template v-slot:[`item.booking`]="{ item }">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click="addbooking(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="far fa-clock" style="position: relative;left:5px"></i>
                        حجز موعد


                    </v-btn>

                </template>



                <template v-slot:[`item.status`]="{ item }">
                    <v-chip class="ma-2" :color="item.status.status_color" outlined>
                        <v-icon left>
                            {{item.status.status_icon}}
                        </v-icon>
                        {{item.status.status_name_ar}}
                    </v-chip>





                </template>
                <template v-slot:[`item.actions`]="{ item }">







                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon class="ml-5" @click="editItem(item)" v-if="!item.isDeleted" v-bind="attrs"
                                v-on="on">mdi-pencil</v-icon>
                        </template>
                        <span>{{ $t("edite") }} </span>
                    </v-tooltip>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon @click="deleteItem(item)" v-if="!item.isDeleted" v-bind="attrs" v-on="on">
                                mdi-delete</v-icon>
                        </template>
                        <span>{{$t('Delete')}}</span>
                    </v-tooltip>



                </template>


                <template v-slot:[`item.date`]="{ item }">
                    {{cropdate(item.created_at)}}




                </template>

                <template v-slot:[`item.bills`]="{ item }">




                    <v-chip v-if="(sumPaybills(item.bills)==item.price)" class="ma-2" color="green" outlined>
                        تم التسديد
                    </v-chip>

                    <v-chip v-else class="ma-2" color="red" outlined>
                        لم يتم التسديد
                    </v-chip>



                </template>



                <template v-slot:[`item.status`]="{ item }">
                    <v-chip class="ma-2" :color="item.status.status_color" outlined>
                        <v-icon left>
                            {{item.status.status_icon}}
                        </v-icon>
                        {{item.status.status_name_ar}}
                    </v-chip>





                </template>
                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize">{{ $t("Reloading") }}</v-btn>
                </template>
            </v-data-table>

            <v-layout row wrap>
                <v-flex xs12>

                    <div class="text-center pt-2">
                        <v-pagination @input="goTop()" v-model="page" :length="pageCount"></v-pagination>

                    </div>
                </v-flex>
            </v-layout>
            <Fancybox></Fancybox>
        </v-container>
    </div>
</template>

<script>
    import {
        Fancybox
    } from "@fancyapps/ui";
    //Recipe
    import cases from './case.vue';
    import billsReport from './billsReport.vue';
    import {
        EventBus
    } from "./event-bus.js";
    import Recipe from './Recipe.vue';
    import OwnerBooking from './sub_components/OwnerBooking.vue'
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
            billsReport,
            OwnerBooking,
            cases,
            Recipe,
            Fancybox
        },
        data() {
            return {
                CaseCategoriess: [],
                desserts: [

                ],
                search: {
                    case_categores_id: null,
                    status_id: ''
                },

                paymentsCount: 1,
                booking: false,
                cats: [],
                patientInfo: {},
                loadingData: true,
                pageCount: 11,
                page: 1,
                allItem: false,
                RecipeInfo: {},
                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu: [],
                imgCount: 1,
                images: [],

                selecBill: {},
                dialog: false,
                mask: "07XX XXX XXXX",
                valid: false,
                loadSave: false,
                casesheet: false,
                CaseCategories: [

                ],

                editedIndex: -1,

                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                progressValue: 0,

                // search: null,

                editedItem: {
                    name: "",
                    age: "",
                    sex: "",
                    phone: "",
                    tooth_num: [],
                    systemic_conditions: "",
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        lower_right: "",
                        lower_left: "",
                        tooth_num: [],
                        case_categories: {
                            name_ar: ''
                        },
                        status_id: 1,
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
                doctorsAll: [],
                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "patient.name"
                    },
                    {
                        text: 'نوع الحاله',
                        align: "start",
                        value: "case_categories.name_ar"
                    },


                    {
                        text: this.$t('datatable.doctor'),
                        align: "start",
                        value: "doctor"
                    },


                    {
                        text: this.$t('datatable.status_Description'),
                        align: "start",
                        value: "sessions[0].note"
                    },


                    {
                        text: this.$t('datatable.date'),
                        align: "start",
                        value: "date"
                    },

                    {
                        text: this.$t('datatable.status'),
                        align: "start",
                        value: "status"
                    },

                    {
                        text: 'حاله الدفع',
                        align: "start",
                        value: "bills"
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
            sumPaybills(bills) {
                let sum = 0;

                for (let i = 0; i < bills.length; i++) {

                    sum += parseInt(bills[i].price);


                }


                if (isNaN(sum)) {
                    return 0;
                }
                return sum
            },


            cropdate(x) {
                return x.slice(0, 10);
            },
            goTop() {
                if (/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

                    window.scrollTo(0, 0);

                }
            },
            BillsSum(bills_amount) {
                var totle_coast = 0;

                for (var i = 0; i < bills_amount.length; i++) {
                    totle_coast += bills_amount[i].price;

                }
                return totle_coast;

            },
            addCase(item) {


                this.patientInfo = {
                    case_categores_id: "",
                    upper_right: "",
                    upper_left: "",
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
                    images: [{
                            img: '',
                            descrption: ''
                        }

                    ],
                    notes: ""
                }


                this.casesheet = true;

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
            // uploadImageSuccess(formData, index, fileList) {
            //   console.log('data', formData, index, fileList)
            //   // Upload image api
            //   // axios.post('http://your-url-upload', formData).then(response => {
            //   //   console.log(response)
            //   // })
            // },
            // beforeRemove (index, done, fileList) {
            //   console.log('index', index, fileList)
            //   var r = confirm("remove image")
            //   if (r == true) {
            //     done()
            //   } else {
            //      console.log('index', index, fileList)  
            //   }
            // },
            // editImages (formData, index, fileList) {
            //     alert('df');
            //   console.log('edit data', formData, index, fileList)
            // }


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
                        Axios.delete("cases/" + item.id, {
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
            // onUploaded(e) {
            //     const {
            //         file
            //     } = e;
            //     const fileReader = new FileReader();
            //     fileReader.onload = () => {
            //         this.isDropZoneActive = false;
            //         this.imageSource = fileReader.result;
            //         this.editedItem.case.images = [{
            //             'img': [this.imageSource],
            //             'descrption': this.editedItem.case.images[0].descrption
            //         }];

            //         //      this.imageSource= '',
            //         //   this.textVisible= true,
            //         //   this.progressVisible=false,
            //         //   this.progressValue= 0
            //         //   this.imgCount=this.imgCount+1;

            //     };
            //     fileReader.readAsDataURL(file);
            //     this.textVisible = false;
            //     this.progressVisible = false;
            //     this.progressValue = 0;
            // },
            // onProgress(e) {
            //     this.progressValue = (e.bytesLoaded / e.bytesTotal) * 100;


            // },
            // onUploadStarted() {
            //     this.imageSource = '';
            //     this.progressVisible = true;
            // },





            editItem(item) {
                this.editedIndex = this.desserts.indexOf(item);
                var doc = [];
                item.doctors.forEach((item, index) => {
                    index
                    doc.push(item.id)
                })
                item.doctors = doc;

                this.editedItem = Object.assign({}, item);



                if (this.editedItem.bills.length == 0) {
                    this.editedItem.bills = [{
                        price: '',
                        PaymentDate: ''
                    }]

                }


                if (this.editedItem.images.length == 0) {
                    this.editedItem.images = [{
                            img: '',
                            descrption: ''
                        }

                    ]

                }
                if (this.editedItem.images.length > 0) {
                    this.imageSource = 'http://127.0.0.1:8000/images/' + this.editedItem.images[0].image_url;

                }

                this.casesheet = true;
                //        this.dia
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
                        case_categories: {
                            name_ar: ''
                        },
                        patient_id: "",
                        lower_right: "",
                        lower_left: "",
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
                };
                this.editedItem = {
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        patient_id: "",
                        lower_right: "",
                        lower_left: "",
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


            getByDocor() {

                if (this.searchDocorId == 0) {

                    return this.initialize()
                }
                Axios.get("cases/getByDoctor/" + this.searchDocorId, {
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

            seachs() {


                if (this.search.case_categores_id !== null && this.search.case_categores_id == 0) {
                    this.search.case_categores_id = '';
                }


                if (this.search.case_categores_id == null) {
                    this.search.case_categores_id = '';
                }
                Axios.get("cases/search?filter[status_id]=" + this.search.status_id +
                        "&filter[case_categores_id]=" + this.search.case_categores_id, {
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token
                            }
                        })
                    .then(res => {
                        this.loading = false;
                        //this.search = null;
                        this.allItem = true;
                        this.desserts = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            initialize() {
                this.loading = true;
                Axios.get("cases/UserCases", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.loadingData = false;
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
                        //  this.CaseCategories
                        this.CaseCategoriess = res.data;
                        this.CaseCategoriess = res.data;

                        this.CaseCategories.push({
                            id: 0,
                            name_ar: 'الكل',
                            name_en: '',
                            updated_at: '2022-02-02T12:20:30.000000Z'
                        })
                        for (var i = 0; i < this.CaseCategoriess.length; i++) {
                            this.CaseCategories.push({
                                id: this.CaseCategoriess[i].id,
                                name_ar: this.CaseCategoriess[i].name_ar,
                                name_en: '',
                                updated_at: this.CaseCategoriess[i].updated_at
                            })
                        }

                        console.log(this.CaseCategories);

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
                                title: this.$t('Added'),
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

                                this.SaveCase();
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
                                this.addCase(this.patientInfo);



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
                return this.editedIndex === -1 ? 'اضافه مراجع جديد' : this.$t('update');

            },
        },
        created() {
            this.getCaseCategories();
            //changeStatusCloseCase
            this.getclinicDoctor();

            EventBus.$on("changeStatusCloseCase", (from) => {

                from

                this.casesheet = false;
                this.initialize();
                ///  this.dialog = true
            });
            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                //  this.dialog = true
            });

            this.initialize();

            this.getDectors();

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
</style>