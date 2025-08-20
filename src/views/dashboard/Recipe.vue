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
        defaultImageBase64: '',
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
          const response = await this.axios.get('https://smartclinicv5.tctate.com/api/getrecipesItem', {
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
      
      mobileFriendlyPrint() {
        try {
          // Create a simple, mobile-friendly print approach
          const currentDate = new Date().toLocaleDateString('en-US');
          const clinicName = this.getClinicName();
          const clinicPhone = this.getClinicPhone();
          
          const printContent = 
            '<!DOCTYPE html>' +
            '<html dir="rtl">' +
            '<head>' +
            '<meta charset="UTF-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
            '<title>وصفة طبية</title>' +
            '<style>' +
            'body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; font-size: 16px; }' +
            '.header { text-align: center; margin-bottom: 20px; }' +
            '.patient-info { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; }' +
            '.prescription { border: 1px solid #ddd; padding: 20px; margin: 15px 0; min-height: 200px; border-radius: 5px; }' +
            '.footer { text-align: center; margin-top: 20px; font-size: 14px; }' +
            'img { max-width: 100%; height: auto; }' +
            '@media print { body { margin: 0; } .no-print { display: none; } }' +
            '</style>' +
            '</head>' +
            '<body>' +
            '<div class="header">' +
            '<img src="' + (this.rx_img || this.defaultImageBase64) + '" style="max-height: 100px;" />' +
            '<h1 style="font-size: 48px; color: #007bff; margin: 10px 0;">℞</h1>' +
            '</div>' +
            '<div class="patient-info">' +
            '<h3 style="margin-bottom: 10px;">بيانات المريض</h3>' +
            '<p><strong>الاسم:</strong> ' + (this.RecipeInfo.name || 'غير محدد') + '</p>' +
            '<p><strong>العمر:</strong> ' + (this.RecipeInfo.age || 'غير محدد') + '</p>' +
            '<p><strong>الجنس:</strong> ' + (this.RecipeInfo.sex === 1 ? 'ذكر' : this.RecipeInfo.sex === 0 ? 'أنثى' : 'غير محدد') + '</p>' +
            '</div>' +
            '<div class="prescription">' +
            '<h3 style="color: #007bff; margin-bottom: 15px;">الوصفة الطبية:</h3>' +
            '<div style="line-height: 1.6;">' + (this.RecipeInfo.notes || 'لا توجد وصفة مكتوبة').replace(/\n/g, '<br>') + '</div>' +
            '</div>' +
            '<div class="footer">' +
            '<p><strong>' + clinicName + '</strong></p>' +
            (clinicPhone ? '<p>الهاتف: ' + clinicPhone + '</p>' : '') +
            '<p>تاريخ الوصفة: ' + currentDate + ' ميلادي</p>' +
            '<p>يرجى اتباع تعليمات الطبيب المعالج</p>' +
            '</div>' +
            '<div class="no-print" style="margin-top: 20px; text-align: center;">' +
            '<button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; margin: 5px;">طباعة</button>' +
            '<button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; margin: 5px;">إغلاق</button>' +
            '</div>' +
            '</body>' +
            '</html>';
          
          // Try to open a new window first
          const printWindow = window.open('', '_blank', 'width=400,height=600');
          
          if (printWindow && !printWindow.closed) {
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            // Auto print after a short delay
            setTimeout(() => {
              printWindow.print();
            }, 1000);
          } else {
            // Fallback: replace current page content temporarily
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            // Restore original content
            setTimeout(() => {
              document.body.innerHTML = originalContent;
              window.location.reload();
            }, 100);
          }
          
        } catch (error) {
          console.error('Mobile print error:', error);
          throw error;
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
        if (!this.RecipeInfo.rx_img) {
          this.rx_img = this.defaultImageBase64;
          this.captureImage(printSection);
        } else if (this.RecipeInfo.rx_img && !this.RecipeInfo.rx_img.startsWith('data:image')) {
          const response = await fetch(this.RecipeInfo.rx_img);
          const blob = await response.blob();
          const reader = new FileReader();
          reader.onloadend = () => {
            this.rx_img = reader.result;
            this.captureImage(printSection);
          };
          reader.readAsDataURL(blob);
        } else {
          this.captureImage(printSection);
        }

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
            case_categories: "",
          },
          sex: "",
          age: "",
          notes: "",
          rx_img: ''
        };
        EventBus.$emit("changeStatusCloseField", false);
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