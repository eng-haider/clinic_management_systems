<template>
  <v-dialog v-model="dialog" max-width="800px">
    <template v-slot:activator="{ on, attrs }" v-if="!hideActivator">
      <v-btn 
        color="primary" 
        @click="openDialog" 
        dark 
        class="mb-2" 
        v-bind="attrs" 
        v-on="on" 
        style="color:#fff;font-family: 'Cairo'"
      >
        <i class="fas fa-plus" style="position: relative;left:5px"></i>
        {{ isEditing ? $t("edit") : $t("patients.addnewpatients") }}
      </v-btn>
    </template>

    <v-form ref="form" v-model="valid">
      <v-card>
        <v-toolbar dark color="primary lighten-1 mb-5">
          <v-toolbar-title>
            <h3 style="color:#fff;font-family: 'Cairo'">{{ formTitle }}</h3>
          </v-toolbar-title>
          <v-spacer />
          <v-btn @click="closeDialog()" icon>
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text>
          <v-container>
            <v-row>
              <!-- Name Field -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-text-field 
                  v-model="editedItem.name"
                  style="direction: rtl;text-align: right;"
                  :rules="[rules.required]" 
                  :label="$t('datatable.name')"
                  outlined
                />
              </v-col>

              <!-- Phone Field -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-text-field 
                  v-model="editedItem.phone" 
                  v-mask="mask"
                  placeholder="07XX XXX XXXX" 
                  style="direction:ltr"
                  onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                  :label="$t('datatable.phone')" 
                  outlined
                />
              </v-col>

              <!-- Birth Date Field -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-text-field 
                  v-model="editedItem.birth_date" 
                  type="date"
                  :label="$t('datatable.birth_date')" 
                  outlined
                />
              </v-col>

              <!-- Age Field (only show when editing) -->
              <v-col class="py-0" v-if="isEditing" cols="12" sm="6" md="6">
                <v-text-field 
                  :disabled="true" 
                  v-model="editedItem.age"
                  type="number" 
                  :label="$t('datatable.age')" 
                  outlined
                />
              </v-col>

              <!-- Gender Field -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-radio-group v-model="editedItem.sex" row>
                  <v-radio :label="$t('sex.female')" :value="1"></v-radio>
                  <v-radio :label="$t('sex.male')" :value="0"></v-radio>
                </v-radio-group>
              </v-col>

              <!-- Address Field -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-text-field 
                  v-model="editedItem.address"
                  style="direction: rtl;text-align: right;"
                  :label="$t('datatable.address')" 
                  outlined
                />
              </v-col>

              <!-- RX_ID Field -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-text-field 
                  v-model="editedItem.rx_id"
                  style="direction: rtl;text-align: right;"
                  label="RX_ID" 
                  outlined
                />
              </v-col>

              <!-- Doctor Selection (only for secretaries with multiple doctors) -->
              <v-col 
                class="py-0" 
                cols="12" 
                sm="6" 
                md="6"
                v-if="($store.state.role=='secretary' || $store.state.role=='accounter') && doctors.length > 1
                && this.$store.state.AdminInfo.clinics_info.doctor_show_all_patient ==0
                "
              >
                <v-select 
                  :rules="[rules.required]" 
                  v-model="editedItem.doctors"
                  :label="$t('doctor')" 
                  :items="doctors" 
                  outlined 
                  item-text="name"
                  item-value="id"
                />
              </v-col>

              <!-- From Where Come Selection (if enabled) -->
              <v-col 
                class="py-0" 
                cols="12" 
                sm="6" 
                md="6"
                v-if="isFromWhereComeEnabled"
              >
                <v-select 
                  v-model="editedItem.from_where_come_id"
                  :items="fromWhereComeList"
                  :loading="loadingFromWhereCome"
                  :rules="[rules.required]"
                  label="من أين جئت؟"
                  outlined
                  item-text="name"
                  item-value="id"
                  style="direction: rtl;text-align: right;"
                />
              </v-col>

              <!-- Identifier Field (only show when option 2 is selected) -->
              <v-col 
                class="py-0" 
                cols="12" 
                sm="6" 
                md="6"
                v-if="isFromWhereComeEnabled && showIdentifierField"
              >
                <v-text-field 
                  v-model="editedItem.identifier"
                  :rules="[rules.required]"
                  label="المعرف"
                  placeholder="أدخل المعرف"
                  outlined
                  style="direction: rtl;text-align: right;"
                />
              </v-col>
            </v-row>

            <!-- Systemic Conditions -->
            <v-row>
              <v-col class="py-0" cols="12" sm="12" md="12">
                <v-textarea 
                  dense 
                  v-model="editedItem.systemic_conditions"
                  :label="$t('patients.systemicdisease')" 
                  outlined
                />
              </v-col>
            </v-row>

            <!-- Image Upload Section -->
            <v-row style="height: auto;">
              <v-col cols="12" md="12">
                <vue2-dropzone 
                  ref="myVueDropzone" 
                  id="dropzone" 
                  :options="dropzoneOptions"
                  @vdropzone-success="handleImageSuccess"
                  @vdropzone-error="handleImageError"
                  @vdropzone-removed-file="handleImageRemoved"
                />
              </v-col>
            </v-row>

            <!-- Schedule Today Option -->
          
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="red darken-1" text @click="closeDialog()">
            {{ $t("close") }}
          </v-btn>
          <v-btn 
            :loading="loadSave" 
            style="color: #fff;" 
            color="green darken-1"
            @click="save()"
          >
            {{ $t("next") }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script>
import { mask } from "vue-the-mask";
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";

export default {
  name: 'PatientEditDialog',
  
  components: {
    vue2Dropzone
  },
  
  directives: {
    mask,
  },

  props: {
    // Patient data to edit (null for new patient)
    patient: {
      type: Object,
      default: null
    },
    
    // List of doctors for selection
    doctors: {
      type: Array,
      default: () => []
    },
    
    // Loading state for save button
    loading: {
      type: Boolean,
      default: false
    },

    // Control dialog visibility from parent
    value: {
      type: Boolean,
      default: false
    },

    // Hide the activator button (for external dialog control)
    hideActivator: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      valid: false,
      loadSave: false,
      mask: "07XX XXX XXXXX",
      
      // From where come data
      fromWhereComeList: [],
      loadingFromWhereCome: false,
      showIdentifierField: false,
      
      // Default patient structure
      defaultPatient: {
        name: "",
        age: "",
        birth_date: "",
        sex: "",
        address: "",
        phone: "",
        rx_id: "",
        is_scheduled_today: false,
        doctors: "",
        systemic_conditions: "",
        email: "",
        from_where_come_id: "",
        identifier: "",
        case: {
          case_categores_id: "",
          upper_right: "",
          upper_left: "",
          sessions: [{
            note: "",
            date: ""
          }],
          case_categories: {
            name_ar: ""
          },
          status_id: 42,
          bills: [{
            price: "",
            PaymentDate: ""
          }],
          images: [{
            img: "",
            descrption: ""
          }],
          notes: ""
        },
        images: []
      },

      editedItem: {},
      uploadedImages: [],

      // Dropzone configuration
      dropzoneOptions: {
        url: "https://hasan.tctate.com/api/cases/uploude_image",
        thumbnailWidth: 150,
        maxFilesize: 5,
        acceptedFiles: "image/*",
        dictDefaultMessage: this.isMobileDevice ? 
          '<i class="fas fa-camera"></i> اضغط هنا لفتح الكاميرا' : 
          '<i class="fas fa-upload"></i> اضغط هنا لرفع صور الحاله',
        paramName: "file",
        maxFiles: 10,
        addRemoveLinks: true,
        dictRemoveFile: "حذف الصورة",
        dictCancelUpload: "إلغاء الرفع",
        capture: this.isMobileDevice ? "environment" : false
      },

      // Validation rules
      rules: {
        minPhon: (v) => (v.length == 13 || v.length == 0) || this.$t('phone_length'),
        required: value => !!value || this.$t('required'),
        min: (v) => v.length >= 6 || this.$t('password_min_length'),
        email: value => {
          if (value && value.length > 0) {
            const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return pattern.test(value) || this.$t('invalid_email');
          }
          return true;
        },
      }
    }
  },

  computed: {
    dialog: {
      get() {
        return this.value;
      },
      set(val) {
        this.$emit('input', val);
      }
    },

    isEditing() {
      return this.patient && this.patient.id;
    },

    formTitle() {
      return this.isEditing ? this.$t('update') : this.$t('patients.addnewpatients');
    },

    isMobileDevice() {
      return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    },

    // Check if "from where come" feature is enabled
    isFromWhereComeEnabled() {
      return this.$store.state.AdminInfo?.clinics_info?.add_from_where_come == 1 || 
             this.$store.state.AdminInfo?.clinics_info?.add_from_where_come === true;
    }
  },

  watch: {
    // Watch for changes in patient prop to update editedItem
    patient: {
      handler(newPatient) {
        this.updateEditedItem(newPatient);
      },
      immediate: true,
      deep: true
    },

    // Watch for dialog opening/closing
    dialog(newVal) {
      if (newVal) {
        this.updateEditedItem(this.patient);
      } else {
        this.resetForm();
      }
    },

    // Pass loading state to internal loadSave
    loading(newVal) {
      this.loadSave = newVal;
    },

    // Watch for "from where come" selection changes
    'editedItem.from_where_come_id'(newVal) {
      // Show identifier field when option 2 is selected
      this.showIdentifierField = newVal == 2;
      
      // Clear identifier when not needed
      if (!this.showIdentifierField) {
        this.editedItem.identifier = '';
      }
    }
  },

  mounted() {
    // Set authorization header for dropzone after component is mounted
    if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.token) {
      this.dropzoneOptions.headers = {
        "Authorization": `Bearer ${this.$store.state.AdminInfo.token}`
      };
    }
    
    // Update dropzone options based on device type
    this.updateDropzoneForDevice();
    
    // Fetch from where come list if feature is enabled
    if (this.isFromWhereComeEnabled) {
      this.fetchFromWhereComeList();
    }
  },

  methods: {
    // Fetch "from where come" options from API
    async fetchFromWhereComeList() {
      try {
        this.loadingFromWhereCome = true;
        const response = await fetch('https://hasan.tctate.com/api/from-where-come', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          }
        });
        
        if (response.ok) {
          const result = await response.json();
          this.fromWhereComeList = result.data || result || [];
          console.log('From where come list loaded:', this.fromWhereComeList);
        } else {
          console.error('Failed to fetch from where come list:', response.status);
          this.fromWhereComeList = [];
        }
      } catch (error) {
        console.error('Error fetching from where come list:', error);
        this.fromWhereComeList = [];
      } finally {
        this.loadingFromWhereCome = false;
      }
    },
    openDialog() {
      this.updateEditedItem(this.patient);
      this.uploadedImages = [];
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.removeAllFiles();
      }
      this.$emit('input', true);
      
      // Update dropzone for device type after dialog opens
      this.$nextTick(() => {
        this.updateDropzoneForDevice();
      });
    },

    closeDialog() {
      this.resetForm();
      this.$emit('input', false);
      this.$emit('close');
    },

    updateEditedItem(patient) {
      if (patient && patient.id) {
        // Editing existing patient
        this.editedItem = { ...this.defaultPatient, ...patient };
        
        // Handle doctor data conversion for editing
        if (patient.doctor_id && !this.editedItem.doctors) {
          // If patient has doctor_id but no doctors field, set doctors to doctor_id
          this.editedItem.doctors = parseInt(patient.doctor_id);
          console.log('Set doctors field from doctor_id:', patient.doctor_id);
        } else if (patient.doctors) {
          if (typeof patient.doctors === 'object' && patient.doctors.id) {
            // If doctors is an object (from API), extract the ID
            this.editedItem.doctors = parseInt(patient.doctors.id);
            console.log('Set doctors field from doctors object:', patient.doctors.id);
          } else if (typeof patient.doctors === 'number' || typeof patient.doctors === 'string') {
            // If doctors is already an ID
            this.editedItem.doctors = parseInt(patient.doctors);
            console.log('Set doctors field from existing ID:', patient.doctors);
          }
        }
        
        // Ensure doctors field is valid for the select component
        if (this.editedItem.doctors && isNaN(parseInt(this.editedItem.doctors))) {
          console.error('Invalid doctors value, resetting:', this.editedItem.doctors);
          this.editedItem.doctors = '';
        }
        
        console.log('EditedItem doctors field:', this.editedItem.doctors);
        
        // Handle from where come data
        if (patient.from_where_come_id) {
          this.editedItem.from_where_come_id = patient.from_where_come_id;
          // Check if identifier field should be shown
          this.showIdentifierField = patient.from_where_come_id == 2;
        }
        
        if (patient.identifier) {
          this.editedItem.identifier = patient.identifier;
        }
      } else {
        // Creating new patient
        this.editedItem = { ...this.defaultPatient };
        this.uploadedImages = [];
        this.showIdentifierField = false;
      }
    },

    resetForm() {
      this.editedItem = { ...this.defaultPatient };
      this.uploadedImages = [];
      this.showIdentifierField = false;
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.removeAllFiles();
      }
    },

    // Dropzone event handlers
    handleImageSuccess(file, response) {
      console.log('Image uploaded successfully:', response);

           if (response) {
        this.uploadedImages.push({
          fileName: response.data,
          originalName: file.name,
          file: file
        });

      }

     
    },

    handleImageError(file, message) {
      console.error('Image upload error:', message);
      this.$swal.fire({
        title: "خطأ في رفع الصورة",
        text: message,
        icon: "error",
        confirmButtonText: "اغلاق",
      });
    },

    handleImageRemoved(file) {
      console.log('Image removed:', file);
      // Remove from uploadedImages array if needed
      if (file.upload && file.upload.filename) {
        const index = this.uploadedImages.indexOf(file.upload.filename);
        if (index > -1) {
          this.uploadedImages.splice(index, 1);
        }
      }
    },

    save() {
      if (this.$refs.form.validate()) {
        // Prepare data for saving
        const patientData = { ...this.editedItem };
        
        console.log('Original editedItem.doctors:', this.editedItem.doctors);
        console.log('PatientData before doctor processing:', patientData);
        
        // Handle doctor assignment - send as doctor_id
        if (patientData.doctors !== null && patientData.doctors !== undefined && patientData.doctors !== '') {
          // Determine doctor_id based on the data type and editing mode
          if (this.isEditing) {
            // When editing, doctors might be an object (from API) or ID (from selection)
            if (typeof patientData.doctors === 'object' && patientData.doctors.id) {
              patientData.doctor_id = patientData.doctors.id;
            } else if (typeof patientData.doctors === 'number' || typeof patientData.doctors === 'string') {
              patientData.doctor_id = parseInt(patientData.doctors);
            }
          } else {
            // When creating new patient, doctors should be the selected ID
            patientData.doctor_id = parseInt(patientData.doctors);
          }
          
          // Ensure doctor_id is valid
          if (isNaN(patientData.doctor_id) || patientData.doctor_id <= 0) {
            console.error('Invalid doctor_id generated:', patientData.doctor_id);
            delete patientData.doctor_id;
          }
          
          // Clean up the doctors property
          delete patientData.doctors;
          
          console.log('Doctor ID assigned:', patientData.doctor_id);
        } else {
          console.log('No doctor selected or doctor data is empty/null');
          
          // Check if doctor selection should be required but wasn't provided
          const isDoctorFieldVisible = (this.$store.state.role === 'secretary' || this.$store.state.role === 'accounter') 
            && this.doctors.length > 1 
            && this.$store.state.AdminInfo.clinics_info.doctor_show_all_patient == 0;
          
          if (isDoctorFieldVisible) {
            console.warn('Doctor field is visible but no doctor was selected');
          } else {
            console.log('Doctor field is not visible for current user/clinic settings');
          }
          
          // Ensure no invalid doctor_id is sent
          delete patientData.doctor_id;
        }

        // Handle from where come data
        if (this.isFromWhereComeEnabled && patientData.from_where_come_id) {
          // Ensure from_where_come_id is a valid number
          patientData.from_where_come_id = parseInt(patientData.from_where_come_id);
          
          // Include identifier if provided and option 2 is selected
          if (patientData.from_where_come_id === 2 && patientData.identifier) {
            patientData.identifier = patientData.identifier.trim();
          } else {
            // Remove identifier if not needed
            delete patientData.identifier;
          }
        } else {
          // Remove from where come fields if feature is disabled
          delete patientData.from_where_come_id;
          delete patientData.identifier;
        }

        console.log('Final patientData:', patientData);

        // Remove images from patient data (they will be uploaded separately)
        delete patientData.images;

        // Make API call to create/update patient
        this.savePatientToAPI(patientData);
      }
    },

    async savePatientToAPI(patientData) {
      this.loadSave = true;
      
      try {
        const apiUrl = "https://hasan.tctate.com/api/patients";
        const method = this.isEditing ? 'PATCH' : 'POST';
        const url = this.isEditing ? `${apiUrl}/${patientData.id}` : apiUrl;
        


        console.log('API Request - URL:', url);
        console.log('API Request - Method:', method);
        console.log('API Request - Body:', JSON.stringify(patientData, null, 2));
        
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          },
          body: JSON.stringify(patientData)
        });

        console.log('API Response Status:', response.status);
        console.log('API Response OK:', response.ok);

        if (response.ok) {
          const result = await response.json();
          console.log('API Response Data:', result);
          
          // Get patient ID from result (for new patients) or from existing patient data
          const patientId = result.data?.id || patientData.id;
        
          // Upload images if any were uploaded
         await this.uploadPatientImages(patientId);
          
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: this.isEditing ? "تم تعديل المريض بنجاح" : "تم إضافة المريض بنجاح",
            showConfirmButton: false,
            timer: 1500
          });
          
          // Emit save event with patient data
          this.$emit('save', {
            patient: result.data || patientData,
            isEditing: this.isEditing
          });

          this.closeDialog();
        } else {
          const errorData = await response.text();
          console.error('API Error Response:', errorData);
          throw new Error(`فشل في حفظ بيانات المريض: ${response.status} - ${errorData}`);
        }
      } catch (error) {
        console.error('Save patient error:', error);
        console.error('Error details:', {
          message: error.message,
          stack: error.stack,
          patientData: patientData
        });
        this.$swal.fire({
          title: "خطأ في الحفظ",
          text: `حدث خطأ أثناء حفظ بيانات المريض: ${error.message}`,
          icon: "error",
          confirmButtonText: "اغلاق",
        });
      } finally {
        this.loadSave = false;
      }
    },

    // Upload patient images using the specified API
    async uploadPatientImages(patientId) {
      try {
      
        const requestBody = {
          images: this.uploadedImages.map(image => ({
            img: image.fileName,
            descrption: image.originalName || image.fileName,
            patient_id: patientId
          })),
          patient_id:patientId
        };
        

        console.log('📸 Uploading patient images:', requestBody);

        const response = await fetch('https://hasan.tctate.com/api/cases/uploude_images', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          },
          body: JSON.stringify(requestBody)
        });

        if (response.ok) {
          const result = await response.json();
          console.log('✅ Images uploaded successfully:', result);
        } else {
          throw new Error('فشل في رفع الصور');
        }
      } catch (error) {
        console.error('❌ Error uploading images:', error);
        // Show warning but don't fail the entire operation
        this.$swal.fire({
          title: "تحذير",
          text: "تم حفظ بيانات المريض ولكن فشل في رفع الصور",
          icon: "warning",
          confirmButtonText: "موافق",
        });
      }
    },

    updateDropzoneForDevice() {
      if (this.isMobileDevice) {
        // For mobile devices, configure to open camera
        this.dropzoneOptions.acceptedFiles = "image/*";
        this.dropzoneOptions.capture = "environment";
        this.dropzoneOptions.dictDefaultMessage = '<i class="fas fa-camera"></i> اضغط هنا لفتح الكاميرا';
        
        // Add click handler to open camera directly
        this.$nextTick(() => {
          if (this.$refs.myVueDropzone) {
            const dropzoneElement = this.$refs.myVueDropzone.$el;
            const fileInput = dropzoneElement.querySelector('input[type="file"]');
            if (fileInput) {
              fileInput.setAttribute('capture', 'environment');
              fileInput.setAttribute('accept', 'image/*');
            }
          }
        });
      }
    },
  }
}
</script>

<style scoped>
/* Add any specific styles for the dialog here */
.v-dialog {
  font-family: 'Cairo', sans-serif;
}

/* Dropzone Styles */
.vue-dropzone {
  border: 2px dashed #007bff !important;
  border-radius: 5px !important;
  background: white !important;
  min-height: 150px !important;
}

.vue-dropzone .dz-message {
  font-size: 16px !important;
  color: #007bff !important;
  font-family: 'Cairo', sans-serif !important;
}

.vue-dropzone .dz-message i {
  display: block !important;
  margin-bottom: 10px !important;
  font-size: 24px !important;
}

.vue-dropzone:hover {
  border-color: #0056b3 !important;
  background-color: #f8f9fa !important;
}

.vue-dropzone .dz-preview {
  margin: 10px !important;
}

.vue-dropzone .dz-preview .dz-image {
  border-radius: 5px !important;
}

.vue-dropzone .dz-preview .dz-remove {
  color: #dc3545 !important;
  font-size: 12px !important;
}

.vue-dropzone .dz-preview .dz-remove:hover {
  text-decoration: underline !important;
}
</style>
