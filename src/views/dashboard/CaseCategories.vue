<template>
  <div>
    <div>
      <br>
      <v-toolbar flat>
        <v-toolbar-title style="font-family: 'Cairo', sans-serif;">
          {{ $t('caseCategories.title') }}
        </v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
        <v-spacer></v-spacer>
        
        <!-- Add Category Dialog -->
        <v-dialog v-model="categoryDialog" max-width="600px">
          <template v-slot:activator="{ on, attrs }">
            <v-btn 
              color="primary" 
              dark 
              class="mb-2" 
              v-bind="attrs" 
              v-on="on" 
              style="color:#fff;font-family: 'Cairo'"
              @click="editedIndex = -1"
            >
              <i class="fas fa-plus" style="position: relative;left:5px"></i>
              {{ $t('caseCategories.add_new_category') }}
            </v-btn>
          </template>

          <v-form ref="form" v-model="valid">
            <v-card>
              <v-toolbar dark color="primary lighten-1 mb-5">
                <v-toolbar-title>
                  <h3 style="color:#fff;font-family: 'Cairo'">
                    {{ formTitle }}
                  </h3>
                </v-toolbar-title>
                <v-spacer />
                <v-btn @click="close()" icon>
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </v-toolbar>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.name_ar"
                        style="direction: rtl;text-align: right;"
                        :rules="[rules.required]"
                        label="اسم الفئة (عربي)"
                        outlined
                        dense
                      ></v-text-field>
                    </v-col>
                    
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.item_cost"
                        style="direction: rtl;text-align: right;"
                        label="التكلفة الافتراضية"
                        type="number"
                        min="0"
                        step="0.01"
                        outlined
                        dense
                        suffix="د.ع"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red darken-1" text @click="close()">
                  إلغاء
                </v-btn>
                <v-btn 
                  :loading="loadSave" 
                  color="blue darken-1" 
                  @click="save()" 
                  text
                >
                  حفظ
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-form>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">
              تأكيد الحذف
            </v-card-title>
            <v-card-text>
              هل أنت متأكد من حذف هذه الفئة؟ لا يمكن التراجع عن هذا الإجراء.
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="deleteDialog = false">
                إلغاء
              </v-btn>
              <v-btn 
                color="red darken-1" 
                text 
                @click="deleteItemConfirm"
                :loading="deleteLoading"
              >
                حذف
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </div>

    <!-- Categories Data Table -->
    <v-container fluid>
      <v-card>
        <v-card-title>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="البحث في الفئات"
            single-line
            hide-details
            style="direction: rtl;text-align: right;"
          ></v-text-field>
        </v-card-title>
        
        <v-data-table
          :headers="headers"
          :items="categories"
          :search="search"
          :loading="loading"
          class="elevation-1"
          :items-per-page="10"
          :footer-props="{
            'items-per-page-text': 'عدد العناصر في الصفحة:',
            'items-per-page-all-text': 'الكل'
          }"
        >
          <template v-slot:[`item.item_cost`]="{ item }">
            <span v-if="item.item_cost">
              {{ item.item_cost | currency }} د.ع
            </span>
            <span v-else class="grey--text">غير محدد</span>
          </template>
          
          <template v-slot:[`item.created_at`]="{ item }">
            {{ formatDate(item.created_at) }}
          </template>
          
          <template v-slot:[`item.actions`]="{ item }">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-icon
                  class="ml-2"
                  @click="editItem(item)"
                  v-bind="attrs"
                  v-on="on"
                >
                  mdi-pencil
                </v-icon>
              </template>
              <span>تعديل</span>
            </v-tooltip>
            
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-icon
                  @click="deleteItem(item)"
                  v-bind="attrs"
                  v-on="on"
                >
                  mdi-delete
                </v-icon>
              </template>
              <span>حذف</span>
            </v-tooltip>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'CaseCategories',
  
  data() {
    return {
      categoryDialog: false,
      deleteDialog: false,
      loading: false,
      loadSave: false,
      deleteLoading: false,
      search: '',
      valid: false,
      editedIndex: -1,
      
      categories: [],
      
      headers: [
        {
          text: 'اسم الفئة',
          value: 'name_ar',
          align: 'start',
          width: '40%'
        },
        {
          text: 'التكلفة الافتراضية',
          value: 'item_cost',
          align: 'center',
          width: '25%'
        },
        {
          text: 'تاريخ الإنشاء',
          value: 'created_at',
          align: 'center',
          width: '20%'
        },
        {
          text: 'الإجراءات',
          value: 'actions',
          sortable: false,
          align: 'center',
          width: '15%'
        }
      ],
      
      editedItem: {
        name_ar: '',
        item_cost: null
      },
      
      defaultItem: {
        name_ar: '',
        item_cost: null
      },
      
      rules: {
        required: value => !!value || 'هذا الحقل مطلوب'
      }
    }
  },
  
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'إضافة فئة جديدة' : 'تعديل الفئة'
    }
  },
  
  watch: {
    categoryDialog(val) {
      val || this.close()
    },
    deleteDialog(val) {
      val || this.closeDelete()
    }
  },
  
  created() {
    this.initialize()
  },
  
  methods: {
    // Load categories from API
    async initialize() {
      this.loading = true
      try {
        const response = await axios.get('/case-categories', {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          }
        })
        
        this.categories = response.data.data || []
      } catch (error) {
        console.error('Error loading categories:', error)
        this.$swal.fire({
          title: 'خطأ',
          text: 'فشل في تحميل فئات الحالات',
          icon: 'error',
          confirmButtonText: 'موافق'
        })
      } finally {
        this.loading = false
      }
    },
    
    // Edit existing category
    editItem(item) {
      this.editedIndex = this.categories.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.categoryDialog = true
    },
    
    // Delete category
    deleteItem(item) {
      this.editedIndex = this.categories.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.deleteDialog = true
    },
    
    // Confirm delete
    async deleteItemConfirm() {
      this.deleteLoading = true
      try {
        await axios.delete(`/case-categories/${this.editedItem.id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
          }
        })
        
        this.categories.splice(this.editedIndex, 1)
        this.closeDelete()
        
        this.$swal.fire({
          title: 'نجح',
          text: 'تم حذف الفئة بنجاح',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        })
      } catch (error) {
        console.error('Error deleting category:', error)
        this.$swal.fire({
          title: 'خطأ',
          text: 'فشل في حذف الفئة',
          icon: 'error',
          confirmButtonText: 'موافق'
        })
      } finally {
        this.deleteLoading = false
      }
    },
    
    // Close dialogs
    close() {
      this.categoryDialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    
    closeDelete() {
      this.deleteDialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    
    // Save category (create or update)
    async save() {
      if (!this.$refs.form.validate()) {
        return
      }
      
      this.loadSave = true
      try {
        const categoryData = {
          name_ar: this.editedItem.name_ar,
          item_cost: this.editedItem.item_cost
        }
        
        let response
        if (this.editedIndex > -1) {
          // Update existing category
          response = await axios.put(`/case-categories/${this.editedItem.id}`, categoryData, {
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
            }
          })
          
          Object.assign(this.categories[this.editedIndex], response.data.data)
        } else {
          // Create new category
          response = await axios.post('/case-categories', categoryData, {
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
            }
          })
          
          this.categories.push(response.data.data)
        }
        
        this.close()
        
        this.$swal.fire({
          title: 'نجح',
          text: this.editedIndex > -1 ? 'تم تحديث الفئة بنجاح' : 'تم إضافة الفئة بنجاح',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        })
      } catch (error) {
        console.error('Error saving category:', error)
        
        let errorMessage = 'فشل في حفظ الفئة'
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        }
        
        this.$swal.fire({
          title: 'خطأ',
          text: errorMessage,
          icon: 'error',
          confirmButtonText: 'موافق'
        })
      } finally {
        this.loadSave = false
      }
    },
    
    // Format date for display
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  },
  
  filters: {
    currency(value) {
      if (!value) return '0'
      return new Intl.NumberFormat('ar-EG').format(value)
    }
  }
}
</script>

<style scoped>
.v-data-table {
  direction: rtl;
}

.v-toolbar-title {
  font-weight: bold;
}

.v-card-title {
  direction: rtl;
}
</style>