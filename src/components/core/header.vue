<template>

    <div>



        <v-navigation-drawer v-model="drawer" absolute bottom temporary>
            <v-list nav dense>
                <v-list-item-group v-model="group" active-class="deep-purple--text text--accent-4">
                    <v-list-item v-for="item in items" :key="item">
                        <v-list-item-title style="text-align: center;
    font-size: 15px;">
                            <router-link  :to="{ name: item.name}" style="color:#000"> {{item.title}}
                            </router-link>



                        </v-list-item-title>
                    </v-list-item>


                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>




        <div id="zozo_wrapper" class="wrapper-class zozo-main-wrapper">
     <v-app> 

            <div id="mobile-header" class="mobile-header-section hidden-lg-and-up">
                <div>
                    <v-layout class="hidden-lg-and-up" row wrap>

                        <v-toolbar flat app height="90" style="direction:rtl;background-color: #000;">






                            <v-flex x1>
                             
                            <div class="text-center">
    <v-menu offset-y>
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          color="#fff"
          dark
          style="color:#fff"
          icon
          v-bind="attrs"
          v-on="on"
        >
         <i class="fas fa-bars fa-lg"></i>
        </v-btn>
      </template>
      <v-list>
        <v-list-item
          v-for="item in items" :key="item"
        >
          <v-list-item-title>
                <router-link  :to="{ name: item.name}" style="color:#000"> {{item.title}}
                            </router-link>


          </v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </div>



                            </v-flex>



                            <!-- <v-layout row wrap> -->

                            <v-flex xs2>
                                <v-img src="http://abnakr.org/logo.png"></v-img>
                            </v-flex>
                            <v-flex xs6 style="padding-top:5px" sm2>
                                <router-link to="/">

                                    <h5 style="font-family: 'Cairo' !important;color:#fff;text-align:center">
                                        {{ $t("web_name") }}</h5>



                                </router-link>
                            </v-flex>


                            <v-flex xs2>
                                <div class="main-bar-item item-text"><a class="btn "
                                        style="color: #fff;padding:0px !important;padding: 3px !important;  float: left;"
                                        @click="langChang()">{{ $t("lang") }}</a>
                                </div>


                            </v-flex>

                    



                        </v-toolbar>
                    </v-layout>


                </div>
            </div>
     </v-app>

            <div id="header"
                class="header-section type-header-1 header-menu-skin-light header-layout-fullwidth header-skin-dark header-no-transparent header-dropdown-skin-light">
                <div id="header-top-bar" class="header-top-section" style="    height:55px ">
                    <div class="container" style="position: relative;bottom:18px">

                        <div class="row">
                            <div class="col-sm-6 zozo-top-left">
                                <div class="top-bar-item item-contact-info">
                                    <div id="header-contact-info" class="top-contact-info">
                                        <ul class="header-contact-details">

                                            <li><i class="fas fa-phone" style="margin-right:1px"></i> +964 0772 410 0065
                                            </li>


                                            <li><i class="far fa-envelope"></i> <a
                                                    href="mailto:info@yoursite.com">info@abnakr.org</a></li>


                                            <li>
                                                <a
                                                    href="https://www.facebook.com/Sse3132" target="_blank">
                                                    <i class="fab fa-facebook"> </i>
                                                    
                                                    </a>
                                                
                                                </li>



                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{changeLang()}}
                            <div class="col-sm-6 zozo-top-right">
                                <div class="top-bar-item item-social">
                                    <div id="header-social-links" class="header-social">
                                        <span style="color:#fff">{{ $t("You_donate_via_bank_account") }}
                                            866209
                                            {{ $t("bank") }} </span>
                                      
                                    </div>

                                </div>
                            </div>
                         
                        </div>
                    </div>
                </div>

                <div id="mobile-search-wrapper" class="mobile-search-wrapper mobile-overlay-search"> <a href="#"
                        target="_self" class="mobile-search-close"><i class="flaticon flaticon-shapes"></i></a>
                    <div class="mobile-search-inner">
                        <form method="get" action="http://themes.zozothemes.com/charity/" class="search-form"><input
                                type="text" value="" name="s" class="form-control"
                                placeholder="Enter text to search" /><button class="btn btn-search" type="submit"><i
                                    class="simple-icon icon-magnifier"></i></button></form>
                    </div>
                </div>
                <div id="header-main-sticky-wrapper" class="header-sticky" style="height: auto; min-height: 60px;">
                    <div id="header-main" class="header-main-section navbar" style="">
                        <div class="container">


                            <div id="zozo-logo"
                                class="navbar-header nav-respons zozo-logo logo-left zozo-has-sticky-logo has-img">
                                <router-link to="/" class="navbar-brand">
                                    <img class="img-responsive zozo-standard-logo" src="http://abnakr.org/logo.png"
                                        alt="Charity / Nonprofit / Fundraising WordPress Theme" width="155"
                                        style="max-height: 76px !important" height="70"> <img
                                        class="img-responsive zozo-retina-logo" src="http://abnakr.org/logo.png"
                                        alt="Charity / Nonprofit / Fundraising WordPress Theme" height="35" width="77">
                                    <img class="img-responsive zozo-sticky-logo" src="http://abnakr.org/logo.png"
                                        alt="Charity / Nonprofit / Fundraising WordPress Theme" width="155" height="70">

                                </router-link>

                            </div>


                            <div id="zozo-logo" style="    padding-top: 27px;"
                                class="navbar-header nav-respons zozo-logo logo-left zozo-has-sticky-logo has-img">
                                <h5 style="font-family: 'Cairo' !important;">{{ $t("web_name") }}</h5>


                            </div>








                            <!-- <div id="zozo-logo">موسسه</div> -->


                            <div class="zozo-header-main-bar">
                                <ul class="nav navbar-nav navbar-right zozo-main-bar">
                                    <li>
                                        <div class="main-bar-item item-main-menu">
                                            <div class="main-navigation-wrapper">
                                                <div id="main-nav-container" class="main-nav main-menu-container">
                                                    <ul id="main-menu" class="nav navbar-nav navbar-main zozo-main-nav">


                                                        <li v-for="item in items" :key="item"
                                                            v-bind:class="[
                                                           
                                                              {'active' : item.name==$route.name },
                                                            {' menu-item_ar menu-item  menu-item-type-post_type menu-item-object-page menu-item-1091' : $i18n.locale=='ar' },{'menu-item_en menu-item  menu-item-type-post_type menu-item-object-page menu-item-1091':$i18n.locale=='en'}]">
                                                            <router-link :to="item.to" class="external-link"
                                                                data-title="Blog" title="Blog">{{item.title}}
                                                            </router-link>
                                                        </li>




                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="main-bar-item item-text"><a class="btn secondary-bg"
                                                @click="langChang()">{{ $t("lang") }}</a>
                                        </div>


                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>






            <div id="section-top" class="zozo-top-anchor"></div>


        </div>
    </div>

