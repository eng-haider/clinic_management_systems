<template>
  <div id="printSection" class="rx-report">
    <!-- Header Image -->
    <div class="header-image">
      <img :src="rx_img" alt="Header Image" crossOrigin="anonymous" />
    </div>

    <!-- Clinic Info -->
    <v-container>
      <v-layout row wrap class="patient-details">
        <v-flex xs3 pr-3>
          
          <strong>الاسم:</strong> {{ RecipeInfo.name }}
        </v-flex>
        <v-flex xs3>
          <strong>العمر:</strong> {{ RecipeInfo.age }}
        </v-flex>
        <v-flex xs3>
          <strong>الجنس:</strong>
          <span>{{ RecipeInfo.sex === 1 ? 'ذكر' : 'انثى' }}</span>
        </v-flex>
        <v-flex xs3>
          <!-- <strong>الحالة:</strong> {{ RecipeInfo.case.case_categories.name_ar }} -->
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
  padding: 1cm;
  margin-left: auto;
  margin-right: auto;
  border: 1px solid #0877a9;
  font-size: 22px;
  font-family: "GE_Dinar", sans-serif;
  width: calc(100% - 2cm);
  box-sizing: border-box;
  white-space: pre-wrap;
}

.footer {
  margin-top: 40px;
  font-size: 18px;
  font-family: "GE_Dinar", sans-serif;
}

@media print {
  .notes-section {
    width: calc(100% - 2cm);
    margin: auto;
  }
}
</style>

<script>
export default {
  name: "RecipeReport",
  props: {
    rx_img: String,
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
