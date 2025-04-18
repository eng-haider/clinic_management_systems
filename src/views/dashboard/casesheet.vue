<template>

    <div>

        <div>
            <OwnerBooking :patientFound="true" :patientInfo="patientInfo" v-if="booking"
                :doctors="doctors" />
        </div>

        <v-dialog v-model="casesheet" max-width="1000px" v-if="CaseCategories.length>0">
            <cases :editedItem="patientInfo" :doctors="doctors" :CaseCategories="CaseCategories" :gocase="gocase">
            </cases>
        </v-dialog>

        <div>
            <v-dialog v-model="bill" max-width="1000px">
                <Bill :patient="patientInfo"></Bill>
            </v-dialog>
        </div>



        <v-dialog v-model="Recipe" max-width="900px" v-if="CaseCategories.length>0">
            <Recipe :patients="desserts" :RecipeInfo="RecipeInfo" :recipes="recipes" :CaseCategories="CaseCategories">
            </Recipe>
        </v-dialog>
        <v-container id="dashboard" fluid tag="section">


            <v-data-table :headers="headers" :loading="loadingData" disable-pagination :page.sync="page"
                @page-count="pageCount = $event" hide-default-footer :items="desserts" class="elevation-1 request_table"
                items-per-page="15">
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
                                    {{ $t("patients.addnewpatients") }}


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
                                                    <v-text-field v-model="editedItem.phone" v-mask="mask"
                                                        placeholder="07XX XXX XXXX" style="direction:ltr"
                                                        onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                                        :label="$t('datatable.phone')" outlined>
                                                    </v-text-field>
                                                </v-col>





                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-text-field v-model="editedItem.birth_date" type="date"
                                                        :label="$t('datatable.birth_date')" outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" v-if="editedIndex > -1" cols="12" sm="6" md="6">
                                                    <v-text-field :disabled="true" v-model="editedItem.age"
                                                        type="number" :label="$t('datatable.age')" outlined>
                                                    </v-text-field>
                                                </v-col>





                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-radio-group v-model="editedItem.sex" row>
                                                        <v-radio :label="$t('sex.female')" :value="1"></v-radio>
                                                        <v-radio :label="$t('sex.male')" :value="0"></v-radio>
                                                    </v-radio-group>
                                                </v-col>

                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-text-field v-model="editedItem.address"
                                                        style="direction: rtl;text-align: right;"
                                                        :label="$t('datatable.address')" outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" cols="12" sm="6" md="6"
                                                    v-if="$store.state.role=='secretary'  && doctors.length>1">
                                                    <v-select :rules="[rules.required]" v-model="editedItem.doctors"
                                                        :label="$t('doctor')" :items="doctors" outlined item-text="name"
                                                        item-value="id">
                                                    </v-select>

                                                </v-col>





                                            </v-row>


                                            <v-row>









                                            </v-row>


                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-textarea dense v-model="editedItem.systemic_conditions"
                                                        :label="$t('patients.systemicdisease')" outlined>
                                                    </v-textarea>
                                                </v-col>

                                            </v-row>


                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-switch v-model="editedItem.is_scheduled_today"
                                                        :label="$t('patients.add_to_today_sequence')" color="primary"
                                                        inset></v-switch>
                                                </v-col>
                                            </v-row>

                                        </v-container>
                                    </v-card-text>

                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                                        </v-btn>
                                        <v-btn :loading="loadSave" style="color: #fff;" color="green darken-1"
                                            @click="save()">
                                            {{ $t("next") }}</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-form>
                        </v-dialog>
                    </v-toolbar>


                    <v-layout row wrap>
                        <v-flex xs8 md3 sm3 pr-1 style="padding-right: 22px !important;">
                            <v-text-field ref="name" v-model="search" @keyup.enter="seachs"
                                :placeholder="$t('patients.Referencesnameorphonenumber')" required>
                            </v-text-field>
                        </v-flex>


                        <v-flex xs1 pa-5>
                            <v-btn color="green" style="color:#fff" @click="seachs()"> {{ $t("search") }}</v-btn>
                        </v-flex>

                        <v-flex xs1 pt-5 pb-5 pr-2 class="allsee">
                            <v-btn color="blue" v-if="allItem" style="color:#fff"
                                @click="initialize();allItem=false;isSearching=false">
                                {{ $t("all") }}
                            </v-btn>
                        </v-flex>


                        <v-flex xs1 md4 sm4></v-flex>



                        <v-flex xs12 md3 sm3 pt-5 style="  margin-right: 5px;" class="doctor-select">
                            <v-select dense @input="page=1;current_page=1;pageCount=0;getByDocor()"
                                v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors') && doctorsAll.length>2"
                                v-model="searchDocorId" :label="$t('doctor')" :items="doctorsAll" outlined
                                item-text="name" item-value="id" style="width: 100%; max-width: 300px;">
                            </v-select>
                        </v-flex>


                    </v-layout>
                </template>


                <template v-slot:[`item.names`]="{ item }">

                    <span @click="editItem(item)">
                        {{item.name}}
                    </span>


                </template>


                <template v-slot:[`item.phones`]="{ item }">
                    <p @click="editItem(item)" style="direction: ltr; text-align: end;">{{item.phone}}</p>
                </template>

                <template v-slot:[`item.age`]="{ item }">
                    <p @click="editItem(item)" style="direction: ltr; text-align: end;">{{item.age}}</p>
                </template>

                <!-- <template v-slot:[`item.sex`]="{ item }">
                    <span v-if="item.sex==1">{{ $t("male") }}</span>
                    <span v-else>{{ $t("female") }}</span>
                </template> -->


                <template v-if="$store.state.role == 'secretary'" v-slot:[`item.created_at`]="{ item }">
                    {{cropdate(item.created_at)}}





                </template>


                <template v-slot:[`item.doctor`]="{ item }"
                    v-if="$store.state.role == 'secretary'|| this.$store.state.role=='adminDoctor' ">



                    <v-chip style="margin:2px" color="primary" v-if="item.doctors[0]">
                        <v-icon left>
                            mdi-account-circle-outline
                        </v-icon>{{ item.doctors[0].name }}
                    </v-chip>


                </template>


                <template v-slot:[`item.casesx`]="{ item }"
                    v-if="$store.state.AdminInfo.Permissions.includes('show_cases')">


                    <span v-if="item.cases_count==0">
                        {{ $t("no_cases") }}
                    </span>

                    <v-btn v-else dense @click="$router.push('/patient/'+item.id)" color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">{{ $t("header.cases") }}</v-btn>




                </template>




                <template v-slot:[`item.addCase`]="{ item }"
                    v-if="$store.state.AdminInfo.Permissions.includes('add_case')">



                    <v-btn @click="addCase(item);addCase(item);gocase=true" dense color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-plus" style="position: relative;left:5px"></i>


                        {{ $t("addcase") }}

                    </v-btn>




                </template>


                <template v-slot:[`item.Recipe`]="{ item }"
                    v-if="$store.state.AdminInfo.Permissions.includes('show_rx')">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click="addRecipe(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-prescription " style="position: relative;left:5px"></i>


                        {{ $t("rx") }}
                    </v-btn>

                </template>

                <template v-slot:[`item.booking`]="{ item }">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click="addbooking(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="far fa-clock" style="position: relative;left:5px"></i>


                        {{ $t("patients.AppointmentBooking") }}
                    </v-btn>

                </template>

                <template v-slot:[`item.bills`]="{ item }"
                    v-if="$store.state.AdminInfo.Permissions.includes('show_accounts')">


                    <span style="display:none">{{item.id}}</span>



                    <v-btn @click="openbill(item)" v-if="item.cases.length>0" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <!-- <i class="far fa-clock" style="position: relative;left:5px"></i> -->

                        {{ $t("patients.account") }}


                    </v-btn>

                </template>


                <!-- Custom Actions Column Slot -->
                <template v-slot:[`item.actions`]="{ item }">
                    <v-tooltip bottom>
                        <template v-slot:activator="{  }">
                            <v-icon class="ml-5" @click="editItem(item)" v-if="!item.isDeleted" v-bind="attrs">
                                mdi-pencil
                            </v-icon>
                        </template>
                        <span>{{ $t("edit") }}</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon @click="deleteItem(item)" v-if="!item.isDeleted" v-bind="attrs">
                                mdi-delete
                            </v-icon>
                        </template>
                        <span>{{ $t("Delete") }}</span>
                    </v-tooltip>
                </template>


                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize">{{ $t("Reloading") }}</v-btn>
                </template>
            </v-data-table>

        </v-container>
        <v-pagination class="pagination" total-visible="20" v-model="page" color="#c7000b"
            style="position: relative; top: 20px;" circle="" :length="pageCount">
        </v-pagination>

    </div>
