<template>

    <div>




        <v-container id="dashboard" fluid tag="section">
            <!-- <v-text-field class="mt-4" label="اكتب للبحث" outlined append-icon="mdi-magnify" v-model="search">
            </v-text-field> -->


            <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" @page-count="pageCount = $event"
                hide-default-footer :items="desserts" class="elevation-1 request_table" items-per-page="15">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> دكاتره العياده
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <v-dialog v-model="dialog" max-width="800px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn color="primary" @click="editedIndex = -1 
                                ;  
                                
                                " dark class="mb-2" v-bind="attrs" v-on="on" style="color:#fff;font-family: 'Cairo'">
                                    <i class="fas fa-plus" style="position: relative;left:5px"></i>
                                   اضافه حساب جديد
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
                                                    <v-text-field v-model="editedItem.phone"
                                                    :rules="phoneRules"
                                                        required  placeholder="07XX XXX XXXX"
                                                        style="direction:ltr"
                                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                                        :label="$t('datatable.phone')" outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-text-field v-model="editedItem.password" 
                                                        required 
                                                        type="password"
                                                        style="direction:ltr"
                                                        :rules="passwordRules"
                                                        label="الباسورد" outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-radio-group v-model="editedItem.role" row>
                                                        <v-radio label="سكرتير" :value="3"></v-radio>
                                                        <v-radio label="دكاتره" :value="2"></v-radio>
                                                    </v-radio-group>
                                                </v-col>

                               
                                            </v-row>
                                            <v-row>
                                            </v-row>
                                            <v-row>
                                
                                            </v-row>

                                        </v-container>
                                    </v-card-text>

                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                                        </v-btn>
                                        <v-btn :loading="loadSave" style="color: #fff;" color="green darken-1"
                                            @click="save()">
                                            حفظ</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-form>
                        </v-dialog>
                    </v-toolbar>



                </template>


                <template v-slot:[`item.names`]="{ item }">



                    <v-chip style="margin:2px" color="primary">
                        <v-icon left>
                            mdi-account-circle-outline
                        </v-icon> {{item.info.name}}
                    </v-chip>

                </template>

                <template v-slot:[`item.patients_count`]="{ item }">


                    {{ item.info.patients_count }}


                </template>


                <template v-slot:[`item.all`]="{ item }">

                    <div style="color:blue;font-weight: bold;">
                        {{ item.all_sum | currency }} <span class="money">د.ع</span> </div>




                </template>


                <template v-slot:[`item.paids`]="{ item }">


                    <div style="color:green;font-weight: bold;">
                        {{ item.paid | currency }} <span style="color: #000;">د.ع</span> </div>



                </template>


                <template v-slot:[`item.rem`]="{ item }">


                    <div style="color:red;font-weight: bold;">
                        {{ (item.all_sum-item.paid ) | currency }} <span style="color: #000;">د.ع</span> </div>



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

        },
        data() {
            return {
                gocase: false,
                desserts: [

                ],

                editedItem: {
                    name: "",

                    password: "",
                    phone: "",
                    role:""
                    
                },

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
               
                    
                    phoneRules: [
          (v) => !!v || 'يجب ادخال رقم الهاتف',
          (v) => v.length > 10 && v.length < 12 || 'يجب ادخال رقم هاتف صحيح',
          (v) => /^\d+$/.test(v) || 'يجب ادخال رقم هاتف صحيح',
  
        ],
                    passwordRules: [
          v => !!v || 'يجب ادخال الباسورد',
          (v) => v.length >= 6 || 'يجب ان لايقل الباسورد عن ٨ احرف او ارقام',
        //   (v) => /^.*(?=.{3,})(?=.*[a-zA-Z])/.test(v) || 'يجب ان يحتوي على حروف وارقام'
        ],

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
                doctorsAll: [],
                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                search: '',
                progressValue: 0,
                searchDocorId: '',
                items: [

                ],
                doctors: [],
                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "names"
                    },

                    // {
                    //     text: this.$t('datatable.phone'),
                    //     align: "start",
                    //     value: "phones"
                    // },

                    {
                        text: 'عدد المراجعين',
                        align: "start",
                        value: "patients_count"
                    },


                    {
                        text: 'عدد الحالات',
                        align: "start",
                        value: "info.cases_count"
                    },

                    {
                        text: 'مبالغ الحالات',
                        align: "start",
                        value: "all"
                    },


                    {
                        text: 'المبالغ الدفوعه',
                        align: "start",
                        value: "paids"
                    },
                    {
                        text: 'المبالغ المتبقيه',
                        align: "start",
                        value: "rem"
                    },




                    // {
                    //     text: this.$t('Processes'),
                    //     value: "actions",
                    //     sortable: false
                    // }
                ],
                right: null
            }
        },

        methods: {
            save() {

if (this.$refs.form.validate()) {
    this.loadSave = true;
   
    var data = {
  
  name: this.editedItem.name,
 password: this.editedItem.password,
 role: this.editedItem.role,
 phone: "964" + parseInt(this.editedItem.phone),


 
};
        this.axios
            .post("users/adddoctors", data, {
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
                    this.gocase = false;
                    this.addCase(this.patientInfo);
                }




            })
            .catch((err) => {
                err

                this.loadSave = false;

            });
    
}

},

            goTop() {
                if (/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

                    window.scrollTo(0, 0);

                }

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





            editItem(item) {
                this.editedIndex = this.desserts.indexOf(item);

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


            initialize() {
                this.loading = true;
                Axios.get("doctors/clinicDoctorInfo", {
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
                        this.desserts = res.data;


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





           

        },


        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'اضافه دكتور' : this.$t('update');

            },
        },
        mounted() {
            this.getCaseCategories();
        },
        created() {
            this.initialize();

        },

    }
</script>

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