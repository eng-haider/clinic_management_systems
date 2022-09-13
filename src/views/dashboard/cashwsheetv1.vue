<template>

    <div>

        <div id="printMe" style="display:none">
            <billsReport :info="selecBill"></billsReport>
        </div>


        <v-dialog v-model="Recipe" max-width="900px">
            <Recipe :RecipeInfo="editedItem" :CaseCategories="CaseCategories"></Recipe>
        </v-dialog>
        <v-container id="dashboard" fluid tag="section">
            <!-- <v-text-field class="mt-4" label="اكتب للبحث" outlined append-icon="mdi-magnify" v-model="search">
            </v-text-field> -->


            <v-data-table :headers="headers" :items="desserts" class="elevation-1 request_table">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.casesheet") }}
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <v-dialog v-model="dialog" max-width="1100px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn color="primary" @click="editedIndex = -1 
                                ;  
                                
                                " dark class="mb-2" v-bind="attrs" v-on="on" style="color:#fff;font-family: 'Cairo'">
                                    {{ $t("add_new") }}
                                </v-btn>
                            </template>
                            <v-form ref="form" v-model="valid">
                                <v-card>

                                    <v-toolbar dark color="primary lighten-1 mb-5">
                                        <v-toolbar-title>
                                            <h3 style="color:#fff;font-family: 'Cairo'" > {{formTitle}}</h3>
                                        </v-toolbar-title>
                                        <v-spacer />
                                        <v-btn @click="close()" icon>
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </v-toolbar>

                                    <v-card-text>
                                        <v-container>

                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="12" md="6">
                                                    <v-text-field dense v-model="editedItem.name"
                                                        :label="$t('datatable.name')" outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" cols="12" sm="12" md="4">
                                                    <v-text-field dense v-model="editedItem.phone"
                                                        :label="$t('datatable.phone')" outlined>
                                                    </v-text-field>
                                                </v-col>

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











                                            </v-row>


                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="12" md="6">
                                                    <v-text-field dense v-model="editedItem.systemic_conditions"
                                                        label="الامراض الجهازيه" outlined>
                                                    </v-text-field>
                                                </v-col>


                                                <v-col class="py-0" cols="12" sm="2" md="2">
                                                    <v-radio-group v-model="editedItem.sex" row>
                                                        <v-radio label="ذكر " :value="1"></v-radio>
                                                        <v-radio label="انثئ" :value="0"></v-radio>
                                                    </v-radio-group>
                                                </v-col>

                                                <v-col class="py-0" cols="12" sm="12" md="2">
                                                    <v-text-field dense v-model="editedItem.age"
                                                        :label="$t('datatable.age')" outlined>
                                                    </v-text-field>
                                                </v-col>









                                            </v-row>








                                            <br>
                                            <br>
                                            <div class="patiant_status">
                                                <v-layout row wrap>
                                                    <v-flex md5>
                                                        <hr>
                                                    </v-flex>
                                                    <v-flex md2>
                                                        <p  class="se_tit_menu">

                                                            الحـــــالــه

                                                        </p>
                                                    </v-flex>

                                                    <v-flex md5>
                                                        <hr>
                                                    </v-flex>
                                                </v-layout>


                                                <v-row row wrap>
                                                    <v-col cols="12" md="2"></v-col>
                                                    <v-col cols="12" md="4" sm="4" xs="6">
                                                        <v-select dense v-model="editedItem.photo_gallery_cats_id"
                                                            label="الدكتور المعالج" :items="doctors" outlined
                                                            item-text="name" item-value="id"></v-select>
                                                    </v-col>


                                                    <v-col cols="12" md="4" sm="4">
                                                        <v-select dense v-model="editedItem.case.case_categores_id"
                                                            label=" نوع الحاله " :items="CaseCategories" outlined
                                                            item-text="name_ar" item-value="id"></v-select>
                                                    </v-col>
                                                    <v-col cols="12" md="2"></v-col>

                                                </v-row>

                                                <v-layout row wrap dense >
                                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                                    </v-flex>
                                                    <v-flex xs6 md4>
                                                        <v-text-field type="number"
                                                            v-model="editedItem.case.upper_right" filled
                                                            label="العلوي الايمين"></v-text-field>
                                                    </v-flex>
                                                    <v-flex  xs6 md4
                                                        style="    padding: 0px !important;padding-right:5px">
                                                        <v-text-field v-model="editedItem.case.upper_left" type="number"
                                                            filled label="العلوي الايسر"></v-text-field>
                                                    </v-flex>
                                                    <v-flex  xs1 md2 class="d-none d-sm-flex">
                                                    </v-flex>

                                                </v-layout>



    <v-layout row wrap dense >
                                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                                    </v-flex>
                                                    <v-flex xs6 md4>
                                                      <v-text-field v-model="editedItem.case.lower_right"
                                                            type="number" dense filled label=" الاسفل الايمين ">
                                                        </v-text-field>
                                                    </v-flex>
                                                        <v-flex xs6 md4   style="    padding: 0px !important;padding-right:5px"> 
                                                    <v-text-field v-model="editedItem.case.lower_left" type="number"
                                                            dense filled label="الاسفل الايسر "></v-text-field>
                                                        </v-flex>
                                                    <v-flex  xs1 md2 class="d-none d-sm-flex">
                                                    </v-flex>

                                                </v-layout>




                                              






                                                <v-row row wrap>
                                                    <v-col cols="12" md="2" style="    padding: 0px !important;">
                                                    </v-col>
                                                    <v-col cols="12" md="8" sm="8" style="    padding: 0px !important;">

                                                        <v-textarea v-model="editedItem.case.notes" outlined
                                                            name="input-7-1" label="ملاحظات الحاله" hint="Hint text">
                                                        </v-textarea>
                                                    </v-col>

                                                    <v-col cols="12" md="2"></v-col>
                                                </v-row>

                                                <v-row row wrap style="height: auto;">
                                                    <v-col cols="12" md="12">
                                                        <!-- <p style="text-align:center;font-size: 22px;">ـــــــــــــــ صور الحاله
                                                        ـــــــــــــــ </p> -->
                                                        <div class="widget-container flex-box" v-for="uu in imgCount"
                                                            :key="uu" style="padding-right:10px">
                                                            <!-- <span>Profile Picture</span> -->
                                                            <div id="dropzone-external" class="flex-box" :class="[isDropZoneActive
        ? 'dx-theme-accent-as-border-color dropzone-active'
        : 'dx-theme-border-color']">
                                                                <img id="dropzone-image" :src="imageSource"
                                                                    v-if="imageSource" alt="">
                                                                <div id="dropzone-text" class="flex-box"
                                                                    v-if="textVisible">
                                                                    <span>اضغط هنا لرفع صوره الحاله</span>
                                                                    <span>…or click to browse for a file instead.</span>
                                                                </div>
                                                                <DxProgressBar id="upload-progress" :min="0" :max="100"
                                                                    width="30%" :show-status="false"
                                                                    :visible="progressVisible" :value="progressValue" />
                                                            </div>
                                                            <DxFileUploader id="file-uploader"
                                                                dialog-trigger="#dropzone-external"
                                                                drop-zone="#dropzone-external" :multiple="false"
                                                                :allowed-file-extensions="allowedFileExtensions"
                                                                upload-mode="instantly"
                                                                upload-url="https://js.devexpress.com/Demos/NetCore/FileUploader/Upload"
                                                                :visible="false" @drop-zone-enter="onDropZoneEnter"
                                                                @drop-zone-leave="onDropZoneLeave"
                                                                @uploaded="onUploaded" @progress="onProgress"
                                                                @upload-started="onUploadStarted" />


                                                            <v-text-field style="width:100$;padding-top:20px;" dense
                                                                v-model="editedItem.case.images[0].descrption"
                                                                label="وصف الصوره " outlined>
                                                            </v-text-field>
                                                        </div>


                                                    </v-col>
                                                </v-row>
                                            </div>
                                            <br>

                                            <!-- <v-row row wrap>
                                                 <div id="my-strictly-unique-vue-upload-multiple-image" style="display: flex; justify-content: center;">
    <vue-upload-multiple-image
      @upload-success="uploadImageSuccess"
      @before-remove="beforeRemove"
      @edit-image="editImages"
      :data-images="images"
      dragText='ss'
      idUpload="myIdUpload"
      editUpload="myIdEdit"
      ></vue-upload-multiple-image>
  </div>
                                           
                                            </v-row> -->











                                            <v-layout row wrap>
                                                <v-flex md5>
                                                    <hr>
                                                </v-flex>
                                                <v-flex md2>
                                                    <p class="se_tit_menu">

                                                        الفاتوره

                                                    </p>
                                                </v-flex>

                                                <v-flex md5>
                                                    <hr>
                                                </v-flex>
                                            </v-layout>

                                            <v-row row wrap>
                                                <v-col md="2"  class="d-none d-sm-flex"></v-col>
                                                <v-col md="8">
                                                    <v-text-field suffix="IQ" dense v-model="editedItem.case.price"
                                                        type="number" label="مبلغ الحاله" outlined>
                                                    </v-text-field>

                                                </v-col>
                                                <v-col md="2"  class="d-none d-sm-flex"></v-col>
                                            </v-row>





                                            <v-layout row wrap v-for="(item, index) in  editedItem.case.bills"
                                                :key="index">

                                                <v-col  md2   class="d-none d-sm-flex"></v-col>
                                                <v-flex md="4" md4 xs6>
                                                    <v-text-field suffix="IQ" dense
                                                        v-model="editedItem.case.bills[index].price" type="number"
                                                        label="الملغ المدفوع" outlined>
                                                    </v-text-field>

                                                </v-flex>

                                                <v-flex md4 xs5 >
                                                    <v-menu v-model="menu[editedItem.case.bills.indexOf(item)]"
                                                        :close-on-content-click="false" :nudge-right="40" lazy
                                                        transition="scale-transition" offset-y full-width
                                                        max-width="290px" min-width="290px">
                                                        <template v-slot:activator="{ on }">
                                                            <v-text-field dense outlined
                                                                v-model="editedItem.case.bills[index].PaymentDate"
                                                                label="Date" prepend-icon="mdi-calendar" readonly
                                                                v-on="on"></v-text-field>
                                                        </template>
                                                        <v-date-picker
                                                            v-model="editedItem.case.bills[index].PaymentDate" no-title
                                                            @input="menu[editedItem.case.bills.indexOf(item)] = false">
                                                        </v-date-picker>
                                                    </v-menu>




                                                </v-flex>
                                                <v-flex xs1 md1>
                                                    <v-btn color="red" v-if="index !== 0"
                                                        @click="deletePayment(index,item.id)" style="color:#fff">
                                                        حذف الدفعه
                                                    </v-btn>
                                                </v-flex>

                                                <v-flex  md1  class="d-none d-sm-flex">

                                                </v-flex>
                                            </v-layout>

                                            <v-card-actions class="justify-center">
                                                <v-btn color="green" style="color:#fff" @click="addPayment()">
                                                    اضافه دفعه
                                                </v-btn>
                                            </v-card-actions>











                                            <v-row>
                                                <v-col md="2"></v-col>
                                                <v-col md="4">
                                                    <p style="font-weight:bold"> المبلغ المدفوع :
                                                        <span>{{sumPay()}}</span> <span
                                                            style="font-weight:bold;color:red">IQ</span></p>
                                                    <p style="font-weight:bold"> المتبقي :
                                                        {{editedItem.case.price-sumPay()}} <span
                                                            style="font-weight:bold;color:red">IQ</span></p>
                                                    <v-btn color="#2196f3" @click="print()" style="color:#fff">
                                                        طبـــــاعه الفاتوره


                                                        <v-icon right dark>
                                                            fas fa-print
                                                        </v-icon>
                                                    </v-btn>
                                                </v-col>
                                                <v-col md="2"></v-col>

                                            </v-row>


                                            <v-row class="d-flex d-sm-none">
                                                   <v-col class=" py-0" cols="12" sm="12" md="12" >
                                                    <v-btn class="ma-2 white--text" color="green" :height="70"
                                                        style="color:#FFF;
                                                        text-align: center;
    height: 70px;
    color: rgb(255, 255, 255);
    position: r;
    right: 52px;
                                                        
                                                        
                                                        " @click="addRecipe()

                                                        
                                                        
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



                                            </v-row>
                                        </v-container>
                                    </v-card-text>

                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                                        </v-btn>
                                        <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text
                                        
                                        
                                        >
                                            {{ $t("save") }}</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-form>
                        </v-dialog>
                    </v-toolbar>
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
    </div>
