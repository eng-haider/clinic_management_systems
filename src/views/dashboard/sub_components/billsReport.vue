<template>
    <div>
        <v-toolbar dark color="primary lighten-1">
            <v-toolbar-title>
                <h3 style="color:#fff;font-family: 'Cairo'"> تقرير الحساب  
               </h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="close()" icon>
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>




      <v-card style="    padding: 20px;">

            <span class="d-none d-sm-flex" style="font-size:18px;text-align:center;border:0.4px solid #eee;padding:10px;    background-color: #e5f1fa;">
               <h3> اسم المراجع :</h3>
                <h3>

                    {{ patient.name }}
                </h3>
            </span>

            <v-simple-table pa-4>
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-right">
                                نوع الحاله
                            </th>


                            <th class="text-right">
                                السعر
                            </th>

                            <th class="text-right">
                                المدفوع
                            </th>
                            <th class="text-right">
                                المتبقي
                            </th>
                            <th class="text-right">
                                التاريخ
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in patient.cases" :key="item.name">

                            <td>{{ item.case_categories.name_ar }}</td>




                            <td>{{ item.price | currency}}  <span class="money">د.ع</span></td>


                            <td v-if="item.bills.length>0">{{xx(item.bills) | currency}}  <span class="money">د.ع</span></td>

                            <td v-else>0</td>


                            <td v-if="item.bills.length>0">{{ (item.price-xx(item.bills))  | currency }}  <span class="money">د.ع</span></td>
                            <td v-else>{{ item.price | currency}} <span class="money">د.ع</span></td>

                            <td>{{ cropdate(item.created_at) }}</td>

                        </tr>
                    </tbody>
                </template>
            </v-simple-table>



      </v-card>
    </div>
</template>

<script>
    import {
        EventBus
    } from "../event-bus.js";
    export default {
        inheritAttrs: false,

        props: {
            patient:Object
        },

        methods: {
            cropdate(x) {
                return x.slice(0, 10);
            },
            close(){
                EventBus.$emit("billsReportclose", false);
            },
            xx(ite) {



                var x = 0
                for (var i = 0; i < ite.length; i++) {

                    x += JSON.parse(ite[i].price)

                }

                return x;



            }
        }
    }
</script>