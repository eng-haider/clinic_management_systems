<template>
    <div>
        <div>
            <br>
            <v-toolbar flat>
                <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> 
                  {{ $t('conjugationsCategories.title') }}
                </v-toolbar-title>

                <v-divider class="mx-4" inset vertical></v-divider>
                <v-spacer></v-spacer>
                <v-dialog v-model="categoryDialog" max-width="800px">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn color="primary" @click="editedIndex = -1 
                                ;  
                                
                                " dark class="mb-2" v-bind="attrs" v-on="on" style="color:#fff;font-family: 'Cairo'">
                            <i class="fas fa-plus" style="position: relative;left:5px"></i>
                            {{ $t("store.addCate") }}
                        </v-btn>
                    </template>


                    <v-form ref="form" v-model="valid">
                        <v-card>

                            <v-toolbar dark color="primary lighten-1 mb-5">
                                <v-toolbar-title>
                                    <h3 style="color:#fff;font-family: 'Cairo'"> {{ $t("store.addCate") }}</h3>
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
                                            <v-text-field v-model="editedItemCategories.name"
                                                style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                                :label="$t('store.nameCate')" outlined>
                                            </v-text-field>
                                        </v-col>


                                    </v-row>
                                </v-container>
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                                </v-btn>
                                <v-btn :loading="loadSave" color="blue darken-1" @click="saveCat()" text>
                                    {{ $t("save") }}</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>


                </v-dialog>
            </v-toolbar>


        </div>

        <v-container id="dashboard" fluid tag="section">
            <v-expansion-panels>
                <v-expansion-panel v-for="item in ConjugationsCategories" :key="item.id"
                    @click="fetchDataByCategory(item.id)">
                    <v-expansion-panel-header>

                        <v-layout row wrap>
                            <v-flex xs12 md3 sm3 pt-3>
                                {{ item.name }}


                            </v-flex>

                            <v-flex xs12 md3 sm3 pt-3>
                                <span v-if="item.conjugations.length>0">
                                    {{ item.conjugations[0].total_price |currency }} د.ع </span> </v-flex>


                            <v-flex xs12 md2 sm2 pt-3>

                                <div v-if="item.conjugations.length>0"> <span style="color:#000;font-weight: bold;">
                                        المدفوع : </span> {{ item.conjugations[0].paid  | currency}} د.ع </div>
                            </v-flex>


                            <v-flex xs12 md3 sm3 pt-3>
                                <div v-if="item.conjugations.length>0">


                                    <span style="color:#000;font-weight: bold;"> المتبقي : </span>
                                    {{item.conjugations[0].total_price-item.conjugations[0].paid  | currency}} د.ع

                                </div>
                            </v-flex>


                            <v-flex xs12 md1 sm1 pt-3>

                                <v-icon @click.stop="openAddConjugationDialog(item.id)">
                                    mdi-plus</v-icon>
                                <v-icon style="float:left" @click.stop="editCategory(item)" class="ml-2">mdi-pencil
                                </v-icon>

                                <v-icon style="float:left" @click="deleteCatItem(item)" v-bind="attrs" v-on="on">
                                    mdi-delete
                                </v-icon>
                            </v-flex>

                        </v-layout>




                    </v-expansion-panel-header>
                    <v-expansion-panel-content>
                        <div v-if="selectedCategoryData[item.id]">
                            <v-data-table :headers="categoryHeaders" :items="selectedCategoryData[item.id]"
                                class="elevation-1">
                                <template v-slot:[`item.date`]="{ item }">
                                    <span>{{ item.date }}</span>
                                </template>
                                <template v-slot:[`item.name`]="{ item }">
                                    <span>{{ item.name }}</span>
                                </template>
                                <template v-slot:[`item.quantity`]="{ item }">
                                    <span>{{ item.quantity }}</span>
                                </template>
                                <template v-slot:[`item.price`]="{ item }">
                                    <span>{{ item.price }}</span>
                                </template>
                                <template v-slot:[`item.is_paid`]="{ item }">
                                    <v-switch v-model="item.is_paid" :label="item.is_paid ? $t('paid') : $t('not_paid')"
                                        color="green" inset @change="updateIsPaid(item)"></v-switch>
                                </template>
                                <template v-slot:[`item.actions`]="{ item }">
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon class="ml-5" @click="editItemItem(item)" v-bind="attrs" v-on="on">
                                                mdi-pencil</v-icon>
                                        </template>
                                        <span>{{ $t("edit") }} </span>
                                    </v-tooltip>
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon @click="deleteItem(item)" v-bind="attrs" v-on="on">mdi-delete
                                            </v-icon>
                                        </template>
                                        <span>{{ $t("delete") }}</span>
                                    </v-tooltip>
                                </template>
                            </v-data-table>
                        </div>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>

            <v-dialog v-model="dialog" max-width="800px">
                <v-form ref="form" v-model="valid">
                    <v-card>
                        <v-toolbar dark color="primary lighten-1 mb-5">
                            <v-toolbar-title>
                                <h3 style="color:#fff;font-family: 'Cairo'">{{ formTitle }}</h3>
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
                                            style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                            :label="$t('store.name')" outlined>
                                        </v-text-field>
                                    </v-col>
                                    <v-col class="py-0" cols="12" sm="6" md="6">
                                        <v-text-field v-model="editedItem.quantity" type="number"
                                            style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                            :label="$t('store.quantity')" outlined>
                                        </v-text-field>
                                    </v-col>
                                    <v-col class="py-0" cols="12" sm="6" md="6">
                                        <v-text-field v-model="editedItem.price"
                                            style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                            :label="$t('datatable.price')" type="number" outlined>
                                        </v-text-field>
                                    </v-col>
                                    <v-col class="py-0" cols="12" sm="6" md="6">
                                        <v-menu v-model="menu2" :close-on-content-click="false" :nudge-right="40"
                                            transition="scale-transition" offset-y min-width="auto">
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-text-field v-model="editedItem.date" label="Picker without buttons"
                                                    prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on">
                                                </v-text-field>
                                            </template>
                                            <v-date-picker v-model="editedItem.date" @input="setDefaultDate">
                                            </v-date-picker>
                                        </v-menu>
                                    </v-col>
                                    <v-col cols="12" md="6" sm="6" class="display:none">

                                        <v-select :rules="[rules.required]" dense
                                            v-model="editedItem.conjugations_categories_id" required label=" النوع "
                                            :items="ConjugationsCategories" outlined item-text="name" item-value="id">
                                        </v-select>
                                    </v-col>
                                    <v-col cols="12" md="6" sm="6">
                                        <v-switch v-model="editedItem.is_paid"
                                            :label="editedItem.is_paid ? $t('paid') : $t('not_paid')" color="green"
                                            inset></v-switch>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}</v-btn>
                            <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>{{ $t("save") }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-dialog>

        </v-container>


    </div>
</template>

<script>
    //Recipe
    import Swal from "sweetalert2";
    import Axios from "axios";
    import {
        mask
    } from "vue-the-mask";
    // import Axios from "axios";
    export default {
        directives: {
            mask,
        },
        components: {

        },
        data() {
            return {
                loading: false,
                editedItemCategories: {
                    id: '',
                    name: "",
                },
                menu2: false,
                false: false,
                ConjugationsCategories: [],
                categoryDialog: false, // Rename this to avoid conflict
                dialog: false,

                loadSave: false,
                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "names"
                    },

                    {
                        text: this.$t('datatable.price'),
                        align: "start",
                        value: "price"
                    },


                    {
                        text: 'الكمية',
                        align: "start",
                        value: "price"
                    },



                    {
                        text: this.$t('Processes'),
                        value: "actions",
                        sortable: false
                    }
                ],
                rules: {
                    required: value => !!value || "مطلوب",
                
                },
                editedIndex: -1,
                editedCategoryIndex: -1, // Separate index for category dialog
                editedItem: {
                    name: "",
                    quantity: "",
                    required_quantity: "",
                    price: "",
                    date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                    conjugations_categories_id: null,
                    is_paid: 1 // Default value is 1 (paid)
                },
                selectedCategoryData: {},
                categoryHeaders: [{
                        text: this.$t('ConjugationsCategories.name'),
                        value: 'name'
                    },
                    {
                        text: this.$t('ConjugationsCategories.price'),
                        value: 'price'
                    },
                    {
                        text: this.$t('ConjugationsCategories.date'),
                        value: 'date'
                    },
                    {
                        text: this.$t('ConjugationsCategories.quantity'),
                        value: 'quantity'
                    },

                    {
                        text: this.$t('payment_status'),
                        value: 'is_paid'
                    },
                    {
                        text: this.$t('Processes'),
                        value: 'actions',
                        sortable: false
                    },
                ],

            }
        },

        methods: {

            editItem(item) {
                const categoryId = item.conjugations_categories_id;
                this.editedIndex = this.selectedCategoryData[categoryId].indexOf(item);
                this.editedItem = Object.assign({}, item);
                this.dialog = true;
            },
            editItemItem(item) {

                this.editedIndex = this.selectedCategoryData[item.conjugations_categories_id].indexOf(item);

                this.editedItem = Object.assign({}, item);
                this.dialog = true;
            },



            //deleteCatItem
            deleteCatItem(item) {
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
                        Axios.delete("ConjugationsCategories/" + item.id, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                                }
                            })
                            .then(() => {
                                this.$swal.fire(this.$t('Successfully'), this.$t('done'), "success");
                                this.fetchDataByCategory(item.conjugations_categories_id);
                                this.initialize();
                            })
                            .catch(() => {
                                this.$swal.fire('لا يمكن حذف قسم يحتوي علئ فواتير', this.$t('not_done'),
                                    "error");
                            });
                    }
                });
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
                        Axios.delete("Conjugations/" + item.id, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token
                                }
                            })
                            .then(() => {
                                this.$swal.fire(this.$t('Successfully'), this.$t('done'), "success");
                                this.fetchDataByCategory(item.conjugations_categories_id);
                            })
                            .catch(() => {
                                this.$swal.fire(this.$t('not_successful'), this.$t('not_done'), "error");
                            });
                    }
                });
            },
            getClass(item) {
                if (item.required_quantity > item.quantity) {
                    return 'border: 2px solid red'
                }

            },
          
            saveCat() {


                if (this.$refs.form.validate()) {
                    this.loadSave = true;
                    if (this.editedCategoryIndex > -1) {

                        this.axios
                            .patch("ConjugationsCategories/" + this.editedItemCategories.id, this
                                .editedItemCategories, {
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

                        this.axios
                            .post("ConjugationsCategories", this.editedItemCategories, {
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
                                    position: "top-end",
                                    text: "",
                                    icon: "success",
                                    confirmButtonText: this.$t('close'),
                                    timer: 1500
                                });


                                this.categoryDialog = false;
                                this.loadSave = false;
                                this.initialize()
                          



                            })
                            .catch((err) => {
                                err

                                this.loadSave = false;

                            });
                    }
                }

            },
            save() {

                if (this.$refs.form.validate()) {
                    this.loadSave = true;
                    if (this.editedIndex > -1) {

                        this.axios
                            .patch("Conjugations/" + this.editedItem.id, this.editedItem, {
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                                },
                            })
                            .then(() => {
                                this.loadSave = false;
                                this.fetchDataByCategory(this.editedItem.conjugations_categories_id);
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

                        this.axios
                            .post("Conjugations", this.editedItem, {
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


                                this.dialog = false,
                                    this.loadSave = false;
                                // this.initialize();
                                this.fetchDataByCategory(this.editedItem.conjugations_categories_id)
                                this.initialize()
                                this.clearForm();





                            })
                            .catch((err) => {
                                err

                                this.loadSave = false;

                            });
                    }
                }

            },


            //ConjugationsCategories

            getConjugationsCategories() {
                this.loading = true;
                Axios.get("Conjugations/ConjugationsCategories", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {

                        this.loading = false;
                        this.ConjugationsCategories = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            fetchDataByCategory(categoryId) {
                Axios.get(`/Conjugations/getByCat/${categoryId}`, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.$set(this.selectedCategoryData, categoryId, res.data.data);
                    })
                    .catch(() => {
                        this.$swal.fire(this.$t('not_successful'), this.$t('not_done'), "error");
                    });
            },
            openAddConjugationDialog(categoryId) {
                this.editedItem = {
                    name: "",
                    quantity: "",
                    required_quantity: "",
                    price: "",
                    date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0,
                        10),
                    conjugations_categories_id: categoryId,
                    is_paid: 1 // Default value is 1 (paid)
                };
                this.dialog = true;
            },
            clearForm() {
                this.editedItem = {
                    name: "",
                    quantity: "",
                    required_quantity: "",
                    price: "",
                    date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0,
                        10),
                    conjugations_categories_id: "",
                    is_paid: 1 // Default value is 1 (paid)
                };
            },

            initialize() {
                this.loading = true;
                Axios.get("ConjugationsCategories", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.ConjugationsCategories = res.data.data;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            close() {

                this.categoryDialog = false; // Ensure this closes the correct dialog
              
                this.clearForm();

            },
            setDefaultDate() {
                if (!this.editedItem.date) {
                    this.editedItem.date = (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000))
                        .toISOString().substr(0, 10);
                }
                this.menu2 = false;
            },
            updateIsPaid(item) {
                Axios.patch(`Conjugations/updateIsPaid/${item.id}`, {
                        is_paid: item.is_paid
                    }, {
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
                            title: this.$t('done'),
                            showConfirmButton: false,
                            timer: 1500
                        });

                        this.initialize()
                        this.fetchDataByCategory(item.conjugations_categories_id);
                    })
                    .catch(() => {
                        this.$swal.fire(this.$t('not_successful'), this.$t('not_done'), "error");
                    });
            },
            editCategory(item) {
                this.editedCategoryIndex = this.ConjugationsCategories.indexOf(item);
                this.editedItemCategories = Object.assign({}, item);
                this.categoryDialog = true;
            },






        },


        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('store.addItem') : this.$t('update');

            },
        },
        mounted() {

        },
        created() {
            this.getConjugationsCategories()
            this.initialize();

        },

    }
</script>

<style>
    .carditem {
        border: 2px solid red;
    }

    .fgh {
        font-family: "Cairo" !important;
    }

    /* #dropzone-external {
        GetByClinicId
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
        border-style: solid.
    }

    .widget-container>span {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 16px.
    }

    #dropzone-image {
        max-width: 100%;
        max-height: 100%;
    }

    #dropzone-text>span {
        font-weight: 100;
        opacity: 0.5.
    }

    #upload-progress {
        display: flex;
        margin-top: 10px.
    }

    .flex-box {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-ConjugationsCategories: center.
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
        font-weight: normal.
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