<template>

    <div>



        <div>
            <OwnerBooking :patientFound="patientFound=true" :patientInfo="patientInfo" v-if="booking" />
        </div>







        <v-container id="dashboard" fluid tag="section">


            <v-data-table :headers="headers" :loading="loadingData" disable-pagination :page.sync="page"
                @page-count="pageCount = $event" hide-default-footer :items="desserts" class="elevation-1 request_table"
                items-per-page="15">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.waitinglist") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>

                    </v-toolbar>



                </template>




                <template v-slot:[`item.doctor`]="{ item }">


                    <div v-if="item.doctors_count>1">
                        <span style="display: none;">{{ item }}</span>
                        <v-chip style="margin:2px" color="primary" v-for="item in  item.doctors" :key="item">
                            <v-icon left>
                                mdi-account-circle-outline
                            </v-icon>{{ item.name }}
                        </v-chip>
                    </div>

                </template>





                <template v-slot:[`item.names`]="{ item }">
                    {{ item.names }}

                </template>


                <template v-slot:[`item.phone`]="{ item }">
                    {{ item.phones }}

                </template>



                <template v-slot:[`item.date`]="{ item }">


                    {{ formatDate(item.created_at) }}

                </template>





                <!-- Custom Actions Column Slot -->
                <!-- <template v-slot:[`item.actions`]="{ item }">
                  
                    <v-switch :input-value="item.waitinglist_status_id === 2"
                        :label="item.waitinglist_status_id === 2 ? 'تمت الزيارة' : ' منتظر'"
                        @change="() => toggleStatus(item)" color="green" inset></v-switch>
                </template> -->


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
                cats: [],
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
                isSearchingDoctor: false,

                editedItem: {
                    name: "",
                    age: "",
                    birth_date: "",
                    sex: "",
                    address: "",
                    phone: "",
                    waitinglist_status_id: false, // Default to "No"
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
                    }, 
                    {
                        text: this.$t('datatable.phone'),
                        align: "start",
                        value: "phones"
                    },

                    {
                        text: this.$t('datatable.doctor'),
                        align: "start",
                        value: "owner_names"
                    },



                    

                    {
                        text: 'تاريخ التسجيل',
                        align: "date",
                        value: "date"
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

            toggleStatus(item) {

                // Toggle item status between 42 and 43
                item.waitinglist_status_id = item.waitinglist_status_id === 2 ? 1 : 2;
                this.changeStatus(item);
            },


            changeStatus(item) {
                const waitinglist_status_id = item.waitinglist_status_id;
                const patientId = item.id;

                this.axios.post(`/patients/changewaitinglist/${patientId}`, {
                        waitinglist_status_id: waitinglist_status_id
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
            formatDate(date) {
    if (!date) return "—"; // Return a dash if no date is provided
    const normalizedDate = date.includes(' ') ? date.replace(' ', 'T') : date;
    const dateObject = new Date(normalizedDate);
    const options = {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    };
    return new Intl.DateTimeFormat("ar", options).format(dateObject);
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
                        waitinglist_status_id: false, // Default to "No"
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


            initialize(page = 1) {
                page
                if (this.isSearching || this.isSearchingDoctor)
            return; // Prevent initialize from running if a search is active
            const today = new Date();
            const date = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
                this.loading = true;

                // Fetch data from both APIs concurrently
                Promise.all([
                        Axios.get(`patients/getByUserswaitinglist?page=${this.current_page}`, {
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token
                            }
                        }),
                        this.axios.get(`https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=${date}_${date}.&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`, {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${this.$store.state.AdminInfo.tctate_token}`
        }
      })
                    ])
                    .then(([patientsRes, reservationsRes]) => {
                        this.loading = false;
                        this.loadingData = false;
                        this.search = null;

                        // Update pagination based on the first API
                        this.last_page = patientsRes.data.meta.last_page;
                        this.pageCount = patientsRes.data.meta.last_page;

                        // Normalize and map data from both APIs
                        const patientsData = patientsRes.data.data.map((item) => ({
                            id: item.id,
                            names: item.name || "Unknown",
                            phones: item.phone || "Unknown",
                            owner_names:"Unknown",
                            date: item.scheduled_at.split(" ")[0], // Extract date
                            created_at: item.scheduled_at, // Use existing created_at
                        }));
                       

                        const reservationsData = reservationsRes.data.data.map((item) => {
                            // Convert reservation_end_date and reservation_from_time to created_at format
                            const createdAt = new Date(
                                    `${item.reservation_start_date}T${item.reservation_from_time}.000Z`)
                                .toISOString();

                            return {
                                id: item.id,
                                names: item.user ? item.user.full_name : "Unknown",
                                phones: item.user ? item.user.user_phone : "Unknown",
                                date: item.reservation_start_date,
                                owner_names: item.owner_name?item.owner_name:"Unknown",
                                created_at: createdAt, // Newly formatted created_at
                            };
                        });

                        // Combine both datasets
                        this.desserts = [...patientsData, ...reservationsData];
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            async getclinicDoctor() {

                if(this.$store.state.role=='secretary'){
                this.loading = true;
                try {
                    const response = await Axios.get("doctors/secretary", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    });
                    this.loadingData = false;
                    this.loading = false;
                    this.doctors = response.data.data;
                    this.fetchReservations();
                } catch (error) {
                    this.loading = false;
                }

            }else{
                this.fetchReservations();
            }
            },
            async fetchReservations() {
                try {
                    const today = new Date();
                    const date = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
                    let allReservations = [];

                    if (this.doctors.length > 1 && this.$store.state.role === 'secretary') {
                        for (let doctor of this.doctors) {
                            const response = await Axios.get(
                                `https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=${date}_${date}.&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`,
                                {
                                    headers: {
                                        "Content-Type": "application/json",
                                        Accept: "application/json",
                                        Authorization: "Bearer " + doctor.user.tctate_token,
                                    }
                                }
                            );
                            const reservations = response.data.data.map(reservation => ({
                                id: reservation.id,
                                names: reservation.user ? reservation.user.full_name : "Unknown",
                                phones: reservation.user ? reservation.user.user_phone : "Unknown",
                                date: reservation.reservation_start_date,
                                created_at: new Date(`${reservation.reservation_start_date}T${reservation.reservation_from_time}.000Z`).toISOString(),
                            }));
                            allReservations = allReservations.concat(reservations);
                        }
                    } else {
                        const response = await Axios.get(
                            `https://tctate.com/api/api/reservation/owner/search?filter[BetweenDate]=${date}_${date}.&filter[status_id]=&filter[user.user_phone]=&filter[user.full_name]=&sort=-id&page=1`,
                            {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.tctate_token,
                                }
                            }
                        );
                        allReservations = response.data.data.map(reservation => ({
                            id: reservation.id,
                            names: reservation.user ? reservation.user.full_name : "Unknown",
                            phones: reservation.user ? reservation.user.user_phone : "Unknown",
                            date: reservation.reservation_start_date,
                            created_at: new Date(`${reservation.reservation_start_date}T${reservation.reservation_from_time}.000Z`).toISOString(),
                        }));
                    }
                    this.desserts = allReservations;
                } catch (error) {
                    console.error('Error fetching reservations:', error);
                }
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
            this.getCaseCategories();
        },
        created() {
            this.initialize();
            this.getclinicDoctor();
            // this.getrecipes();
            // this.getclinicDoctor();
            // this.getCaseCategories();



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
</style>