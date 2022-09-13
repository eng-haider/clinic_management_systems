<template>

    <div>
        <v-container id="dashboard" fluid tag="section">
            <v-text-field class="mt-4" label="اكتب للبحث" outlined append-icon="mdi-magnify" v-model="search">
            </v-text-field>

            <v-data-table :headers="headers" :items="desserts" sort-by="phone" class="elevation-1">
                <template v-slot:top>
                    <v-toolbar flat>
                        <v-toolbar-title> {{ $t("Drawer.messages") }} </v-toolbar-title>
                        <v-divider class="mx-4" inset vertical></v-divider>
                        <v-spacer></v-spacer>
                        <v-dialog v-model="dialog" max-width="800px">
                            <!-- <template v-slot:activator="{ on, attrs }">
                                <v-btn color="primary" @click="editedIndex = -1 " dark class="mb-2" v-bind="attrs"
                                    v-on="on">
                                    {{ $t("add_new") }}
                                </v-btn>
                            </template> -->
                            <v-form ref="form" v-model="valid">
                                <v-card>

                                    <v-toolbar dark color="primary lighten-1 mb-5">
                                        <v-toolbar-title>
                                            <h3 style="color:#fff"> {{formTitle}}</h3>
                                        </v-toolbar-title>
                                        <v-spacer />
                                        <v-btn @click="close()" icon>
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </v-toolbar>

                                    <v-card-text>
                                        <v-container>

                                            <v-row>
                                                <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-text-field v-model="editedItem.translations[0].title"
                                                        :label="$t('datatable.title')+' '+$t('arbic')" outlined>
                                                    </v-text-field>
                                                </v-col>




                                                <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-text-field v-model="editedItem.translations[1].title"
                                                        :label="$t('datatable.title')+' '+$t('en')" outlined>
                                                    </v-text-field>
                                                </v-col>

                                                <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-select v-model="editedItem.articles_cats_id"
                                                        :label="$t('cats')" :items="cats" outlined item-text="name"
                                                        item-value="id"></v-select>
                                                </v-col>


                                            
                                                      <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-textarea v-model="editedItem.translations[0].description"
                                                        :label="$t('datatable.description')+' '+$t('arbic')" outlined>
                                                    </v-textarea>
                                                </v-col>



                                                <v-col class="py-0" cols="12" sm="12" md="12">
                                                    <v-textarea v-model="editedItem.translations[1].description"
                                                        :label="$t('datatable.description')+' '+$t('en')" outlined>
                                                    </v-textarea>
                                                </v-col>





    <v-col class="py-0" cols="6" sm="12" md="6"  v-if="editedIndex == -1">
                                                    <v-text-field label="أختار الصوره " @click='pickImg'
                                                        v-model='imageName' prepend-icon='fas fa-paperclip'></v-text-field>
                                                    <input type="file" style="display: none" ref="image"
                                                        accept="image/*" @change="onFilePicked">

                                                </v-col>


                                                <v-col class="py-0" cols="6" sm="6" md="6">
                                                    <v-img :src="imageUrl" height="150" :lazy-src="thumb_small"
                                                        v-if="imageUrl" />
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
                </template>

                <!-- <template v-slot:[`item.actions`]="{ item }">





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
                        <span>{{$t('users.Deactivate')}}</span>
                    </v-tooltip>



                </template> -->
                <template v-slot:no-data>
                    <v-btn color="primary" @click="initialize">{{ $t("Reloading") }}</v-btn>
                </template>
            </v-data-table>
        </v-container>
    </div>
</template>

<script>
    import Axios from "axios";
      import Swal from "sweetalert2";
    export default {
        data() {
            return {
                desserts: [

                ],
                cats: [],
                dialog: false,
                loadSave: false,
                         imagesUrl: '',
                imageName: '',
                imageUrl:'',
                img_old: '',
                imageFile: '',
               
                imagesName: ' ',
                editedIndex: -1,
                editedItem: {
                    translations: [{
                            title: '',
                            description:""
                        },
                        {
                            title: '',
                            description:""
                        }
                    ],
                    id: "",
                    title: "",

                    description: "",

                    articles_cats_id: ""

                },
                items: [

                ],
                headers: [{
                        text: this.$t('datatable.name'),
                        align: "start",
                        value: "name"
                    }, {
                        text: this.$t('datatable.email'),
                        value: "email"
                    },
                    {
                        text: this.$t('datatable.message'),
                        value: "message"
                    },
                    //  {
                    //     text: this.$t('phone'),
                    //     value: "phone"
                    // },



                    // {
                    //     text: this.$t('Processes'),
                    //     value: "actions",
                    //     sortable: false
                    // }
                ],
                right: null
            }
        },

        methods: {

            editItem(item) {
                this.editedIndex = this.desserts.indexOf(item);
                this.editedItem = Object.assign({}, item);
                this.dialog = true;

                //   if (this.imageUrl[0] == null) {

                    this.img_old = this.editedItem.img_file;
                    this.imageUrl = 'http://127.0.0.1:8000/art_img/thumb/' + this.img_old;
                 ///   this.thumb_small = 'https://alkafeel.net/women_library/photo/thumb_small/' + this.img_old;

              //  }
            },
            close() {
                this.dialog = false;
                // this.editedItem ={};
                this.editedItem = {
                    translations: [{
                            title: '',
                            description: ''
                        },
                        {
                            title: '',
                            description: ''
                        }
                    ],
                    id: "",
                    title: "",

                    description: "",

                    photo_gallery_cats_id: ""

                };
                // this.$nextTick(() => {
                //     this.editedItem = Object.assign({}, this.defaultItem);
                //     this.editedIndex = -1;
                // });
            },
            initialize() {
                this.loading = true;
                Axios.get("messsages", {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " +this.$store.state.AdminInfo.token
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.desserts = res.data.data;
                        //  console.log(res.data);
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },


 pickImg() {
                this.$refs.image.click()
            },


            pickFiles() {
                this.$refs.images.click()

            },


            onFilePicked(e) {


                const files = e.target.files


                if (files[0] !== undefined) {



                    this.imageName = files[0].name


                    if (this.imageName.lastIndexOf('.') <= 0) {
                        return
                    }


                    const fr = new FileReader()
                    fr.readAsDataURL(files[0])
                    fr.addEventListener('load', () => {

                        this.imageUrl = fr.result
                        this.imageFile = files[0]


                    })
                } else {
                    this.imageName = ''
                    this.imageFile = ''
                    this.imageUrl = ''


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
          confirmButtonText:  this.$t('yes'),
          cancelButtonText: this.$t('no'),
        }).then(result => {
          if (result.value) {
            Axios.delete("articles/" + item.id, {
                headers: {
                  "Content-Type": "application/json",
                  Accept: "application/json",
                  Authorization: "Bearer " +this.$store.state.AdminInfo.token
                }
              })
              .then(() => {
                this.$swal.fire(this.$t('Successfully'), this.$t('done'), "success");
                this.initialize();
              })
              .catch(() => {
                this.$swal.fire( this.$t('not_successful'), this.$t('not_done'), "error");
              });
          }
        });
      },


          

           

        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? this.$t('add_new') : this.$t('update');

            },
        },
        created() {
            this.initialize();
         //   this.getCats();
        },

    }
</script>