</template>

<style>

.v-application--wrap
{
  min-height: 0vh !important;
 
}
    .v-navigation-drawer--bottom.v-navigation-drawer--is-mobile {
        bottom: 45%;
        z-index: 2222;

    }

    @media (max-width: 767px) {
        .page-title-breadcrumbs {
            display: none !important;

        }
    }

    .grid-text-right {
        text-align: right !important;
    }

    .grid-text-right {
        text-align: left !important;
    }

    @media (min-width: 1904px) {
        .container {
            max-width: 1285px !important;
        }
    }

    .parallax-title.text-left:before {
        left: auto !important;
        right: 0px !important;
    }

    .parallax-title:before {
        left: 0px !important;
        right: 0px !important;

    }

    .row_ar {
        direction: rtl !important;
    }

    .row_en {
        direction: ltr !important;
    }

    .text_Ar {
        text-align: right !important;
        float: right !important;
        font-family: 'Cairo' !important;
        direction: rtl;
    }

    .text_en {
        text-align: left !important;
        float: left !important;

    }

    .menu-item_ar a {

        float: right !important;
        font-family: 'Cairo' !important;
    }

    .menu-item_ar {

        text-align: right !important;
        font-family: 'Cairo' !important;
        float: right !important;

    }


    .menu-item_en {
        float: left !important;
    }
</style>


<script>
    import {
        EventBus
    } from './event-bus.js';
    // import cc from './ccc.vue';
    import axios from "axios";
    import Vue from 'vue'
    export default {
        data() {

            return {
                drawer: false,
                group: null,
                dialog: false,
                pope: true,
                items: [{
                        name: 'home',
                        title: 'Home',
                        to: "/"
                    },

                    {
                        name: 'blogs',
                        title: this.$t("header.casesheet"),
                        to: "/blog"
                    },
                    {
                        name: 'about',
                        title: ' About Us',
                        to: "/about"
                    },

                    {
                        name: "Gallery",
                        title: 'Gallery',
                        to: "/Gallery"
                    },
                    {
                        name: "contact",
                        title: 'Contact',
                        to: "/contact"
                    },



                ],
                right: null,
            }
        },
        components: {

        },
        methods: {

            langChang() {

                let authenticate = Promise.resolve({
                    role: "admin"
                });
                authenticate.then(user => {
                    Vue.prototype.$user.set(user);
                })
                this.$i18n.locale = this.$i18n.locale == "ar" ? "en" : "ar";

                EventBus.$emit("changeLangs", this.$i18n.locale);
                localStorage.setItem("lang", this.$i18n.locale);
                this.$store.dispatch("UpdateLang", this.$i18n.locale);
                this.$vuetify.rtl = localStorage.getItem("lang") == "ar" ? true : false;
                axios.defaults.headers.get['lang'] = this.$i18n.locale;


                //    moment.locale(this.$i18n.locale == "en" ? "en" : "ar-kw");
            },
            changeLang() {

                //blog
                this.items[0].title = this.$t("header.home")
                this.items[1].title = this.$t("header.casesheet")
                this.items[2].title = this.$t("header.About_Us")
            
                this.items[3].title = this.$t("header.Gallery")

                this.items[4].title = this.$t("header.Contact")

            },
        },
        created() {
            //           EventBus.$on("changeLangs", (from) => {
            //               from
            //  alert('dsd');
            // });
            //        this.getByCatId(1)
        },
    }
</script>