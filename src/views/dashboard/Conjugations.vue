<template>

    <div>


        <div>
            <br>
            <v-toolbar flat>
                <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.Conjugations") }}
                </v-toolbar-title>

                <v-divider class="mx-4" inset vertical></v-divider>
                <v-spacer></v-spacer>
                <v-dialog v-model="dialog" max-width="800px">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn color="primary" @click="editedIndex = -1 
                                ;  
                                
                                " dark class="mb-2" v-bind="attrs" v-on="on" style="color:#fff;font-family: 'Cairo'">
                            <i class="fas fa-plus" style="position: relative;left:5px"></i>
                            {{ $t("store.addItem") }}


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
                                                style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                                :label="$t('store.name')" outlined>
                                            </v-text-field>
                                        </v-col>


                                        <v-col class="py-0" cols="12" sm="6" md="6">
                                            <v-text-field v-model="editedItem.quantity"
                                            type="number"
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

                                            <v-menu
        v-model="menu2"
        :close-on-content-click="false"
        :nudge-right="40"
        transition="scale-transition"
        offset-y
        min-width="auto"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
          v-model="editedItem.date"
            label="Picker without buttons"
            prepend-icon="mdi-calendar"
            readonly
            v-bind="attrs"
            v-on="on"
          ></v-text-field>
        </template>
        <v-date-picker
        v-model="editedItem.date"
          @input="menu2 = false"
        ></v-date-picker>
      </v-menu>

                                            <!-- <v-text-field v-model="editedItem.date"
                                                style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                                :label="$t('datatable.date')" type="number" outlined>
                                            </v-text-field> -->
                                        </v-col>


                                        <v-col cols="12" md="6" sm="6">
                                    <p style="display:none;"></p>

                                    <v-select :rules="[rules.required]" dense v-model="editedItem.conjugations_categories_id"
                                        required label=" النوع " :items="ConjugationsCategories" outlined
                                        item-text="name" item-value="id"></v-select>


                                </v-col>

                                <v-col cols="12" md="6" sm="6">
                                    <v-switch v-model="editedItem.is_paid" :label="editedItem.is_paid ? $t('paid') : $t('not_paid')" color="green" inset></v-switch>
                                </v-col>



                                    </v-row>
                                </v-container>
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" text @click="close()">{{ $t("close") }}
                                </v-btn>
                                <v-btn :loading="loadSave" color="blue darken-1" @click="save()" text>
                                    {{ $t("save") }}</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>


                </v-dialog>
            </v-toolbar>


        </div>

        <v-container id="dashboard" fluid tag="section">
         
            <v-data-table :headers="headers" :loading="loadingData" :page.sync="page" @page-count="pageCount = $event"
                hide-default-footer :items="items" class="elevation-1 request_table" items-per-page="15">
                <template v-slot:top>
                    <v-toolbar flat>
                     
                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        
                    </v-toolbar>


                </template>


                <template v-slot:[`item.names`]="{ item }">

                    <span>
                        {{item.name}}
                    </span>



                </template>

                <template v-slot:[`item.is_paid`]="{ item }">
                    <v-switch v-model="item.is_paid" :label="item.is_paid ? $t('paid') : $t('not_paid')" color="green" inset @change="updateIsPaid(item)"></v-switch>
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
                menu2: false,
                false: false,
                ConjugationsCategories:[],
                dialog: false,
                items: [],
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

                editedItem: {
                    name: "",
                    quantity: "",
                    required_quantity: "",
                    price:"",
                    date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                    conjugations_categories_id:"",
                    is_paid: 1 // Default value is 1 (paid)

                },

            }
        },

        methods: {

            editItem(item) {

                this.editedIndex = this.items.indexOf(item);

                this.editedItem = Object.assign({}, item);


             


                this.dialog = true;
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
                                this.initialize();
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
            update(item, main) {
                this.axios
                    .patch("Items/" + item.id, {
                        name: item.name,
                        quantity: main
                    }, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token,
                        },
                    })

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
                                this.initialize();





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
                Axios.get("https://mina-api.tctate.com/api/Conjugations/ConjugationsCategories", {
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


            initialize() {
                this.loading = true;
                Axios.get("Conjugations/GetByClinicId", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {

                        this.loading = false;
                        this.items = res.data.data;


                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },

            close() {

                this.dialog = false;
                this.editedItem={
                    name: "",
                    quantity: "",
                    required_quantity: "",
                    price:"",
                    date:"",
                    conjugations_categories_id:"",
                    is_paid: 1 // Default value is 1 (paid)


                }

            },

            updateIsPaid(item) {
                Axios.patch(`Conjugations/${item.id}`, { is_paid: item.is_paid }, {
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




    @media only screen and (max-width: 600px) {
        .allsee {
            display: none;
        }
    }
</style>