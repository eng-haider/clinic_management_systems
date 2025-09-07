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
              <!-- Name Field with Patient Combobox -->
              <v-col class="py-0" cols="12" sm="6" md="6">
                <v-combobox
                  v-model="editedItem.name"
                  :items="patientsList"
                  :loading="loadingPatients"
                  :search-input.sync="patientSearch"
                  item-text="text"
                  item-value="value"
                  :rules="[rules.required]"
                  :label="$t('datatable.name')"
                  placeholder="ابحث عن مريض أو أدخل اسم جديد"
                  style="direction: rtl;text-align: right;"
                  outlined
                  clearable
                  @change="handlePatientNameSelect"
                  @update:search-input="searchPatientsByName"
                >
                  <template v-slot:no-data>
                    <v-list-item>
                      <v-list-item-content>
                        <v-list-item-title>
                          لا توجد نتائج. سيتم إضافة "{{ patientSearch }}" كمريض جديد
                        </v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </template>
                  
                  <template v-slot:item="{ item }">
                    <v-list-item-content>
                      <v-list-item-title>{{ item.text || item.name }}</v-list-item-title>
                      <v-list-item-subtitle>{{ item.phone }} - العمر: {{ item.age }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </template>
                </v-combobox>
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
      
      // Patient combobox properties
      patientsList: [],
      loadingPatients: false,
      patientSearch: '',
      patientSearchTimeout: null,
      
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
        url: "https://titaniumapi.tctate.com/api/cases/uploude_image",
        thumbnailWidth: 150,
        maxFilesize: 5,
        acceptedFiles: "image/*",
        dictDefaultMessage: '<i class="fas fa-upload"></i> اضغط هنا لرفع صور الحاله',
        paramName: "file",
        maxFiles: 10,
        addRemoveLinks: true,
        dictRemoveFile: "حذف الصورة",
        dictCancelUpload: "إلغاء الرفع"
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
    }

    // Removed the auto-fill watcher for editedItem.name to prevent 
    // automatic completion of other fields when name is selected
  },

  mounted() {
    // Set authorization header for dropzone after component is mounted
    if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.token) {
      this.dropzoneOptions.headers = {
        "Authorization": `Bearer ${this.$store.state.AdminInfo.token}`
      };
    }
    
    // Initialize patients list for combobox
    this.fetchPatientsForCombobox();
  },

  methods: {
    // Patient Combobox Methods
    /**
     * Fetches patients list for combobox
     */
    async fetchPatientsForCombobox(searchTerm = '') {
      try {
        this.loadingPatients = true;
        const response = await fetch(
          `https://titaniumapi.tctate.com/api/patients/getByUserIdv3?per_page=50${searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : ''}`,
          {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
            }
          }
        );
        
        if (response.ok) {
          const result = await response.json();
          if (result.data) {
            this.patientsList = result.data.map(patient => ({
              text: patient.name,
              value: patient.name,
              name: patient.name,
              phone: patient.phone || '',
              age: patient.age || 'غير محدد',
              id: patient.id,
              fullData: patient
            }));
          }
        }
      } catch (error) {
        console.error('Error fetching patients for combobox:', error);
        this.patientsList = [];
      } finally {
        this.loadingPatients = false;
      }
    },

    /**
     * Handles patient search input with debouncing
     */
    searchPatientsByName(searchTerm) {
      // Clear existing timeout
      if (this.patientSearchTimeout) {
        clearTimeout(this.patientSearchTimeout);
      }

      // If search term is empty or too short, load initial patients
      if (!searchTerm || searchTerm.length < 2) {
        this.fetchPatientsForCombobox();
        return;
      }

      // Debounce search to avoid too many API calls
      this.patientSearchTimeout = setTimeout(() => {
        this.fetchPatientsForCombobox(searchTerm);
      }, 300);
    },

    /**
     * Handles patient name selection
     */
    handlePatientNameSelect(selectedItem) {
      console.log('Patient selected:', selectedItem, typeof selectedItem);
      
      if (!selectedItem) return;

      // If selectedItem is a string, it's a new patient name
      if (typeof selectedItem === 'string') {
        // Just set the name, don't auto-fill other fields
        this.editedItem.name = selectedItem;
        return;
      }

      // If selectedItem is an object with patient data, only set the name
      if (selectedItem && typeof selectedItem === 'object') {
        if (selectedItem.fullData) {
          // Only set the name, don't fill other fields
          this.editedItem.name = selectedItem.fullData.name || '';
        } else {
          // Just set the name
          this.editedItem.name = selectedItem.name || selectedItem.text || selectedItem;
        }
      }
    },

    /**
     * Fills the form with patient data (disabled - keeping for reference)
     */
    fillPatientData() {
      // This method is now disabled to prevent auto-completion
      // Only the name field will be filled from the combobox selection
      console.log('Auto-fill disabled. Only name will be set from combobox selection.');
    },

    openDialog() {
      this.updateEditedItem(this.patient);
      this.uploadedImages = [];
      this.patientSearch = '';
      if (this.$refs.myVueDropzone) {
        this.$refs.myVueDropzone.removeAllFiles();
      }
      this.$emit('input', true);
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
      } else {
        // Creating new patient
        this.editedItem = { ...this.defaultPatient };
        this.uploadedImages = [];
      }
    },

    resetForm() {
      this.editedItem = { ...this.defaultPatient };
      this.uploadedImages = [];
      this.patientSearch = '';
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
        
        // Handle doctor assignment - send as doctor_id
        if (patientData.doctors) {

           patientData.doctor_id =this.isEditing ?  typeof patientData.doctors === 'object' ? patientData.doctors.id : patientData.doctors : patientData.doctors;
          delete patientData.doctors;
        }

        // Remove images from patient data (they will be uploaded separately)
        delete patientData.images;

        // Make API call to create/update patient
        this.savePatientToAPI(patientData);
      }
    },

    async savePatientToAPI(patientData) {
      this.loadSave = true;
      
      try {
        const apiUrl = "https://titaniumapi.tctate.com/api/patients";
        const method = this.isEditing ? 'PATCH' : 'POST';
        const url = this.isEditing ? `${apiUrl}/${patientData.id}` : apiUrl;
        


        
        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          },
          body: JSON.stringify(patientData)
        });

        if (response.ok) {
          const result = await response.json();
          
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
          throw new Error('فشل في حفظ بيانات المريض');
        }
      } catch (error) {
        console.error('Save patient error:', error);
        this.$swal.fire({
          title: "خطأ في الحفظ",
          text: "حدث خطأ أثناء حفظ بيانات المريض",
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

        const response = await fetch('https://titaniumapi.tctate.com/api/cases/uploude_images', {
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
    }
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
