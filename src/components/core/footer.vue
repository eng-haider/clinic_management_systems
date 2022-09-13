<template>
    <div>
        <div id="footer" class="footer-section footer-overlay-actived footer-style-default footer-skin-dark">
            <div id="footer-top-area" class="footer-top-area">
                <div class="container">
                    <div id="text-1" class="widget widget_text">
                        <div class="textwidget">
                            <div class="row widget-button"
                                v-bind:class="[{'row_ar' : $i18n.locale=='ar' },{'row_en':$i18n.locale=='en'}]">



                                <div class="col-xs-12 col-sm-6 col-md-6 footer-logo"
                                    style="font-family: 'Cairo' !important;">



                                    <div
                                        v-bind:class="[{'foo_row_ar' : $i18n.locale=='ar' },{'foo_row_en':$i18n.locale=='en'}]">
                                        <div id="zozo-logo" style="    padding-top: 27px;"
                                            class="navbar-header nav-respons zozo-logo logo-left zozo-has-sticky-logo has-img">
                                            <h5 style="font-family: 'Cairo' !important;color:#fff">{{ $t("web_name") }}
                                            </h5>


                                        </div>


                                        <div  id="zozo-logo"
                                            class="d-none d-sm-flex xxxx navbar-header nav-respons zozo-logo logo-left zozo-has-sticky-logo has-img">
                                            <router-link to="/" class="navbar-brand">
                                                <img  class="d-none d-sm-flex img-responsive zozo-standard-logo" src="https://abnakr.org/logo.png"
                                                    alt="Charity / Nonprofit / Fundraising WordPress Theme" width="155"
                                                    style="max-height: 76px !important" height="70"> <img
                                                    class="img-responsive zozo-retina-logo" src="https://abnakr.org/logo.png"
                                                    alt="Charity / Nonprofit / Fundraising WordPress Theme" height="35"
                                                    width="77">
                                                <img class="d-none d-sm-flex img-responsive zozo-sticky-logo" src="https://abnakr.org/logo.png"
                                                    alt="Charity / Nonprofit / Fundraising WordPress Theme" width="155"
                                                    height="70">

                                            </router-link>

                                        </div>
                                    </div>





                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                        <!-- <div class="footer-button">
                                            <h4 class="typo-light" style="font-family: 'Cairo' !important;">نحن نحتاج
                                                مساعدتك</h4> <span
                                                    class="btn secondary-bg"
                                                    style="font-family: 'Cairo' !important;">تبرع الان
                                                </span> 
                                        </div> -->
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="footer-widgets-container" class="footer-widgets-section">
                <div class="container">
                    <div class="zozo-row row"
                        v-bind:class="[{'row_ar' : $i18n.locale=='ar' },{'row_en':$i18n.locale=='en'}]">


                        <div id="footer-widgets-1" class="footer-widgets col-md-3 col-sm-6 col-xs-12">
                            <div id="zozo_mailchimp_form_widget-widget-1" class="widget zozo_mailchimp_form_widget">
                                <h3 class="widget-title" style="display:block;font-family: 'Cairo' !important;">
                                    {{ $t("contact") }}</h3>

                                <div id="mc-subscribe-widget1"
                                    class="zozo-mc-form subscribe-form mailchimp-form-wrapper">
                                    <p class="mailchimp-msg zozo-form-success"></p>
                                    <form action="/charity/contact/#wpcf7-f803-p789-o1" method="post"
                                        class="wpcf7-form init" novalidate="novalidate" data-status="init">




                                        <v-layout row wrap>
                                            <v-flex xs12 class="wpcf7-form-control-wrap tel-265">
                                                <v-text-field :placeholder="$t('name')" v-model="editedItem.name"
                                                    style="width: 100%;" :rules="nameRules" outlined dense
                                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                                </v-text-field>
                                            </v-flex>


                                            <v-flex xs12 class="wpcf7-form-control-wrap tel-265">
                                                <v-text-field :placeholder="$t('email')" v-model="editedItem.email"
                                                    type="email" style="width: 100%;" :rules="nameRules" height="30"
                                                    outlined dense
                                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                                </v-text-field>
                                            </v-flex>


                                            <v-flex xs12>
                                                <v-textarea outlined :label="$t('datatable.message')" :rules="comRules"
                                                    style="width:100%" v-model="editedItem.message"
                                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                                </v-textarea>
                                            </v-flex>

                                            <v-flex xs12>
                                                <v-btn
                                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]"
                                                    depressed color="green" @click="submit()">
                                                    {{  $t('send')}}
                                                </v-btn>
                                            </v-flex>


                                        </v-layout>





                                    </form>
                                </div>
                            </div>
                        </div>


                        <div id="footer-widgets-2" class="footer-widgets col-md-4 col-sm-6 col-xs-12"
                            v-bind:class="[{'paddd_Ar xxxxxx' : $i18n.locale=='ar' },{'paddd_en':$i18n.locale=='en'}]">
                            <div id="nav_menu-1" class="widget widget_nav_menu">
                                <h3 class="widget-title"
                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                    <i class="fas fa-map-marker"></i>

                                    <span> {{ $t("address") }} </span> : <p
                                        v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                        {{ $t("loc_address") }}</p>
                                </h3>




                                <h3 class="widget-title" style="width: 100%;"
                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                    <i class="fas fa-phone"></i>

                                    <span> {{ $t("phone") }} : </span>
                                    <p>+9647724100065</p>
                                </h3>



                                <h3 class="widget-title"
                                    v-bind:class="[{'text_Ar' : $i18n.locale=='ar' },{'text_en':$i18n.locale=='en'}]">
                                    <i class="fa fa-envelope"></i>

                                    <span> {{ $t("email") }} : </span>
                                    <p>abnkarbala165@gmail.com</p>
                                </h3>


                            </div>
                        </div>





                        <div id="footer-widgets-3" class="footer-widgets col-md-2 col-sm-6 col-xs-12">
                            <div id="nav_menu-2" class="widget widget_nav_menu">
                                <h3 class="widget-title" style="font-family: 'Cairo' !important;">{{ $t('web_pages') }}
                                </h3>
                                <div class="menu-footer-menu-3-container">
                                    <ul id="menu-footer-menu-3" class="menu">
                                        <li id="menu-item-1183" v-for="item in items" :key="item"
                                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1183">
                                            <router-link :to="item.to">{{ $t('header.'+item.title) }}</router-link>

                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>



                        <div id="footer-widgets-4" class="footer-widgets col-md-3 col-sm-6 col-xs-12">
                            <div id="zozo_social_link_widget-widget-2" class="widget zozo_social_link_widget">
                                <h3 class="widget-title" style="font-family: 'Cairo' !important;">
                                    {{$t('flow_me') }}

                                </h3>
                                <ul class="zozo-social-icons soc-icon-rounded soc-icon-size-normal ">
                                    <li class="facebook"><a target="_blank"
                                            href="https://www.facebook.com/Sse3132">
                                            <i style="font-size: 23px;margin: 2px;" class="fab fa-facebook fab-lg"></i>

                                        </a></li>
                                    <li class="twitter"><a target="_blank" href=""><i
                                                class="fab fa-twitter fab-lg"
                                                style="font-size: 20px;margin: 3px;"></i></a>
                                    </li>
                                    <li class="linkedin" style="font-size: 20px;margin: 3px;"><a target="_blank"
                                            href="">
                                            <i class="fab fa-linkedin fab-lg"
                                                style="font-size: 23px;margin: 2px;"></i></a></li>
                                    <li class="instagram" style="font-size: 20px;margin:2px;"><a target="_blank"
                                            href="">
                                            <i style="font-size: 20px;margin:3px;" class="fab fa-instagram"></i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>







                    </div>
                </div>
            </div>
            <div id="footer-copyright-container" class="footer-copyright-section">
                <div class="container">
                    <div class="zozo-row row">
                        <div id="copyright-text" class="col-sm-6 col-xs-12 footer-copyright-left">
                            <p>© Copyright 2021. All Rights Reserved. Designed by Haider ALtemimy</p>
                        </div>
                        <div id="footer-nav" class="zozo-nav footer-menu-navigation">
                            <ul id="footer-menu" class="nav navbar-nav zozo-footer-nav">
                                <li id="menu-item-1189" v-for="item in items" :key="item"
                                    class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-789 current_page_item menu-item-1189 active">
                              <router-link  :to="item.to">
  {{ $t('header.'+item.title) }}
                              </router-link>
                                        
                                      
                                        
                                        
                                       </li>

                            </ul>
                        </div>
                        <div class="footer-menu-wrapper col-sm-6 col-xs-12 footer-menu-left">
                            <div class="hidden-xs"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>







