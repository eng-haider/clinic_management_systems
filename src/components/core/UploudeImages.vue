<template>
    <div>
        <v-container fluid>
            <v-card class=" mb-12" flat style="min-height: 586
px
;">




                <v-row row wrap>

                    <v-col class="py-0" cols="6" sm="12" md="6" v-if="editedIndex == -1">
                        <v-text-field label="أختر الصور المرفقه للمقال " @click='pickImg()' v-model='imageName'
                            prepend-icon='fas fa-paperclip'></v-text-field>

                        <v-btn color="green" style="color:#fff" v-if="imageUrl !== ''" @click='UploudeImg()'>رفع الصوره
                        </v-btn>
                        <input type="file" style="display: none" ref="image" accept="image/*" @change="onFilePicked">

                    </v-col>
                    <v-col class="py-0" cols="6" sm="6" md="6">
                        <v-img :src="imageUrl" height="150" :lazy-src="thumb_small" v-if="imageUrl" />
                    </v-col>




                </v-row>



                <br>
                <br>
                <br>
                <br>


                <v-row row wrap>

                    <v-flex xs6 md3 sm3 pt-5 v-for="(item ,index) in images.slice(1, 112)" :key="index" pr-2>
                        <v-card height="190" width="190" class="card_img">

                            <v-img 
                            
                                                        :src="Url+'/art_img/thumb_new/'+item.image_name"
                                                                 :lazy-src="Url+'/art_img/small_thumb/'+item.image_name"
                            
                             height="190" width="190"
                                class="card_img">
                    




                                <v-btn icon="" @click="delete_img(item.id,index)">
                                    <v-icon color="#fff">fas fa-window-close</v-icon>
                                </v-btn>




                            </v-img>

                        </v-card>
                    </v-flex>

                </v-row>



            </v-card>

        </v-container>
    </div>
</template>


<script>
    const swal = require('sweetalert2')
    const axios = require('axios');

    import Axios from "axios";
    export default {

        props: {

            'art': Object,
            'images': Array


        },

        data() {
            return {
                // images:this.art.images,
                show: false,
                imagesUrl: '',
                imageName: '',
                editedIndex: -1,
                imageUrl: '',
                img_old: '',
                first: false,
                imageFile: '',



            }
        },

        mounted() {


        },

        created() {

        },

        methods: {



            UploudeImg() {

                // if (this.imageFile != null) {

                let formData = new FormData();
                const AuthStr = 'Bearer ' + this.token;
                formData.append('photo', this.imageFile);

                Axios.post('articles/uploude/' + this.art.id,
                        formData, {
                            headers: {
                                Authorization: AuthStr,
                                'Content-Type': 'multipart/form-data',
                            }
                        }
                    )

                    .then(() => {
                        this.getitem()

                    })


                this.img_old = ''
                this.imageName = ''
                this.imageFile = ''
                this.imageUrl = ''
                this.initialize();
                // }
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





            getitem() {
                Axios.get('/articles/' + this.art.id, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            lang: this.$i18n.locale,
                            Authorization: "Bearer " + this.$store.state.idToken
                        }
                    })

                    .then(response => {
                        this.images = response.data.data[0].images;

                    });


            },

            delete_img(img_id, index) {



                const Swal = require('sweetalert2');



                Swal.fire({
                    title: this.$t('sure_process'),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: this.$t('yes'),
                    cancelButtonText: this.$t('close'),

                }).then((result) => {
                    if (result.value) {



                        var url = "/images/" + img_id;
                        axios({
                            method: 'delete',
                            url: url,
                           headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                                Authorization: "Bearer " +this.$store.state.AdminInfo.token,
                            },

                        }).then(response => {
                            if (index == 0) {
                                this.first = true,
                                    this.pickImg();
                            }
                            response,
                            this.getitem();
                            swal.fire(
                                this.$t('Successfully'),
                                '',
                                'success'
                            )

                        }).catch(error => {
                            error
                        }).finally(d => {
                            d


                        });



                    }
                })




            },












        }


    }
</script>