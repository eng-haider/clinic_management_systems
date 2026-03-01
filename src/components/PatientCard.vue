<template>
  <div>
    <!-- Patient Card Dialog -->
    <v-dialog v-model="dialog" max-width="1400px" persistent>
      <v-card>
        <v-card-title class="headline d-flex align-center">
          <v-icon left>mdi-card-account-details</v-icon>
          {{ $t('patients.patient_card') || 'بطاقة المراجع' }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-6" style="overflow-x:auto;">
          <!-- Card Preview using card.jpeg as background - exact image size: 1280×818 -->
          <div 
            ref="printCard" 
            class="patient-card-preview"
            :class="{ 'card-female': patient.sex != '1' && patient.sex != 1 }"
            dir="rtl"
          >
        
            <!-- Background image -->
            <img 
              v-if="patient.sex=='1'"
              src="/card.jpeg" 
              class="card-bg-image"
              alt="card background"
            />
            <img
              v-else
              src="/card2.jpeg" 
              class="card-bg-image"
              alt="card background"
            />
           
            <!-- Patient name overlay on السيد field -->
            <div class="overlay-name">
              {{ patient.name }}
            </div>

            <!-- QR number overlay on الرقم field -->
            <div class="overlay-number">
              {{ patient.qr_code }}
            </div>

            <!-- QR Code on the left side of card -->
            <div class="overlay-qr">
              <qrcode-vue :value="getPatientLookupUrl()" :size="220" level="H" />
              <div class="overlay-qr-hint">امسح الـ QR لمشاهدة ملفك الطبي</div>
            </div>
          </div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn 
            text 
            @click="closeDialog"
            color="grey darken-1"
          >
            <v-icon left>mdi-close</v-icon>
            {{ $t('patients.close') || 'إغلاق' }}
          </v-btn>
          <v-btn 
            color="success" 
            @click="saveCardAsImage"
            class="mr-2"
          >
            <v-icon left>mdi-image-plus</v-icon>
            {{ $t('patients.save_as_image') || 'حفظ كصورة' }}
          </v-btn>
          <v-btn 
            color="primary" 
            @click="printPatientCard"
          >
            <v-icon left>mdi-printer</v-icon>
            {{ $t('patients.print') || 'طباعة' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import QrcodeVue from 'qrcode.vue';

export default {
  name: 'PatientCard',
  components: {
    'qrcode-vue': QrcodeVue
  },
  props: {
    value: {
      type: Boolean,
      default: false
    },
    patient: {
      type: Object,
      required: true,
      default: () => ({
        id: null,
        name: '',
        sex: '',
        qr_code: '',
        phone: ''
      })
    },
    clinicName: {
      type: String,
      default: 'IS Dental Center'
    }
  },
  data() {
    return {
      dialog: this.value
    };
  },
  watch: {
    value(newVal) {
      this.dialog = newVal;
    },
    dialog(newVal) {
      this.$emit('input', newVal);
    }
  },
  methods: {
    closeDialog() {
      this.dialog = false;
    },

    getFormattedSex() {
      if (this.patient.sex == 1 || this.patient.sex == '1') {
        return 'ذكر';
      } else if (this.patient.sex == 0 || this.patient.sex == '0') {
        return 'أنثى';
      } else {
        return this.$t('patients.not_specified') || 'غير محدد';
      }
    },

    getPatientLookupUrl() {
      const qrCode = this.patient.qr_code;
      const phone = this.patient.phone ? this.patient.phone.replace(/\D/g, '') : '';
      return `https://isdentalcenter.com/patient-lookup?code=${qrCode}&phone=${phone}`;
    },

    generateQRCodeDataUrl(text) {
      try {
        const encodedText = encodeURIComponent(text);
        return `https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=${encodedText}`;
      } catch (error) {
        console.error('Error generating QR code:', error);
        return '';
      }
    },

    // Print patient card - use card.jpeg as background, overlay text + QR
    printPatientCard() {
      try {
        const patientName = this.patient.name || '';
        const patientQR = this.patient.qr_code || '';
        const qrUrl = this.getPatientLookupUrl();
        const baseUrl = window.location.origin;

        // Choose background image and name position based on sex
        const isMale = this.patient.sex == '1' || this.patient.sex == 1;
        const bgFile = isMale ? 'card.jpeg' : 'card2.jpeg';
        const nameRight = isMale ? 276 : 328;  // CSS: male=276px, female=328px

        const cardHtml = `
          <!DOCTYPE html>
          <html lang="ar" dir="rtl">
            <head>
              <meta charset="UTF-8" />
              <title>بطاقة مراجع</title>
              <style>
                @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=block');
                * { margin:0; padding:0; box-sizing:border-box; }
                html, body {
                  width:100%; height:100%;
                  font-family:'Cairo','Arial',sans-serif;
                  background:#fff;
                }
                .card-wrapper {
                  width:1280px; height:818px;
                  position:relative;
                  margin:0 auto;
                  overflow:hidden;
                }
                .card-wrapper img.bg {
                  width:1280px; height:818px;
                  display:block;
                  object-fit:cover;
                }
                .overlay-name {
                    position: absolute;
    top: 303px;
    right: ${nameRight}px;
    font-size: 45px;
    font-weight: 800;
    color: #1a1a2e;
    font-family: 'Cairo', 'Arial', sans-serif;
    direction: rtl;
    text-align: right;
    white-space: nowrap;
    max-width: 600px;
    /* overflow: hidden; */
    text-overflow: ellipsis;
                }
                .overlay-number {
                   position: absolute;
    top: 470px;
    right: 267px;
    font-size: 45px;
    font-weight: 700;
    color: #1a1a2e;
    font-family: 'Cairo', 'Arial', sans-serif;
    direction: rtl;
    letter-spacing: 2px;
                }
                .overlay-qr {
                  position:absolute;
                  top:200px;
                  left:70px;
                  background:#fff;
                  padding:12px 12px 10px 12px;
                  border-radius:14px;
                  box-shadow:0 2px 8px rgba(0,0,0,0.1);
                  text-align:center;
                }
                .overlay-qr-hint {
                  margin-top:8px;
                  font-size:14px;
                  font-weight:700;
                  color:#1a1a2e;
                  font-family:'Cairo','Arial',sans-serif;
                  text-align:center;
                  direction:rtl;
                  white-space:nowrap;
                }
                @page { size:1280px 818px; margin:0; }
                @media print {
                  body { width:1280px; height:818px; margin:0; padding:0; }
                }
              </style>
            </head>
            <body>
              <div class="card-wrapper">
                <img class="bg" src="${baseUrl}/${bgFile}" alt="card" />
                <div class="overlay-name">${patientName}</div>
                <div class="overlay-number">${patientQR}</div>
                <div class="overlay-qr">
                  <img src="${this.generateQRCodeDataUrl(qrUrl)}" alt="QR" width="220" height="220" />
                  <div class="overlay-qr-hint">امسح الـ QR لمشاهدة ملفك الطبي</div>
                </div>
              </div>
            </body>
          </html>
        `;

        const w = window.open('', '_blank');
        if (!w) {
          this.$swal.fire({ 
            title: this.$t('error') || 'خطأ', 
            text: 'تم حجب نافذة الطباعة', 
            icon: 'error' 
          });
          return;
        }
        w.document.open();
        w.document.write(cardHtml);
        w.document.close();
        
        setTimeout(() => {
          w.focus();
          w.print();
        }, 1200);
      } catch (e) {
        console.error('Error printing patient card:', e);
        this.$swal.fire({ 
          title: this.$t('error') || 'خطأ', 
          text: 'حدث خطأ أثناء الطباعة', 
          icon: 'error' 
        });
      }
    },

    // Save patient card as image - draw card.jpeg then overlay text + QR on canvas
    async saveCardAsImage() {
      try {
        this.$swal.fire({
          title: this.$t('patients.generating_image') || 'جاري إنشاء الصورة',
          text: this.$t('patients.please_wait') || 'يرجى الانتظار...',
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            this.$swal.showLoading();
          }
        });

        const patientName = this.patient.name || '';
        const patientQR = this.patient.qr_code || '';
        const qrUrl = this.getPatientLookupUrl();
        const qrImageUrl = this.generateQRCodeDataUrl(qrUrl);

        // Choose background image and name position based on sex
        const isMale = this.patient.sex == '1' || this.patient.sex == 1;
        const bgFile = isMale ? '/card.jpeg' : '/card2.jpeg';
        const nameRight = isMale ? 276 : 328;  // CSS: male=276px, female=328px

        // Load Cairo font
        await document.fonts.load('800 36px Cairo');
        await new Promise(resolve => setTimeout(resolve, 300));

        // Card dimensions match the image: 1280×818
        const cardW = 1280;
        const cardH = 818;

        const canvas = document.createElement('canvas');
        canvas.width = cardW;
        canvas.height = cardH;
        const ctx = canvas.getContext('2d');

        // Step 1: Load and draw card.jpeg as background
        const bgImg = new Image();
        bgImg.crossOrigin = 'anonymous';
        await new Promise((resolve, reject) => {
          bgImg.onload = () => {
            ctx.drawImage(bgImg, 0, 0, cardW, cardH);
            resolve();
          };
          bgImg.onerror = (err) => {
            console.error('Failed to load card.jpeg', err);
            reject(new Error('Failed to load card background image'));
          };
          bgImg.src = window.location.origin + bgFile;
        });

        // Step 2: Overlay patient name — synced with CSS: top:303px, right:276px(male) / 328px(female), font-size:45px
        ctx.textAlign = 'right';
        ctx.direction = 'rtl';
        ctx.font = '800 45px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#1a1a2e';
        ctx.fillText(patientName, cardW - nameRight, 303 + 20);

        // Step 3: Overlay QR number — synced with CSS: top:470px, right:267px, font-size:45px
        ctx.font = '700 45px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#1a1a2e';
        ctx.textAlign = 'right';
        ctx.direction = 'rtl';
        ctx.fillText(patientQR, cardW - 267, 470 + 20);

        // Step 4: Load and draw QR code on the LEFT side — synced with CSS: top:240px, left:80px
        const qrImg = new Image();
        qrImg.crossOrigin = 'anonymous';
        await new Promise((resolve, reject) => {
          qrImg.onload = () => {
            // White background box for QR — synced with CSS: top:200px, left:70px
            const qrX = 70;
            const qrY = 200;
            const qrSize = 220;  // matches :size="220" in template
            const pad = 14;

            // Draw white rounded rect behind QR
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            const br = 12;
            ctx.moveTo(qrX - pad + br, qrY - pad);
            ctx.lineTo(qrX + qrSize + pad - br, qrY - pad);
            ctx.quadraticCurveTo(qrX + qrSize + pad, qrY - pad, qrX + qrSize + pad, qrY - pad + br);
            ctx.lineTo(qrX + qrSize + pad, qrY + qrSize + pad - br);
            ctx.quadraticCurveTo(qrX + qrSize + pad, qrY + qrSize + pad, qrX + qrSize + pad - br, qrY + qrSize + pad);
            ctx.lineTo(qrX - pad + br, qrY + qrSize + pad);
            ctx.quadraticCurveTo(qrX - pad, qrY + qrSize + pad, qrX - pad, qrY + qrSize + pad - br);
            ctx.lineTo(qrX - pad, qrY - pad + br);
            ctx.quadraticCurveTo(qrX - pad, qrY - pad, qrX - pad + br, qrY - pad);
            ctx.closePath();
            ctx.fill();

            // Light border
            ctx.strokeStyle = '#d0d0d0';
            ctx.lineWidth = 2;
            ctx.stroke();

            // Draw QR image
            ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);

            // Draw hint text below QR — synced with CSS .overlay-qr-hint
            ctx.textAlign = 'center';
            ctx.direction = 'rtl';
            ctx.font = '700 14px Cairo, Arial, sans-serif';
            ctx.fillStyle = '#1a1a2e';
            ctx.fillText('امسح الـ QR لمشاهدة ملفك الطبي', qrX + qrSize / 2, qrY + qrSize + pad + 16);

            resolve();
          };
          qrImg.onerror = reject;
          qrImg.src = qrImageUrl;
        });

        // Step 5: Download as image
        canvas.toBlob((blob) => {
          const url = URL.createObjectURL(blob);
          const link = document.createElement('a');
          const cleanName = (this.patient.name || 'patient').replace(/[^a-zA-Z0-9آ-ي\s]/g, '').trim();
          const fileName = `${cleanName}-card-${new Date().getTime()}.png`;
          link.href = url;
          link.download = fileName;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);

          this.$swal.fire({
            title: this.$t('success') || 'نجاح',
            text: this.$t('patients.image_saved_successfully') || `تم حفظ الصورة: ${fileName}`,
            icon: 'success',
            confirmButtonText: this.$t('close') || 'إغلاق'
          });
        }, 'image/png');

      } catch (e) {
        console.error('Error saving card as image:', e);
        this.$swal.fire({
          title: this.$t('error') || 'خطأ',
          text: this.$t('patients.error_saving_image') || 'حدث خطأ أثناء حفظ الصورة',
          icon: 'error',
          confirmButtonText: this.$t('close') || 'إغلاق'
        });
      }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap');

/* ===== Patient Card Preview ===== */
/* Uses card.jpeg (1280×818) as background with text overlay */
.patient-card-preview {
  width: 1280px;
  height: 818px;
  position: relative;
  margin: 0 auto;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
  font-family: 'Cairo', 'Arial', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Background card image */
.card-bg-image {
  width: 1280px;
  height: 818px;
  display: block;
  object-fit: cover;
  pointer-events: none;
  user-select: none;
}

/* Patient name overlay - positioned on السيد field */
.overlay-name {
     position: absolute;
    top: 303px;
    right: 276px;
    font-size: 45px;
    font-weight: 800;
    color: #1a1a2e;
    font-family: 'Cairo', 'Arial', sans-serif;
    direction: rtl;
    text-align: right;
    white-space: nowrap;
    max-width: 600px;
    /* overflow: hidden; */
    text-overflow: ellipsis;
}

/* Female card (card2.jpeg) — name field is shifted more to the right */
.card-female .overlay-name {
  right: 328px;
}

/* QR number overlay - positioned on الرقم field */
.overlay-number {
     position: absolute;
    top: 470px;
    right: 267px;
    font-size: 45px;
    font-weight: 700;
    color: #1a1a2e;
    font-family: 'Cairo', 'Arial', sans-serif;
    direction: rtl;
    letter-spacing: 2px;
}

/* QR code overlay - positioned on the LEFT side of card */
.overlay-qr {
  position: absolute;
  top: 200px;
  left: 70px;
  background: #ffffff;
  padding: 12px 12px 10px 12px;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border: 2px solid #e0e0e0;
  text-align: center;
}

/* Hint text below QR */
.overlay-qr-hint {
  margin-top: 8px;
  font-size: 14px;
  font-weight: 700;
  color: #1a1a2e;
  font-family: 'Cairo', 'Arial', sans-serif;
  text-align: center;
  direction: rtl;
  white-space: nowrap;
}
</style>
