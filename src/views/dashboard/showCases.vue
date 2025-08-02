<template>

    <div>



        <v-dialog v-model="casesheet" max-width="1200px" v-track-dialog>

            <cases :doctors="doctors" :editedItem="editedItem" :CaseCategories="CaseCategories"></cases>
        </v-dialog>



        <v-container id="dashboard" fluid tag="section">


            <v-data-table :headers="headers" :loading="loadingData" :items="desserts" class="elevation-1 request_table">
                <template v-slot:top>


                    <v-toolbar flat>


                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;">

                            <v-btn color="green" style="color:#fff;font-weight:bold" @click="$router.go(-1)"> <i
                                    class="fas fa-arrow-right"></i> {{ $t("back") }} </v-btn>
                        </v-toolbar-title>

                        <!-- <v-divider class="mx-4" inset vertical></v-divider> -->
                        <v-spacer></v-spacer>
                        <span class="d-none d-sm-flex" v-if="desserts.length>0"
                            style="font-family: 'Cairo', sans-serif;font-weight:bold;font-size:20px;text-align:center">
                            {{ $t("patient_name") }} :
                            <span style="color:blue">

                                <v-chip> {{desserts[0].patient.name}}</v-chip>
                            </span>

                        </span>

                        <v-spacer></v-spacer>
                        <v-btn color="primary" @click="addCase()" dark class="mb-2" v-bind="attrs" v-on="on"
                            style="color:#fff;font-family: 'Cairo'">
                            {{ $t("add_new") }}
                        </v-btn>
                    </v-toolbar>

                    <v-row v-if="desserts.length>0" class="d-flex d-sm-none" style="    padding: 25px;">
                        <span
                            style="font-family: 'Cairo', sans-serif;font-weight:bold;font-size:20px;text-align:center">
                            {{ $t("patient_name") }} :
                            <span style="color:blue">
                                {{desserts[0].patient.name}}
                            </span>

                        </span>

                    </v-row>
                </template>

                <template v-slot:[`item.sex`]="{ item }">
                    <span v-if="item.sex==1">{{ $t("male") }}</span>
                    <span v-else>{{ $t("female") }}</span>
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
                        <span>{{$t('Delete')}}</span>
                    </v-tooltip>
                </template>
                <template v-slot:[`item.date`]="{ item }">
                    {{cropdate(item.created_at)}}
                </template>
                <template v-slot:[`item.bills`]="{ item }">

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
                    <!-- <v-chip class="ma-2" :color="item.status.status_color" outlined>
                        <v-icon left>
                            {{item.status.status_icon}}
                        </v-icon>
                        {{item.status.status_name_ar}}
                    </v-chip> -->


                    <v-switch
    :input-value="item.status.id === 43"
    :label="item.status.id === 43 ? $t('completed') : $t('not_completed')"
    @change="() => toggleStatus(item)"
    color="green"
    inset
  ></v-switch>


                </template>

                <template v-slot:item.tooth_num="{ item }">
                    <span v-for="(num, index) in parseToArray(item.tooth_num)" :key="index" style="font-weight:bold">
                        {{ num }}
                        <span v-if="index < parseToArray(item.tooth_num).length - 1"> - </span>
                    </span>
                </template>

                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize">{{ $t("Reloading") }}</v-btn>
                </template>
            </v-data-table>
        </v-container>
    </div>
</template>


<script>
import cases from './case.vue';

import Swal from "sweetalert2";
import { mask } from "vue-the-mask";
import Axios from "axios";
import {
        EventBus
    } from "./event-bus.js";


