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
                            <strong>المريض:</strong> {{ selectedPatient ? selectedPatient.name : '' }}
                        </p>
                        <p class="subtitle-2 mb-1">
                            <strong>الهاتف:</strong> {{ selectedPatient ? selectedPatient.phone : '' }}
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
                        <v-icon left>{{ apiWhatsappEnabled ? 'mdi-send' : 'mdi-open-in-new' }}</v-icon>
                        {{ apiWhatsappEnabled ? 'إرسال' : 'فتح واتساب' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-container id="dashboard" fluid tag="section">


            <v-data-table 
                :headers="headers"
                :items="desserts"
                :loading="loadingData"
                loading-text="جاري تحميل البيانات..."
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
                    <v-progress-linear
                        color="primary"
                        indeterminate
                        height="4"
                    ></v-progress-linear>
                </template>
                
                <template v-slot:loading>
                    <div class="text-center pa-4">
                        <v-progress-circular
                            indeterminate
                            color="primary"
                            size="40"
                        ></v-progress-circular>
                        <div class="mt-2">جاري تحميل البيانات...</div>
                    </div>
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


           


                        <v-flex xs12 md3 sm3 pt-5 style="  margin-right: 5px;" class="doctor-select">
                            <v-select dense @input="page=1;current_page=1;pageCount=0;getByDocor()"
                                v-if="$store.state.AdminInfo.Permissions.includes('show_all_clinic_doctors') && doctorsAll.length>2"
                                v-model="searchDocorId" :label="$t('doctor')" :items="doctorsAll" outlined
                                item-text="name" item-value="id" style="width: 100%; max-width: 300px;">
                            </v-select>
                        </v-flex>

                        <v-flex xs12 md3 sm3 pt-5 style="  margin-right: 5px;" class="payment-filter">
                            <v-select
                                v-model="fullyPaidFilter"
                                :items="[
                                    { text: 'جميع المرضى', value: null },
                                    { text: 'مدفوع بالكامل', value: true },
                                    { text: 'غير مدفوع بالكامل', value: false }
                                ]"
                                item-text="text"
                                item-value="value"
                                label="حالة الدفع"
                                @change="initialize()"
                                clearable
                                dense
                                outlined
                                style="width: 100%; max-width: 300px;"
                            ></v-select>
                        </v-flex>


                    </v-layout>
                </template>


                <template v-slot:[`item.name`]="{ item }">

                    <span @click="editItem(item)">
                        {{item.name}}
                    </span>


                </template>


                <template v-slot:[`item.phone`]="{ item }">
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


                <template v-slot:[`item.doctor`]="{ item }">
                    <span>
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
                        <span>{{ apiWhatsappEnabled ? "إرسال رسالة واتساب" : "فتح واتساب في نافذة جديدة" }}</span>
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
        <!-- Add pagination component -->
        <v-pagination 
            class="pagination" 
            total-visible="20" 
            v-model="page" 
            color="#c7000b"
            style="position: relative; top: 20px;" 
            circle="" 
            :length="pageCount"
            @input="handlePaginationChange"
        >
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
            cases: () => import("./case.vue"),
            Recipe: () => import("./Recipe.vue"),
            Bill: () => import("./sub_components/billsReport.vue"),
            PatientEditDialog: () => import("@/components/PatientEditDialog.vue")
        },

        data() {
            return {
                // Add pagination variables
                page: 1,
                pageCount: 0,
                last_page: 0,
                
                // Add timeout variable
                paginationTimeout: null,
                
                // Add fully paid filter
                fullyPaidFilter: null, // null = all, true = fully paid, false = not fully paid
                
                // Cache configuration
                cacheConfig: {
                    patients: {
                        key: 'patients_cache',
                        ttl: 5 * 60 * 1000, // 5 minutes
                    },
                    doctors: {
                        key: 'doctors_cache',
                        ttl: 30 * 60 * 1000, // 30 minutes
                    },
                    caseCategories: {
                        key: 'case_categories_cache',
                        ttl: 60 * 60 * 1000, // 1 hour
                    },
                    recipes: {
                        key: 'recipes_cache',
                        ttl: 15 * 60 * 1000, // 15 minutes
                    }
                },

                // WhatsApp Dialog - properly initialize
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

                // Server-side pagination - initialize with default values
                tableOptions: {
                    page: 1,
                    itemsPerPage: 10
                },
                totalItems: 0,

                gocase: false,
                isSearching: false,
                desserts: [

                ],
                bill: false,
                current_page: 1,
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

                    this.$store.getters.isSecretary ? {
                        text: this.$t('datatable.data_create'),
                        align: "start",
                        value: "created_at"
                    } : '',

                    this.$store.getters.isSecretary || this.$store.getters.userRole == 'adminDoctor' || this.$store.getters.userRole == 'accounter' ? {
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
            // Add pagination handler
            handlePaginationChange(newPage) {
                this.tableOptions.page = newPage;
                this.page = newPage;
                this.loadTableData();
            },

            // Cache management methods
            setCache(key, data, ttl = 5 * 60 * 1000) {
                const cacheItem = {
                    data: data,
                    timestamp: Date.now(),
                    ttl: ttl
                };
                try {
                    localStorage.setItem(key, JSON.stringify(cacheItem));
                } catch (error) {
                    console.warn('Failed to set cache:', error);
                }
            },

            getCache(key) {
                try {
                    const cached = localStorage.getItem(key);
                    if (!cached) return null;

                    const cacheItem = JSON.parse(cached);
                    const now = Date.now();
                    
                    // Check if cache is expired
                    if (now - cacheItem.timestamp > cacheItem.ttl) {
                        localStorage.removeItem(key);
                        return null;
                    }
                    
                    return cacheItem.data;
                } catch (error) {
                    console.warn('Failed to get cache:', error);
                    return null;
                }
            },

            clearCache(key) {
                try {
                    localStorage.removeItem(key);
                } catch (error) {
                    console.warn('Failed to clear cache:', error);
                }
            },

            clearAllCache() {
                Object.values(this.cacheConfig).forEach(config => {
                    this.clearCache(config.key);
                });
            },

            // Generate cache key for paginated data
            generateCacheKey(baseKey, page, perPage, searchTerm = '', doctorId = '', fullyPaid = null) {
                return `${baseKey}_p${page}_pp${perPage}_s${searchTerm}_d${doctorId}_fp${fullyPaid}`;
            },

            // WhatsApp Methods
            /**
             * Opens WhatsApp dialog for sending messages to patients
             * @param {Object} item - Patient data object
             */
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
                const patientName = this.selectedPatient ? this.selectedPatient.name : 'المريض';
                const messages = {
                    'تذكير بالموعد': `مرحباً ${patientName}،\n\nنذكركم بموعدكم القادم في العيادة.\n\nشكراً لكم`,
                    'تذكير بالدفع': `مرحباً ${patientName}،\n\nنذكركم بوجود مستحقات مالية متبقية.\n\nيرجى التواصل معنا لتسوية الحساب.\n\nشكراً لكم`,
                    'متابعة الحالة': `مرحباً ${patientName}،\n\nنود الاطمئنان على حالتكم الصحية.\n\nكيف تشعرون بعد العلاج؟\n\nشكراً لكم`
                };
                this.whatsappMessage.message = messages[messageType] || '';
            },

            /**
             * Sends WhatsApp message using either API or URL method
             * Based on api_whatsapp setting in store:
             * - If api_whatsapp = 1: Uses API endpoint to send message
             * - If api_whatsapp = 0: Opens WhatsApp web/app with pre-filled message
             */
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
                    // Check if api_whatsapp is enabled (1) or disabled (0)
                    const apiWhatsappEnabled = this.$store.getters.getApiWhatsapp;
                    
                    if (apiWhatsappEnabled === 1) {
                        // Use API method for sending WhatsApp message
                        await this.apiRequest('whatsapp/storeNow', 'post', {
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
                    } else {
                        // Use URL method for sending WhatsApp message
                        const phoneNumber = this.selectedPatient.phone || '';
                        
                        if (!phoneNumber.trim()) {
                            this.$swal.fire({
                                title: "خطأ",
                                text: "رقم الهاتف غير موجود",
                                icon: "error",
                                confirmButtonText: "اغلاق",
                            });
                            return;
                        }
                        
                        const message = encodeURIComponent(this.whatsappMessage.message.trim());
                        
                        // Clean phone number (remove spaces, dashes, etc.) and ensure it starts with country code
                        let cleanPhone = phoneNumber.replace(/[\s-+()]/g, '');
                        
                        // If phone starts with 0, replace it with Iraq country code (+964)
                        if (cleanPhone.startsWith('0')) {
                            cleanPhone = '964' + cleanPhone.substring(1);
                        }
                        // If phone doesn't start with country code but starts with 7, add Iraq code
                        else if (!cleanPhone.startsWith('964') && cleanPhone.startsWith('7')) {
                            cleanPhone = '964' + cleanPhone;
                        }
                        
                        // Create WhatsApp URL
                        const whatsappUrl = `https://api.whatsapp.com/send/?phone=${cleanPhone}&text=${message}`;
                        
                        // Open WhatsApp in new window/tab
                        window.open(whatsappUrl, '_blank');
                        
                        this.$swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "تم فتح واتساب في نافذة جديدة",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }

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
                // Add a small delay to prevent multiple rapid calls
                if (this.paginationTimeout) {
                    clearTimeout(this.paginationTimeout);
                }
                this.paginationTimeout = setTimeout(() => {
                    this.loadTableData();
                }, 100);
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
                // Check if doctors is an object (single doctor assigned)
                if (item.doctors && typeof item.doctors === 'object' && item.doctors.name) {
                    return item.doctors.name;
                }
                // Check if doctors is an array (multiple doctors - legacy format)
                if (item.doctors && Array.isArray(item.doctors) && item.doctors.length > 0 && item.doctors[0] && item.doctors[0].name) {
                    return item.doctors[0].name;
                }
                return '';
            },

            // Helper method to check if doctor exists
            hasDoctorAssigned(item) {
                // Check if doctors is an object
                if (item.doctors && typeof item.doctors === 'object' && !Array.isArray(item.doctors)) {
                    return true;
                }
                // Check if doctors is an array
                return item.doctors && Array.isArray(item.doctors) && item.doctors.length > 0 && item.doctors[0];
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
            reset() {
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
            getrecipes() {
                const cached = this.getCache(this.cacheConfig.recipes.key);
                if (cached) {
                    this.recipes = cached;
                    return;
                }

                Axios.get("getrecipes", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.recipes = res.data;
                        this.setCache(this.cacheConfig.recipes.key, res.data, this.cacheConfig.recipes.ttl);
                    })
                    .catch(() => {
                        // If API fails, try to use expired cache as fallback
                        const expiredCache = localStorage.getItem(this.cacheConfig.recipes.key);
                        if (expiredCache) {
                            try {
                                this.recipes = JSON.parse(expiredCache).data;
                            } catch (e) {
                                console.warn('Failed to parse expired cache');
                            }
                        }
                    });
            },

            getByDocor() {
                if (this.searchDocorId == 0) {
                    return this.initialize();
                }
                
                // Clear doctor search cache before performing new search
                this.clearDoctorSearchCache();
                
                this.isSearchingDoctor = true;
                this.loadingData = true;
                
                const currentPage = this.tableOptions.page || 1;
                const itemsPerPage = this.tableOptions.itemsPerPage || 10;
                
                // Don't use cache for doctor search - always fetch fresh data
                this.apiRequest(`https://smartclinicv5.tctate.com/api/patients/getByDoctor/${this.searchDocorId}?page=${currentPage}&per_page=${itemsPerPage}`)
                    .then(res => {
                        this.loadingData = false;
                        
                        if (res.data && res.data.data) {
                            this.totalItems = res.data.meta ? res.data.meta.total : res.data.data.length;
                            this.desserts = res.data.data;
                            this.pageCount = res.data.meta ? Math.ceil(res.data.meta.total / itemsPerPage) : Math.ceil(res.data.data.length / itemsPerPage);
                            this.page = currentPage;
                            
                            // Cache the fresh response
                            const cacheKey = this.generateCacheKey('doctor_patients', currentPage, itemsPerPage, '', this.searchDocorId, this.fullyPaidFilter);
                            this.setCache(cacheKey, {
                                data: res.data.data,
                                total: this.totalItems,
                                pageCount: this.pageCount
                            }, this.cacheConfig.patients.ttl);
                        } else {
                            // No data found
                            this.desserts = [];
                            this.totalItems = 0;
                            this.pageCount = 0;
                            this.page = 1;
                        }
                    })
                    .catch((error) => {
                        this.loadingData = false;
                        console.error('Doctor search error:', error);
                        
                        // Show error message to user
                        this.$swal.fire({
                            title: "خطأ في البحث",
                            text: "حدث خطأ أثناء البحث عن مرضى الطبيب. يرجى المحاولة مرة أخرى.",
                            icon: "error",
                            confirmButtonText: "اغلاق",
                        });
                        
                        // Reset to empty results
                        this.desserts = [];
                        this.totalItems = 0;
                        this.pageCount = 0;
                    });
            },

            clearDoctorSearchCache() {
                // Clear all doctor search-related cache keys
                Object.keys(localStorage).forEach(key => {
                    if (key.includes('doctor_patients')) {
                        localStorage.removeItem(key);
                    }
                });
            },

            clearPatientCache() {
                // Clear all patient-related cache keys
                Object.keys(localStorage).forEach(key => {
                    if (key.includes('patients') || key.includes('search_patients') || key.includes('all_patients')) {
                        localStorage.removeItem(key);
                    }
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
                        Axios.delete("https://smartclinicv5.tctate.com/api/patients/" + item.id, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                                }
                            })
                            .then(() => {
                                // Clear patient-specific cache first
                                this.clearPatientCache();
                                
                                this.$swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: this.$t('Successfully'),
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Clear all cache and refresh data
                                this.refreshAllData();
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
                // Clear all search-related cache before performing new search
                this.clearSearchCache();
                
                this.isSearching = true;
                this.loadingData = true;
                
                // Reset pagination for new search
                if (this.tableOptions.page === 1) {
                    // If we're already on page 1, proceed with search
                    this.performSearch();
                } else {
                    // Reset to page 1 and let the table update handler trigger the search
                    this.tableOptions.page = 1;
                    this.page = 1;
                }
            },

            clearSearchCache() {
                // Clear all search-related cache keys
                Object.keys(localStorage).forEach(key => {
                    if (key.includes('search_patients')) {
                        localStorage.removeItem(key);
                    }
                });
            },


            performSearch() {
                const currentPage = this.tableOptions.page || 1;
                const itemsPerPage = this.tableOptions.itemsPerPage || 10;
                
                // Don't use cache for search - always fetch fresh data
                this.apiRequest(`https://smartclinicv5.tctate.com/api/patients/searchv2/${this.search}?page=${currentPage}&per_page=${itemsPerPage}`)
                    .then(res => {
                        this.loadingData = false;
                        this.allItem = true;
                        
                        // Check if response has data
                        if (res.data && res.data.data) {
                            this.desserts = res.data.data;
                            this.totalItems = res.data.meta ? res.data.meta.total : res.data.data.length;
                            this.pageCount = res.data.meta ? Math.ceil(res.data.meta.total / itemsPerPage) : Math.ceil(res.data.data.length / itemsPerPage);
                            this.page = currentPage;
                            
                            // Cache the fresh response
                            const cacheKey = this.generateCacheKey('search_patients', currentPage, itemsPerPage, this.search);
                            this.setCache(cacheKey, {
                                data: res.data.data,
                                total: res.data.meta ? res.data.meta.total : res.data.data.length,
                                pageCount: this.pageCount
                            }, this.cacheConfig.patients.ttl);
                        } else {
                            // No data found
                            this.desserts = [];
                            this.totalItems = 0;
                            this.pageCount = 0;
                            this.page = 1;
                        }
                    })
                    .catch((error) => {
                        this.loadingData = false;
                        console.error('Search error:', error);
                        
                        // Show error message to user
                        this.$swal.fire({
                            title: "خطأ في البحث",
                            text: "حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.",
                            icon: "error",
                            confirmButtonText: "اغلاق",
                        });
                        
                        // Reset to empty results
                        this.desserts = [];
                        this.totalItems = 0;
                        this.pageCount = 0;
                    });
            },

            getclinicDoctor() {
                const cached = this.getCache(this.cacheConfig.doctors.key);
                if (cached) {
                    this.doctors = cached.doctors;
                    this.doctorsAll = cached.doctorsAll;
                    return;
                }

                const endpoint = this.$store.getters.isSecretary ? 'doctors/secretary' : 'doctors/clinic';
                this.apiRequest(endpoint)
                    .then(res => {
                        this.loadingData = false;
                        this.loading = false;
                        this.doctors = res.data.data;
                        this.doctorsAll = [{ id: 0, name: ' الكل' }, ...this.doctors];
                        
                        // Cache the response
                        this.setCache(this.cacheConfig.doctors.key, {
                            doctors: this.doctors,
                            doctorsAll: this.doctorsAll
                        }, this.cacheConfig.doctors.ttl);
                    })
                    .catch(() => {
                        this.loading = false;
                        // Try to use expired cache as fallback
                        const expiredCache = localStorage.getItem(this.cacheConfig.doctors.key);
                        if (expiredCache) {
                            try {
                                const data = JSON.parse(expiredCache).data;
                                this.doctors = data.doctors;
                                this.doctorsAll = data.doctorsAll;
                            } catch (e) {
                                console.warn('Failed to parse expired cache');
                            }
                        }
                    });
            },

            initialize() {
                if (this.isSearching || this.isSearchingDoctor) {
                    // Reset search states when initializing
                    this.isSearching = false;
                    this.isSearchingDoctor = false;
                    this.search = '';
                    this.searchDocorId = '';
                }
                
                this.loadingData = true;
                
                // Get pagination parameters from tableOptions
                const currentPage = this.tableOptions.page || 1;
                const itemsPerPage = this.tableOptions.itemsPerPage || 10;
                const cacheKey = this.generateCacheKey('all_patients', currentPage, itemsPerPage, '', '', this.fullyPaidFilter);
                
                const cached = this.getCache(cacheKey);
                if (cached) {
                    this.loadingData = false;
                    this.search = null;
                    this.totalItems = cached.total;
                    this.desserts = cached.data;
                    this.pageCount = Math.ceil(cached.total / itemsPerPage);
                    this.page = currentPage;
                    return;
                }
                
                this.apiRequest(`https://smartclinicv5.tctate.com/api/patients/getByUserIdv3?page=${currentPage}&per_page=${itemsPerPage}&fully_paid=${this.fullyPaidFilter}`)
                    .then(res => {
                        this.loadingData = false;
                        this.search = null;
                        
                        if (res.data && res.data.data) {
                            this.totalItems = res.data.meta ? res.data.meta.total : res.data.data.length;
                            this.desserts = res.data.data;
                            this.pageCount = res.data.meta ? Math.ceil(res.data.meta.total / itemsPerPage) : Math.ceil(res.data.data.length / itemsPerPage);
                            this.last_page = res.data.meta ? res.data.meta.last_page : 1;
                            this.page = currentPage;
                            
                            // Cache the response
                            this.setCache(cacheKey, {
                                data: res.data.data,
                                total: this.totalItems,
                                pageCount: this.pageCount
                            }, this.cacheConfig.patients.ttl);
                        } else {
                            // No data found
                            this.desserts = [];
                            this.totalItems = 0;
                            this.pageCount = 0;
                            this.page = 1;
                        }
                    })
                    .catch((error) => {
                        this.loadingData = false;
                        console.error('Initialize error:', error);
                        
                        // Try to use expired cache as fallback
                        const expiredCache = localStorage.getItem(cacheKey);
                        if (expiredCache) {
                            try {
                                const data = JSON.parse(expiredCache).data;
                                this.desserts = data.data || [];
                                this.totalItems = data.total || 0;
                                this.pageCount = data.pageCount || 0;
                            } catch (e) {
                                console.warn('Failed to parse expired cache');
                                this.desserts = [];
                                this.totalItems = 0;
                                this.pageCount = 0;
                            }
                        } else {
                            this.desserts = [];
                            this.totalItems = 0;
                            this.pageCount = 0;
                        }
                    });
            },

            getCaseCategories() {
                const cached = this.getCache(this.cacheConfig.caseCategories.key);
                if (cached) {
                    this.CaseCategories = cached;
                    return;
                }

                this.apiRequest('case-categories')
                    .then(res => {
                        this.loading = false;
                        this.CaseCategories = res.data;
                        this.setCache(this.cacheConfig.caseCategories.key, res.data, this.cacheConfig.caseCategories.ttl);
                    })
                    .catch(() => {
                        this.loading = false;
                        // Try to use expired cache as fallback
                        const expiredCache = localStorage.getItem(this.cacheConfig.caseCategories.key);
                        if (expiredCache) {
                            try {
                                this.CaseCategories = JSON.parse(expiredCache).data;
                            } catch (e) {
                                console.warn('Failed to parse expired cache');
                            }
                        }
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
                    
                    // Clear patient cache immediately when a patient is edited or created
                    this.clearPatientCache();
                    
                    // Clear all cache and refresh data
                    this.refreshAllData();
                    
                    // Handle redirection based on user role
                    const userRole = this.$store.getters.userRole;
                    if (!isEditing) {
                        // Only redirect for new patients
                        if (userRole === 'adminDoctor' || userRole === 'admin' || userRole === 'doctor') {
                            // Redirect to patient page for these roles
                            this.$router.push(`/patient/${patientData.id}`);
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
                                // Clear patient cache immediately when a patient is edited
                                this.clearPatientCache();
                                this.refreshAllData();
                                this.close();

                                this.$swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "تم تعديل ",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            })
                            .catch((error) => {
                                this.loadSave = false;

                                // Check if it's a phone duplication error
                                if (error.response && error.response.status === 422 && 
                                    error.response.data && error.response.data.errors && 
                                    error.response.data.errors.phone) {
                                    
                                    // Show specific message for phone duplication
                                    this.$swal.fire({
                                        title: "رقم الهاتف موجود مسبقاً",
                                        text: "يوجد مريض آخر بنفس رقم الهاتف. يرجى استخدام رقم هاتف مختلف.",
                                        icon: "warning",
                                        confirmButtonText: "اغلاق",
                                    });
                                } else if (error.response && error.response.status === 422 && 
                                          error.response.data && error.response.data.errors) {
                                    
                                    // Handle other validation errors
                                    const errors = error.response.data.errors;
                                    let errorMessage = "يرجى التحقق من المعلومات المدخلة:\n\n";
                                    
                                    Object.keys(errors).forEach(field => {
                                        if (Array.isArray(errors[field])) {
                                            errors[field].forEach(message => {
                                                errorMessage += `• ${message}\n`;
                                            });
                                        }
                                    });
                                    
                                    this.$swal.fire({
                                        title: "خطأ في البيانات",
                                        text: errorMessage,
                                        icon: "error",
                                        confirmButtonText: "اغلاق",
                                    });
                                } else {
                                    // Generic error message
                                    this.$swal.fire({
                                        title: "تأكد من ملء المعلومات",
                                        text: "يرجى المحاولة مرة أخرى",
                                        icon: "error",
                                        confirmButtonText: "اغلاق",
                                    });
                                }
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
                                // Clear patient cache immediately when a new patient is created
                                this.clearPatientCache();
                                this.refreshAllData();

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
                            .catch((error) => {
                                this.loadSave = false;

                                // Check if it's a phone duplication error
                                if (error.response && error.response.status === 422 && 
                                    error.response.data && error.response.data.errors && 
                                    error.response.data.errors.phone) {
                                    
                                    // Show specific message for phone duplication
                                    this.$swal.fire({
                                        title: "رقم الهاتف موجود مسبقاً",
                                        text: "يوجد مريض بنفس رقم الهاتف. يرجى استخدام رقم هاتف مختلف أو البحث عن المريض الموجود.",
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonText: "البحث عن المريض",
                                        cancelButtonText: "إغلاق",
                                        confirmButtonColor: "#3085d6",
                                        cancelButtonColor: "#d33",
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Search for the patient with this phone number
                                            this.search = patientData.phone;
                                            this.close();
                                            this.seachs();
                                        }
                                    });
                                } else if (error.response && error.response.status === 422 && 
                                          error.response.data && error.response.data.errors) {
                                    
                                    // Handle other validation errors
                                    const errors = error.response.data.errors;
                                    let errorMessage = "يرجى التحقق من المعلومات المدخلة:\n\n";
                                    
                                    Object.keys(errors).forEach(field => {
                                        if (Array.isArray(errors[field])) {
                                            errors[field].forEach(message => {
                                                errorMessage += `• ${message}\n`;
                                            });
                                        }
                                    });
                                    
                                    this.$swal.fire({
                                        title: "خطأ في البيانات",
                                        text: errorMessage,
                                        icon: "error",
                                        confirmButtonText: "اغلاق",
                                    });
                                } else {
                                    // Generic error message
                                    this.$swal.fire({
                                        title: "حدث خطأ أثناء الإضافة",
                                        text: "يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني",
                                        icon: "error",
                                        confirmButtonText: "اغلاق",
                                    });
                                }
                            });
                    }
            },

            // Method to refresh all data and clear cache
            refreshAllData() {
                // Clear all cache
                this.clearAllCache();
                
                // Reset search states
                this.isSearching = false;
                this.isSearchingDoctor = false;
                this.search = '';
                this.searchDocorId = '';
                
                // Reset pagination
                this.tableOptions.page = 1;
                this.page = 1;
                
                // Reload data
                this.initialize();
            },

        },

        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('patients.addnewpatients') : this.$t('update');
            },
            apiWhatsappEnabled() {
                return this.$store.getters.getApiWhatsapp === 1;
            }
            // Remove the old selected computed property that was calling getMoreitems
            // selected: function () {
            //     return this.getMoreitems();
            // }
        },

        watch: {
            // Add watcher for search to handle pagination reset
            search: {
                handler(newVal, oldVal) {
                    if (newVal !== oldVal && this.isSearching) {
                        this.performSearch();
                    }
                }
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