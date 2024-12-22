<template>
    <div id="printSection" class="rx-report">
      <!-- Header Image -->
      <div class="header-image">
        <img src="/rx_header.jpeg" alt="Header Image" />
      </div>
  
      <!-- Clinic Info -->
    <v-container>


        <v-layout row wrap class="patient-details">
            <v-flex xs3>
                <strong>الاسم:</strong> {{ RecipeInfo.case.patient_id.name }}
            </v-flex>
            <v-flex xs3>
                <strong>العمر:</strong> {{ RecipeInfo.age }}
            </v-flex>
            <v-flex xs3>
                <strong>الجنس:</strong>
                <span>{{ RecipeInfo.sex === 1 ? 'ذكر' : 'انثى' }}</span>
            </v-flex>
            <v-flex xs3>
                <strong>الحالة:</strong> {{ RecipeInfo.case.case_categories.name_ar }}
            </v-flex>


            <v-flex xs12 class="notes-section" pt-4>
    
    {{ RecipeInfo.notes }}

</v-flex>
        </v-layout>
     
  
  
        <!-- Doctor Signature -->
        <div class="row text-right">
          <div class="col-md-12">
            <strong>توقيع الدكتور:</strong>
          </div>
        </div>
  
        <!-- Footer -->
        <div class="footer text-center">
        <div>{{ $store.state.AdminInfo.clinics_info.address }}</div>
          <div>{{ $store.state.AdminInfo.phone }}</div>
        </div>
        
    </v-container>
        <!-- <div class="row">
          <div class="col-md-3"></div>
          <div class="col-md-6 text-center">
            <h3 class="clinic-name">{{ $store.state.AdminInfo.clinics_info.name }}</h3>
            <h3 class="clinic-specialty">لطب الاسنان</h3>
          </div>
          <div class="col-md-3"></div>
        </div> -->
  
        <!-- Patient Details -->

    </div>
  </template>
  
  <style scoped>

  
  /* General Styles */
  .rx-report {
    direction: rtl;
    font-family: "Cairo", sans-serif;
    background-color: #fff;
  }
  
  .header-image img {
    width: 100%;
  }
  
  .clinic-name,
  .clinic-specialty {
    font-family: "GE_Dinar", sans-serif;
    font-size: 37px;
  }
  
  .patient-details {
    margin-top: 20px;
    font-size: 18px;
    font-family: "GE_Dinar", sans-serif;
  }
  

  
  .notes-section {
  min-height: 400px;
  margin-top: 30px;
  padding: 1cm; /* Add padding to prevent text from sticking to the border */
  margin-left: auto; /* Center align if necessary */
  margin-right: auto; /* Center align if necessary */
  border: 1px solid #0877a9;

  font-size: 22px;
  font-family: "GE_Dinar", sans-serif;
  width: calc(100% - 2cm); /* Ensure it stays within printable area */
  box-sizing: border-box; /* Includes padding and border in the width */
}

@media print {
  .notes-section {
    width: calc(100% - 2cm); /* Adjust for print to avoid cut-off */
    margin: auto; /* Center align for print */
  }
}

  .footer {
    margin-top: 40px;
    font-size: 18px;
    font-family: "GE_Dinar", sans-serif;
  }
  
  @media print {
    body {
      direction: rtl;
      font-family: "Cairo", sans-serif;
      background-color: #fff;
    }
  
    .container {
      width: 21cm;
      height: 29.7cm;
      margin: auto;
      padding: 1cm;
      box-sizing: border-box;
    }
  
    .header-image img {
      max-width: 100%;
      height: auto;
    }
  }
  </style>
  
  <script>
  export default {
    name: "RecipeReport",
    props: {
      RecipeInfo: {
        type: Object,
        required: true,
      },
    },
    methods: {
      // Optional: Method for printing the report
      print() {
        this.$htmlToPaper("printSection");
      },
    },
  };
  </script>
  