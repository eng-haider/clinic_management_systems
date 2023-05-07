<template>
    <v-hover>
        
                    <template v-slot="{ hover }">
                        <v-card :elevation="hover ? 24 : 6" class="mx-auto"  >
                            <v-layout row warp pr-3 style="height:124px;width:100%;font-family:'cairo_semi'">
                                <v-flex xs8 pt-3>
                                    <v-card-title class="card_dash" style="padding-bottom1% !important;" >
                                        <span >{{name}} </span>
                                    </v-card-title>
    
    
    
                                    <div class="card_num" >
                                        <span v-if="!loading" :color="text_color" :style="'font-size:25px;color:'+text_color">{{count}}</span>
                                         <v-progress-circular indeterminate :color="'color:'+text_color" v-else ></v-progress-circular>
                                    </div>
                                </v-flex>
    
    
                                <v-flex xs4 pt-3>
                                    <v-card-title>
                                        <v-icon x-large left  :color="icon_color"  class="dashbourd_card_icon"> {{icon}}</v-icon>
                                            
                                    </v-card-title>
    
    
                                </v-flex>
    
                            </v-layout>
                        </v-card>
                    </template>
                </v-hover>
    </template>
    
    <script>
      export default {
        inheritAttrs: false,
    
        props: {
          name:String,
          color:String,
          icon_color:String,
          icon:String,
          text_color:String,
          count:Number,
          loading:Boolean
        },
    
      
      }
    </script>
    