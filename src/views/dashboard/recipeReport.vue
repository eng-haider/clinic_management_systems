<template>
    <div style="direction: rtl;font-family: 'Cairo';    background-color: #fff;">

        <div style="width:100%">
            <img src="/rx_header.jpeg" style="width:100%" />
        </div>


        <div class="container" style="    margin-top: 44px;">




            <div class="row">

                <div class="col-md-3 col-xs-3"></div>


                <div class="col-md-3 col-xs-3">

                    <h3 style="    text-align: center;font-size:37px;
    font-family: 'GE_Dinar';">عيادة انفنتي </h3>
                    <h3
                    style="    text-align: center;font-size:37px;
    font-family: 'GE_Dinar';"
                    >لطب الاسنان</h3>



                </div>



                <div class="col-md-3 col-xs-3">
                    <div class="rexipe_logo" style="    height: 114px;
        margin-bottom: 27px;
            margin-right: 10px;">
                        <img src="/clini.png" style="height:100%" />
                    </div>
                </div>



                <div class="col-md-3 col-xs-3"></div>


            </div>

            <br>
            <br>
            <div class="row" style="text-align: right;">


                <div class="col-md-4 col-xs-6 titl_top_min" >
                    الاسم : {{RecipeInfo.name}}
                </div>

                <div class="col-md-2 col-xs-6 titl_top_min">
                    العمر : {{RecipeInfo.age}}
                </div>



                <div class="col-md-3 col-xs-6 titl_top_min">
                    الجنس : <span v-if="RecipeInfo.sex==1">ذكر</span> <span v-if="RecipeInfo.sex==0">انثئ</span>
                </div>

                <div class="col-md-3 col-xs-6 titl_top_min">
                    الحاله : {{RecipeInfo.case.case_categories.name_ar}}
                </div>


            </div>
            <div class="row" style="text-align: right;">

                <div class="col-md-12 col-xs-12">


                    <div style="
              height: 750px;
    margin-top: 30px;
    border-radius: 12px;
    padding: 29px;
    font-size: 22px;
    border: 2px solid rgb(8, 119, 169);
    
    ">
                        {{RecipeInfo.rx}}
                    </div>
                </div>

            </div>
            <br>
            <br>
            <div class="row" style="text-align: right;">

                <div class="col-md-12 col-xs-12 titl_top_min">
                    توقيع الدكتور :
                </div>
                <!-- <div class="col-md-12 col-xs-12">


                    {{RecipeInfo.user.name}}
                </div> -->

            </div>


<br>
<br>

            <div class="titl_top_min" style="text-align:center">
              كربلاء المقدسة - شارع الاسكان - مقابل مستشفى الشيخ الوائلي

            </div>

              <div class="titl_top_min" style="text-align:center">
            0771 999 7558

            </div>






        </div>

    </div>
</template>

<style>

.titl_top_min
{
    font-family: 'GE_Dinar';
    font-size: 24px;
}
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
    // recipeReport
    export default {
        inheritAttrs: false,

        props: {
            RecipeInfo: Object,
        },
        components: {

        },
        methods: {
            print() {

                this.$htmlToPaper('printMex');
            },

        }
    }
</script>