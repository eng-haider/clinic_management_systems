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

        <!-- WhatsApp Dialog -->
        <v-dialog v-model="whatsappDialog" max-width="500px">
            <v-card>
                <v-toolbar color="green" dark>
                    <v-icon left>mdi-whatsapp</v-icon>
                    <v-toolbar-title>إرسال رسالة واتساب</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="closeWhatsappDialog">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>
                
                <v-card-text class="pt-4">
                    <div class="patient-info mb-3">
                        <p class="subtitle-1 mb-1">
                            <strong>المريض:</strong> {{ selectedPatient.name }}
                        </p>
                        <p class="subtitle-2 mb-1">
                            <strong>الهاتف:</strong> {{ selectedPatient.phone }}
                        </p>
                    </div>
                    
                    <v-textarea
                        v-model="whatsappMessage.message"
                        label="الرسالة"
                        rows="5"
                        outlined
                        counter
                        :loading="sendingMessage"
                    ></v-textarea>
                    
                    <div class="d-flex mt-2 mb-2">
                        <v-chip-group>
                            <v-chip 
                                @click="setQuickMessage('تذكير بالموعد')"
                                color="green" 
                                text-color="white"
                                clickable
                            >
                                تذكير بالموعد
                            </v-chip>
                            <v-chip 
                                @click="setQuickMessage('تذكير بالدفع')"
                                color="green" 
                                text-color="white"
                                clickable
                            >
                                تذكير بالدفع
                            </v-chip>
                            <v-chip 
                                @click="setQuickMessage('متابعة الحالة')"
                                color="green" 
                                text-color="white"
                                clickable
                            >
                                متابعة الحالة
                            </v-chip>
                        </v-chip-group>
                    </div>
                </v-card-text>
                
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn 
                        text 
                        color="grey darken-1" 
                        @click="closeWhatsappDialog"
                        :disabled="sendingMessage"
                    >
                        إلغاء
                    </v-btn>
                    <v-btn 
                        color="green" 
                        dark 
                        @click="sendWhatsappMessage"
                        :loading="sendingMessage"
                        :disabled="!whatsappMessage.message.trim()"
                    >
                        <v-icon left>mdi-send</v-icon>
                        إرسال
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-container id="dashboard" fluid tag="section">


            <v-data-table 
                :headers="headers"
                :items="desserts"
                :loading="loadingData"
                :options.sync="tableOptions"
                :server-items-length="totalItems"
                :footer-props="{
                    'items-per-page-options': [5, 10, 15, 20, 25],
                    'items-per-page-text': $t('rows_per_page')
                }"
                @update:options="handleTableUpdate"
                class="elevation-1 request_table"
                @click:row="handleRowClick"
            >
                <template v-slot:progress>
                    <v-overlay absolute :value="loadingData">
                        <v-progress-circular indeterminate size="64"></v-progress-circular>
                    </v-overlay>
                </template>
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.casesheet") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <!-- Add Patient Button -->
                        <!-- <v-btn 
                            color="primary" 
                            @click="openAddPatientDialog" 
                            dark 
                            class="mb-2" 
                            style="color:#fff;font-family: 'Cairo'"
                        >
                            <i class="fas fa-plus" style="position: relative;left:5px"></i>
                            {{ $t("patients.addnewpatients") }}
                        </v-btn> -->

                        <!-- Patient Edit Dialog -->
                        <PatientEditDialog
                            v-model="dialog"
                            :patient="editedIndex > -1 ? editedItem : null"
                            :doctors="doctors"
                            :loading="loadSave"
                            @save="save"
                            @close="close"
                        />
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


                <template v-if="$store.getters.isSecretary" v-slot:[`item.created_at`]="{ item }">
                    {{cropdate(item.created_at)}}





                </template>


                <template v-slot:[`item.doctor`]="{ item }"
                    v-if="$store.getters.isSecretary || this.$store.getters.userRole == 'adminDoctor' ">

                    <v-chip style="margin:2px" color="primary" v-if="hasDoctorAssigned(item)">
                        <v-icon left>
                            mdi-account-circle-outline
                        </v-icon>{{ getDoctorName(item) }}
                    </v-chip>

                    <span v-else style="color: #666; font-style: italic;">
                        {{ getDoctorName(item) }}
                    </span>

                </template>


             




                <template v-slot:[`item.Recipe`]="{ item }"
                    v-if="$store.state.AdminInfo.Permissions.includes('show_rx')">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click.stop="addRecipe(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="fas fa-prescription " style="position: relative;left:5px"></i>


                        {{ $t("rx") }}
                    </v-btn>

                </template>

                <template v-slot:[`item.booking`]="{ item }">


                    <span style="display:none">{{item.id}}</span>

                    <v-btn @click.stop="addbooking(item)" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <i class="far fa-clock" style="position: relative;left:5px"></i>


                        {{ $t("patients.AppointmentBooking") }}
                    </v-btn>

                </template>

                <template v-slot:[`item.bills`]="{ item }"
                    v-if="$store.state.AdminInfo.Permissions.includes('show_accounts')">


                    <span style="display:none">{{item.id}}</span>



                    <v-btn @click.stop="openbill(item)" v-if="item.cases.length>0" dense color="#3b6a75"
                        style="color:#fff;height:28px;font-weight:bold">
                        <!-- <i class="far fa-clock" style="position: relative;left:5px"></i> -->

                        {{ $t("patients.account") }}


                    </v-btn>

                </template>


                <!-- Custom Actions Column Slot -->
                <template v-slot:[`item.actions`]="{ item }">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon 
                                class="ml-2" 
                                @click.stop="openWhatsappDialog(item)" 
                                v-bind="attrs" 
                                v-on="on"
                                color="green"
                                style="color: rgb(37, 211, 102);"
                            >
                                mdi-whatsapp
                            </v-icon>
                        </template>
                        <span>{{ $t("Send WhatsApp") }}</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon class="ml-2" @click.stop="editItem(item)" v-if="!item.isDeleted" v-bind="attrs" v-on="on">
                                mdi-pencil
                            </v-icon>
                        </template>
                        <span>{{ $t("edit") }}</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon class="ml-2" @click.stop="deleteItem(item)" v-if="!item.isDeleted" v-bind="attrs" v-on="on">
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
        },        components: {
            OwnerBooking: () => import("./sub_components/ownerBookinfDed.vue"),
            cases: () => import(/* webpackChunkName: "cases" */ "./case.vue"),
            Recipe: () => import(/* webpackChunkName: "recipe" */ "./Recipe.vue"),
            Bill: () => import(/* webpackChunkName: "bill" */ "./sub_components/billsReport.vue"),
            PatientEditDialog: () => import("@/components/PatientEditDialog.vue")
        },

        data() {
            return {
                // WhatsApp Dialog
                whatsappDialog: false,
                whatsappMessage: {
                    message: ''
                },
                selectedPatient: {
                    id: null,
                    name: '',
                    phone: ''
                },
                sendingMessage: false,

                // Server-side pagination
                tableOptions: {},
                totalItems: 0,

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

                    this.$store.getters.isSecretary ? {
                        text: this.$t('datatable.data_create'),
                        align: "start",
                        value: "created_at"
                    } : '',

                    this.$store.getters.isSecretary || this.$store.getters.userRole == 'adminDoctor' ? {
                        text: this.$t('datatable.doctor'),
                        align: "start",
                        value: "doctor"
                    } : '',






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
            // WhatsApp Methods
            openWhatsappDialog(item) {
                this.selectedPatient = {
                    id: item.id,
                    name: item.name || '',
                    phone: item.phone || ''
                };
                this.whatsappMessage = {
                    message: ''
                };
                this.whatsappDialog = true;
            },

            closeWhatsappDialog() {
                this.whatsappDialog = false;
                this.whatsappMessage = {
                    message: ''
                };
                this.selectedPatient = {
                    id: null,
                    name: '',
                    phone: ''
                };
                this.sendingMessage = false;
            },

            setQuickMessage(messageType) {
                const messages = {
                    'تذكير بالموعد': `مرحباً ${this.selectedPatient.name}،\n\nنذكركم بموعدكم القادم في العيادة.\n\nشكراً لكم`,
                    'تذكير بالدفع': `مرحباً ${this.selectedPatient.name}،\n\nنذكركم بوجود مستحقات مالية متبقية.\n\nيرجى التواصل معنا لتسوية الحساب.\n\nشكراً لكم`,
                    'متابعة الحالة': `مرحباً ${this.selectedPatient.name}،\n\nنود الاطمئنان على حالتكم الصحية.\n\nكيف تشعرون بعد العلاج؟\n\nشكراً لكم`
                };
                this.whatsappMessage.message = messages[messageType] || '';
            },

            async sendWhatsappMessage() {
                if (!this.whatsappMessage.message.trim()) {
                    this.$swal.fire({
                        title: "خطأ",
                        text: "يرجى كتابة الرسالة",
                        icon: "error",
                        confirmButtonText: "اغلاق",
                    });
                    return;
                }

                if (!this.selectedPatient.id) {
                    this.$swal.fire({
                        title: "خطأ",
                        text: "معرف المريض غير موجود",
                        icon: "error",
                        confirmButtonText: "اغلاق",
                    });
                    return;
                }

                this.sendingMessage = true;

                try {
                    const response = await this.apiRequest('whatsapp/storeNow', 'post', {
                        patient_id: this.selectedPatient.id,
                        message: this.whatsappMessage.message.trim()
                    });

                    this.$swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "تم إرسال الرسالة بنجاح",
                        showConfirmButton: false,
                        timer: 1500
                    });

                    this.closeWhatsappDialog();
                } catch (error) {
                    console.error('WhatsApp send error:', error);
                    
                    let errorMessage = "حدث خطأ أثناء إرسال الرسالة";
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }

                    this.$swal.fire({
                        title: "فشل في الإرسال",
                        text: errorMessage,
                        icon: "error",
                        confirmButtonText: "اغلاق",
                    });
                } finally {
                    this.sendingMessage = false;
                }
            },

            // Server-side pagination handler
            handleTableUpdate(options) {
                this.tableOptions = options;
                this.loadTableData();
            },

            loadTableData() {
                if (this.isSearching) {
                    this.seachs();
                } else if (this.isSearchingDoctor) {
                    this.getByDocor();
                } else {
                    this.initialize();
                }
            },

            // Open the patient dialog for adding new patient
            openAddPatientDialog() {
                this.editedIndex = -1;
                this.dialog = true;
            },

            // Helper method to safely get doctor name
            getDoctorName(item) {
                if (item.doctors && item.doctors.length > 0 && item.doctors[0] && item.doctors[0].name) {
                    return item.doctors[0].name;
                }
                return '';
            },

            // Helper method to check if doctor exists
            hasDoctorAssigned(item) {
                return item.doctors && item.doctors.length > 0 && item.doctors[0];
            },

            // Handle row click to navigate to patient page
            handleRowClick(item) {
                this.$router.push(`/patient/${item.id}`);
            },

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
                this.loadingData = true;
                
                const currentPage = this.tableOptions.page || 1;
                const itemsPerPage = this.tableOptions.itemsPerPage || 10;
                
                this.apiRequest(`patients/getByDoctor/${this.searchDocorId}?page=${currentPage}&per_page=${itemsPerPage}`)
                    .then(res => {
                        this.loadingData = false;
                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;
                        this.totalItems = res.data.meta.total;
                        this.desserts = res.data.data;
                    })
                    .catch(() => {
                        this.loadingData = false;
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
                        lower_left: "",
                        patient_id: "",
                        lower_right: "",
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
                this.loadingData = true;
                
                const currentPage = this.tableOptions.page || 1;
                const itemsPerPage = this.tableOptions.itemsPerPage || 10;
                
                this.apiRequest(`patients/searchv2/${this.search}?page=${currentPage}&per_page=${itemsPerPage}`)
                    .then(res => {
                        this.loadingData = false;
                        this.allItem = true;
                        this.desserts = res.data.data;
                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;
                        this.totalItems = res.data.meta.total;
                    })
                    .catch(() => {
                        this.loadingData = false;
                    });
            },


            getclinicDoctor() {
                const endpoint = this.$store.getters.isSecretary ? 'doctors/secretary' : 'doctors/clinic';
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
                if (this.isSearching || this.isSearchingDoctor) return;
                
                this.loadingData = true;
                
                // Get pagination parameters from tableOptions
                const currentPage = this.tableOptions.page || 1;
                const itemsPerPage = this.tableOptions.itemsPerPage || 10;
                
                this.apiRequest(`patients/getByUserIdv3?page=${currentPage}&per_page=${itemsPerPage}`)
                    .then(res => {
                        this.loadingData = false;
                        this.search = null;
                        this.last_page = res.data.meta.last_page;
                        this.pageCount = res.data.meta.last_page;
                        this.totalItems = res.data.meta.total;
                        this.desserts = res.data.data;
                    })
                    .catch(() => {
                        this.loadingData = false;
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

            save(eventData) {
                // Handle PatientEditDialog events - the dialog now handles API calls internally
                if (eventData && typeof eventData === 'object' && eventData.patient) {
                    const patientData = eventData.patient;
                    const isEditing = eventData.isEditing;
                    
                    // Update local data
                    this.initialize();
                    
                    // Handle redirection based on user role
                    const userRole = this.$store.getters.userRole;
                    if (!isEditing) {
                        // Only redirect for new patients
                        if (userRole === 'adminDoctor' || userRole === 'admin' || userRole === 'doctor') {
                            // Redirect to patient page for these roles
                            this.$router.push(`/patient/${patientData.id}`);
                        } else if (!this.$store.getters.isSecretary) {
                            this.gocase = false;
                            this.addCase(patientData);
                        }
                    }
                    
                    this.close();
                    return;
                }

                // Legacy code for old direct calls (keeping for backward compatibility)
                if (this.$refs.form && !this.$refs.form.validate()) {
                    return;
                }
                
                const patientData = { ...this.editedItem };
                const isEditing = this.editedIndex > -1;
                
                if (this.doctors.length > 1 && this.$store.getters.isSecretary) {
                    patientData.doctors = [patientData.doctors];
                } else {
                    delete patientData.doctors;
                }

                this.loadSave = true;

                if (isEditing) {
                        this.axios
                            .patch("patients/" + (patientData.id || this.editedItem.id), patientData, {
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
                            .post("patients", patientData, {
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

                                // Check user role and redirect accordingly
                                const userRole = this.$store.getters.userRole;
                                if (userRole === 'adminDoctor' || userRole === 'admin' || userRole === 'doctor') {
                                    // Redirect to patient page for these roles
                                    this.$router.push(`/patient/${this.patientInfo.id}`);
                                } else if (!this.$store.getters.isSecretary) {
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