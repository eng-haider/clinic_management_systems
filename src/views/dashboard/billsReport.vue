<template>



    <div class="container">



        <div class="row">
            <div class="col-md-12 col-xs-12">
                <br>
 <h1   style="    text-align: center;
    font-family: 'Cairo';">عيادة انفنتي لطب الاسنان</h1>
       <br>
            <!-- <div class="row">
                    <div class="col-md-6 col-xs6">
                   
                </div>

 <div class="col-md-6 col-xs6">
                <div class="invoice-title">
               
                        <h1>عيادة انفنتي لطب الاسنان</h1>
                    
                </div>
 </div>

            </div> -->

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
                              <strong style="  display: block;font-family: 'Cairo'">{{info.case.case_categories.name_ar}} </strong>
                         <strong style="  display: block;font-family: 'Cairo'">{{info.user.name}} </strong>
                        <strong style="  display: block;font-family: 'Cairo';direction: rtl;"> {{info.case.price}}  د.ع</strong>
                       


                           
                       

                      

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
                    <tr style="text-align: right;" v-for="item in info.case.bills" :key="item.id">
 <td style="text-align: right;" >{{item.PaymentDate}}</td>
     <td style="text-align: right;    direction: rtl;" > {{item.price}}  د.ع</td>
                       
                        <!-- <td>{{item.id}}</td> -->
                    
            
                    </tr>

                    <tr>

                        <td></td>
                    
                        <td style="text-align: right;font-weight:bold;font-family: 'Cairo'">
                          <p>   المبلغ المدفوع : {{BillsSum(info.case.bills)}}  د.ع</p>
                          <p> المبلغ المتبقي : {{info.case.price-BillsSum(info.case.bills)}}  د.ع</p>
                             

                        </td>
 



                    </tr>

                </tbody>
            </table>
        </div>

      <div style="position: relative;; width: 600px; height: 800px;">    

    <div style="position: absolute; bottom: 5px; font-family: 'Cairo'">
   توقيع الدكتور
    </div>
</div>
    </div>
</template>


<script>
    export default {
        inheritAttrs: false,

        props: {
            info:Object,
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