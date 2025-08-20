<template>
  <div>
    <div ref="printSection" class="print-section" style="display: none;">
      <recipeReport :RecipeInfo="RecipeInfo" :rx_img="rx_img" />
    </div>

    <v-form ref="form" v-model="valid">
      <v-card>
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on">
            Click Me
          </v-btn>
        </template>

        <v-toolbar dark color="primary lighten-1 mb-5">
          <v-toolbar-title>
            <h3 style="color:#fff;font-family: 'Cairo'">{{ $t("write_prescription") }}</h3>
          </v-toolbar-title>
          <v-spacer />
          <v-btn @click="close" icon>
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-container>
          <v-card flat>
            <v-layout row wrap>
              <input type="file" style="display: none" ref="image" accept="image/*" @change="onFilePicked">
              <span style="color:#fff">{{ img_name }}</span>

              <v-flex justify="center" class="mb-12">
                <v-card style="width:100%; min-height: 370px;">
                  <v-img :src="rx_img || defaultImageBase64" style="height: 370px;">
                    <v-btn style="padding-right: 24px; position:relative; left: 46px;" @click="pickFile">
                      xx
                      <v-icon style="margin:0px" size="40">fas fa-plus-square</v-icon>
                    </v-btn>
                  </v-img>
                </v-card>
              </v-flex>
            </v-layout>

            <v-layout row wrap>
              <!-- <v-flex xs12 md3 mr-4>
                <div>{{ $t("name") }} :</div>
                <v-select dense :rules="[rules.required]" v-model="RecipeInfo.name"
                  :label="$t('datatable.name')" @change="getItem(RecipeInfo.name)" :items="patients" outlined
                  return-object item-text="name" item-value="id" />
              </v-flex> -->

              <v-flex xs12 md3 mr-4>
                <div>{{ $t("name") }} :</div>
                <v-text-field dense :rules="[rules.required]" v-model="RecipeInfo.name"
                  :label="$t('datatable.name')"  outlined
                   />
              </v-flex>

              <v-flex xs12 md3 mr-4>
                <div>{{ $t("gender") }} :</div>
                <v-select dense v-model="RecipeInfo.sex" :items="sexs" outlined item-text="name" item-value="id" />
              </v-flex>

              <v-flex xs12 md3 mr-4>
                <div>{{ $t("age") }} :</div>
                <v-text-field dense v-model="RecipeInfo.age" outlined :rules="[rules.required]" required />
              </v-flex>


            </v-layout>

            <v-layout row wrap>
              <v-flex xs12>
                <v-autocomplete
                  v-model="selectedNote"
                  :items="noteSuggestions"
                  label="اختر ملاحظة"
                  outlined
                  dense
                  @change="addNote"
                  @input="onInput"
                />
                <v-textarea
                  outlined
                  v-model="RecipeInfo.notes"
                  :label="$t('prescription')"
                />
              </v-flex>
            </v-layout>
          </v-card>
        </v-container>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="#2196f3" @click="print" style="color:#fff">
            {{ $t("print") }}
            <v-icon right dark>fas fa-print</v-icon>
          </v-btn>
          <v-btn color="#4caf50" @click="exportAsImage" style="color:#fff">
            {{ $t("export_as_image") }}
            <v-icon right dark>fas fa-file-image</v-icon>
          </v-btn>
          <!-- <v-btn color="#25D366" @click="sendViaWhatsApp" style="color:#fff">
            {{ $t("send_via_whatsapp") }}
            <v-icon right dark>fab fa-whatsapp</v-icon>
          </v-btn> -->
        </v-card-actions>
      </v-card>
    </v-form>

    <!-- Dialog for adding new note -->
    <v-dialog v-model="dialog" max-width="500px" v-track-dialog>
      <v-card>
        <v-card-title>
          <span class="headline">{{ $t("add_new_note") }}</span>
        </v-card-title>
        <v-card-text>
          <v-text-field v-model="newNote" label="New Note" />
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="dialog = false">{{ $t("cancel") }}</v-btn>
          <v-btn color="blue darken-1" text @click="saveNewNote">{{ $t("save") }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  import recipeReport from "./recipeReport.vue";
  import { EventBus } from "./event-bus.js";
  import html2canvas from 'html2canvas';

  export default {
    mounted() {
    // Example of setting RecipeInfo.notes without null values
    this.updateRecipeNotes();
  },
    data() {
      return {
        valid: false,
        rx_img: '',
        base64Image: '',
        defaultImageBase64: "https://apismartclinicv3.tctate.com/rx1.jpg",
        rules: {
          required: (value) => !!value || this.$t('required'),
          minPhon: (v) => v.length === 13 || this.$t('phone_length'),
          email: (value) => {
            if (value && value.length > 0) {
              const pattern =
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
              return pattern.test(value) || this.$t('invalid_email');
            }
          },
        },
        sexs: [
          { id: 1, name: this.$t("male") },
          { id: 0, name: this.$t("female") },
        ],
        noteSuggestions: [],
        selectedNote:'',
        dialog: false,
        newNote: '',
      };
    },
    props: {
      RecipeInfo: Object,
      CaseCategories: Array,
      recipes: Array,
      patients: Array,
    },
    components: {
      recipeReport,
    },
    methods: {
      pickFile() {
        this.$refs.image.click();
      },
      onFilePicked(event) {
        const files = event.target.files;
        if (files[0] !== undefined) {
          this.imageName = files[0].name;
          if (this.imageName.lastIndexOf('.') <= 0) {
            return;
          }
          this.imageFile = files[0];
          this.rx_img = URL.createObjectURL(files[0]); // Update rx_img directly
          this.imgname = files[0].name;

          const reader = new FileReader();
          reader.onload = (e) => {
            this.base64Image = e.target.result;
            this.$emit('update:rx_img', this.base64Image); // Emit the updated image
          };
          reader.readAsDataURL(files[0]); // Start reading the file as a data URL
        } else {
          this.imageName = '';
          this.imageFile = '';
          this.base64Image = '';
        }
      },
      updateRecipeNotes() {
      // Example of updating RecipeInfo.notes and removing null values
      this.RecipeInfo.notes = this.RecipeInfo.notes ? this.RecipeInfo.notes.filter(n => n !== null) : [];
    },
      getClinicLogo() {
        try {
          // Debug: log the entire store state
          console.log('Full AdminInfo:', this.$store.state.AdminInfo);
          
          const clinicInfo = this.$store.getters.getClinicInfo;
          console.log('Clinic Info from getter:', clinicInfo);
          
          if (clinicInfo && clinicInfo.logo) {
            console.log('Clinic Logo URL from getter:', clinicInfo.logo);
            return clinicInfo.logo;
          }
          
          // Fallback: check AdminInfo directly
          const adminInfo = this.$store.state.AdminInfo;
          if (adminInfo && adminInfo.clinics_info) {
            console.log('AdminInfo clinics_info:', adminInfo.clinics_info);
            
            if (adminInfo.clinics_info.logo) {
              console.log('Logo from AdminInfo:', adminInfo.clinics_info.logo);
              return adminInfo.clinics_info.logo;
            }
          }
          
          // Check if it's stored differently
          if (adminInfo && adminInfo.photo) {
            console.log('Using admin photo as logo:', adminInfo.photo);
            return adminInfo.photo;
          }
          
          console.log('No clinic logo found - using default');
          return null;
        } catch (error) {
          console.error('Error getting clinic logo:', error);
          return null;
        }
      },
      getClinicName() {
        try {
          console.log('Getting clinic name...');
          
          // Try store getters first
          const clinicName = this.$store.getters.getClinicName;
          console.log('Clinic name from getter:', clinicName);
          
          if (clinicName && clinicName !== '') {
            return clinicName;
          }
          
          // Fallback: check AdminInfo directly
          const adminInfo = this.$store.state.AdminInfo;
          if (adminInfo && adminInfo.clinics_info) {
            console.log('Clinic info:', adminInfo.clinics_info);
            
            if (adminInfo.clinics_info.name) {
              console.log('Clinic name from AdminInfo:', adminInfo.clinics_info.name);
              return adminInfo.clinics_info.name;
            }
          }
          
          // Use admin name as fallback
          if (adminInfo && adminInfo.name) {
            console.log('Using admin name:', adminInfo.name);
            return adminInfo.name;
          }
          
          console.log('Using default clinic name');
          return 'العيادة';
        } catch (error) {
          console.error('Error getting clinic name:', error);
          return 'العيادة';
        }
      },
      getClinicPhone() {
        try {
          console.log('Getting clinic phone...');
          
          // Try store getters first
          const clinicPhone = this.$store.getters.getClinicPhone;
          console.log('Clinic phone from getter:', clinicPhone);
          
          if (clinicPhone && clinicPhone !== '') {
            return clinicPhone;
          }
          
          // Fallback: check AdminInfo directly
          const adminInfo = this.$store.state.AdminInfo;
          
          if (adminInfo && adminInfo.phone) {
            console.log('Phone from AdminInfo.phone:', adminInfo.phone);
            return adminInfo.phone;
          }
          
          if (adminInfo && adminInfo.clinics_info) {
            if (adminInfo.clinics_info.phone) {
              console.log('Phone from clinics_info.phone:', adminInfo.clinics_info.phone);
              return adminInfo.clinics_info.phone;
            }
            
            if (adminInfo.clinics_info.whatsapp_phone) {
              console.log('Phone from clinics_info.whatsapp_phone:', adminInfo.clinics_info.whatsapp_phone);
              return adminInfo.clinics_info.whatsapp_phone;
            }
          }
          
          console.log('No phone found');
          return '';
        } catch (error) {
          console.error('Error getting clinic phone:', error);
          return '';
        }
      },
      addNote() {
        if (this.selectedNote) {
          
          this.RecipeInfo.notes += (this.RecipeInfo.notes ? '\n' : '') + this.selectedNote;
          this.selectedNote = '';
        }
      },
      onInput(value) {
        if (!this.noteSuggestions.includes(value)) {
          this.dialog = true;
          this.newNote = value;
        }
      },
      saveNewNote() {
        if (this.newNote) {
          this.noteSuggestions.push(this.newNote);
          this.selectedNote = this.newNote;
          this.dialog = false;
          this.newNote = '';
        }
      },
      async fetchNoteSuggestions() {
        try {
          const response = await this.axios.get('/getrecipesItem', {
              headers: {
                "Content-Type": "multipart/form-data",
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.state.AdminInfo.token}`,
              },
            });
          this.noteSuggestions = response.data.map(item => item.name);
        } catch (error) {
          console.error('Error fetching note suggestions:', error);
        }
      },
      async printContent() {
        try {
          // Detect if we're on mobile
          const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
          
          if (isMobile) {
            // For mobile devices, use a different approach
            await this.mobileFriendlyPrint();
          } else {
            // Desktop approach with popup window
            await this.desktopPrint();
          }
          
        } catch (error) {
          console.error('Print error:', error);
          // More specific error handling
          if (error.name === 'NotAllowedError' || error.message.includes('popup')) {
            this.$swal.fire({
              icon: 'warning',
              title: 'تنبيه',
              text: 'يرجى السماح للنوافذ المنبثقة أو استخدام "تصدير كصورة" بدلاً من الطباعة.',
              showCancelButton: true,
              confirmButtonText: 'تصدير كصورة',
              cancelButtonText: 'إلغاء'
            }).then((result) => {
              if (result.isConfirmed) {
                this.exportAsImage();
              }
            });
          } else {
            this.$swal.fire({
              icon: 'error',
              title: 'خطأ في الطباعة',
              text: 'حدث خطأ أثناء محاولة الطباعة. يرجى استخدام "تصدير كصورة" كبديل.',
              showCancelButton: true,
              confirmButtonText: 'تصدير كصورة',
              cancelButtonText: 'إلغاء'
            }).then((result) => {
              if (result.isConfirmed) {
                this.exportAsImage();
              }
            });
          }
        }
      },
      
      async mobileFriendlyPrint() {
        try {
          // Clean up any existing print elements first
          this.cleanupPrintElements();
          
          // Create print styles
          const printStyles = document.createElement('style');
          printStyles.id = 'mobile-print-styles';
          printStyles.innerHTML = `
            @media print {
              body * {
                visibility: hidden;
              }
              #mobile-print-content, #mobile-print-content * {
                visibility: visible;
              }
              #mobile-print-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block !important;
                font-size: 14px;
                padding: 10px;
              }
              .rx-container {
                border: 1px solid #333 !important;
                border-radius: 5px !important;
                page-break-inside: avoid;
              }
              .rx-header img {
                max-height: 80px !important;
              }
              .clinic-logo-footer {
                width: 60px !important;
                height: 60px !important;
              }
              .prescription-content {
                min-height: 150px !important;
                font-size: 16px !important;
              }
            }
            
            #mobile-print-content {
              display: none;
              font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
              background: white;
              direction: rtl;
              padding: 20px;
              font-size: 16px;
              line-height: 1.6;
            }
            
            #mobile-print-content .rx-container {
              max-width: 100%;
              margin: 0 auto;
              background: white;
              border: 2px solid #333;
              border-radius: 10px;
              overflow: hidden;
            }
            
            #mobile-print-content .rx-header {
              background: #f8f9fa;
              padding: 15px;
              text-align: center;
              border-bottom: 2px solid #333;
            }
            
            #mobile-print-content .rx-header img {
              max-width: 100%;
              height: auto;
              max-height: 120px;
              border-radius: 5px;
            }
            
            #mobile-print-content .rx-symbol {
              font-size: 48px;
              font-weight: bold;
              color: #007bff;
              margin: 10px 0;
            }
            
            #mobile-print-content .rx-body {
              padding: 20px;
            }
            
            #mobile-print-content .patient-info {
              background: #f1f3f4;
              padding: 15px;
              border-radius: 8px;
              margin-bottom: 20px;
              border-right: 4px solid #007bff;
            }
            
            #mobile-print-content .patient-info h3 {
              color: #333;
              margin-bottom: 10px;
              font-size: 18px;
            }
            
            #mobile-print-content .info-row {
              display: flex;
              justify-content: space-between;
              margin-bottom: 8px;
              font-size: 16px;
            }
            
            #mobile-print-content .info-label {
              font-weight: bold;
              color: #555;
            }
            
            #mobile-print-content .prescription-content {
              background: white;
              border: 1px solid #ddd;
              border-radius: 8px;
              padding: 20px;
              margin: 20px 0;
              min-height: 200px;
              font-size: 18px;
              line-height: 1.8;
            }
            
            #mobile-print-content .prescription-label {
              font-weight: bold;
              color: #007bff;
              font-size: 20px;
              margin-bottom: 15px;
              display: block;
              border-bottom: 1px solid #007bff;
              padding-bottom: 5px;
            }
            
            #mobile-print-content .rx-footer {
              text-align: center;
              padding: 15px;
              border-top: 1px solid #ddd;
              background: #f8f9fa;
              font-size: 14px;
              color: #666;
            }
            
            #mobile-print-content .clinic-info {
              display: flex;
              align-items: center;
              justify-content: center;
              gap: 15px;
              margin-bottom: 10px;
              flex-wrap: wrap;
            }
            
            #mobile-print-content .clinic-logo-footer {
              width: 80px;
              height: 80px;
              border-radius: 50%;
              object-fit: cover;
              border: 2px solid #007bff;
            }
            
            #mobile-print-content .clinic-details {
              text-align: center;
            }
            
            #mobile-print-content .clinic-name {
              font-weight: bold;
              color: #333;
              font-size: 16px;
              margin-bottom: 5px;
            }
            
            #mobile-print-content .clinic-phone {
              color: #007bff;
              font-size: 14px;
              direction: ltr;
            }
          `;
          document.head.appendChild(printStyles);
          
          // Create print content div
          const tempDiv = document.createElement('div');
          tempDiv.id = 'mobile-print-content';
          
          // Get clinic info with fallbacks
          const clinicLogo = this.getClinicLogo();
          const clinicName = this.getClinicName();
          const clinicPhone = this.getClinicPhone();
          const currentDate = new Date().toLocaleDateString('en-US');
          
          const rxContent = `
            <div class="rx-container">
              <div class="rx-header">
                <img src="${this.rx_img || this.defaultImageBase64}" alt="Header Image" />
                <div class="rx-symbol">℞</div>
              </div>
              
              <div class="rx-body">
                <div class="patient-info">
                  <h3>بيانات المريض</h3>
                  <div class="info-row">
                    <span class="info-label">الاسم:</span>
                    <span>${this.RecipeInfo.name || 'غير محدد'}</span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">العمر:</span>
                    <span>${this.RecipeInfo.age || 'غير محدد'}</span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">الجنس:</span>
                    <span>${this.RecipeInfo.sex === 1 ? 'ذكر' : this.RecipeInfo.sex === 0 ? 'أنثى' : 'غير محدد'}</span>
                  </div>
                </div>
                
                <div class="prescription-content">
                  <span class="prescription-label">الوصفة الطبية:</span>
                  <div>${(this.RecipeInfo.notes || 'لا توجد وصفة مكتوبة').replace(/\n/g, '<br>')}</div>
                </div>
              </div>
              
              <div class="rx-footer">
                <div class="clinic-info">
                  ${clinicLogo ? `<img src="${clinicLogo}" alt="Clinic Logo" class="clinic-logo-footer" />` : ''}
                  <div class="clinic-details">
                    <div class="clinic-name">${clinicName}</div>
                    ${clinicPhone ? `<div class="clinic-phone">${clinicPhone}</div>` : ''}
                  </div>
                </div>
                <p>تاريخ الوصفة: ${currentDate} ميلادي</p>
                <p>يرجى اتباع تعليمات الطبيب المعالج</p>
              </div>
            </div>
          `;
          
          tempDiv.innerHTML = rxContent;
          document.body.appendChild(tempDiv);
          
          // Wait for images to load
          const images = tempDiv.querySelectorAll('img');
          if (images.length > 0) {
            await Promise.all(Array.from(images).map(img => {
              return new Promise((resolve) => {
                if (img.complete) {
                  resolve();
                } else {
                  img.onload = () => resolve();
                  img.onerror = () => resolve();
                  // Timeout after 3 seconds
                  setTimeout(() => resolve(), 3000);
                }
              });
            }));
          }
          
          // Small delay to ensure everything is rendered
          await new Promise(resolve => setTimeout(resolve, 500));
          
          // Show print dialog
          window.print();
          
          // Clean up after a delay
          setTimeout(() => {
            this.cleanupPrintElements();
          }, 2000);
          
        } catch (error) {
          console.error('Mobile print error:', error);
          // Clean up and fallback to export as image
          this.cleanupPrintElements();
          throw error;
        }
      },
      
      cleanupPrintElements() {
        const printContent = document.getElementById('mobile-print-content');
        const printStyles = document.getElementById('mobile-print-styles');
        
        if (printContent && printContent.parentNode) {
          printContent.parentNode.removeChild(printContent);
        }
        if (printStyles && printStyles.parentNode) {
          printStyles.parentNode.removeChild(printStyles);
        }
      },
      
      async desktopPrint() {
        // Check if popup is allowed
        const testWindow = window.open('', '_blank', 'width=1,height=1');
        if (!testWindow || testWindow.closed || typeof testWindow.closed == 'undefined') {
          throw new Error('Popup blocked');
        }
        testWindow.close();
        
        // Create desktop print window
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        
        if (!printWindow) {
          throw new Error('Could not open print window');
        }
        
        const currentDate = new Date().toLocaleDateString('en-US');
        const rxContent = `
          <!DOCTYPE html>
          <html dir="rtl" lang="ar">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>وصفة طبية</title>
            <style>
              * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
              }
              
              body {
                font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: white;
                direction: rtl;
                padding: 20px;
                font-size: 16px;
                line-height: 1.6;
              }
              
              .rx-container {
                max-width: 100%;
                margin: 0 auto;
                background: white;
                border: 2px solid #333;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
              }
              
              .rx-header {
                background: #f8f9fa;
                padding: 15px;
                text-align: center;
                border-bottom: 2px solid #333;
              }
              
              .rx-header img {
                max-width: 100%;
                height: auto;
                max-height: 120px;
                border-radius: 5px;
              }
              
              .rx-symbol {
                font-size: 48px;
                font-weight: bold;
                color: #007bff;
                margin: 10px 0;
              }
              
              .rx-body {
                padding: 20px;
              }
              
              .patient-info {
                background: #f1f3f4;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-right: 4px solid #007bff;
              }
              
              .patient-info h3 {
                color: #333;
                margin-bottom: 10px;
                font-size: 18px;
              }
              
              .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                font-size: 16px;
              }
              
              .info-label {
                font-weight: bold;
                color: #555;
              }
              
              .prescription-content {
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                min-height: 200px;
                font-size: 18px;
                line-height: 1.8;
              }
              
              .prescription-label {
                font-weight: bold;
                color: #007bff;
                font-size: 20px;
                margin-bottom: 15px;
                display: block;
                border-bottom: 1px solid #007bff;
                padding-bottom: 5px;
              }
              
              .rx-footer {
                text-align: center;
                padding: 15px;
                border-top: 1px solid #ddd;
                background: #f8f9fa;
                font-size: 14px;
                color: #666;
              }
              
              .clinic-info {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
                margin-bottom: 10px;
                flex-wrap: wrap;
              }
              
              .clinic-logo-footer {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: fill;
                border: 2px solid #007bff;
              }
              
              .clinic-details {
                text-align: center;
              }
              
              .clinic-name {
                font-weight: bold;
                color: #333;
                font-size: 16px;
                margin-bottom: 5px;
              }
              
              .clinic-phone {
                color: #007bff;
                font-size: 14px;
                direction: ltr;
              }
              
              .print-buttons {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 10px;
                z-index: 1000;
              }
              
              .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
                font-weight: bold;
                color: white;
              }
              
              .btn-print {
                background: #007bff;
              }
              
              .btn-close {
                background: #6c757d;
              }
              
              .btn:hover {
                opacity: 0.9;
              }
              
              @media print {
                .print-buttons {
                  display: none !important;
                }
                
                .rx-container {
                  border: 1px solid #333;
                  box-shadow: none;
                  margin: 0;
                  padding: 0;
                }
                
                body {
                  padding: 0;
                }
              }
            </style>
          </head>
          <body>
            <div class="rx-container">
              <div class="rx-header">
                <img src="${this.rx_img || this.defaultImageBase64}" alt="Header Image" />
                <div class="rx-symbol">℞</div>
              </div>
              
              <div class="rx-body">
                <div class="patient-info">
                  <h3>بيانات المريض</h3>
                  <div class="info-row">
                    <span class="info-label">الاسم:</span>
                    <span>${this.RecipeInfo.name || 'غير محدد'}</span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">العمر:</span>
                    <span>${this.RecipeInfo.age || 'غير محدد'}</span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">الجنس:</span>
                    <span>${this.RecipeInfo.sex === 1 ? 'ذكر' : this.RecipeInfo.sex === 0 ? 'أنثى' : 'غير محدد'}</span>
                  </div>
                </div>
                
                <div class="prescription-content">
                  <span class="prescription-label">الوصفة الطبية:</span>
                  <div>${(this.RecipeInfo.notes || 'لا توجد وصفة مكتوبة').replace(/\n/g, '<br>')}</div>
                </div>
              </div>
              
              <div class="rx-footer">
                <div class="clinic-info">
                  ${this.getClinicLogo() ? `<img src="${this.getClinicLogo()}" alt="Clinic Logo" class="clinic-logo-footer" />` : ''}
                  <div class="clinic-details">
                    <div class="clinic-name">${this.getClinicName()}</div>
                    ${this.getClinicPhone() ? `<div class="clinic-phone">${this.getClinicPhone()}</div>` : ''}
                  </div>
                </div>
                <p>تاريخ الوصفة: ${currentDate} ميلادي</p>
                <p>يرجى اتباع تعليمات الطبيب المعالج</p>
              </div>
            </div>
            
            <div class="print-buttons">
              <button class="btn btn-print" onclick="window.print()">طباعة</button>
              <button class="btn btn-close" onclick="window.close()">إغلاق</button>
            </div>
            
            <script>
              // Auto-focus for better mobile experience
              window.addEventListener('load', function() {
                setTimeout(function() {
                  if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) {
                    // On mobile, automatically trigger print dialog after a short delay
                    window.print();
                  }
                }, 500);
              });
            </script>
          </body>
          </html>
        `;
        
        printWindow.document.write(rxContent);
        printWindow.document.close();
      },
      async exportAsImage() {
        try {
          // Get clinic information
          const clinicLogo = this.getClinicLogo();
          const clinicName = this.getClinicName();
          const clinicPhone = this.getClinicPhone();
          const currentDate = new Date().toLocaleDateString('en-US');
          
          console.log('Export - Clinic Logo:', clinicLogo);
          console.log('Export - Clinic Name:', clinicName);
          console.log('Export - Clinic Phone:', clinicPhone);
          
          // Create the same styled content as the print function
          const tempDiv = document.createElement('div');
          tempDiv.style.position = 'absolute';
          tempDiv.style.left = '-9999px';
          tempDiv.style.top = '-9999px';
          tempDiv.style.width = '800px';
          tempDiv.style.backgroundColor = 'white';
          
          // Use a default logo if none is available
          const defaultLogo = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiMwMDdiZmYiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJ3aGl0ZSI+CjxwYXRoIGQ9Im0xMi4zNSAxNi44NS0uMTEuMTFhLjU1LjU1IDAgMCAwLS4xOC40N3Y2LjI1YzAgLjM4LjMxLjY5LjY5LjY5cy42OS0uMzEuNjktLjY5di02LjI1YS41NS41NSAwIDAgMC0uMTgtLjQ3bC0uMTEtLjExaC4wMXptNC44Mi0xLjU0YS4yNS4yNSAwIDAgMC0uMjktLjA3Yy0uMDYuMDEtLjA5LjA4LS4wOS4xNHYuNjFoLS44M2wuNDMtLjkxVjEzaC0uNjV2MS4wOWwtLjQzLjkxaC0uODN2LS42MWMwLS4wNi0uMDMtLjEzLS4wOS0uMTRhLjI1LjI1IDAgMCAwLS4yOS4wN2MtLjc3Ljc2LTEuNzUgMS4yNS0yLjg0IDEuMjVzLTIuMDctLjQ5LTIuODQtMS4yNWEuMjUuMjUgMCAwIDAtLjI5LS4wN2MtLjA2LjAxLS4wOS4wOC0uMDkuMTR2Ni4zNmMwIC4zOC4zMS42OS42OS42OXMuNjktLjMxLjY5LS42OXYtNS41N2MuODMuNiAxLjg1Ljk0IDIuOTYuOTQgMS4xMSAwIDIuMTMtLjM0IDIuOTYtLjk0djUuNTdjMCAuMzguMzEuNjkuNjkuNjlzLjY5LS4zMS42OS0uNjl2LTYuMzZ6bTAuMTMtMy42M2MwIDIuMjEtMi40OSA0LTUuNTcgNHMtNS41Ny0xLjc5LTUuNTctNHYtMi4zNWMwLTIuMjEgMi40OS00IDUuNTctNHM1LjU3IDEuNzkgNS41NyA0djIuMzV6Ii8+Cjwvc3ZnPgo8L3N2Zz4K';
          
          const logoToUse = clinicLogo || defaultLogo;
          
          const rxContent = `
            <div style="
              font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
              background: white;
              direction: rtl;
              padding: 20px;
              font-size: 16px;
              line-height: 1.6;
              width: 800px;
              min-height: 1000px;
            ">
              <div style="
                max-width: 100%;
                margin: 0 auto;
                background: white;
                border: 2px solid #333;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
              ">
                <div style="
                  background: #f8f9fa;
                  padding: 15px;
                  text-align: center;
                  border-bottom: 2px solid #333;
                ">
                  <div style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 15px;
                    padding: 0 20px;
                  ">
                    <img src="${logoToUse}" alt="Clinic Logo" style="
                      width: 80px;
                      height: 80px;
                      border-radius: 50%;
                      object-fit: cover;
                      border: 2px solid #007bff;
                    " />
                    <div style="text-align: center; flex-grow: 1;">
                      <div style="
                        font-weight: bold;
                        color: #333;
                        font-size: 20px;
                        margin-bottom: 5px;
                      ">${clinicName}</div>
                      ${clinicPhone ? `<div style="
                        color: #007bff;
                        font-size: 16px;
                        direction: ltr;
                      ">${clinicPhone}</div>` : ''}
                    </div>
                    <img src="${logoToUse}" alt="Clinic Logo" style="
                      width: 80px;
                      height: 80px;
                      border-radius: 50%;
                      object-fit: cover;
                      border: 2px solid #007bff;
                    " />
                  </div>
                  <img src="${this.rx_img || this.defaultImageBase64}" alt="Header Image" style="
                    max-width: 100%;
                    height: auto;
                    max-height: 120px;
                    border-radius: 5px;
                    margin-bottom: 10px;
                  " />
                  <div style="
                    font-size: 48px;
                    font-weight: bold;
                    color: #007bff;
                    margin: 10px 0;
                  ">℞</div>
                </div>
                
                <div style="padding: 20px;">
                  <div style="
                    background: #f1f3f4;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-right: 4px solid #007bff;
                  ">
                    <h3 style="
                      color: #333;
                      margin-bottom: 10px;
                      font-size: 18px;
                    ">بيانات المريض</h3>
                    <div style="
                      display: flex;
                      justify-content: space-between;
                      margin-bottom: 8px;
                      font-size: 16px;
                    ">
                      <span style="font-weight: bold; color: #555;">الاسم:</span>
                      <span>${this.RecipeInfo.name || 'غير محدد'}</span>
                    </div>
                    <div style="
                      display: flex;
                      justify-content: space-between;
                      margin-bottom: 8px;
                      font-size: 16px;
                    ">
                      <span style="font-weight: bold; color: #555;">العمر:</span>
                      <span>${this.RecipeInfo.age || 'غير محدد'}</span>
                    </div>
                    <div style="
                      display: flex;
                      justify-content: space-between;
                      margin-bottom: 8px;
                      font-size: 16px;
                    ">
                      <span style="font-weight: bold; color: #555;">الجنس:</span>
                      <span>${this.RecipeInfo.sex === 1 ? 'ذكر' : this.RecipeInfo.sex === 0 ? 'أنثى' : 'غير محدد'}</span>
                    </div>
                  </div>
                  
                  <div style="
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    min-height: 200px;
                    font-size: 18px;
                    line-height: 1.8;
                  ">
                    <span style="
                      font-weight: bold;
                      color: #007bff;
                      font-size: 20px;
                      margin-bottom: 15px;
                      display: block;
                      border-bottom: 1px solid #007bff;
                      padding-bottom: 5px;
                    ">الوصفة الطبية:</span>
                    <div>${(this.RecipeInfo.notes || 'لا توجد وصفة مكتوبة').replace(/\n/g, '<br>')}</div>
                  </div>
                </div>
                
                <div style="
                  text-align: center;
                  padding: 15px;
                  border-top: 1px solid #ddd;
                  background: #f8f9fa;
                  font-size: 14px;
                  color: #666;
                ">
                  <div style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 15px;
                    margin-bottom: 10px;
                    flex-wrap: wrap;
                  ">
                    ${logoToUse ? `<img src="${logoToUse}" alt="Clinic Logo" style="
                      width: 80px;
                      height: 80px;
                      border-radius: 50%;
                      object-fit: fill;
                      border: 2px solid #007bff;
                    " />` : ''}
                    <div style="text-align: center;">
                      <div style="
                        font-weight: bold;
                        color: #333;
                        font-size: 16px;
                        margin-bottom: 5px;
                      ">${clinicName}</div>
                      ${clinicPhone ? `<div style="
                        color: #007bff;
                        font-size: 14px;
                        direction: ltr;
                      ">${clinicPhone}</div>` : ''}
                    </div>
                  </div>
                  <p>تاريخ الوصفة: ${currentDate} ميلادي</p>
                  <p>يرجى اتباع تعليمات الطبيب المعالج</p>
                </div>
              </div>
            </div>
          `;
          
          tempDiv.innerHTML = rxContent;
          document.body.appendChild(tempDiv);
          
          // Wait for images to load with better error handling
          const images = tempDiv.querySelectorAll('img');
          await Promise.all(Array.from(images).map(img => {
            return new Promise((resolve) => {
              if (img.complete) {
                resolve();
              } else {
                img.onload = resolve;
                img.onerror = () => {
                  console.warn('Image failed to load:', img.src);
                  resolve(); // Continue even if image fails
                };
                // Set a timeout for images
                setTimeout(resolve, 3000);
              }
            });
          }));
          
          // Wait a bit more for rendering
          await new Promise(resolve => setTimeout(resolve, 1000));
          
          // Capture the image with better options
          const canvas = await html2canvas(tempDiv, {
            useCORS: true,
            allowTaint: true,
            scale: 2,
            width: 800,
            height: tempDiv.scrollHeight,
            backgroundColor: 'white',
            logging: true, // Enable logging for debugging
            foreignObjectRendering: true,
            imageTimeout: 5000,
            onclone: function(clonedDoc) {
              // Ensure all images are properly set in the cloned document
              const clonedImages = clonedDoc.querySelectorAll('img');
              clonedImages.forEach(img => {
                if (!img.src || img.src === '') {
                  img.src = defaultLogo;
                }
              });
            }
          });
          
          // Remove the temporary element
          document.body.removeChild(tempDiv);
          
          // Download the image
          const link = document.createElement('a');
          link.download = `prescription-${Date.now()}.png`;
          link.href = canvas.toDataURL('image/png');
          link.click();
          
          // Save the recipe data
          const formData = new FormData();
          
          // Check if case categories exists before accessing its id
          if (this.RecipeInfo.case && this.RecipeInfo.case.case_categories && this.RecipeInfo.case.case_categories.id) {
            formData.append('case_categores_id', this.RecipeInfo.case.case_categories.id);
          }
          
          formData.append('notes', this.RecipeInfo.notes || '');
          formData.append('patient_id', this.RecipeInfo.name || '');
          
          if (this.imageFile) {
            formData.append('rx_img', this.imageFile);
          }

          await this.axios.post("recipes", formData, {
            headers: {
              "Content-Type": "multipart/form-data",
              Accept: "application/json",
              Authorization: `Bearer ${this.$store.state.AdminInfo.token}`,
            },
          });
          
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: this.$t("Added"),
            showConfirmButton: false,
            timer: 1500
          });
          
        } catch (error) {
          console.error('Error exporting image:', error);
          this.$swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: 'حدث خطأ أثناء تصدير الصورة. يرجى المحاولة مرة أخرى.'
          });
        }
      },
      captureImage(printSection) {
        printSection.style.display = 'block';
        this.$nextTick(() => {
          html2canvas(printSection, {
            useCORS: true,
            proxy: 'https://thingproxy.freeboard.io/fetch/', // Different public proxy for testing
          }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'prescription.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            printSection.style.display = 'none';
          });
        });
      },
      async sendViaWhatsApp() {
        const printSection = this.$refs.printSection;
        if (!this.RecipeInfo.rx_img) {
          this.rx_img = this.defaultImageBase64;
        } else if (this.RecipeInfo.rx_img && !this.RecipeInfo.rx_img.startsWith('data:image')) {
          const response = await fetch(this.RecipeInfo.rx_img);
          const blob = await response.blob();
          const reader = new FileReader();
          reader.onloadend = () => {
            this.rx_img = reader.result;
            this.uploadImageAndSendLink(printSection);
          };
          reader.readAsDataURL(blob);
        } else {
          this.uploadImageAndSendLink(printSection);
        }
      },
      async uploadImageAndSendLink(printSection) {
        printSection.style.display = 'block';
        this.$nextTick(async () => {
          const canvas = await html2canvas(printSection, {
            useCORS: true,
            proxy: 'https://thingproxy.freeboard.io/fetch/', // Different public proxy for testing
          });
          const imageData = canvas.toDataURL('image/png');
          const formData = new FormData();
          formData.append('image', imageData);

          try {
            const response = await this.axios.post('/upload', formData, {
              headers: {
                'Content-Type': 'multipart/form-data',
              },
            });
            const imageUrl = response.data.url;
            const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(imageUrl)}`;
            window.open(whatsappUrl, '_blank');
          } catch (error) {
            console.error('Error uploading image:', error);
          } finally {
            printSection.style.display = 'none';
          }
        });
      },
      getItem(id) {
        const patient = this.patients.find((p) => p.id === id);
        if (patient) {
          this.RecipeInfo = {
            case: {
              patient_id: patient.name,
              case_categories: patient.case_categores_id,
            },
            sex: patient.sex,
            age: patient.age,
            notes: "",
          };
        }
      },
      close() {
        this.RecipeInfo = {
          case: {
            patient_id: "",
          },
          sex: "",
          age: "",
          name: "",
          notes: "",
          rx_img: ''
        };
        EventBus.$emit("changeStatusCloseField", false);
      },
      async print() {
       
        if (this.$refs.form.validate()) {
          const formData = new FormData();
          
          // Check if case categories exists before accessing its id
          if (this.RecipeInfo.case && this.RecipeInfo.case.case_categories && this.RecipeInfo.case.case_categories.id) {
            formData.append('case_categores_id', this.RecipeInfo.case.case_categories.id);
          } else {
            // Use a default value or skip this field if not required
            console.warn('Case categories not found, skipping case_categores_id');
          }
          
          formData.append('notes', this.RecipeInfo.notes || '');
          formData.append('patient_id', this.RecipeInfo.name || '');
          
          if (this.imageFile) {
            formData.append('rx_img', this.imageFile);
          }

          if (this.base64Image) {
            this.rx_img = this.base64Image;
          }
          else if (this.RecipeInfo.rx_img !== null) {
            this.rx_img = this.RecipeInfo.rx_img;
          }
          else {
            this.rx_img = this.defaultImageBase64;
          }

          try {
            const response = await this.axios.post("recipes", formData, {
              headers: {
                "Content-Type": "multipart/form-data",
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.state.AdminInfo.token}`,
              },
            });
            response
            this.$swal.fire({
              position: "top-end",
              icon: "success",
              title: this.$t("Added"),
              showConfirmButton: false,
              timer: 1500
            });

            this.printContent();
            this.close();
          } catch (error) {
            console.error('Error uploading image:', error);
            this.$swal.fire({
              icon: 'error',
              title: 'خطأ',
              text: 'حدث خطأ أثناء حفظ الوصفة. يرجى المحاولة مرة أخرى.'
            });
          }
        }
      },
    },
    async created() {
      this.fetchNoteSuggestions();
      // Convert default image URL to base64
      const response = await fetch('https://smartclinic.tctate.com/rx_header.jpeg');
      const blob = await response.blob();
      const reader = new FileReader();
      reader.onloadend = () => {
        this.defaultImageBase64 = reader.result;
      };
      reader.readAsDataURL(blob);
    },
  };
</script>

<style>
  .rexipe_logo {
    height: 114px;
    margin-bottom: 27px;
    margin-right: 71px;
  }
</style>