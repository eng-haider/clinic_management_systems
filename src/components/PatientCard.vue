<template>
  <div>
    <!-- Patient Card Dialog -->
    <v-dialog v-model="dialog" max-width="1200px" persistent>
      <v-card>
        <v-card-title class="headline d-flex align-center">
          <v-icon left>mdi-card</v-icon>
          {{ $t('patients.patient_card') || 'بطاقة المراجع' }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-6">
          <!-- Card Preview -->
          <div 
            ref="printCard" 
            style="width:1011px; height:638px; box-sizing:border-box; padding:24px; background:#ffffff; margin:0 auto; border: 2px solid #e0e0e0; border-radius:8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:space-between; direction:rtl; text-align:right; font-family: 'Cairo', 'Arial', sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;" 
            dir="rtl"
          >
            <div style="flex:1; padding:20px; direction:rtl; text-align:right;">
              <h1 style="margin:0 0 16px 0; font-size:48px; font-weight:900; color:#1a1a1a; word-break:break-word; font-family: 'Cairo', 'Arial', sans-serif; direction:rtl; text-align:right; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; letter-spacing:0.5px;">
                {{ patient.name }}
              </h1>
              
              <div style="margin:16px 0; font-size:22px; color:#555; word-break:break-word; font-family: 'Cairo', 'Arial', sans-serif; line-height:1.8; direction:rtl; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
                <span style="font-weight:700; color:#333;">{{ $t('patients.sex') }}:</span>
                <span style="font-weight:500; color:#666; margin-right:8px;">{{ getFormattedSex() }}</span>
              </div>
              <div style="margin:16px 0; font-size:22px; color:#555; word-break:break-word; font-family: 'Cairo', 'Arial', sans-serif; line-height:1.8; direction:rtl; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
                <span style="font-weight:700; color:#333;">{{ $t('patients.qr_number') }}:</span>
                <span style="font-weight:600; color:#666; margin-right:8px; direction:ltr; display:inline-block; unicode-bidi:bidi-override; font-family:'Courier New', monospace; letter-spacing:2px;">
                  {{ patient.qr_code }}
                </span>
              </div>
            </div>
            <div style="flex-shrink:0; padding:20px; text-align:center;">
              <qrcode-vue :value="getPatientLookupUrl()" :size="300" level="H" />
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
            {{ $t('patients.close') }}
          </v-btn>
          <v-btn 
            color="success" 
            @click="saveCardAsImage"
            class="mr-2"
          >
            <v-icon left>mdi-image-plus</v-icon>
            {{ $t('patients.save_as_image') }}
          </v-btn>
          <v-btn 
            color="primary" 
            @click="printPatientCard"
          >
            <v-icon left>mdi-printer</v-icon>
            {{ $t('patients.print') }}
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

    // Get formatted sex label
    getFormattedSex() {
      if (this.patient.sex == 1 || this.patient.sex == '1') {
        return 'ذكر';
      } else if (this.patient.sex == 0 || this.patient.sex == '0') {
        return 'أنثى';
      } else {
        return this.$t('patients.not_specified') || 'غير محدد';
      }
    },

    // Get patient lookup URL for QR code
    getPatientLookupUrl() {
      const qrCode = this.patient.qr_code;
      const phone = this.patient.phone ? this.patient.phone.replace(/\D/g, '') : '';
      return `https://isdentalcenter.com/patient-lookup?code=${qrCode}&phone=${phone}`;
    },

    /**
     * Generate QR Code as Data URL for printing
     */
    generateQRCodeDataUrl(text) {
      try {
        const encodedText = encodeURIComponent(text);
        return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodedText}`;
      } catch (error) {
        console.error('Error generating QR code:', error);
        return '';
      }
    },

    // Print patient card by opening a print window with the card HTML
    printPatientCard() {
      try {
        const el = this.$refs.printCard;
        if (!el) {
          console.warn('Print card element not found');
          return;
        }

        const patientName = this.patient.name || 'Patient';
        const patientSex = this.getFormattedSex();
        const patientQR = this.patient.qr_code || '';
        const qrUrl = this.getPatientLookupUrl();
        
        const cardHtml = `
          <!DOCTYPE html>
          <html lang="ar" dir="rtl">
            <head>
              <meta charset="UTF-8" />
              <meta http-equiv="X-UA-Compatible" content="IE=edge" />
              <meta name="viewport" content="width=device-width, initial-scale=1.0" />
              <meta http-equiv="Content-Language" content="ar" />
              <title>بطاقة المراجع</title>
              <style>
                @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&display=block');
                
                * {
                  margin: 0;
                  padding: 0;
                  box-sizing: border-box;
                  -webkit-font-smoothing: antialiased;
                  -moz-osx-font-smoothing: grayscale;
                  text-rendering: optimizeLegibility;
                }
                
                html, body {
                  width: 100%;
                  height: 100%;
                  direction: rtl;
                  text-align: right;
                }
                
                body {
                  margin: 0;
                  padding: 0;
                  font-family: 'Cairo', 'Arial', 'Helvetica', sans-serif;
                  background: white;
                  color: #333;
                  direction: rtl;
                  text-align: right;
                }
                
                .print-container {
                  width: 1011px;
                  height: 638px;
                  padding: 24px;
                  box-sizing: border-box;
                  background: white;
                  direction: rtl;
                  text-align: right;
                  margin: 0 auto;
                  display: flex;
                  align-items: center;
                  justify-content: space-between;
                }
                
                .content-wrapper {
                  flex: 1;
                  padding: 20px;
                  direction: rtl;
                }
                
                .qr-wrapper {
                  flex-shrink: 0;
                  padding: 20px;
                  text-align: center;
                }
                
                h1 {
                  margin: 0 0 16px 0;
                  font-size: 48px;
                  font-weight: 900;
                  color: #1a1a1a;
                  word-break: break-word;
                  font-family: 'Cairo', sans-serif;
                  letter-spacing: 0.5px;
                  direction: rtl;
                }
                
                .info-item {
                  margin: 16px 0;
                  font-size: 22px;
                  color: #555;
                  word-break: break-word;
                  font-family: 'Cairo', sans-serif;
                  line-height: 1.8;
                  direction: rtl;
                }
                
                .label {
                  font-weight: 700;
                  color: #333;
                  display: inline;
                }
                
                .value {
                  font-weight: 500;
                  color: #666;
                  display: inline;
                  margin-right: 8px;
                }
                
                .qr-number {
                  direction: ltr;
                  unicode-bidi: embed;
                  display: inline-block;
                  font-family: 'Courier New', monospace;
                  font-weight: bold;
                  letter-spacing: 2px;
                }
                
                @page {
                  size: 1011px 638px;
                  margin: 0;
                  padding: 0;
                }
                
                @media print {
                  body {
                    margin: 0;
                    padding: 0;
                    width: 1011px;
                    height: 638px;
                    background: white;
                  }
                  .print-container {
                    box-shadow: none;
                    border: none;
                    margin: 0;
                    padding: 24px;
                    width: 1011px;
                    height: 638px;
                  }
                }
              </style>
            </head>
            <body dir="rtl">
              <div class="print-container">
                <div class="content-wrapper">
                  <h1>${patientName}</h1>
                  <div class="info-item">
                    <span class="label">${this.$t('patients.sex') || 'الجنس'}:</span>
                    <span class="value">${patientSex}</span>
                  </div>
                  <div class="info-item">
                    <span class="label">${this.$t('patients.qr_number') || 'رقم QR'}:</span>
                    <span class="qr-number">${patientQR}</span>
                  </div>
                </div>
                <div class="qr-wrapper">
                  <img src="${this.generateQRCodeDataUrl(qrUrl)}" alt="QR Code" width="300" height="300" />
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
        }, 800);
      } catch (e) {
        console.error('❌ Error printing patient card:', e);
        this.$swal.fire({ 
          title: this.$t('error') || 'خطأ', 
          text: 'حدث خطأ أثناء الطباعة', 
          icon: 'error' 
        });
      }
    },

    // Save patient card as image
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

        const patientName = this.patient.name || 'Patient';
        const patientSex = this.getFormattedSex();
        const patientQR = this.patient.qr_code || '';
        const qrUrl = this.getPatientLookupUrl();
        const qrImageUrl = this.generateQRCodeDataUrl(qrUrl);

        // Load Cairo font before creating canvas
        await document.fonts.load('900 96px Cairo');
        await document.fonts.load('bold 44px Cairo');
        await new Promise(resolve => setTimeout(resolve, 500));

        // Create canvas manually to have full control over text rendering
        const canvas = document.createElement('canvas');
        canvas.width = 2022;  // 1011 * 2 for scale
        canvas.height = 1276; // 638 * 2 for scale
        const ctx = canvas.getContext('2d');

        // Fill background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Draw border (matching the dialog card style)
        ctx.strokeStyle = '#e0e0e0';
        ctx.lineWidth = 4;
        ctx.strokeRect(0, 0, canvas.width, canvas.height);

        // Load and draw QR code image first (LEFT SIDE to match card)
        const qrImg = new Image();
        qrImg.crossOrigin = 'anonymous';
        
        await new Promise((resolve, reject) => {
          qrImg.onload = () => {
            // Draw QR code on LEFT side - matching card layout
            ctx.drawImage(qrImg, 80, 188, 600, 600);
            resolve();
          };
          qrImg.onerror = reject;
          qrImg.src = qrImageUrl;
        });

        // Set text properties for RIGHT SIDE
        ctx.textAlign = 'right';
        ctx.direction = 'rtl';
        ctx.fillStyle = '#1a1a1a';

        // Draw patient name (large) on RIGHT SIDE - with more padding top
        ctx.font = '900 96px Cairo, Arial, sans-serif';
        ctx.fillText(patientName, canvas.width - 80, 280);

        // Draw sex label and value on RIGHT SIDE
        ctx.font = 'bold 44px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#333';
        const sexLabel = this.$t('patients.sex') || 'الجنس';
        ctx.fillText(sexLabel + ':', canvas.width - 80, 400);
        
        ctx.font = '500 44px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#666';
        const sexLabelWidth = ctx.measureText(sexLabel + ':').width;
        ctx.fillText(patientSex, canvas.width - 100 - sexLabelWidth, 400);

        // Draw QR number label and value on RIGHT SIDE
        ctx.font = 'bold 44px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#333';
        const qrLabel = this.$t('patients.qr_number') || 'رقم المراجع';
        
        // Draw QR number (LTR) - value above label
        ctx.textAlign = 'right';
        ctx.direction = 'ltr';
        ctx.font = 'bold 44px Courier New, monospace';
        ctx.fillStyle = '#666';
        ctx.fillText(patientQR, canvas.width - 80, 480);
        
        // Draw label below value
        ctx.textAlign = 'right';
        ctx.direction = 'rtl';
        ctx.font = 'bold 44px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#333';
        ctx.fillText(qrLabel, canvas.width - 80, 540);

        // Draw bottom text instruction (centered at bottom)
        ctx.textAlign = 'center';
        ctx.direction = 'rtl';
        ctx.font = 'bold 40px Cairo, Arial, sans-serif';
        ctx.fillStyle = '#555';
        ctx.fillText('امسح الـ QR لمشاهدة ملفك الطبي', canvas.width / 2, canvas.height - 100);

        // Convert canvas to blob and download
        canvas.toBlob((blob) => {
          const url = URL.createObjectURL(blob);
          const link = document.createElement('a');
          const cleanName = (this.patient.name || 'patient').replace(/[^a-zA-Z0-9آ-ي\s]/g, '').trim();
          const fileName = `${cleanName}-${new Date().getTime()}.jpg`;
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
        }, 'image/jpeg', 0.95);

      } catch (e) {
        console.error('❌ Error saving card as image:', e);
        
        if (e.message && e.message.includes('html2canvas')) {
          this.$swal.fire({
            title: this.$t('error') || 'خطأ',
            html: `<p>${this.$t('patients.please_install_html2canvas') || 'يرجى تثبيت مكتبة html2canvas'}</p>
                   <code style="display:block; margin-top:10px; padding:10px; background:#f5f5f5; border-radius:4px; direction:ltr;">
                   npm install html2canvas
                   </code>`,
            icon: 'error',
            confirmButtonText: this.$t('close') || 'إغلاق'
          });
        } else {
          this.$swal.fire({
            title: this.$t('error') || 'خطأ',
            text: this.$t('patients.error_saving_image') || 'حدث خطأ أثناء حفظ الصورة',
            icon: 'error',
            confirmButtonText: this.$t('close') || 'إغلاق'
          });
        }
      }
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&display=swap');

/* Patient Card Component Styles */
</style>
