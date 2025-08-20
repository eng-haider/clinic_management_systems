<template>



    <div class="container">



        <div class="row">
            <div class="col-md-12 col-xs-12">
                <br>
                <div style="text-align: center; margin-bottom: 20px;">
                    <div v-if="getClinicLogo()" style="margin-bottom: 10px;">
                        <img :src="getClinicLogo()" alt="Clinic Logo" style="max-height: 80px; max-width: 200px;" />
                    </div>
                    <h1 style="font-family: 'Cairo'; margin: 10px 0;">{{ getClinicName() || 'عيادة انفنتي لطب الاسنان' }}</h1>
                    <p v-if="getClinicPhone()" style="font-family: 'Cairo'; color: #666; margin: 5px 0;">{{ getClinicPhone() }}</p>
                </div>
                <br>
        

                <hr>

                <div class="row">


                    <div class="col-md-4 col-xs-6">

                    </div>


                    <div class="col-md-4 col-xs-6 ">

                    </div>


                    <div class="col-md-2 col-xs-6 text-left">
                        <address style="
                            text-align: right;
    position: relative;

    left: 16px;
}">
                            <strong style="  display: block;font-family: 'Cairo'">{{info.name}} </strong>
                            <strong style="  display: block;font-family: 'Cairo'">{{info.id}} </strong>
                          
                       

                      

                        </address>
                    </div>
                    <div class="col-md-2 col-xs-6">
                        <address style="text-align: right;">
                            <strong style="font-family: 'Cairo'">: الاسم</strong><br>
                            <strong style="font-family: 'Cairo'">: ID </strong><br>
                            <strong style="font-family: 'Cairo'"> : الحاله </strong><br>
                             <strong style="font-family: 'Cairo'">: الدكتور </strong><br>
                               <strong style="font-family: 'Cairo'">: مجموع الفاتوره </strong><br>
                        </address>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <table class="table">
                <thead>
                    <tr style="text-align: right;" >
  <th scope="col" style="text-align: right;font-family: 'Cairo'" >
                           تاريخ الدفعه
                        </th>

                        <th style="text-align: right;font-family: 'Cairo'"  scope="col">
                            قيمه الدفعه
                        </th>
                        <!-- <th scope="col">
                           رقم الدفعه
                        </th>

                    
 -->


                      
                     


                    </tr>
                </thead>
              
                <tbody>
                    <!-- <tr style="text-align: right;" v-for="item in info.case.bills" :key="item.id">
 <td style="text-align: right;" >{{item.PaymentDate}}</td>
     <td style="text-align: right;    direction: rtl;" > {{item.price}}  د.ع</td>
                       
                      
            
                    </tr>

                    <tr>

                        <td></td>
                    
                        <td style="text-align: right;font-weight:bold;font-family: 'Cairo'">
                          <p>   المبلغ المدفوع : {{BillsSum(info.case.bills)}}  د.ع</p>
                          <p> المبلغ المتبقي : {{info.case.price-BillsSum(info.case.bills)}}  د.ع</p>
                             

                        </td>
 



                    </tr> -->

                </tbody>
            </table>
        </div>

      <div style="margin-top: 40px; width: 100%;">    
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-top: 1px solid #ddd;">
            <div style="text-align: left; font-family: 'Cairo';">
                توقيع الدكتور
                <div style="border-bottom: 1px solid #333; width: 150px; margin-top: 30px;"></div>
            </div>
            <div style="text-align: right; display: flex; align-items: center; gap: 10px;">
                <div v-if="getClinicLogo()" style="margin-left: 10px;">
                    <img :src="getClinicLogo()" alt="Clinic Logo" style="max-height: 40px; max-width: 80px;" />
                </div>
                <div>
                    <div style="font-weight: bold; font-size: 14px; font-family: 'Cairo';">{{ getClinicName() }}</div>
                    <div v-if="getClinicPhone()" style="font-size: 12px; color: #666; direction: ltr;">{{ getClinicPhone() }}</div>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>


<script>
    import { mapGetters } from 'vuex'
    
    export default {
        inheritAttrs: false,

        props: {
            info:Object,
        },

        computed: {
            ...mapGetters(['getClinicLogo', 'getClinicName', 'getClinicPhone'])
        },

        methods: {

            BillsSum(bills_amount) {
                var totle_coast = 0;

                for (var i = 0; i < bills_amount.length; i++) {
                    totle_coast += bills_amount[i].price;

                }
                return totle_coast;

            },
            count_amount(amount) {
                amount
                //   for()

                //   var abount_all='';
                //   return abount_all+=amount;
            }
        }
    }
</script>

<style scoped>
.container {
    max-width: 100%;
    margin: 0;
    padding: 15px;
}

@media print {
    .container {
        padding: 10px;
        margin: 0;
        max-width: 100%;
        width: 100%;
        page-break-inside: avoid;
    }
    
    .row {
        margin: 0;
        padding: 5px 0;
    }
    
    .table {
        margin-bottom: 10px;
        font-size: 12px;
    }
    
    .table th,
    .table td {
        padding: 6px 8px;
        border: 1px solid #ddd;
        font-size: 12px;
    }
    
    h1 {
        font-size: 18px;
        margin: 10px 0;
    }
    
    p {
        margin: 5px 0;
        font-size: 12px;
    }
    
    img {
        max-height: 60px !important;
        max-width: 120px !important;
    }
    
    /* Ensure single page */
    body {
        margin: 0;
        padding: 0;
    }
    
    @page {
        margin: 0.5in;
        size: A4;
    }
    
    /* Hide any potential scroll bars or extra spacing */
    * {
        box-sizing: border-box;
    }
}

/* Regular screen styles */
.table {
    margin-bottom: 20px;
}

.table th,
.table td {
    padding: 8px 12px;
    border: 1px solid #ddd;
}
</style>