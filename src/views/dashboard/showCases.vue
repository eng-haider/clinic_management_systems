<template>

    <div>

        <div id="printMe" style="display:none">
            <billsReport :info="selecBill"></billsReport>
        </div>

        <v-dialog v-model="casesheet" max-width="900px">
      
            <cases :doctors="doctors" :editedItem="editedItem" :CaseCategories="CaseCategories"></cases>
        </v-dialog>


        <v-dialog v-model="Recipe" max-width="900px">

            <Recipe :RecipeInfo="editedItem" :CaseCategories="CaseCategories"></Recipe>
        </v-dialog>
        <v-container id="dashboard" fluid tag="section">
       

            <v-data-table :headers="headers" :items="desserts" class="elevation-1 request_table">
                <template v-slot:top>
                    <v-toolbar flat>


                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;">
                            
                        <v-btn color="green" style="color:#fff;font-weight:bold" @click="$router.go(-1)"> <i class="fas fa-arrow-right"></i>  رجــوع </v-btn>
                        </v-toolbar-title>

                        <!-- <v-divider class="mx-4" inset vertical></v-divider> -->
                         <v-spacer></v-spacer>
                        <span class="d-none d-sm-flex"
                        v-if="desserts.length>0"
                            style="font-family: 'Cairo', sans-serif;font-weight:bold;font-size:20px;text-align:center"> اسم المراجع : 
                            <span style="color:blue">
                               
                                <v-chip> {{desserts[0].patient.name}}</v-chip>
                            </span>
                            
                            </span>
                            
                          <v-spacer></v-spacer>
                         <v-btn color="primary" @click="addCase()"
                                 dark class="mb-2" v-bind="attrs" v-on="on" style="color:#fff;font-family: 'Cairo'">
                                    {{ $t("add_new") }}
                                </v-btn>
                    </v-toolbar>

                    <v-row   v-if="desserts.length>0" class="d-flex d-sm-none" style="    padding: 25px;">
                         <span 
                            style="font-family: 'Cairo', sans-serif;font-weight:bold;font-size:20px;text-align:center"> اسم المراجع : 
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
        </v-container>
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

    import Swal from "sweetalert2";
    // import {
    //     DxFileUploader
    // } from 'devextreme-vue/file-uploader';
    // import {
    //     DxProgressBar
    // } from 'devextreme-vue/progress-bar';

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
           cases,
            // DxFileUploader,
            Recipe,
            // DxProgressBar,
            // VueUploadMultipleImage
        },
        data() {
            return {
                desserts: [

                ],
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
                         sessions: [{
                            note: '',
                            date: ''
                        }],
                        notes: ""
                    }
                },
                items: [

                ],
                doctors: [],
                headers: [{
                        text: 'نوع الحاله',
                        align: "start",
                        value: "case_categories.name_ar"
                    },
                 
                    {
                        text: this.$t('datatable.price'),
                        align: "start",
                        value: "price"
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
                    //status_name_ar

                    {
                        text: this.$t('Processes'),
                        value: "actions",
                        sortable: false
                    }
                ],
                right: null
            }
        },

        mounted() {
            this.getCaseCategories();
            this.getclinicDoctor();
            this.id = this.$route.params.id;
            this.initialize();
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

            cropdate(x){
return  x.slice(0, 10);
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
                            //    alert('hi');
                                this.$swal.fire(this.$t('Successfully'), this.$t('done'), "success");
                                this.initialize();
                                this.initialize();
                            })
                            .catch(() => {
                                 this.$swal.fire(this.$t('Successfully'), this.$t('done'), "success");
                                    this.initialize();
                                    this.initialize();
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




addCase(){
 

                     this.editedItem={
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        patient_id:this.id,
                        lower_right: "",
                        lower_left: "",
                        case_categories: {
                            name_ar: ''
                        },
                        status_id:42,
                        bills: [{
                            price: '',
                            PaymentDate: ''
                        }],
                        images: [{
                                img: '',
                                descrption: ''
                            }

                        ],
                         sessions: [{
                            note: '',
                            date: ''
                        }],
                        notes: ""
                    }

                     this.editedItem={
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        patient_id:this.id,
                        lower_right: "",
                        lower_left: "",
                         sessions: [{
                            note: '',
                            date: ''
                        }],
                        case_categories: {
                            name_ar: ''
                        },
                        status_id:42,
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
            editItem(item) {
                this.editedIndex = this.desserts.indexOf(item);
                var doc=[];
               item.doctors.forEach((item, index)=>{
                index
                doc.push(item.id)
                    })
              item.doctors=doc;


                this.editedItem = Object.assign({}, item);
               // this.casesheet=true;


          
              ///  this.selecBill = Object.assign({}, this.editedItem);

                // if (this.editedItem == null) {
                //     this.editedItem = {
                //         case_categores_id: "",
                //         upper_right: "",
                //         upper_left: "",
                //         lower_right: "",
                //         lower_left: "",
                //         status_id: 1,

                //         bills: [{
                //             price: '',
                //             PaymentDate: ''
                //         }],
                //         images: [{
                //                 img: '',
                //                 descrption: ''
                //             }

                //         ],
                //         notes: ""
                //     }

                // }
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

            this.casesheet=true;            
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
                        patient_id: "",
                        lower_right: "",
                        lower_left: "",
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
                        status_id: 1,
                        notes: ""
                    }
                };


            },


            initialize() {
               
                this.loading = true;
                Axios.get("cases/patientCases/" + this.id, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
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
                       // this.loading = false;
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

        
            getDectors() {
                // this.doctors.push({
                //     'id': this.$store.state.AdminInfo.id,
                //     'name': this.$store.state.AdminInfo.name

                // })
            }

        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('add_new') : this.$t('update');

            },
        },
        created() {

    
            this.getCaseCategories();
            this.getclinicDoctor();
            EventBus.$on("changeStatusCloseCase", (from) => {

                from
                
                if(this.$route.name=='showCases'){
              //   window.location.reload()
             // document.location.reload(true);   

              location.href = location.origin + location.pathname + location.search 
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
            
            this.getDectors();

        },

    }
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