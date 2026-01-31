<template>
  <div id="printSection" class="rx-report">
    <!-- Header with Clinic Info and Rx Symbol -->
    <div class="rx-header">
      <div class="clinic-header-row">
        <div class="clinic-logo-left">
          <img v-if="getClinicLogo()" :src="getClinicLogo()" alt="Clinic Logo" class="clinic-logo" />
        </div>
        <div class="clinic-info-center">
          <div class="clinic-name">{{ getClinicName() }}</div>
          <div v-if="getClinicPhone()" class="clinic-phone">{{ getClinicPhone() }}</div>
        </div>
        <div class="clinic-logo-right">
          <img v-if="getClinicLogo()" :src="getClinicLogo()" alt="Clinic Logo" class="clinic-logo" />
        </div>
      </div>
      <!-- Header Image -->
      <div class="header-image">
        <img :src="rx_img" alt="Header Image" crossOrigin="anonymous" />
      </div>
      <!-- Rx Symbol -->
      <div class="rx-symbol">℞</div>
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
      <div class="footer">
        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px; flex-wrap: wrap;">
          <img v-if="getClinicLogo()" :src="getClinicLogo()" alt="Clinic Logo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #007bff;" />
          <div style="text-align: center;">
            <div style="font-weight: bold; color: #333; font-size: 16px; margin-bottom: 5px;">{{ getClinicName() }}</div>
            <div v-if="getClinicPhone()" style="color: #007bff; font-size: 14px; direction: ltr;">{{ getClinicPhone() }}</div>
          </div>
        </div>
        <p>تاريخ الوصفة: {{ new Date().toLocaleDateString('en-US') }}</p>
        <p>يرجى اتباع تعليمات الطبيب المعالج</p>
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
  text-align: right;
  unicode-bidi: bidi-override;
}

.rx-report * {
  direction: rtl;
  text-align: right;
  unicode-bidi: embed;
}

.rx-header {
  background: #f8f9fa;
  padding: 15px;
  text-align: center;
  border-bottom: 2px solid #333;
  margin-bottom: 20px;
}

.clinic-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 15px;
  padding: 0 20px;
}

.clinic-logo-left,
.clinic-logo-right {
  width: 80px;
  flex-shrink: 0;
}

.clinic-logo {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #007bff;
}

.clinic-info-center {
  flex-grow: 1;
  text-align: center;
}

.clinic-name {
  font-weight: bold;
  color: #333;
  font-size: 20px;
  margin-bottom: 5px;
  font-family: "GE_Dinar", sans-serif;
}

.clinic-phone {
  color: #007bff;
  font-size: 16px;
  direction: ltr;
}

.header-image img {
  width: 100%;
  max-height: 120px;
  border-radius: 5px;
  margin-bottom: 10px;
}

.rx-symbol {
  font-size: 48px;
  font-weight: bold;
  color: #007bff;
  margin: 10px 0;
}

.clinic-specialty {
  font-family: "GE_Dinar", sans-serif;
  font-size: 37px;
}

.patient-details {
  margin-top: 20px;
  font-size: 18px;
  font-family: "GE_Dinar", sans-serif;
  direction: rtl;
  text-align: right;
}

.patient-details strong {
  direction: rtl;
  unicode-bidi: embed;
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
  direction: rtl;
  text-align: right;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.footer {
  margin-top: 40px;
  font-size: 18px;
  font-family: "GE_Dinar", sans-serif;
  text-align: center;
  padding: 15px;
  border-top: 1px solid #ddd;
  background: #f8f9fa;
  color: #666;
  direction: rtl;
}

.footer p {
  direction: rtl;
  text-align: center;
}

@media print {
  .notes-section {
    width: calc(100% - 2cm);
    margin: auto;
  }
  
  .rx-header {
    break-inside: avoid;
  }
  
  .clinic-header-row {
    break-inside: avoid;
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
    getClinicLogo() {
      const clinicInfo = this.$store.getters.getClinicInfo;
      if (clinicInfo && clinicInfo.logo) {
        return clinicInfo.logo;
      }
      return null;
    },
    getClinicName() {
      return this.$store.getters.getClinicName || 'العيادة';
    },
    getClinicPhone() {
      return this.$store.getters.getClinicPhone || '';
    },
    // Optional: Method for printing the report
    print() {
      this.$htmlToPaper("printSection");
    },
  },
};
</script>