<style>

   @media only screen and (max-width: 768px) {
     
.xxxx
{
    display: none;
}

  .paddd_Ar {
        padding-right:10px !important;
        


    }

 }
    .foo_row_en {
        float: left;
        direction: lrt;
    }

    .foo_row_ar {
        float: right;
        direction: rtl;
    }

    .paddd_Ar {
        padding-right: 49px ;


    }

    .padd_en {
        padding-left: 49px !important;


    }

    .theme--light.v-input,
    .theme--light.v-input input,
    .theme--light.v-input textarea {
        color: #fff !important;
        font-family: 'Cairo' !important;
    }
</style>
<script>
    import Axios from "axios";
    import {
        EventBus
    } from '../../components/core/event-bus.js';

    export default {

        data() {
            return {
                editedItem: {},
                items: [{
                        title: 'home',
                        to: "/"
                    },
                    {
                        title: 'About_Us',
                        to: "/about"
                    },
                    {
                        title: 'Blog',
                        to: "/blog"
                    },
                    {
                        title: 'Gallery',
                        to: "/Gallery"
                    },
                    {
                        title: 'Contact',
                        to: "/contact"
                    },



                ],
                validate: false,
                nameRules: [
                    (v) => !!v || 'يجب ادخال الاسم',

                ],
                comRules: [
                    (v) => !!v || 'يجب ادخال الرساله',

                ],
                emailRules: [


                    v => !v || /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'الايميل غير صحيح'
                ],


            }
        },
        methods: {
            submit() {

                // if (this.$refs.form.validate()) {


                Axios.post('/messsages', {

                    name: this.editedItem.name,
                    phone: this.editedItem.phone,
                    ///      email: this.editedItem.email,
                    message: this.editedItem.message,


                }).then(() => {
                    this.editedItem = {}
                    this.dialog = false;
                    this.$swal.fire({
                        title: "تم الارسال",
                        text: "",
                        icon: "success",
                        confirmButtonText: "اغلاق",
                    });

                    this.close();

                }).catch(() => {


                });


                // }


            },
            getByCatIdFirst(id) {
                this.loading = true;
                Axios.get("/aboutAs/getByCatId/" + id, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            lang: this.$i18n.locale,
                            Authorization: "Bearer " + this.$store.state.idToken
                        }
                    })
                    .then(res => {
                        this.loading = false;
                        this.about_first = res.data.data;

                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },
        },


        created() {
            EventBus.$on("changeLangs", (from) => {
                from
                this.getByCatIdFirst(1)


            });
            this.getByCatIdFirst(1)

        },

    }
</script>