</template>

<script>
    //Recipe
    import billsReport from './billsReport.vue';
    import {
        EventBus
    } from "./event-bus.js";
    import Recipe from './Recipe.vue';
    import Swal from "sweetalert2";
    import {
        DxFileUploader
    } from 'devextreme-vue/file-uploader';
    import {
        DxProgressBar
    } from 'devextreme-vue/progress-bar';
    // import VueUploadMultipleImage from 'vue-upload-multiple-image'
    import Axios from "axios";
    export default {
        components: {
            billsReport,
            DxFileUploader,
            Recipe,
            DxProgressBar,
            // VueUploadMultipleImage
        },
        data() {
            return {
                desserts: [

                ],
                paymentsCount: 1,
                cats: [],
                RecipeInfo: {},
                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu: [],

                imgCount: 1,
                images: [],
                selecBill: {},
                dialog: false,
                loadSave: false,
                CaseCategories: [],
                editedIndex: -1,

                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                progressValue: 0,
                allowedFileExtensions: ['.jpg', '.jpeg', '.gif', '.png'],



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
                        notes: ""
                    }
                },
                items: [

                ],
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
                    {
                        text: this.$t('datatable.status_Description'),
                        align: "start",
                        value: "case.notes"
                    },
                    //status_Description

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
                // this.sumPay();

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
                    this.imageSource = 'http://127.0.0.1:8000/images/' + this.editedItem.case.images[0].image_url;

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
                Axios.get("patients/getByUserId", {
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

            save() {
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
                            // this.loadSave = false;
                            this.SaveCase();
                            //  this.initialize();
                            //  this.close();
                            // this.$swal.fire({
                            //     title: "تم تعديل ",
                            //     text: "",
                            //     icon: "success",
                            //     confirmButtonText: "اغلاق",
                            // });
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


                            //this.editedItem=res.data.data;
                            console.log(this.editedItem);
                            this.SaveCase(res.data.data.id);


                        })
                        .catch((err) => {
                            err

                            this.loadSave = false;

                        });
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
                return this.editedIndex === -1 ? this.$t('add_new') : this.$t('update');

            },
        },
        created() {
            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                this.dialog = true
            });

            this.initialize();
            this.getCaseCategories();
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

    .se_tit_menu
    {
        text-align: center;
    font-size: 22px;
    color: #19537a;
    font-weight: bold;
    font-size: 27px;
    position: relative;
    bottom: 10px;
}
    
</style>