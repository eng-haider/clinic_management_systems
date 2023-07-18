<template>

    <div>


        <div>
            <br>
            <v-toolbar flat>
                <v-toolbar-title style="font-family: 'Cairo', sans-serif;"> {{ $t("header.store") }}
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
                                                style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                                :label="$t('store.quantity')" outlined>
                                            </v-text-field>
                                        </v-col>

                                        <v-col class="py-0" cols="12" sm="6" md="6">
                                            <v-text-field v-model="editedItem.required_quantity"
                                                style="direction: rtl;text-align: right;" :rules="[rules.required]"
                                                :label="$t('store.required_quantity')" outlined>
                                            </v-text-field>
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
            <!-- <v-text-field class="mt-4" label="اكتب للبحث" outlined append-icon="mdi-magnify" v-model="search">
            </v-text-field> -->

            <v-layout row wrap pt-4>
                <v-flex xs12 md3 sm3 v-for="item in items" :key="item" pl-3 pt-3>

                    <v-card class="mx-auto" max-width="344" outlined :class="getClass(item)">
                        <v-list-item three-line>
                            <v-list-item-content>
                                <div class="text-h5 mb-4">
                                    {{ item.name }}

                                    <v-icon style="float:left" @click="editItem(item)" v-bind="attrs" v-on="on">
                                        mdi-pencil</v-icon>


                                    <v-icon style="float:left" @click="deleteItem(item)" v-bind="attrs" v-on="on">
                                        mdi-delete</v-icon>

                                </div>


                                <p class="fgh text-h5 mb-1 " style=" font-family:Cairo !important;">
                                    المتوفره : {{ item.quantity }}
                                </p>



                                <p class="fgh text-h5 mb-1 " style="font-family:Cairo !important;">
                                    التنبيه عند الكميه : {{ item.required_quantity }}
                                </p>



                            </v-list-item-content>


                        </v-list-item>

                        <v-card-actions>
                            <v-btn outlined rounded text
                                @click="item.quantity=item.quantity+1,update(item,item.quantity)">
                                +1
                            </v-btn>

                            <v-btn outlined rounded text
                                @click="item.quantity=item.quantity-1,update(item,item.quantity)">
                                -1
                            </v-btn>
                        </v-card-actions>
                    </v-card>

                </v-flex>
            </v-layout>


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
                false: false,
                dialog: false,
                items: [],
                loadSave: false,
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
                    required_quantity: ""

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
                        Axios.delete("Items/" + item.id, {
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
                            .patch("Items/" + this.editedItem.id, this.editedItem, {
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
                            .post("Items", this.editedItem, {
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

            initialize() {
                this.loading = true;
                Axios.get("Items/GetByClinicId", {
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