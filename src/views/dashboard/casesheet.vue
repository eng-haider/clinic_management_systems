<template>

    <div>

        <div id="printMe" style="display:none">
            <billsReport :info="selecBill"></billsReport>
        </div>

        <div>
            <OwnerBooking :patientFound="patientFound=true" :patientInfo="patientInfo" v-if="booking" />
        </div>

        <v-dialog v-model="casesheet" max-width="1000px">
            <cases :editedItem="patientInfo"></cases>
        </v-dialog>

      <div>
        <v-dialog v-model="bill" max-width="1000px">
            <Bill :patient="patientInfo"></Bill>
        </v-dialog>
      </div>

        

        <v-dialog v-model="Recipe" max-width="900px">
            <Recipe :RecipeInfo="RecipeInfo" :CaseCategories="CaseCategories"></Recipe>
        </v-dialog>
        <v-container id="dashboard" fluid tag="section">
            <!-- <v-text-field class="mt-4" label="اكتب للبحث" outlined append-icon="mdi-magnify" v-model="search">
            </v-text-field> -->


            <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" @page-count="pageCount = $event"
                hide-default-footer :items="desserts" class="elevation-1 request_table" items-per-page="15">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.casesheet") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <v-dialog v-model="dialog" max-width="800px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn color="primary" @click="editedIndex = -1 
                                ;  
                                
                                " dark class="mb-2" v-bind="attrs" v-on="on" style="color:#fff;font-family: 'Cairo'">
                                    <i class="fas fa-plus" style="position: relative;left:5px"></i>


                                    اضافه مراجع جديد
                                </v-btn>
                            </template>
                            <v-form ref="form" v-model="valid">
                                <v-card>

                                    <v-toolbar dark color="primary lighten-1 mb-5">
                                        <v-toolbar-title>
                                            <h3 style="color:#fff;font-family: 'Cairo'"> {{formTitle}}</h3>
                                        </v-toolbar-title>
                                        <v-spacer />
                                        <v-btn @click="close()" icon>
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </v-toolbar>

                                    <v-card-text>
                                        <v-container>

                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-text-field v-model="editedItem.name"
                                                        style="direction: rtl;text-align: right;"
                                                        :rules="[rules.required]" :label="$t('datatable.name')"
                                                        outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-text-field v-model="editedItem.phone" :rules="[rules.minPhon]"
                                                        required v-mask="mask" placeholder="07XX XXX XXXX"
                                                        style="direction:ltr"
                                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                                        :label="$t('datatable.phone')" outlined>
                                                    </v-text-field>
                                                </v-col>



                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-text-field v-model="editedItem.age" type="number"
                                                        :label="$t('datatable.age')" outlined>
                                                    </v-text-field>
                                                </v-col>

                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-radio-group v-model="editedItem.sex" row>
                                                        <v-radio label="ذكر " :value="1"></v-radio>
                                                        <v-radio label="انثئ" :value="0"></v-radio>
                                                    </v-radio-group>
                                                </v-col>




                                                <!-- 
                                                <v-col class="d-none d-sm-flex py-0" cols="12" sm="2" md="2" >
                                                    <v-btn class="ma-2 white--text" color="green" :height="70"
                                                        style="color:#FFF" @click="addRecipe()
                                                        
                                                        
                                                        ">

                                                        <div style="display: block;
    position: relative;
    bottom: 15px;
    text-align: center;
    right: 6px;
    font-size: 17px;
