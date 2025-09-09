<template>

    <div>


        <v-dialog v-model="casesheet" max-width="1200px">

            <cases :doctors="doctors" :editedItem="editedItem" :CaseCategories="CaseCategoriess"></cases>
        </v-dialog>

        <v-container id="dashboard" fluid tag="section">
            <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" items-per-page="15"
                :items="desserts" class="elevation-1 request_table">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.showCases") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>

                    </v-toolbar>


                    <v-layout row wrap pa-5>





                        <v-flex xs12 md1 sm2 pr-4>

                            <v-select dense v-model="search.case_categores_id" :label="$t('type')" clearable
                                :items="CaseCategories" outlined item-text="name_ar" item-value="id"></v-select>
                        </v-flex>


                        <v-flex xs12 md1 sm2 pr-4>

                            <v-select dense v-model="search.status_id" :label="$t('status')" clearable :items="status"
                                outlined item-text="name" item-value="id"></v-select>
                        </v-flex>



                        <v-flex xs12 md1 sm2 pr-4>

                            <v-select dense v-model="search.is_paid" :label="$t('payment_status')" clearable
                                :items="paid" outlined item-text="name" item-value="id"></v-select>
                        </v-flex>




                        <v-flex xs6 md2 sm2 pr-4>

                            <v-menu v-model="menu2" :close-on-content-click="false" :nudge-right="40"
                                transition="scale-transition" offset-y min-width="auto">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-text-field dense outlined v-model="search.from_date" :label="$t('from_date')"
                                        v-bind="attrs" v-on="on"></v-text-field>
                                </template>
                                <v-date-picker v-model="search.from_date" no-title scrollable>
                                    <v-spacer></v-spacer>

                                </v-date-picker>
                            </v-menu>

                        </v-flex>



                        <v-flex xs6 md2 sm2 pr-4>

                            <v-menu v-model="menu3" :close-on-content-click="false" :nudge-right="40"
                                transition="scale-transition" offset-y min-width="auto">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-text-field dense outlined v-model="search.to_date" :label="$t('to_date')"
                                        v-bind="attrs" v-on="on"></v-text-field>
                                </template>
                                <v-date-picker v-model="search.to_date" no-title scrollable>
                                    <v-spacer></v-spacer>

                                </v-date-picker>
                            </v-menu>

                        </v-flex>

                        <v-flex xs12 md2 sm2 pr-4>
                            <v-text-field v-model="search.note" dense style="direction: rtl;text-align: right;"
                                :label="$t('note')" outlined>
                            </v-text-field>
                        </v-flex>


                        <v-flex xs11 md2 sm2 pr-4
                            v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors')  && doctorsAll.length>2">
                            <v-select dense v-model="search.doctors" :label="$t('doctor')" :items="doctorsAll" outlined
                                item-text="name" item-value="id">
                            </v-select>
                        </v-flex>

                        <v-flex xs3 md1 sm1 pr-4>
                            <v-btn color="green" style="color:#fff"
                                @click="page=1;current_page=1;pageCount=0;seachs();">{{ $t("search") }}</v-btn>
                        </v-flex>



                        <v-flex xs3 md1 sm1 pr-4>
                            <v-btn color="blue" v-if="allItem" style="color:#fff;" @click="searchCansle()">
                                {{ $t("cancel_search") }}
                            </v-btn>
                        </v-flex>





                    </v-layout>
                </template>


                <template v-slot:[`item.names`]="{ item }">

                    <span>
                        {{item.name}}
                    </span>



                </template>

                <template v-slot:item.tooth_num="{ item }">
                    <span v-for="(num, index) in parseToArray(item.tooth_num)" :key="index" style="font-weight:bold">
                        {{ num }}
                        <span v-if="index < parseToArray(item.tooth_num).length - 1"> - </span>
                    </span>
                </template>


                <template v-slot:[`item.doctor`]="{ item }">

                    <div v-if="item.doctors.length>0">
                        <span style="display: none;">{{ item }}</span>
                        <v-chip style="margin:2px" color="primary" v-for="item in  item.doctors" :key="item">
                            <v-icon left>
                                mdi-account-circle-outline
                            </v-icon>{{ item.name }}
                        </v-chip>
                    </div>

                </template>


          
                <template v-slot:[`item.patient.phone`]="{ item }">
                    <p style="direction: ltr; text-align: end;">{{item.patient.phone}}</p>
                </template>
                <template v-slot:[`item.sex`]="{ item }">
                    <span v-if="item.sex==1">{{ $t("male") }}</span>
                    <span v-else>{{ $t("female") }}</span>
                </template>



                <template v-slot:[`item.cases`]="{ item }">


                    <span v-if="item.case==null">
                        {{ $t("no_cases") }}
                    </span>

                    <v-btn v-else dense @click="$router.push('/admin/case/'+item.id)" color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">{{ $t("cases") }}</v-btn>




                </template>




                <template v-slot:[`item.addCase`]="{ item }">



                    <v-btn @click="addCase(item)" dense color="#0a304ed4"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-plus" style="position: relative;left:5px"></i>
                        {{ $t("add_case") }}


                    </v-btn>




                </template>


                <template v-slot:[`item.Recipe`]="{ item }">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click="addRecipe(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-prescription " style="position: relative;left:5px"></i>
                        {{ $t("prescription") }}


                    </v-btn>

                </template>

                <template v-slot:[`item.booking`]="{ item }">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click="addbooking(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="far fa-clock" style="position: relative;left:5px"></i>
                        {{ $t("book_appointment") }}


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
                        <span>{{ $t("edit") }} </span>
                    </v-tooltip>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon @click="deleteItem(item)" v-if="!item.isDeleted" v-bind="attrs" v-on="on">
                                mdi-delete</v-icon>
                        </template>
                        <span>{{$t('delete')}}</span>
                    </v-tooltip>



                </template>


                <template v-slot:[`item.date`]="{ item }">
                    {{cropdate(item.created_at)}}




                </template>

                <template v-slot:[`item.bills`]="{ item }">


                    <!-- <v-chip v-if="(item.price==0 || item.pric==null)" class="ma-2" color="green" outlined>
                     
                    </v-chip> -->
                    <v-chip v-if="(item.is_paid==1)" class="ma-2" color="green" outlined>
                        {{ $t("paid") }}
                    </v-chip>

                    <v-chip v-else class="ma-2" color="red" outlined>
                        {{ $t("not_paid") }}
                    </v-chip>



                    <!-- 
                    <v-chip v-if="(sumPaybills(item.bills)==item.price)" class="ma-2" color="green" outlined>
                        تم التسديد
                    </v-chip>

                    <v-chip v-else class="ma-2" color="red" outlined>
                        لم يتم التسديد
                    </v-chip> -->



                </template>



                <template v-slot:[`item.status`]="{ item }">
                    <v-switch :input-value="item.status.id === 43"
                        :label="item.status.id === 43 ? $t('completed') : $t('not_completed')"
                        @change="() => toggleStatus(item)" color="green" inset></v-switch>
                </template>
                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize">{{ $t("Reloading") }}</v-btn>
                </template>
            </v-data-table>


            <v-pagination class="pagination" total-visible="20" v-model="page" color="#c7000b"
                style="position: relative; top: 20px;" circle="" :length="pageCount">
            </v-pagination>

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

            cases,

            Fancybox
        },
        data() {
            return {


                status: [{
                        'id': 42,
                        'name': 'غير مكتملة'
                    },
                    {
                        'id': 43,
                        'name': 'مكتملة'
                    }
                ],

                paid: [{
                        'id': 1,
                        'name': 'تم التسديد'
                    },
                    {
                        'id': 0,
                        'name': 'لم يتم التسديد'
                    }
                ],
                CaseCategoriess: [],
                page: 1,
                pageCount: 0,
                current_page: 1,
                isSearching: false,
                last_page: 0,
                desserts: [

                ],
                search: {
                    case_categores_id: null,
                    status_id: '',
                    is_paid: '',
                    doctors: '',
                    from_date: "",
                    to_date: "",
                    note: ""
                },

                paymentsCount: 1,
                booking: false,
                cats: [],
                menu2: false,
                menu3: false,
                patientInfo: {},
                loadingData: true,

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
                doctorsIdsAll: [],
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
                        root_stuffing: {
                            "access_opening": [
                                ['', '', '', '']
                            ],
                            "oburation": [
                                ['', '', '', '']
                            ],
                        },
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
                        text: this.$t('datatable.phone'),
                        align: "left",
                        value: "patient.phone"
                    },
                    {
                        text: this.$t('case_type'),
                        align: "start",
                        value: "case_categories.name_ar"
                    },


                    {
                        text: this.$t('datatable.touth_num'),
                        align: "start",
                        value: "tooth_num"
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
                        text: this.$t('payment_status'),
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
            searchCansle() {
                this.search = {
                    case_categores_id: null,
                    status_id: '',
                    is_paid: '',
                    doctors: '',
                    from_date: "",
                    to_date: "",
                }

                this.initialize();
                this.allItem = false;
                this.isSearching = false;


            },
            toggleStatus(item) {
                // Toggle item status between 42 and 43
                item.status.id = item.status.id === 43 ? 42 : 43;
                this.changeStatus(item);
            },
            changeStatus(item) {
                const statusId = item.status.id;
                const caseId = item.id;

                this.axios.post(`/cases/changestatus/${caseId}`, {
                        status_id: statusId
                    }, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then((response) => {
                        response
                        this.initialize(); // Refresh data if needed
                    })
                    .catch((error) => {
                        error
                        this.$notify({
                            type: "error",
                            text: "Failed to update status."
                        });
                    });
            },
            parseToArray(toothNum) {
                // Check if it's a string that looks like an array, parse it
                if (typeof toothNum === 'string') {
                    try {
                        return JSON.parse(toothNum);
                    } catch (e) {
                        return [toothNum]; // Return the value as-is if it's not a valid array string
                    }
                }
                // If it's already an array, return it
                return Array.isArray(toothNum) ? toothNum : [toothNum];
            },
            getMoreitems() {

                if (this.current_page <= this.last_page) {

                    if (this.isSearching) {
                        this.current_page = this.page;
                        this.seachs();
                    } else {
                        this.current_page = this.page;
                        this.initialize();
                    }

                }
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
                            name: this.$t('all')
                        });
                        this.doctors.forEach((item, index) => {
                            index
                            this.doctorsAll.push(item)
                            this.doctorsIdsAll.push(item.id)
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
                        Axios.delete("cases/" + item.id, {
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

            onDropZoneLeave(e) {
                if (e.dropZoneElement.id === 'dropzone-external') {
                    this.isDropZoneActive = false;
                }
            },

            editItem(item) {
                this.editedIndex = this.desserts.indexOf(item);
                var doc = [];

                item.doctors.forEach((item, index) => {
                    index
                    doc.push(item.id)
                })



                this.editedItem = Object.assign({}, item);
                this.editedItem.doctors = doc;


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
                    this.imageSource = 'https://apismartclinicv2.tctate.com/public/images/' + this.editedItem.images[0]
                        .image_url;

                }






                // Check if `root_stuffing` is a string and attempt to parse it
                if (typeof this.editedItem.root_stuffing === 'string') {
                    try {
                        this.editedItem.root_stuffing = JSON.parse(this.editedItem.root_stuffing);
                    } catch (error) {
                        console.error("Failed to parse root_stuffing:", error);
                        // If parsing fails, initialize it as an object
                        this.editedItem.root_stuffing = {};
                    }
                }

                // Check that `root_stuffing` is an object after parsing or initialization
                if (!this.editedItem.root_stuffing || typeof this.editedItem.root_stuffing !== 'object') {
                    this.editedItem.root_stuffing = {};
                }

                // Ensure `access_opening` is an array in `root_stuffing`
                if (!Array.isArray(this.editedItem.root_stuffing.access_opening)) {
                    this.editedItem.root_stuffing.access_opening = [
                        [null, null, null, null]
                    ];
                }

                // Ensure `oburation` is an array in `root_stuffing`
                if (!Array.isArray(this.editedItem.root_stuffing.oburation)) {
                    this.editedItem.root_stuffing.oburation = [
                        [null, null, null, null]
                    ];
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

                this.isSearching = true; // Set the flag to true when search is active

                if (this.search.case_categores_id !== null && this.search.case_categores_id == 0) {
                    this.search.case_categores_id = '';
                }


                if (this.search.case_categores_id == null) {
                    this.search.case_categores_id = '';
                }

                if (this.search.status_id == null) {
                    this.search.status_id = '';
                }


                if (this.search.is_paid == null) {
                    this.search.is_paid = '';
                }


                if (this.search.doctors == null) {
                    this.search.doctors = '';
                }



                if (this.search.note == null) {
                    this.search.note = '';
                }


                if (this.search.doctors == 0) {
                    this.search.doctors = this.doctorsIdsAll;
                }



                Axios.get("cases/searchv3?filter[status_id]=" + this.search.status_id +
                        "&filter[case_categores_id]=" + this.search.case_categores_id +
                        "&filter[sessions.note]=" + this.search.note +
                        '&filter[is_paid]=' + this
                        .search.is_paid + '&doctors=' + this
                        .search.doctors +
                        "&from_date=" + this.search.from_date +
                        "&to_date=" + this.search.to_date +

                        '&page=' + this.current_page, {
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
                        // this.desserts = res.data.data;
                        this.desserts = res.data.data;
                        this.last_page = res.data.last_page;
                        this.pageCount = res.data.last_page;








                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            initialize() {
                this.loading = true;
                if (this.isSearching) return; // Prevent initialize from running if a search is active
                Axios.get(`cases/UserCasesv2?page=${this.current_page}`, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.loadingData = false;


                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;


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
                            name_ar: this.$t('all'),
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
                                title: this.$t('updated'),
                                text: "",
                                icon: "success",

                            });
                        })
                        .catch(() => {
                            this.loadSave = false;

                            this.$swal.fire({
                                title: this.$t('fill_information'),
                                text: "",
                                icon: "error",
                                confirmButtonText: this.$t('close'),
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
                                    title: this.$t('updated'),
                                    text: "",
                                    icon: "success",
                                    confirmButtonText: this.$t('close'),
                                });
                            })
                            .catch(() => {
                                this.loadSave = false;

                                this.$swal.fire({
                                    title: this.$t('fill_information'),
                                    text: "",
                                    icon: "error",
                                    confirmButtonText: this.$t('close'),
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
        watch: {
            'item.status.id'(newVal) {
                // Keep `switchState` in sync if `item.status.id` changes elsewhere in the app
                this.switchState = newVal === 43;
            },
            selected: 'search by sub_cat_id',

        },
        computed: {
            switchState: {
                get() {
                    // Return true if status is 43 ("مكتمله"), false otherwise
                    return this.item.status.id === 43;
                },
                set(value) {
                    // Update item status based on switch position
                    this.item.status.id = value ? 43 : 42;
                    this.changeStatus(this.item);
                }
            },
            formTitle() {
                return this.editedIndex === -1 ? this.$t('patients.addnewpatients') : this.$t('update');

            }

            ,
            selected: function () {
                return this.getMoreitems();
            }
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