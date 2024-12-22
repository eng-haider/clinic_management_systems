<template>

    <div>
        <br>
        
        <v-btn color="primary" @click="Recipe = true  
                                ;  
                                
                                " dark class="mb-2" v-bind="attrs" v-on="on"
            style="color:#fff;font-family: 'Cairo';float:left">
            <i class="fas fa-plus" style="position: relative;left:5px"></i>
            اضافه راجيته

        </v-btn>
     
        <v-dialog v-model="Recipe" max-width="900px">


            <Recipe :RecipeInfo="RecipeInfo" :recipes="recipes" :CaseCategories="CaseCategoriess" :patients="patients">
            </Recipe>
        </v-dialog>
        <v-container id="dashboard" fluid tag="section">
            <v-data-table :headers="headers" :loading="loading" :page.sync="page" items-per-page="15"
                @page-count="pageCount = $event" :items="desserts" class="elevation-1 request_table">



                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title style="font-family: 'Cairo', sans-serif;">الراجيتة
                        </v-toolbar-title>

                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>

                    </v-toolbar>



                </template>







                <template v-slot:[`item.date`]="{ item }">
                    {{cropdate(item.created_at)}}




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

            <v-layout row wrap>
                <v-flex xs12>

                    <div class="text-center pt-2">
                        <v-pagination @input="goTop()" v-model="page" :length="pageCount"></v-pagination>

                    </div>
                </v-flex>
            </v-layout>

        </v-container>
    </div>
</template>

<script>
    import {
        EventBus
    } from "./event-bus.js";
    import Recipe from './Recipe.vue';
    import Swal from "sweetalert2";
    import Axios from "axios";
    export default {

        components: {

            Recipe,

        },
        data() {
            return {

                desserts: [

                ],


                paymentsCount: 1,
                booking: false,
                cats: [],
                patientInfo: {},

                pageCount: 11,
                page: 1,
                allItem: false,
                RecipeInfo: {
                    case: {
                        case_categories: ""
                    }
                },
                Recipe: false,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),

                dialog: false,

                editedIndex: -1,


                recipes: [],
                // search: null,

                editedItem: {

                },


                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "patient.name"
                    },
                    {
                        text: 'نوع الحاله',
                        align: "start",
                        value: "case_categories.name_ar"
                    },






                    {
                        text: this.$t('datatable.date'),
                        align: "start",
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

            getpatient() {
                this.loading = true;
                Axios.get("patients/getByUserId", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.patients = res.data.data;
                    })
                    .catch(() => {

                    });
            },
            cropdate(x) {
                return x.slice(0, 10);
            },
            initialize() {
                this.loading = true;
                Axios.get("getrecipes", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {

                        this.desserts = res.data;
                        this.loading = false;

                    })

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



            editItem(item) {


                this.Recipe = true;

                console.log(item)
                this.RecipeInfo = {
                    "case": {
                        "patient_id": item.patient_id,
                        "case_categories": item.case_categores_id,
                    },
                    "sex": item.patient.sex,
                    "age": item.patient.age,
                    "notes": item.notes

                };


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

                        //  this.CaseCategories
                        this.CaseCategoriess = res.data;
                        this.CaseCategoriess = res.data;

                        this.CaseCategories.push({
                            id: 0,
                            name_ar: 'الكل',
                            name_en: '',
                            updated_at: '2022-02-02T12:20:30.000000Z'
                        })
                        for (var i = 0; i < this.CaseCategoriess.length; i++) {
                            this.CaseCategories.push({
                                id: this.CaseCategoriess[i].id,
                                name_ar: this.CaseCategoriess[i].name_ar,
                                name_en: '',
                                updated_at: this.CaseCategoriess[i].updated_at
                            })
                        }

                        console.log(this.CaseCategories);

                    })
                    .catch(() => {
                        this.loading = false;
                    });

            },


        },
   
        created() {
            this.initialize();
    this.getpatient();
            this.getCaseCategories();

            EventBus.$on("changeStatusCloseField", (from) => {

                from

                this.Recipe = false;
                this.initialize();
            });



        },

    }
</script>