">كتابه وصفه</div>

                                                        <div>
                                                            <v-icon style="    display: block;
    position: relative;
    left: 50px;
    top: 14px;
    font-size: 31px;
  " dark>
                                                                fas fa-prescription fa-2x
                                                            </v-icon>
                                                        </div>
                                                    </v-btn>
                                                </v-col>
 -->










                                            </v-row>


                                            <v-row>









                                            </v-row>


                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-textarea dense v-model="editedItem.systemic_conditions"
                                                        label="الامراض الجهازيه" outlined>
                                                    </v-textarea>
                                                </v-col>

                                            </v-row>









                                        </v-container>
                                    </v-card-text>

                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                                        </v-btn>
                                        <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>
                                            {{ $t("save") }}</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-form>
                        </v-dialog>
                    </v-toolbar>


                    <v-layout row wrap>
                        <v-flex xs5 md3 sm3 pr-1 style="padding-right: 22px !important;">
                            <v-text-field ref="name" v-model="search" placeholder="اسم المراجع او رقم  الهاتف" required>
                            </v-text-field>
                        </v-flex>

                        <v-flex xs1 pa-5>
                            <v-btn color="green" style="color:#fff" @click="seachs()">بحــث</v-btn>
                        </v-flex>

                        <v-flex xs1 pt-5 pb-5 pr-2 v-if="allItem">
                            <v-btn color="blue" style="color:#fff" @click="initialize();allItem=false">رجوع</v-btn>
                        </v-flex>


                    </v-layout>
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



                <template v-slot:[`item.cases`]="{ item }" v-if="$store.state.role !== 'secretary'">


                    <span v-if="item.case==null">
                        لاتوجد
                    </span>

                    <v-btn v-else dense @click="$router.push('/patient/'+item.id)" color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">الحالات</v-btn>




                </template>




                <template v-slot:[`item.addCase`]="{ item }" v-if="$store.state.role !== 'secretary'">



                    <v-btn @click="addCase(item)" dense color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-plus" style="position: relative;left:5px"></i>
                        اضافه حاله


                    </v-btn>




                </template>


                <template v-slot:[`item.Recipe`]="{ item }" v-if="$store.state.role !== 'secretary'">


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

                <template v-slot:[`item.bills`]="{ item }">


<span style="display:none">{{item.id}}</span>

<v-btn @click="openbill(item)" v-if="item.cases.length>0" dense color="#3b6a75"
    style="color:#fff;height:28px;font-weight:bold">
    <!-- <i class="far fa-clock" style="position: relative;left:5px"></i> -->
     الحساب 


</v-btn>

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
    import cases from './case.vue';
    import billsReport from './billsReport.vue';
    
    import {
        EventBus
    } from "./event-bus.js";
    import Recipe from './Recipe.vue';
    import OwnerBooking from './sub_components/ownerBookinfDed.vue'
    import Bill from './sub_components/billsReport.vue'
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
            Bill
        },
        data() {
            return {
                desserts: [

                ],
                bill:false,
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
                editedIndex: -1,

                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                search:'',
                progressValue: 0,

                

                editedItem: {
                    name: "",
                    age: "",
                    sex: "",
                    phone: "",
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


                    //status_Description

                    {
                        text: 'الحالات',
                        value: "cases",
                        sortable: false
                    },
                    {
                        text: '',
                        value: "addCase",
                        sortable: false
                    },
                    //Recipe

                    {
                        text: '',
                        value: "Recipe",
                        sortable: false
                    },
                    {
                        text: '',
                        value: "booking",
                        sortable: false
                    },
                    {
                        text: '',
                        value: "bills",
                        sortable: false
                    },
                    //booking
                    //booking
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

            goTop() {
                if (/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

                    window.scrollTo(0, 0);

                }

            },
            openbill(item){
                item

                this.patientInfo = item;
                this.bill=true;
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


                if (/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

                    this.$router.push("/case/" + item.id)

                } else {

                    this.casesheet = true;
                }

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

                this.editedItem = Object.assign({}, item);
                this.selecBill = Object.assign({}, this.editedItem);

                if (this.editedItem.case == null) {
                    this.editedItem.case = {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
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
                        this.search = null;
                        this.allItem = true;
                        this.desserts=[];
                        this.desserts = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
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
                        this.loadingData = false;
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
                                if (this.$store.state.role !== 'secretary') {
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
                this.doctors.push({
                    'id': this.$store.state.AdminInfo.id,
                    'name': this.$store.state.AdminInfo.name

                })
            }

        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'اضافه مراجع جديد' : this.$t('update');

            },
        },
        created() {
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
            this.getCaseCategories();
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