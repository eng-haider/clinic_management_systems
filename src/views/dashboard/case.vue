<template>
    <div>
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
                        <div class="patiant_status">
                            <v-chip class="ma-2" color="primary">
                                اختر السن :
                            </v-chip>
                            <v-radio-group style="    float: left;" row v-model="editedItem.status_id">
                                <template v-slot:label>


                                </template>
                                <v-radio :value="42">
                                    <template v-slot:label>
                                        <div><strong class="error--text">غير مكتمله</strong></div>
                                    </template>
                                </v-radio>
                                <v-radio :value="43">
                                    <template v-slot:label>
                                        <div> <strong class="success--text">مكتمله</strong></div>
                                    </template>
                                </v-radio>
                            </v-radio-group>
                            <div v-if="editedItem.id !== undefined && editedItem.id < 87">
                                <v-layout row wrap dense>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>
                                    <v-flex xs6 md4>
                                        <v-text-field type="number" v-model="editedItem.upper_right" filled
                                            label="العلوي الايمين"></v-text-field>
                                    </v-flex>
                                    <v-flex xs6 md4 style="    padding: 0px !important;padding-right:5px">
                                        <v-text-field v-model="editedItem.upper_left" type="number" filled
                                            label="العلوي الايسر"></v-text-field>
                                    </v-flex>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>
                                </v-layout>
                                <v-layout row wrap dense>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>
                                    <v-flex xs6 md4>
                                        <v-text-field v-model="editedItem.lower_right" type="number" dense filled
                                            label=" الاسفل الايمين ">
                                        </v-text-field>
                                    </v-flex>
                                    <v-flex xs6 md4 style="    padding: 0px !important;padding-right:5px">
                                        <v-text-field v-model="editedItem.lower_left" type="number" dense filled
                                            label="الاسفل الايسر "></v-text-field>
                                    </v-flex>
                                    <v-flex xs1 md2 class="d-none d-sm-flex">
                                    </v-flex>

                                </v-layout>
                            </div>

                            <div v-else>
                                <teeth :tooth_num="editedItem.tooth_num" :id="editedItem.id" />

                            </div>

                            <v-row row wrap>

                                <v-col cols="12" md="6" sm="6">
                                    <p style="display:none;"></p>

                                    <v-select :rules="[rules.required]" dense v-model="editedItem.case_categores_id"
                                        required label=" نوع الحاله " :items="CaseCategories" outlined
                                        item-text="name_ar" item-value="id"></v-select>


                                </v-col>


                                <!-- 
                                <v-col cols="12" md="6" sm="6" v-if="doctors.length>1">
                                    <p style="display:none;"></p>



                                    <v-select :rules="[rules.requiredd]" dense required v-model="editedItem.doctors"
                                        multiple :label="$t('doctor')" :items="doctors" outlined item-text="name"
                                        item-value="id">
                                    </v-select>


                                </v-col> -->


                            </v-row>



                            <div v-if="editedItem.case_categores_id==3">

                                <h2 style="    padding: 19px;" pa-4>Access Opening</h2>
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-simple-table style="direction: ltr !important;">
                                            <template v-slot:default>
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            Canel
                                                        </th>

                                                        <th>
                                                            Working length
                                                        </th>

                                                        <th>
                                                            Refreence point
                                                        </th>

                                                        <th>
                                                            File Used
                                                        </th>


                                                        <th>

                                                        </th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <tr v-for="(row, rowIndex) in editedItem.root_stuffing.access_opening"
                                                        :key="rowIndex">
                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" dense outlined v-model="row[0]">
                                                            </v-text-field>
                                                        </td>


                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" dense outlined v-model="row[1]">
                                                            </v-text-field>
                                                        </td>


                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" outlined dense v-model="row[2]">
                                                            </v-text-field>
                                                        </td>

                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" outlined dense v-model="row[3]">
                                                            </v-text-field>
                                                        </td>

                                                        <td style="    padding: 0px;">
                                                            <v-icon style="color:red" @click="deleterow(rowIndex)"
                                                                v-bind="attrs" v-on="on">
                                                                mdi-delete</v-icon>
                                                        </td>
                                                    </tr>


                                                </tbody>
                                            </template>
                                        </v-simple-table>
                                    </v-flex>
                                    <v-card-actions class="justify-center">
                                        <v-btn small color="green" style="color:#fff" @click="addRow()">
                                            <i class="fas fa-plus"></i>
                                            Add Canel

                                        </v-btn>
                                    </v-card-actions>

                                </v-layout>
                                <br>
                                <br>

                            </div>





                            <div v-if="editedItem.case_categores_id==3">
                                <h2 style="    padding: 19px;" pa-4>oburation</h2>
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-simple-table style="direction: ltr !important;">
                                            <template v-slot:default>
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            Canel
                                                        </th>

                                                        <th>
                                                            Last file size
                                                        </th>

                                                        <th>
                                                            
                                                            Sealer Type & duration
                                                        </th>

                                                        <th>
                                                            Gutta percha size
                                                        </th>


                                                        <th>

                                                        </th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    <tr v-for="(row, rowIndex) in editedItem.root_stuffing.oburation"
                                                        :key="rowIndex">
                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" dense outlined v-model="row[0]">
                                                            </v-text-field>
                                                        </td>


                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" dense outlined v-model="row[1]">
                                                            </v-text-field>
                                                        </td>


                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" outlined dense v-model="row[2]">
                                                            </v-text-field>
                                                        </td>

                                                        <td style="    padding: 0px;">
                                                            <v-text-field label="" outlined dense v-model="row[3]">
                                                            </v-text-field>
                                                        </td>

                                                        <td style="    padding: 0px;">
                                                            <v-icon style="color:red"
                                                                @click="deletoburationerow(rowIndex)" v-bind="attrs"
                                                                v-on="on">
                                                                mdi-delete</v-icon>
                                                        </td>
                                                    </tr>


                                                </tbody>
                                            </template>
                                        </v-simple-table>
                                    </v-flex>
                                    <v-card-actions class="justify-center">
                                        <v-btn small color="green" style="color:#fff" @click="addoburationRow()">
                                            <i class="fas fa-plus"></i>
                                            Add Canel


                                        </v-btn>
                                    </v-card-actions>

                                </v-layout>


                            </div>



                            <v-row row wrap v-for="(note, index) in editedItem.sessions" :key="note">
                                <v-col cols="12" md="2">


                                    <v-chip class="ma-2" color="primary">
                                        الجلسه رقم : {{index+1}}
                                    </v-chip>

                                </v-col>
                                <v-col cols="12" md="12" sm="12" style="    padding: 0px !important;">

                                    <v-textarea v-model="editedItem.sessions[index].note" outlined name="input-7-1"
                                        :label="(' ملاحظات الجلسه رقم '+sum(index))">>
                                    </v-textarea>
                                </v-col>

                                <v-col cols="12" md="2" v-if="editedItem.sessions[index].date==''">
                                    <p :class="$vuetify.breakpoint.xs ? 'onl_ph_datea' : ''">
                                        {{ new Date().toLocaleDateString('en-GB').replace(/\//g, '-') }}</p>
                                </v-col>
                                <v-col cols="12" md="2" v-else>

                                    <p :class="$vuetify.breakpoint.xs ? 'onl_ph_datea' : ''">
                                        {{ new Date(editedItem.sessions[index].date).toLocaleDateString('en-GB').replace(/\//g, '-') }}
                                    </p>



                                </v-col>


                            </v-row>

                            <v-card-actions class="justify-center">
                                <v-btn small color="green" style="color:#fff" @click="addSession()">
                                    <i class="fas fa-plus"></i>
                                    اضافه جلسه

                                </v-btn>
                            </v-card-actions>



                            <v-row row wrap v-if="editedItem.images.length >0">

                                <v-col cols="12" md="4" v-for="(img, index) in  editedItem.images" :key="img" pr-2 pl-2>
                                    <div style="height:auto;width:auto">



                                        <CoolLightBox :items="editedItem.images" :index="index" @close="index = null">
                                        </CoolLightBox>

                                        <div class="images-wrapper">
                                            <div class="image" v-for="(image, imageIndex) in editedItem.images"
                                                :key="imageIndex" @click="index = imageIndex"
                                                :style="{ backgroundImage: 'url(' +Url+'/case_photo/'+ image.image_url + ')' }">
                                            </div>
                                        </div>


                                        <!-- <a data-fancybox="gallery"  :href="Url+'/case_photo/'+img.image_url" class=""> -->

                                        <div style="position: relative; width: 100%; height: 100%;">
                                            <img v-if="img.image_url !== undefined"
                                                :src="Url + '/case_photo/' + img.image_url"
                                                style="width: 100%; height: 100%;" />

                                            <!-- Delete icon positioned in the top-right corner of the image -->
                                            <span v-if="img.image_url !== undefined" @click="deleteImage(index,img.id)"
                                                style="position: absolute; top: 5px; right: 5px; cursor: pointer;">
                                                <i class="fas fa-trash" style="font-size: 1.5em; color: red;"></i>
                                            </span>
                                        </div>

                                        <!-- </a> -->
                                    </div>
                                </v-col>
                            </v-row>
                            <v-row row wrap style="height: auto;">
                                <v-col cols="12" md="12">

                                    <vue-dropzone ref="myVueDropzone" @vdropzone-success="success" id="dropzone"
                                        :options="dropzoneOptions">

                                    </vue-dropzone>


                                </v-col>
                            </v-row>
                        </div>

                        <br>
                        <br>

                        <v-card class="cre_bill">
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
                                <v-col md="2" class="d-none d-sm-flex"></v-col>
                                <v-col md="8">
                                    <v-text-field suffix="IQ" dense v-model="editedItem.price" type="number"
                                        label="مبلغ الحاله" outlined>
                                    </v-text-field>

                                </v-col>
                                <v-col md="2" class="d-none d-sm-flex"></v-col>
                            </v-row>





                            <v-layout row wrap v-for="(item, index) in  editedItem.bills" :key="index">

                                <v-flex md2 class="d-none d-sm-flex"></v-flex>
                                <v-flex md="4" md4 xs6>
                                    <v-text-field suffix="IQ" dense v-model="editedItem.bills[index].price"
                                        type="number" label="الملغ المدفوع" outlined>
                                    </v-text-field>

                                </v-flex>

                                <v-flex md4 xs5>
                                    <v-menu v-model="menu[editedItem.bills.indexOf(item)]"
                                        :close-on-content-click="false" :nudge-right="40" lazy
                                        transition="scale-transition" offset-y full-width max-width="290px"
                                        min-width="290px">
                                        <template v-slot:activator="{ on }">
                                            <v-text-field dense v-bind="attrs" outlined
                                                @blur="date = parseDate(editedItem.bills[index].PaymentDate)"
                                                v-model="editedItem.bills[index].PaymentDate" label="التاريخ"
                                                prepend-icon="mdi-calendar" v-on="on">
                                            </v-text-field>
                                        </template>
                                        <v-date-picker v-model="editedItem.bills[index].PaymentDate" no-title
                                            @input="menu[editedItem.bills.indexOf(item)] = false">
                                        </v-date-picker>
                                    </v-menu>
                                </v-flex>
                                <v-flex xs1 md1>
                                    <v-icon style="color:red" @click="deletePayment(index,item.id)" v-bind="attrs"
                                        v-on="on">
                                        mdi-delete</v-icon>
                                </v-flex>
                                <v-flex md1 class="d-none d-sm-flex">
                                </v-flex>
                            </v-layout>
                            <v-card-actions class="justify-center"
                                v-if="editedItem.price-sumPay() !==0 && editedItem.price-sumPay() >0">
                                <v-btn small color="green" style="color:#fff" @click="addPayment()">
                                    <i class="fas fa-plus"></i>
                                    اضافه دفعه
                                </v-btn>
                            </v-card-actions>




                            <v-layout row wrap pt-5 mt-5>
                                <v-flex md="2" xs="1"></v-flex>
                                <v-flex md="4" xs="10">
                                    <div style="font-weight:bold"> المبلغ المدفوع :

                                        <v-chip class="ma-2" color="success" outlined label>
                                            {{sumPay()|currency}} IQ
                                        </v-chip>
                                    </div>
                                    <div style="font-weight:bold"><span style="    padding-left: 34px;"> المتبقي
                                            :</span>

                                        <v-chip color="success" class="ma-2" outlined label>
                                            {{editedItem.price-sumPay()|currency}} IQ
                                        </v-chip>


                                    </div>

                                    <br>

                                </v-flex>
                                <v-flex md="2" xs="1"></v-flex>

                            </v-layout>

                        </v-card>
                        <!-- <v-btn color="#2196f3" @click="print()" style="color:#fff;    float: left;
    margin-top: 21px;
">
                            طبـــــاعه الفاتوره


                            <v-icon right dark>
                                fas fa-print
                            </v-icon>
                        </v-btn> -->






                    </v-container>
                </v-card-text>

                <br>

                <br>
                <br>
                <br>
                <v-card-actions style="padding:30px">
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                    </v-btn>
                    <v-btn :loading="loadSave" color="blue darken-1" @click="save()" class="success">
                        {{ $t("save") }}</v-btn>
                </v-card-actions>

            </v-card>
        </v-form>
    </div>
</template>

<style>
    .onl_ph_datea {
        position: relative;
        bottom: 37px;
        float: left;



    }

    .theme--dark .cre_bill {
        background-color: #252525 !important;
        color: #fff !important;
    }

    .cre_bill {
        background-color: #f6f6f6 !important;

    }
</style>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    import teeth from '../../components/core/teeth_v2.vue';
    import {
        EventBus
    } from "./event-bus.js";


    import Axios from "axios";
    export default {

        props: {
            editedItem: Object,
            CaseCategories: Array,
            doctors: Array,
            gocase: Boolean


        },


        components: {
            // DxFileUploader,

            teeth,
            // Fancybox,
            vueDropzone: vue2Dropzone
            // DxProgressBar,
        },
        data() {
            return {
                oldDoctors: {},
                recipes: [],
                UP_url: 'https://mina-api.tctate.com/public/api/cases/uploude_image',
                dropzoneOptions: {
                    url: 'https://mina-api.tctate.com/api/cases/uploude_image',
                    thumbnailWidth: 150,
                    maxFilesize: 5.5,

                    renameFile: function (file) {
                        return new Date().getTime() + '_' + file.name
                    },

                    sending: function (file, xhr, formData) {
                        formData.append("_token", "{{{ csrf_token() }}}");
                    },

                    dictDefaultMessage: "<i class='fas fa-upload'></i> اضغط هنا لرفع صور الحاله",
                    headers: {
                        "My-Awesome-Header": "header value"
                    }
                },
                desserts: [],
                tableData: [
                    ["", " ", "", ""]
                ],
                paymentsCount: 1,

                cats: [],
                valid: false,
                image: [],
                RecipeInfo: {},
                status: [{
                        id: '42',
                        name: 'لم تنمنهي'
                    },
                    {
                        id: '43',
                        name: 'مكتمله'
                    }
                ],
                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu: [],

                rules: {
                    minPhon: (v) => v.length == 13 || "رقم الهاتف يجب ان يتكون من 11 رقم",
                    required: value => !!value || "مطلوب",
                    requiredd: value => !!value || "مطلوب",
                    min: (v) => v.length >= 6 || "كلمة المرور يجب ان تتكون من 6 عناصر او اكثر",
                    email: value => {
                        if (value.length > 0) {
                            const pattern =
                                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return pattern.test(value) || 'يجب ان يكون ايميل صحيح';
                        }
                    },
                },
                imgCount: 1,
                images: [],
                selecBill: {},
                disabled: false,
                timeout: null,
                dialog: false,
                loadSave: false,
                //  CaseCategories: [],
                editedIndex: -1,

                isDropZoneActive: false,
                imageSource: '',
                textVisible: true,
                progressVisible: false,
                progressValue: 0,
                allowedFileExtensions: ['.jpg', '.jpeg', '.gif', '.png'],
                items: [

                ],
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
            deleteImage(index, id) {


                let text = "هل انت متاكد من الحذف ؟ ";
                if (confirm(text) == true) {


                    this.editedItem.images.splice(index, 1);
                    Axios.delete("/cases/delete_image/" + id, {
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token
                            }
                        })



                        .then(() => {
                            // this.$swal.fire({
                            //         title: "تم الحذف ",
                            //         text: "",
                            //         icon: "success",
                            //         confirmButtonText: "اغلاق",
                            //     });
                            //      this.initialize();

                        })
                        .catch(() => {});


                }

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


            addRow() {
                // Check if `root_stuffing` is a JSON string and parse it if necessary
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



                // Now add the new row to `oburation`
                const newRow = ['', '', '', ''];

                this.editedItem.root_stuffing.access_opening.push(newRow);
            },


            deleterow(index) {
                this.editedItem.root_stuffing.access_opening.splice(index, 1);
            },

            addoburationRow() {
                const newRow = Array('', '', '', '');
                this.editedItem.root_stuffing.oburation.push(newRow);
            },
            deletoburationerow(index) {
                this.editedItem.root_stuffing.oburation.splice(index, 1);
            },


            formatDate(date) {
                if (!date) return null

                const [year, month, day] = date.split('-')
                return `${month}/${day}/${year}`
            },
            parseDate(date) {

                if (!date) return null

                const [month, day, year] = date.split('/')
                return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`


            },
            sum(x) {

                return x + 1

            },
            addSession() {
                this.editedItem.sessions.push({

                    note: '',
                    date: ''


                })
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
                if (this.editedItem.bills.length == 1) {

                    //  return 0;
                }
                for (let i = 0; i < this.editedItem.bills.length; i++) {
                    if (this.editedItem.bills.price !== 0) {
                        sum += parseInt(this.editedItem.bills[i].price);
                    }

                }


                if (isNaN(sum)) {
                    return 0;
                }
                return sum
            },

            deletePayment(index, id) {


                this.sumPay();
                let text = "هل انت متاكد من الحذف ؟ ";
                if (confirm(text) == true) {
                    this.editedItem.bills.splice(index, 1);
                    Axios.delete("bills/" + id, {
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " + this.$store.state.AdminInfo.token
                            }
                        })
                        .then(() => {

                            this.initialize();
                        })
                        .catch(() => {});

                }

            },
            addPayment() {

                const date = new Date();

                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();

                this.editedItem.bills.push({

                    price: 0,
                    PaymentDate: `${year}-${month}-${day}`


                })
            },

            success(file, response) {

                this.images.push(response.data);


            },
            //uploude photos
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


            editItem(item) {
                console.log(item)
                this.editedIndex = this.desserts.indexOf(item);

                this.editedItem = Object.assign({}, item);
                this.selecBill = Object.assign({}, this.editedItem);







                this.oldDoctors = this.editedItem.doctors;
                if (this.editedItem == null) {
                    this.editedItem = {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        lower_right: "",
                        lower_left: "",
                        doctors: [],
                        tooth_num: [],
                        status_id: 42,

                        bills: [{
                            price: '',
                            PaymentDate: ''
                        }],
                        sessions: [{
                            note: '',
                            date: ''
                        }],
                        images: [],
                        notes: ""
                    }

                }
                if (this.editedItem.bills.length == 0) {
                    this.editedItem.bills = [{
                        price: '',
                        PaymentDate: ''
                    }]

                }


                if (this.editedItem.bills.length == 0) {
                    this.editedItem.images = [{
                            img: '',
                            descrption: ''
                        }

                    ]

                }
                if (this.editedItem.images.length > 0) {
                    this.imageSource = '/images/' + this.editedItem.images[0].image_url;

                }










                this.dialog = true;
            },
            close() {

                this.editedItem = {
                    case: {
                        case_categores_id: "",
                        upper_right: "",
                        upper_left: "",
                        patient_id: "",
                        doctors: [],
                        lower_right: "",
                        tooth_num: [],
                        lower_left: "",
                        images: [{
                            img: '',
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

                this.dialog = false;
                EventBus.$emit("changeStatusCloseCase", false);
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
                        doctors: [],
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




            save() {

                if (this.$refs.form.validate() && this.loadSave == false) {
                    this.loadSave = true;
                    // if (this.editedIndex > -1 && this.editedItem.id !== undefined) {


                    this.editedItem.images = this.images;

                    if (this.editedItem.id !== undefined) {


                        if (this.editedItem.bills == undefined) {
                            this.editedItem.bills = [];
                        } else if (this.editedItem.bills.length == 0) {
                            this.editedItem.bills = [];
                        }

                        this.axios
                            .patch("cases/" + this.editedItem.id, this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then(() => {


                                this.loadSave = false;




                                this.$swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "تم تعديل ",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                
                                
                                this.close();
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

                        this.editedItem.images = this.images;
                        this.axios
                            .post("cases", this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then((res) => {


                                //cases
                                this.loadSave = false;

                                this.editedIndex = -1;
                                this.close();
                                EventBus.$emit("changeStatusCloseCase", false);
                                res

                                this.$swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: 'تمت اضافه حاله جديده',
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                if (this.$route.name !== 'showCases') {

                                    // this.initialize();
                                    if (this.gocase) {
                                        this.$router.push('/patient/' + res.data.data.patient_id);
                                        // this.$router.push({
                                        //     name: 'showCases',
                                        //     params: {
                                        //         id: res.data.data.patient_id
                                        //     }
                                        // })
                                    } else {
                                        //   window.location.reload()
                                        //  document.location.reload(true);  
                                        //   location.href = location.origin + location.pathname + location.search
                                    }

                                }




                            })
                            .catch((err) => {
                                err

                                this.loadSave = false;

                            });
                    }

                }
            },



        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'اضافه حاله جديده' : this.$t('update');

            },
        },
        date(val) {
            val
            this.dateFormatted = this.formatDate(this.date)
        },
        created() {
            this.getrecipes()
            EventBus.$on("changetooth", (tooth) => {
                this.editedItem.tooth_num = tooth;

            });

            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                this.dialog = true
            });




        },

    }
</script>

<style>
    body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow-y: visible !important;
    }


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
    .dropzone-container {
        display: flex;
        flex-direction: column;
        border: 1px dashed #ccc;
        border-radius: 15px;
    }

    .dropzone .dz-preview.dz-image-preview {
        z-index: 1 !important;
    }

    body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow-y: visible !important;
    }
</style>