</template>

<script>
    import {
        EventBus
    } from "./event-bus.js";

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
            OwnerBooking: () => import("./sub_components/ownerBookinfDed.vue"),
  cases: () => import(/* webpackChunkName: "cases" */ "./case.vue"),
  Recipe: () => import(/* webpackChunkName: "recipe" */ "./Recipe.vue"),
  Bill: () => import(/* webpackChunkName: "bill" */ "./sub_components/billsReport.vue")
            


        },

        data() {
            return {
                gocase: false,
                isSearching: false,
                desserts: [

                ],
                bill: false,
                page: 1,
                pageCount: 0,
                current_page: 1,
                last_page: 0,
                paymentsCount: 1,
                booking: false,
                patientInfo: {},
                loadingData: true,
                allItem: false,
                RecipeInfo: {
                    rx_img: null,
                    notes: '',
                    case: {
                        case_categories: ""
                    }
                },


                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
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
                    minPhon: (v) => (v.length == 13 || v.length == 0) || this.$t('phone_length'),

                    required: value => !!value || this.$t('required'),
                    min: (v) => v.length >= 6 || this.$t('password_min_length'),
                    email: value => {
                        if (value.length > 0) {
                            const pattern =
                                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return pattern.test(value) || this.$t('invalid_email');
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
                isSearchingDoctor: false,

                editedItem: {
                    name: "",
                    age: "",
                    birth_date: "",
                    sex: "",
                    address: "",
                    phone: "",
                    is_scheduled_today: false, // Default to "No"
                    doctors: "",
                    systemic_conditions: "",
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
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

                    this.$store.state.role == 'secretary' ? {
                        text: this.$t('datatable.data_create'),
                        align: "start",
                        value: "created_at"
                    } : '',

                    this.$store.state.role == 'secretary' || this.$store.state.role == 'adminDoctor' ? {
                        text: this.$t('datatable.doctor'),
                        align: "start",
                        value: "doctor"
                    } : '',





                    {
                        text: 'الحالات',
                        value: "casesx",
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
                ]
            }
        },

        methods: {
            apiRequest(url, method = 'get', data = null) {
                return Axios({
                    url,
                    method,
                    data,
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        Authorization: "Bearer " + this.$store.state.AdminInfo.token
                    }
                });
            },

            getFormattedDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            },
            cropdate(x) {
                return x.slice(0, 10);
            },
            getMoreitems() {

                if (this.current_page <= this.last_page) {

                    if (this.isSearchingDoctor) {
                        this.current_page = this.page;
                        this.getByDocor();
                    } else if (this.isSearching) {
                        this.current_page = this.page;
                        this.initialize();
                    } else {
                        this.current_page = this.page;
                        this.initialize();
                    }

                }
            },
            reset() {
                this.editedItem = {
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        is_scheduled_today: false, // Default to "No"
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
                if (this.searchDocorId === 0) {
                    this.isSearchingDoctor = false;
                    return this.initialize();
                }
                this.isSearchingDoctor = true;
                this.apiRequest(`patients/getByDoctor/${this.searchDocorId}?page=${this.current_page}`)
                    .then(res => {
                        this.loading = false;
                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;
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
                        PaymentDate: this.getFormattedDate(new Date())
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

                                this.$swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: this.$t('Successfully'),
                                    showConfirmButton: false,
                                    timer: 1500
                                });

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
                this.editedItem = Object.assign({}, item);
                this.selecBill = Object.assign({}, this.editedItem);
                if (this.editedItem.case == null) {
                    this.editedItem.case = {
                        case_categores_id: "",
                        upper_right: "",
                        is_scheduled_today: false, // Default to "No"
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
                        is_scheduled_today: false, // Default to "No"
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
                this.isSearching = true;
                this.apiRequest(`patients/searchv2/${this.search}`)
                    .then(res => {
                        this.loading = false;
                        this.allItem = true;
                        this.desserts = res.data.data;
                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },


            getclinicDoctor() {
                const endpoint = this.$store.state.role === 'secretary' ? 'doctors/secretary' : 'doctors/clinic';
                this.apiRequest(endpoint)
                    .then(res => {
                        this.loadingData = false;
                        this.loading = false;
                        this.doctors = res.data.data;
                        this.doctorsAll = [{ id: 0, name: ' الكل' }, ...this.doctors];
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },


            initialize(page = 1) {
                page
                if (this.isSearching || this.isSearchingDoctor) return;
                this.loading = true;
                this.apiRequest(`patients/getByUserIdv2?page=${this.current_page}`)
                    .then(res => {
                        this.loading = false;
                        this.loadingData = false;
                        this.search = null;
                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;
                        this.desserts = res.data.data;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            getCaseCategories() {
                this.apiRequest('cases/CaseCategories')
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
                                position: "top-end",
                                icon: "success",
                                title: 'تمت اضافه مراجع جديد',
                                showConfirmButton: false,
                                timer: 1500
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

                    if (this.doctors.length > 1 && this.$store.state.role == 'secretary') {
                        this.editedItem.doctors = [this.editedItem.doctors];
                    } else {
                        delete this.editedItem.doctors;
                    }

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
                                this.initialize();
                                this.close();

                                this.$swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "تم تعديل ",
                                    showConfirmButton: false,
                                    timer: 1500
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
                                this.loadSave = false;
                                this.reset();

                                this.patientInfo = res.data.data;
                                this.dialog = false;
                                this.initialize();

                                if (this.$store.state.role !== 'secretary') {
                                    this.gocase = false;
                                    this.addCase(this.patientInfo);
                                } else {
                                    this.$swal.fire({
                                        position: "top-end",
                                        icon: "success",
                                        title: this.$t('Added'),
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                            })
                            .catch(() => {
                                this.loadSave = false;

                                this.$swal.fire({
                                    title: "حدث خطأ أثناء الإضافة",
                                    text: "",
                                    icon: "error",
                                    confirmButtonText: "اغلاق",
                                });
                            });
                    }
                }
            },

        },

        watch: {

            selected: 'search by sub_cat_id',

        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('patients.addnewpatients') : this.$t('update');

            }

            ,
            selected: function () {
                return this.getMoreitems();
            }
        },
        mounted() {
         
        },
        created() {
            this.initialize();
            this.getrecipes();
            this.getclinicDoctor();
            this.getCaseCategories();



            EventBus.$on("GetResCancel", (tooth) => {
                tooth
                this.booking = false;
                this.booking = false;
            });

            EventBus.$on("billsReportclose", (tooth) => {
                tooth
                this.bill = false;
            });
            //changeStatusCloseCase  GetResCancel

            EventBus.$on("changeStatusCloseCase", (from) => {
                from
                this.casesheet = false;
                console.log('1');
                this.initialize();
                ///  this.dialog = true
            });
            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                //  this.dialog = true
            });





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

    @media only screen and (max-width: 600px) {
        .doctor-select {
            margin-right: 5px;
        }
    }
</style>