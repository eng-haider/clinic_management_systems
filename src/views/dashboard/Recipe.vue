<template>
    <div>
      <div ref="printSection" class="print-section" style="display: none;">
        <recipeReport :RecipeInfo="RecipeInfo" />
      </div>

  
      <v-form ref="form" v-model="valid">
        <v-card>
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on">
              Click Me
            </v-btn>
          </template>
  
          <v-toolbar dark color="primary lighten-1 mb-5">
            <v-toolbar-title>
              <h3 style="color:#fff;font-family: 'Cairo'"> كتابه الوصفه الطبيه</h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="close" icon>
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>
  
          <v-container>
            <v-card flat>
              <div style="width:100%; height: 110px;">
                <img src="/rx_header.jpeg" style="width:100%;height:100%" />
              </div>
  
              <v-layout row wrap style="padding: 20px 0;">
                <v-flex xs12 md3></v-flex>
                <v-flex xs6 md3>
                  <p class="text-center" style="font-size:28px;font-family: 'GE_Dinar';">
                    {{ $store.state.AdminInfo.clinics_info.name }}
                  </p>
                  <p class="text-center" style="font-size:28px;font-family: 'GE_Dinar'; position:relative; bottom:21px;">
                    لطب الاسنان
                  </p>
                </v-flex>
                <v-flex xs6 md3>
                  <div class="rexipe_logo">
                    <img :src="`${Url}/users/${$store.state.AdminInfo.img_name}`" style="height:100%" />
                  </div>
                </v-flex>
                <v-flex xs12 md3></v-flex>
              </v-layout>
  
              <v-layout row wrap>
                <v-flex xs12 md3 mr-4>
                  <div>الاسم :</div>
                  <v-select
                    dense
                    :rules="[rules.required]"
                    v-model="RecipeInfo.case.patient_id"
                    :label="$t('datatable.name')"
                    @change="getItem(RecipeInfo.case.patient_id)"
                    :items="patients"
                    outlined
                    return-object
                    item-text="name"
                    item-value="id"
                  />
                </v-flex>
  
                <v-flex xs12 md3 mr-4>
                  <div>الجنس :</div>
                  <v-select
                    dense
                  
                    v-model="RecipeInfo.sex"
                    :items="sexs"
                    outlined
                    item-text="name"
                    item-value="id"
                  />
                </v-flex>
  
                <v-flex xs12 md2 mr-4>
                  <div>العمر :</div>
                  <v-text-field
                    dense
                    v-model="RecipeInfo.age"
                    outlined
                    :rules="[rules.required]"
                    required
                  />
                </v-flex>
  
                <v-flex xs12 md3 mr-4>
                  <div>الحاله :</div>
                  <v-select
                    dense
                    :rules="[rules.required]"
                    v-model="RecipeInfo.case.case_categories"
                    :items="CaseCategories"
                    outlined
                    return-object
                    item-text="name_ar"
                    item-value="id"
                  />
                </v-flex>
              </v-layout>
  
              <v-layout row wrap>
                <v-flex xs12>
                  <v-textarea
                    outlined
                    v-model="RecipeInfo.notes"
                    label="الوصفه"
                  />
                </v-flex>
              </v-layout>
            </v-card>

  
          </v-container>
  
          <v-divider></v-divider>
  
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="#2196f3" @click="print" style="color:#fff">
              طبـــــاعه
              <v-icon right dark>fas fa-print</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </div>
  </template>
  
  <script>
  import recipeReport from "./recipeReport.vue";
  import { EventBus } from "./event-bus.js";
  
  export default {
    data() {
      return {
        valid: false,
        rules: {
          required: (value) => !!value || "مطلوب",
          minPhon: (v) => v.length === 13 || "رقم الهاتف يجب ان يتكون من 11 رقم",
          email: (value) => {
            if (value && value.length > 0) {
              const pattern =
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
              return pattern.test(value) || "يجب ان يكون ايميل صحيح";
            }
          },
        },
        sexs: [
          { id: 1, name: "ذكر" },
          { id: 0, name: "انثئ" },
        ],
      };
    },
    props: {
      RecipeInfo: Object,
      CaseCategories: Array,
      recipes: Array,
      patients: Array,
    },
    components: {
      recipeReport,
    },
    methods: {
        printContent() {
      const printContent = this.$refs.printSection;
      const originalContent = document.body.innerHTML;

      // Temporarily replace the body content with print content
      document.body.innerHTML = printContent.innerHTML;
      window.print();

      // Restore original content
      document.body.innerHTML = originalContent;
      window.location.reload(); // Reload the page to restore event bindings
    },
      getItem(id) {
        const patient = this.patients.find((p) => p.id === id);
        if (patient) {
          this.RecipeInfo = {
            case: {
              patient_id: patient.id,
              case_categories: patient.case_categores_id,
            },
            sex: patient.sex,
            age: patient.age,
            notes: "",
          };
        }
      },
      close() {
        this.RecipeInfo = {
          case: {
            patient_id: "",
            case_categories: "",
          },
          sex: "",
          age: "",
          notes: "",
        };
        EventBus.$emit("changeStatusCloseField", false);
      },
      print() {
        if (this.$refs.form.validate()) {
          const send = {
            case_categores_id: this.RecipeInfo.case.case_categories.id,
            notes: this.RecipeInfo.notes,
            patient_id: this.RecipeInfo.case.patient_id.id,
          };
  
          this.axios
            .post("recipes", send, {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.state.AdminInfo.token}`,
              },
            })
            .then(() => {
              
              this.$swal.fire({
                    position: "top-end",
  icon: "success",
  title: this.$t("Added"),
  showConfirmButton: false,
  timer: 1500
              });
              
                this.printContent()
        
              this.close();
            })
            .catch((err) => {
              console.error(err);
            });
          
        
        }
      },
    },
  };
  </script>
  
  <style>
  .rexipe_logo {
    height: 114px;
    margin-bottom: 27px;
    margin-right: 71px;
  }
  </style>
  