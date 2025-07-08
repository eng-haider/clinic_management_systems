<template>
    <div class="performance-optimized">
        <v-container id="dashboard" fluid tag="section">
            <!-- Optimized search with debouncing -->
            <v-text-field 
                v-if="showSearch"
                class="mt-4" 
                :label="$t('search')" 
                outlined 
                append-icon="mdi-magnify" 
                v-model="searchQuery"
                @input="debouncedSearch"
                clearable
            />

            <v-data-table 
                :headers="headers" 
                :loading="loadingData" 
                :page.sync="page" 
                @page-count="pageCount = $event"
                hide-default-footer 
                :items="filteredDesserts" 
                class="elevation-1 request_table data-table-optimized" 
                items-per-page="15"
            >
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> دكاتره العياده
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <v-dialog v-model="dialog" max-width="800px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn 
                                    color="primary" 
                                    @click="editedIndex = -1" 
                                    dark 
                                    class="mb-2" 
                                    v-bind="attrs" 
                                    v-on="on" 
                                    style="color:#fff;font-family: 'Cairo'"
                                >
                                    <v-icon left>mdi-plus</v-icon>
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


                                                <!-- <v-col class="py-0" cols="12" sm="6" md="6">
                                                    <v-radio-group v-model="editedItem.role" row>
                                                        <v-radio label="سكرتير" :value="3"></v-radio>
                                                        <v-radio label="دكاتره" :value="2"></v-radio>
                                                    </v-radio-group>
                                                </v-col> -->

                               
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
    import { mask } from "vue-the-mask";
    import Axios from "axios";
    
    export default {
        name: 'DoctorsView',
        directives: {
            mask,
        },
        components: {},
        data() {
            return {
                // Performance optimizations
                searchQuery: '',
                showSearch: true,
                cachedData: null,
                dataTimestamp: null,
                
                // Existing data
                gocase: false,
                desserts: [],
                filteredDesserts: [], // Add filtered data for performance

                editedItem: {
                    name: "",

                    password: "",
                    phone: "",
                    
                    
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
//  role: this.editedItem.role,
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
                this.dialog = false;
                this.initialize();


                // if (this.$store.state.role !== 'secretary') {
                //     this.gocase = false;
                //     this.addCase(this.patientInfo);
                // }




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


            // Performance Methods
            debouncedSearch() {
                this.$debounce(() => {
                    this.filterData();
                }, 300);
            },

            filterData() {
                if (!this.searchQuery) {
                    this.filteredDesserts = this.desserts;
                    return;
                }
                
                const query = this.searchQuery.toLowerCase();
                this.filteredDesserts = this.desserts.filter(item => {
                    return (item.names && item.names.toLowerCase().includes(query)) ||
                           (item.phones && item.phones.toLowerCase().includes(query));
                });
            },

            // Cache management
            shouldRefreshData() {
                const now = Date.now();
                const fiveMinutes = 5 * 60 * 1000;
                return !this.dataTimestamp || (now - this.dataTimestamp) > fiveMinutes;
            },

            async loadDoctorsData() {
                this.$startTimer('doctors_load');
                
                // Check cache first
                if (this.cachedData && !this.shouldRefreshData()) {
                    this.desserts = this.cachedData;
                    this.filteredDesserts = this.desserts;
                    this.loadingData = false;
                    this.$endTimer('doctors_load');
                    return;
                }

                try {
                    const cacheKey = 'doctors_clinic_info';
                    const data = await this.$apiCall("doctors/clinicDoctorInfo", {}, cacheKey);
                    
                    this.desserts = data;
                    this.filteredDesserts = data;
                    this.cachedData = data;
                    this.dataTimestamp = Date.now();
                    this.loadingData = false;
                } catch (error) {
                    console.error('Failed to load doctors data:', error);
                    this.loadingData = false;
                } finally {
                    this.$endTimer('doctors_load');
                }
            },

            async initialize() {
                this.loading = true;
                await this.loadDoctorsData();
                this.loading = false;
            },

            // Optimized item operations
            async editItem(item) {
                this.$startTimer('edit_item');
                // ...existing code...
                this.$endTimer('edit_item');
            },

            async deleteItem(item) {
                // Add confirmation with performance tracking
                this.$startTimer('delete_item');
                try {
                    const result = await this.$swal({
                        title: 'هل أنت متأكد؟',
                        text: "لا يمكن التراجع عن هذا الإجراء!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، احذف!'
                    });

                    if (result.isConfirmed) {
                        // Perform delete operation
                        // Add your delete API call here
                        
                        // Update local data
                        const index = this.desserts.findIndex(d => d.id === item.id);
                        if (index > -1) {
                            this.desserts.splice(index, 1);
                            this.filterData();
                        }
                    }
                } catch (error) {
                    console.error('Delete operation failed:', error);
                } finally {
                    this.$endTimer('delete_item');
                }
            },

            // Original initialize method (keeping for compatibility)
            originalInitialize() {
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

            





           

        },

        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'اضافه دكتور' : this.$t('update');
            },
        },

        watch: {
            searchQuery: {
                handler() {
                    this.debouncedSearch();
                },
                immediate: false
            }
        },

        async mounted() {
            await this.getCaseCategories();
            this.setupLazyImages();
        },

        async created() {
            await this.initialize();
        }
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