export default {
    directives: {
        mask,
    },
    components: {
        cases,
    },
    data() {
        return {
            desserts: [],
            loadingData: false,
            paymentsCount: 1,
            cats: [],
            patientInfo: {},
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
    {
        "id": 1,
        "name_ar": "قلع",
        "name_en": "sds",
        "created_at": "2022-01-04T16:45:37.000000Z",
        "updated_at": "2022-01-04T16:45:37.000000Z"
    },
    {
        "id": 2,
        "name_ar": "حشوة",
        "name_en": "en",
        "created_at": "2022-01-04T16:47:51.000000Z",
        "updated_at": "2022-01-04T16:47:51.000000Z"
    },
    {
        "id": 3,
        "name_ar": "حشوة جذر",
        "name_en": "زراعة",
        "created_at": "2022-02-02T12:19:49.000000Z",
        "updated_at": "2022-02-02T12:19:49.000000Z"
    },
    {
        "id": 4,
        "name_ar": "صناعة اسنان ثابتة",
        "name_en": "",
        "created_at": "2022-02-02T12:19:49.000000Z",
        "updated_at": "2022-02-02T12:19:49.000000Z"
    },
    {
        "id": 5,
        "name_ar": "امراض اللثة",
        "name_en": "",
        "created_at": "2022-02-02T12:20:14.000000Z",
        "updated_at": "2022-02-02T12:20:14.000000Z"
    },
    {
        "id": 6,
        "name_ar": "اطفال",
        "name_en": "",
        "created_at": "2022-02-02T12:20:14.000000Z",
        "updated_at": "2022-02-02T12:20:14.000000Z"
    },
    {
        "id": 7,
        "name_ar": "فحص",
        "name_en": "",
        "created_at": "2022-02-02T12:20:30.000000Z",
        "updated_at": "2022-02-02T12:20:30.000000Z"
    },
    {
        "id": 8,
        "name_ar": "اشعة",
        "name_en": "",
        "created_at": "2022-02-02T12:20:30.000000Z",
        "updated_at": "2022-02-02T12:20:30.000000Z"
    },
    {
        "id": 9,
        "name_ar": "زراعه اسنان",
        "name_en": "",
        "created_at": "2023-08-05T14:15:28.000000Z",
        "updated_at": "2023-08-05T14:15:28.000000Z"
    }, 
    {
        "id":10,
        "name_ar": "تقويم",
        "name_en": "",
        "created_at": "2023-08-05T14:15:28.000000Z",
        "updated_at": "2023-08-05T14:15:28.000000Z"
    }

    , 
    {
        "id":11,
        "name_ar": "تنظيف الاسنان",
        "name_en": "",
        "created_at": "2023-08-05T14:15:28.000000Z",
        "updated_at": "2023-08-05T14:15:28.000000Z"
    }


    , 
    {
        "id":12,
        "name_ar": "تبيض الاسنان باليزر",
        "name_en": "",
        "created_at": "2023-08-05T14:15:28.000000Z",
        "updated_at": "2023-08-05T14:15:28.000000Z"
    }


   
],
            rules: {
                minPhon: (v) => v.length == 13 || this.$t('phone_length'),
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
            isDropZoneActive: false,
            imageSource: '',
            textVisible: true,
            progressVisible: false,
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
                    }],
                    sessions: [{
                        note: '',
                        date: ''
                    }],
                    notes: ""
                }
            },
            items: [],
            doctors: [],
            headers: [
                { text: this.$t('case_type'), align: "start", value: "case_categories.name_ar" },
                { text: this.$t('datatable.touth_num'), align: "start", value: "tooth_num" },
                { text: this.$t('datatable.price'), align: "start", value: "price" },
                { text: this.$t('datatable.status_Description'), align: "start", value: "sessions[0].note" },
                { text: this.$t('datatable.date'), align: "start", value: "date" },
                { text: this.$t('datatable.status'), align: "start", value: "status" },
                { text: this.$t('payment_status'), align: "start", value: "bills" },
                { text: this.$t('Processes'), value: "actions", sortable: false }
            ],
            right: null
        };
    },

    created() {

        this.getCaseCategories();

EventBus.$on("changeStatusCloseCase", (from) => {

    from

    if (this.$route.name == 'showCases') {
        //   window.location.reload()
        // document.location.reload(true);   

        //    location.href = location.origin + location.pathname + location.search 
    }
    // window.location.reload()

    this.casesheet = false;
    this.initialize();
    this.getclinicDoctor();

});
EventBus.$on("changeStatusCloseField", (from) => {

    from

    this.Recipe = false;
    this.dialog = true
    this.getclinicDoctor();
});

/// this.initialize();


},
    mounted() {
        this.getCaseCategories();
        this.id = this.$route.params.id;
        this.initialize();
       
        this.getclinicDoctor();
    },
    methods: {
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
            if (typeof toothNum === 'string') {
                try {
                    return JSON.parse(toothNum);
                } catch (e) {
                    return [toothNum];
                }
            }
            return Array.isArray(toothNum) ? toothNum : [toothNum];
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
                this.loading = false;
                this.doctors = res.data.data;
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
            return isNaN(sum) ? 0 : sum;
        },
        cropdate(x) {
            return x.slice(0, 10);
        },
        addRecipe() {
            this.Recipe = true;
            this.dialog = false;
        },
        print() {
            this.$htmlToPaper('printMe');
        },
        sumPay() {
            let sum = 0;
            for (let i = 0; i < this.editedItem.case.bills.length; i++) {
                sum += parseInt(this.editedItem.case.bills[i].price);
            }
            return isNaN(sum) ? 0 : sum;
        },
        deletePayment(index, id) {
            Swal.fire({
                title: this.$t('sure_process'),
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
            });
        },
        onDropZoneEnter(e) {
            if (e.dropZoneElement.id === 'dropzone-external') {
                this.isDropZoneActive = true;
            }
        },
        onDropZoneLeave(e) {
            if (e.dropZoneElement.id === 'dropzone-external') {
                this.isDropZoneActive = false;
            }
        },
        onUploaded(e) {
            const { file } = e;
            const fileReader = new FileReader();
            fileReader.onload = () => {
                this.isDropZoneActive = false;
                this.imageSource = fileReader.result;
                this.editedItem.case.images = [{
                    'img': [this.imageSource],
                    'descrption': this.editedItem.case.images[0].descrption
                }];
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
        addCase() {
            this.editedItem = {
                case_categores_id: "",
                upper_right: "",
                upper_left: "",
                patient_id: this.id,
                lower_right: "",
                root_stuffing: {
                    "access_opening": [
                        ['', '', '', '']
                    ],
                    "oburation": [
                        ['', '', '', '']
                    ],
                },
                tooth_num: [],
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
                }],
                notes: ""
            };
            this.casesheet = true;
        },
        editItem(item) {
            this.editedIndex = this.desserts.indexOf(item);
            var doc = [];
            item.doctors.forEach((item, index) => {
                index
                doc.push(item.id);
            });
            item.doctors = doc;
            this.editedItem = Object.assign({}, item);
            if (this.editedItem.bills.length == 0) {
                this.editedItem.bills = [{
                    price: '',
                    PaymentDate: ''
                }];
            }
            if (this.editedItem.images.length == 0) {
                this.editedItem.images = [{
                    img: '',
                    descrption: ''
                }];
            }


            if (this.editedItem.images.length > 0) {
                this.imageSource = 'https://smartclinicv5.tctate.com/public/images/' + this.editedItem.images[0].image_url;
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
    this.editedItem.root_stuffing.access_opening = [[null, null, null, null]];
}

// Ensure `oburation` is an array in `root_stuffing`
if (!Array.isArray(this.editedItem.root_stuffing.oburation)) {
    this.editedItem.root_stuffing.oburation = [[null, null, null, null]];
}
    
            this.casesheet = true;
        },
        save() {
            this.loadSave = true;
            Axios.put("patients/caseSheet/" + this.editedItem.id, {
                "case_categores_id": this.editedItem.case.case_categores_id,
                "upper_right": this.editedItem.case.upper_right,
                "upper_left": this.editedItem.case.upper_left,
                "lower_right": this.editedItem.case.lower_right,
                "lower_left": this.editedItem.case.lower_left,
                "tooth_num": this.editedItem.case.tooth_num,
                "notes": this.editedItem.case.notes,
                "sessions": this.editedItem.case.sessions,
                "images": this.editedItem.case.images,
                "status_id": 1,
                "bills": this.editedItem.case.bills,
                "patient_id": this.id,
                "doctors": this.editedItem.doctors,
            }, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                }
            })
            .then(res => {
                res
                this.casesheet = false;
                this.Recipe = false;
                this.loadSave = false;
                this.editedItem = Object.assign({}, this.editedItem);
                this.initialize();
                Swal.fire(
                    this.$t('Successfully'),
                    this.$t('done'),
                    'success'
                );
            })
            .catch(() => {
                Swal.fire(this.$t('not_successful'), this.$t('not_done'), 'error');
                this.loadSave = false;
            });
        },
        close() {
            this.casesheet = false;
            this.dialog = false;
            this.Recipe = false;
            this.initialize();
        },
        initialize() {
            this.loadingData = true;
            Axios.get("cases/patientCases/" + this.id, {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                }
            })
            .then(res => {
                this.loadingData = false;
                this.desserts = res.data.data;
                this.patientInfo = res.data.patient;
            })
            .catch(() => {
                this.loadingData = false;
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

        }
    }
};
</script>


<style>
    #dropzone-external {
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
    }
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