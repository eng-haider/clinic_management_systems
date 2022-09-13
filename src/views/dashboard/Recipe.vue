<template>
    <div>

        <div id="printMex" style="display:none">
            <recipeReport :RecipeInfo="RecipeInfo"></recipeReport>
        </div>

        <v-card>
            <template v-slot:activator="{ on, attrs }">
                <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on">
                    Click Me
                </v-btn>
            </template>
            <!-- 
            <v-card-title class="text-h5 grey lighten-2" style="font-family: 'Cairo' !important;    height: 55px;">
                كتابه الوصفه الطبيه
            </v-card-title> -->


            <v-toolbar dark color="primary lighten-1 mb-5">
                <v-toolbar-title>
                    <h3 style="color:#fff;font-family: 'Cairo'"> كتابه الوصفه الطبيه</h3>
                </v-toolbar-title>
                <v-spacer />
                <v-btn @click="close()" icon>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>
            <v-container>
                <v-card flat style="
                    position: relative;
    bottom: 32px;">
                    <div style="width:100%;    height: 110px;">
                        <img src="/rx_header.jpeg" style="width:100%;height:100%" />
                    </div>


                    <v-layout row wrap style="padding-top: 20px;;padding-bottom:20px">
                  <v-flex xs3></v-flex>

               <v-flex xs3>

                    <p style="    text-align: center;font-size:37px;
    font-family: 'GE_Dinar';">عيادة انفنتي </p>
                    <p
                    style="    text-align: center;font-size:37px;

                    position: relative;
    bottom: 21px;

    font-family: 'GE_Dinar';"
                    >لطب الاسنان</p>



               </v-flex>


              <v-flex xs3>
                    <div class="rexipe_logo" style="    height: 114px;
        margin-bottom: 27px;
            margin-right: 10px;">
                        <img src="/clini.png" style="height:100%" />
                    </div>
              </v-flex>



               <v-flex xs3></v-flex>

            </v-layout>

                    <!-- <v-layout row wrap>
                        <v-flex xs5 style="padding-top: 19px;">
                            <h3 style="    text-align: center;
    font-family: 'Cairo';">عيادة انفنتي لطب الاسنان</h3>
                            <p style="    text-align: center;padding-top: 5px;
    font-family: 'Cairo';">كربﻻء المقدسة - شارع الإسكان - مقابل مستشفى الشيخ الوائلي</p>

                        </v-flex>
                        <v-flex xs4></v-flex>
                        <v-flex xs3>
                            <div class="rexipe_logo">
                                <img src="/logo.png" style="height:100%" />
                            </div>
                        </v-flex>

                    </v-layout> -->

                    <v-layout row wrap>

                        <!-- <v-flex xs1>الاسم :</v-flex> -->
                        <v-flex xs12 md3 sm3 mr-4>
                            <div>الاسم :</div>
                            <v-text-field dense v-model="RecipeInfo.name" outlined>
                            </v-text-field>
                        </v-flex>





                        <v-flex xs12 md3 sm3 mr-4>
                            الجنس :


                            <v-select dense v-model="RecipeInfo.sex" return-object :items="sexs" outlined
                                item-text="name" item-value="id"></v-select>
                        </v-flex>






                        <v-flex xs12 md2 sm2 mr-4>
                            العمر :

                            <v-text-field dense v-model="RecipeInfo.age" outlined>
                            </v-text-field>
                        </v-flex>


                        <v-flex xs12 md3 sm3 mr-4>
                            الحاله :

                            <v-select dense v-model="RecipeInfo.case.case_categories" return-object
                                :items="CaseCategories" outlined item-text="name_ar" item-value="id"></v-select>
                        </v-flex>




                    </v-layout>

                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-textarea outlined v-model="RecipeInfo.rx" name="input-7-4" label="الوصفه" value="">
                            </v-textarea>
                        </v-flex>
                    </v-layout>


                    <!-- <v-layout row wrap>
                        <v-flex xs2>توقيع الدكتور :</v-flex>
                        <v-flex x4 md2 sm2>

                            <v-text-field dense v-model="RecipeInfo.user.name" outlined>
                            </v-text-field>
                        </v-flex>
                    </v-layout> -->


                </v-card>
            </v-container>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <!-- <v-btn
            color="primary"
            
            @click="dialog = false"
          >
            طبـــــاعه
          </v-btn> -->

                <v-btn color="#2196f3" @click="print()" style="color:#fff"> طبـــــاعه


                    <v-icon right dark>
                        fas fa-print
                    </v-icon>
                </v-btn>
            </v-card-actions>
        </v-card>

    </div>
</template>

<style>
    .rexipe_logo {
        height: 114px;
        margin-bottom: 27px;
        margin-right: 71px;
    }

    .parent {
        position: relative;
        float: left;
        width: 91.5%;
        min-width: 825px;
        height: 120px;
        text-align: left;
        z-index: 1;
        border: solid 1px #19365D;

        background-repeat: repeat-x;
        margin-left: 3.7%;
        margin-right: 3.7%;
        padding-left: 0.5%;
        padding-right: 0.5%;
    }

    .link {
        position: absolute;
        width: 100%;
        bottom: 0;
        text-align: center;
    }
</style>
<script>
    import recipeReport from './recipeReport.vue';
    import {
        EventBus
    } from "./event-bus.js";

    export default {
        data() {
            return {


                sexs: [{
                        id: 1,
                        name: 'ذكر'
                    },
                    {
                        id: 0,
                        name: 'انثئ'
                    }

                ]
            }
        },
        inheritAttrs: false,
//patientInfo
        props: {
            RecipeInfo: Object,
            CaseCategories: Array
        },
        components: {
            recipeReport
        },
        methods: {
            close() {

                this.RecipeInfo = {
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
                //  this.RecipeInfo = {},
                EventBus.$emit("changeStatusCloseField", false);
            },
            print() {


                this.$htmlToPaper('printMex');
            },

        }
    }
</script>