<template>
  <div>
    <div ref="printSection" class="print-section" style="display: none;">
      <recipeReport :RecipeInfo="RecipeInfo" :rx_img="getClinicLogo()" />
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
                  <v-img :src="getClinicLogo()" style="height: 370px;">
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

              <v-flex xs12 md4 mr-4>
                <div>{{ $t("name") }} :</div>
                <v-text-field dense :rules="[rules.required]" v-model="RecipeInfo.name"
                  :label="$t('datatable.name')"  outlined
                   />
              </v-flex>

              <v-flex xs12 md4 mr-4>
                <div>{{ $t("gender") }} :</div>
                <v-select dense v-model="RecipeInfo.sex" :items="sexs" outlined item-text="name" item-value="id" />
              </v-flex>

              <v-flex xs12 md3 mr-4>
                <div>{{ $t("age") }} :</div>
                <v-text-field dense v-model="RecipeInfo.age" outlined :rules="[rules.required]" required />
              </v-flex>

              <!-- <v-flex xs12 md3 mr-4>
                <div>{{ $t("case") }} :</div>
                <v-select dense :rules="[rules.required]" v-model="RecipeInfo.case.case_categories"
                  :items="CaseCategories" outlined return-object item-text="name_ar" item-value="id" />
              </v-flex> -->
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
        defaultImageBase64: 'https://apismartclinicv3.tctate.com/rx1.jpg',
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
      getClinicLogo() {
        // Get clinic logo from Vuex store
        return 'https://apismartclinicv3.tctate.com/rx1.jpg';
      },
      updateRecipeNotes() {
      // Example of updating RecipeInfo.notes and removing null values
      this.RecipeInfo.notes = this.RecipeInfo.notes ? this.RecipeInfo.notes.filter(n => n !== null) : [];
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
          const response = await this.axios.get('https://apismartclinicv3.tctate.com/api/getrecipesItem', {
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
      printContent() {
        try {
          // Detect if we're on mobile
          const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
          
          if (isMobile) {
            // For mobile devices, use simple approach
            this.mobileFriendlyPrint();
          } else {
            // Desktop approach
            this.desktopPrint();
          }
          
        } catch (error) {
          console.error('Print error:', error);
          // Fallback to export as image
          this.$swal.fire({
            icon: 'warning',
            title: 'خطأ في الطباعة',
            text: 'حدث خطأ أثناء محاولة الطباعة. هل تريد تصدير الوصفة كصورة بدلاً من ذلك؟',
            showCancelButton: true,
            confirmButtonText: 'تصدير كصورة',
            cancelButtonText: 'إلغاء'
          }).then((result) => {
            if (result.isConfirmed) {
              this.exportAsImage();
            }
          });
        }
      },
      
      getClinicName() {
        try {
          // Try to get clinic name from store
          if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.clinics_info && this.$store.state.AdminInfo.clinics_info.name) {
            return this.$store.state.AdminInfo.clinics_info.name;
          }
          // Fallback to admin name
          if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.name) {
            return this.$store.state.AdminInfo.name;
          }
          return 'العيادة';
        } catch (error) {
          console.error('Error getting clinic name:', error);
          return 'العيادة';
        }
      },
      
      getClinicPhone() {
        try {
          // Try to get clinic phone from store
          if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.clinics_info && this.$store.state.AdminInfo.clinics_info.phone) {
            return this.$store.state.AdminInfo.clinics_info.phone;
          }
          // Fallback to admin phone
          if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.phone) {
            return this.$store.state.AdminInfo.phone;
          }
          return '';
        } catch (error) {
          console.error('Error getting clinic phone:', error);
          return '';
        }
      },
      
      getClinicAddress() {
        try {
          // Try to get clinic address from store
          if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.clinics_info.address) {
            return this.$store.state.AdminInfo.clinics_info.address;
          }
          return '';
        } catch (error) {
          console.error('Error getting clinic address:', error);
          return '';
        }
      },
      
      getClinicRegNum() {
        try {
          // Try to get clinic registration number from store
          if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.clinics_info.clinic_reg_num) {
            return this.$store.state.AdminInfo.clinics_info.clinic_reg_num;
          }
          return '';
        } catch (error) {
          console.error('Error getting clinic registration number:', error);
          return '';
        }
      },
      
      mobileFriendlyPrint() {
        try {
          // For mobile devices, create an in-page print preview
          this.showMobilePrintPreview();
        } catch (error) {
          console.error('Mobile print error:', error);
          // If all else fails, show the export option
          throw error;
        }
      },
      
      showMobilePrintPreview() {
        // Create a full-screen overlay with the print preview
        const printHTML = this.buildMobilePrintHTML();
        
        // Create overlay container
        const overlay = document.createElement('div');
        overlay.id = 'mobile-print-overlay';
        overlay.style.cssText = `
          position: fixed;
          top: 0;
          left: 0;
          width: 100vw;
          height: 100vh;
          background: white;
          z-index: 10000;
          overflow-y: auto;
          -webkit-overflow-scrolling: touch;
        `;
        
        // Add the print content
        overlay.innerHTML = printHTML;
        
        // Add to body
        document.body.appendChild(overlay);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Add event listeners for the buttons
        setTimeout(() => {
          const printBtn = overlay.querySelector('.print-btn');
          const closeBtn = overlay.querySelector('.close-btn');
          
          if (printBtn) {
            printBtn.onclick = () => {
              // Hide controls during print
              const controls = overlay.querySelector('.print-controls');
              if (controls) controls.style.display = 'none';
              
              // Hide the main page content behind the overlay
              const mainContent = document.body.children;
              const hiddenElements = [];
              
              // Hide all other content except our overlay
              for (let i = 0; i < mainContent.length; i++) {
                const element = mainContent[i];
                if (element !== overlay && element.style.display !== 'none') {
                  hiddenElements.push({
                    element: element,
                    originalDisplay: element.style.display
                  });
                  element.style.display = 'none';
                }
              }
              
              // Trigger print
              window.print();
              
              // Restore all hidden elements and show controls again after print
              setTimeout(() => {
                hiddenElements.forEach(item => {
                  item.element.style.display = item.originalDisplay;
                });
                if (controls) controls.style.display = 'block';
              }, 1000);
            };
          }
          
          if (closeBtn) {
            closeBtn.onclick = () => {
              document.body.removeChild(overlay);
              document.body.style.overflow = '';
              this.close(); // Close the recipe dialog
            };
          }
        }, 100);
      },
      
      printInCurrentWindow(printHTML) {
        // Simplified version for desktop
        try {
          const printWindow = window.open('', '_blank');
          if (printWindow) {
            printWindow.document.write(printHTML);
            printWindow.document.close();
            printWindow.focus();
          } else {
            // Fallback: show in current window overlay
            this.showMobilePrintPreview();
          }
        } catch (error) {
          console.error('Print in current window error:', error);
          this.showMobilePrintPreview();
        }
      },
      
      desktopPrint() {
        try {
          // Simple desktop print using the print section
          const printSection = this.$refs.printSection;
          const originalContent = document.body.innerHTML;
          const printContent = printSection.cloneNode(true);
          printContent.style.display = 'block';
          document.body.innerHTML = printContent.innerHTML;
          window.print();
          document.body.innerHTML = originalContent;
          window.location.reload();
        } catch (error) {
          console.error('Desktop print error:', error);
          throw error;
        }
      },
      async exportAsImage() {
        const printSection = this.$refs.printSection;
        // Simply use the clinic logo without complex image handling
        this.captureImage(printSection);

        const formData = new FormData();
          formData.append('case_categores_id', this.RecipeInfo.case.case_categories.id);
          formData.append('notes', this.RecipeInfo.notes);
          formData.append('patient_id', this.RecipeInfo.name);
          if (this.imageFile) {
            formData.append('rx_img', this.imageFile);
          }


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
        // Simply use the clinic logo without complex image handling
        this.uploadImageAndSendLink(printSection);
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
            case_categories: "",
          },
          sex: "",
          age: "",
          notes: "",
          rx_img: ''
        };
        EventBus.$emit("changeStatusCloseField", false);
        this.$emit('close'); // Add this line to emit close event
      },
      async print() {
       
        if (this.$refs.form.validate()) {
          const formData = new FormData();
          // Only append case_categores_id if it exists
          if (this.RecipeInfo.case && this.RecipeInfo.case.case_categories && this.RecipeInfo.case.case_categories.id) {
            formData.append('case_categores_id', this.RecipeInfo.case.case_categories.id);
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
      // Build complete HTML for new window method - mobile print preview
      buildMobilePrintHTML() {
        const currentDate = new Date().toLocaleDateString('ar-SA', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        const clinicName = this.getClinicName();
        const clinicPhone = this.getClinicPhone();
        const clinicAddress = this.getClinicAddress();
        const clinicRegNum = this.getClinicRegNum();
        
        return `
          <!DOCTYPE html>
          <html dir="rtl" lang="ar">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>وصفة طبية</title>
            <style>
              /* A4 page setup - 210mm x 297mm */
              body {
                font-family: 'Cairo', Arial, sans-serif;
                direction: rtl;
                margin: 0;
                padding: 10px;
                background: #f5f5f5;
                min-height: 100vh;
                /* A4 dimensions simulation - properly centered */
                max-width: 210mm;
                width: 210mm;
                margin: 0 auto;
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                /* Ensure content doesn't get cut from left */
                overflow-x: visible;
                position: relative;
              }
              
              /* Print controls styling */
              .print-controls {
                text-align: center;
                margin: 10px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 12px;
                border: 2px solid #dee2e6;
                position: sticky;
                top: 10px;
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              }
              
              .print-btn, .close-btn {
                padding: 15px 25px;
                margin: 5px;
                font-size: 16px;
                cursor: pointer;
                border: none;
                border-radius: 8px;
                color: white;
                font-family: 'Cairo', Arial, sans-serif;
                transition: all 0.3s ease;
                font-weight: bold;
                min-width: 120px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
              }
              
              .print-btn {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                border: 2px solid #28a745;
              }
              
              .print-btn:hover, .print-btn:active {
                background: linear-gradient(135deg, #218838 0%, #1ea080 100%);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(40,167,69,0.3);
              }
              
              .close-btn {
                background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
                border: 2px solid #dc3545;
              }
              
              .close-btn:hover, .close-btn:active {
                background: linear-gradient(135deg, #c82333 0%, #c0392b 100%);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(220,53,69,0.3);
              }
              
              /* Recipe card styling for A4 */
              .recipe-content {
                width: 100%;
                margin: 0;
                padding: 15mm;
                background: white;
                border: none;
                box-shadow: none;
                font-family: 'Cairo', Arial, sans-serif;
                direction: rtl;
                font-size: 14px;
                line-height: 1.6;
                /* Ensure content fits properly in A4 */
                box-sizing: border-box;
                overflow: visible;
              }
              
              /* Header with prescription symbol */
              .prescription-header {
                text-align: center;
           
              }
              
              .header-image {
                margin-bottom: 20px;
                width: 100%;
              }
              
              .header-image-logo {
                max-width: 100%;
                width: 100%;
                height: auto;
                max-height: 120px;
                object-fit: cover;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
              }
              
              .clinic-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding: 15px 20px;
                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                border-radius: 12px;
                border: 2px solid #007bff;
                box-shadow: 0 4px 12px rgba(0,123,255,0.1);
                position: relative;
                overflow: hidden;
              }
              
              .clinic-header::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="1" fill="rgba(0,123,255,0.05)"/><circle cx="30" cy="25" r="1.5" fill="rgba(0,123,255,0.03)"/><circle cx="60" cy="15" r="1" fill="rgba(0,123,255,0.05)"/><circle cx="80" cy="35" r="1.2" fill="rgba(0,123,255,0.04)"/></svg>');
                pointer-events: none;
              }
              
              .clinic-info {
                flex: 1;
                padding-right: 25px;
                position: relative;
                z-index: 2;
              }
              
              .clinic-logo {
                text-align: center;
                flex-shrink: 0;
                position: relative;
                z-index: 2;
              }
              
              .clinic-logo-image {
                width: 110px;
                height: 110px;
                object-fit: cover;
                border-radius: 50%;
                border: 5px solid #007bff;
                box-shadow: 0 8px 25px rgba(0,123,255,0.3);
                background: white;
                padding: 5px;
                transition: all 0.3s ease;
                position: relative;
              }
              
              .clinic-logo-image:hover {
                transform: scale(1.05) rotate(5deg);
                box-shadow: 0 12px 35px rgba(0,123,255,0.4);
              }
              
              .clinic-name {
                font-weight: bold;
                font-size: 22px;
                color: #007bff;
                margin-bottom: 8px;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                letter-spacing: 0.5px;
              }
              
              .clinic-address {
                font-size: 14px;
                color: #495057;
                margin-bottom: 8px;
                font-style: italic;
              }
              
              .clinic-phone {
                font-size: 14px;
                color: #007bff;
                direction: ltr;
                background: rgba(0,123,255,0.1);
                padding: 8px 15px;
                border-radius: 25px;
                display: inline-block;
                margin-top: 5px;
                border: 1px solid rgba(0,123,255,0.2);
              }
              
              /* Patient information */
              .patient-info {
                background: #f8f9fa;
                padding: 10px;
                border-radius: 8px;
                margin: 15px 0;
                border: 1px solid #dee2e6;
              }
              
              .patient-info h3 {
                margin: 0 0 10px 0;
                color: #007bff;
                font-size: 16px;
                border-bottom: 1px solid #007bff;
                padding-bottom: 3px;
              }
              
              .patient-details {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 6px;
              }
              
              .patient-field {
                display: flex;
                align-items: center;
                gap: 5px;
              }
              
              .field-label {
                font-weight: bold;
                color: #333;
                min-width: 60px;
              }
              
              .field-value {
                color: #555;
                font-weight: 500;
              }
              
              /* Prescription content */
              .prescription-section {
                border: 2px solid #007bff;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
                min-height: 200px;
                background: #fff;
              }
              
              .prescription-section h3 {
                color: #007bff;
                margin: 0 0 10px 0;
                font-size: 18px;
                border-bottom: 2px solid #007bff;
                padding-bottom: 5px;
              }
              
              .prescription-content {
                line-height: 1.8;
                font-size: 14px;
                color: #333;
                white-space: pre-wrap;
                min-height: 150px;
              }
              
              /* Prescription image */
              .prescription-image {
                text-align: center;
                margin: 20px 0;
              }
              
              .prescription-image img {
                max-width: 100%;
                max-height: 300px;
                border: 1px solid #ddd;
                border-radius: 8px;
                object-fit: contain;
              }
              
              /* Footer */
              .prescription-footer {
                margin-top: 20px;
                padding-top: 15px;
                border-top: 2px solid #333;
                text-align: center;
                font-size: 12px;
                color: #666;
              }
              
              .footer-date {
                margin: 6px 0;
                font-weight: bold;
                color: #333;
              }
              
              .footer-reg-num {
                margin: 5px 0;
                font-weight: 600;
                color: #007bff;
                font-size: 13px;
              }
              
              .footer-note {
                font-style: italic;
                margin-top: 15px;
                color: #007bff;
              }
              
              /* Print media query for A4 */
              @media print {
                /* Hide everything except our print overlay */
                body > *:not(#mobile-print-overlay) {
                  display: none !important;
                }
                
                #mobile-print-overlay {
                  position: static !important;
                  width: auto !important;
                  height: auto !important;
                  overflow: visible !important;
                  background: white !important;
                  z-index: auto !important;
                }
                
                .print-controls {
                  display: none !important;
                }
                
                @page {
                  size: A4;
                  margin: 15mm;
                }
                
                body {
                  max-width: none;
                  width: auto;
                  box-shadow: none;
                  background: white;
                  margin: 0;
                  padding: 0;
                  overflow: visible;
                }
                
                .recipe-content {
                  padding: 10mm;
                  font-size: 12px;
                  width: 100%;
                  max-width: none;
                  overflow: visible;
                }
                
                .prescription-header {
                
                }
                
                .header-image {
                  width: 100%;
                  margin-bottom: 15px;
                  padding: 0;
                }
                
                .header-image-logo {
                  max-width: 100%;
                  width: 100%;
                  height: auto;
                  max-height: 150px;
                  object-fit: cover;
                  border-radius: 0;
                  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                  margin-bottom: 20px;
                }
                
                .clinic-header {
                  padding: 20px 25px;
                  margin-bottom: 25px;
                }
                
                .clinic-name {
                  font-size: 18px;
                }
                
                .clinic-phone {
                  font-size: 12px;
                }
                
                .patient-info h3 {
                  font-size: 14px;
                }
                
                .prescription-section h3 {
                  font-size: 16px;
                }
                
                .prescription-content {
                  font-size: 12px;
                }
                
                .clinic-logo-image {
                  width: 80px;
                  height: 80px;
                  border-radius: 50%;
                  border: 4px solid #ffffff;
                  object-fit: cover;
                  background: white;
                  padding: 4px;
                }
              }
              
              /* Responsive adjustments */
              @media screen and (max-width: 480px) {
                body {
                  max-width: 100vw;
                  width: 100vw;
                  padding: 5px;
                  overflow-x: hidden;
                }
                
                .print-controls {
                  position: sticky;
                  top: 0;
                  margin: 0 0 15px 0;
                  padding: 12px;
                  border-radius: 0 0 12px 12px;
                  background: #ffffff;
                  border: none;
                  border-bottom: 3px solid #007bff;
                  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
                }
                
                .print-btn, .close-btn {
                  display: block;
                  width: 100%;
                  margin: 8px 0;
                  padding: 18px;
                  font-size: 18px;
                  border-radius: 12px;
                }
                
                .recipe-content {
                  padding: 15px 10px;
                  overflow-x: hidden;
                  font-size: 16px;
                }
                
                .clinic-header {
                  flex-direction: column;
                  gap: 15px;
                  padding: 20px 15px;
                  text-align: center;
                  margin-bottom: 20px;
                }
                
                .clinic-info {
                  padding-right: 0;
                  order: 2;
                }
                
                .clinic-logo {
                  order: 1;
                }
                
                .patient-details {
                  grid-template-columns: 1fr;
                  gap: 12px;
                }
                
                .patient-field {
                  padding: 10px;
                  background: #f8f9fa;
                  border-radius: 8px;
                  border: 1px solid #dee2e6;
                }
                
                .clinic-logo-image {
                  width: 100px;
                  height: 100px;
                  border-radius: 50%;
                  border: 4px solid #007bff;
                  object-fit: cover;
                  background: white;
                  padding: 4px;
                }
                
                .header-image-logo {
                  max-height: 80px;
                  border-radius: 8px;
                }
                
                .clinic-name {
                  font-size: 20px;
                  margin-bottom: 12px;
                }
                
                .clinic-phone, .clinic-address {
                  font-size: 16px;
                  margin-bottom: 8px;
                }
                
                .prescription-section {
                  margin: 15px 0;
                  padding: 15px;
                }
                
                .prescription-content {
                  font-size: 16px;
                  line-height: 1.8;
                  min-height: 120px;
                }
                
                .patient-info h3, .prescription-section h3 {
                  font-size: 18px;
                  margin-bottom: 12px;
                }
                
                .footer-date {
                  font-size: 16px;
                }
                
                .footer-reg-num {
                  font-size: 15px;
                }
              }
            </style>
          </head>
          <body>
            <div class="print-controls">
              <button onclick="window.print()" class="print-btn">🖨️ طباعة الوصفة</button>
              <button onclick="window.close()" class="close-btn">✕ إغلاق</button>
            </div>
            
            <div class="recipe-content">
              <div class="prescription-header">
                <div class="header-image">
                  <img src="https://apismartclinicv3.tctate.com/rx1.jpg" alt="رأس الوصفة" class="header-image-logo" />
                </div>
            
              </div>
              
              <div class="clinic-header">
                <div class="clinic-info">
                  <div class="clinic-name">${clinicName}</div>
                  ${clinicAddress ? `<div class="clinic-address">📍 ${clinicAddress}</div>` : ''}
                  ${clinicPhone ? `<div class="clinic-phone">📞 ${clinicPhone}</div>` : ''}
                </div>
                <div class="clinic-logo">
                  <img src="${this.$store.state.AdminInfo.clinics_info.logo}" alt="شعار العيادة" class="clinic-logo-image" />
                </div>
              </div>
              
              <div class="patient-info">
                <h3>بيانات المريض</h3>
                <div class="patient-details">
                  <div class="patient-field">
                    <span class="field-label">الاسم:</span>
                    <span class="field-value">${this.RecipeInfo.name || 'غير محدد'}</span>
                  </div>
                  <div class="patient-field">
                    <span class="field-label">العمر:</span>
                    <span class="field-value">${this.RecipeInfo.age || 'غير محدد'}</span>
                  </div>
                  <div class="patient-field">
                    <span class="field-label">الجنس:</span>
                    <span class="field-value">${this.RecipeInfo.sex === 1 ? 'ذكر' : this.RecipeInfo.sex === 0 ? 'أنثى' : 'غير محدد'}</span>
                  </div>
                </div>
              </div>
              
              <div class="prescription-section">
                <h3>الوصفة الطبية:</h3>
                <div class="prescription-content">${(this.RecipeInfo.notes || 'لا توجد وصفة مكتوبة').replace(/\n/g, '<br>')}</div>
              </div>
              
              <div class="prescription-footer">
                <div class="footer-date">تاريخ الوصفة: ${currentDate}</div>
                ${clinicRegNum ? `<div class="footer-reg-num">رقم تسجيل العيادة: ${clinicRegNum}</div>` : ''}
                <div class="footer-note">يرجى اتباع تعليمات الطبيب المعالج</div>
              </div>
            </div>
          </body>
          </html>
        `;
      },
    },
    async created() {
      this.fetchNoteSuggestions();
      // Default image URL is already set in data
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