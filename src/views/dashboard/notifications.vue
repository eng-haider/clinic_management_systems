<template>
  <v-container id="notifications" fluid tag="section">
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title class="pa-4">
            <v-icon large class="mr-3">mdi-bell</v-icon>
            التنبيهات والإشعارات
            <v-spacer></v-spacer>
            
            <!-- Actions -->
            <v-btn
              color="primary"
              outlined
              @click="markAllAsRead"
              :disabled="unreadCount === 0"
              class="mr-2"
            >
              <v-icon left>mdi-check-all</v-icon>
              تحديد الكل كمقروء
            </v-btn>
            
            <v-btn
              color="error"
              outlined
              @click="clearAllNotifications"
              :disabled="notifications.length === 0"
            >
              <v-icon left>mdi-delete-sweep</v-icon>
              حذف جميع التنبيهات
            </v-btn>
          </v-card-title>

          <v-divider></v-divider>

          <!-- Notifications List -->
          <div v-if="notifications.length > 0">
            <v-list three-line>
              <div v-for="(notification, index) in notifications" :key="notification.id">
                <v-list-item
                  :class="{ 'notification-unread': !notification.isRead }"
                  @click="handleNotificationClick(notification)"
                  link
                >
                >
                  <v-list-item-avatar>
                    <v-icon
                      large
                      :color="getNotificationColor(notification)"
                    >
                      {{ getNotificationIcon(notification) }}
                    </v-icon>
                  </v-list-item-avatar>

                  <v-list-item-content>
                    <v-list-item-title class="notification-title">
                      {{ notification.title }}
                      <v-chip
                        v-if="!notification.isRead"
                        x-small
                        color="primary"
                        class="ml-2"
                      >
                        جديد
                      </v-chip>
                    </v-list-item-title>
                    
                    <v-list-item-subtitle class="notification-message">
                      {{ notification.message }}
                    </v-list-item-subtitle>
                    
                    <v-list-item-subtitle class="notification-meta">
                      <span class="notification-time">
                        {{ formatDateTime(notification.timestamp) }}
                      </span>
                      <span v-if="notification.caseCount" class="notification-cases">
                        {{ notification.caseCount }} حالة تحتاج فاتورة
                      </span>
                    </v-list-item-subtitle>
                  </v-list-item-content>

                  <v-list-item-action>
                    <v-btn
                      icon
                      @click.stop="deleteNotification(notification.id)"
                    >
                      <v-icon color="grey darken-1">mdi-delete</v-icon>
                    </v-btn>
                  </v-list-item-action>
                </v-list-item>

                <v-divider
                  v-if="index < notifications.length - 1"
                  :key="`divider-${notification.id}`"
                ></v-divider>
              </div>
            </v-list>
          </div>

          <!-- Empty State -->
          <div v-else class="empty-state">
            <v-icon size="80" color="grey lighten-2">mdi-bell-off</v-icon>
            <h3 class="mt-4 text-h5 grey--text">لا توجد تنبيهات</h3>
            <p class="text-body-1 grey--text">سيتم عرض التنبيهات هنا عند توفرها</p>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Notification Settings Card -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>إعدادات التنبيهات</v-card-title>
          <v-card-text>
            <v-switch
              v-model="browserNotificationsEnabled"
              label="تفعيل تنبيهات المتصفح"
              @change="toggleBrowserNotifications"
              :disabled="!notificationSupported"
            ></v-switch>
            
            <v-switch
              v-model="audioNotificationsEnabled"
              label="تفعيل الصوت للتنبيهات"
              @change="toggleAudioNotifications"
            ></v-switch>
            
            <v-btn
              v-if="audioNotificationsEnabled"
              small
              outlined
              color="primary"
              @click="testNotificationSound"
              class="mb-3"
            >
              <v-icon left small>mdi-volume-high</v-icon>
              تجربة الصوت
            </v-btn>
            
            <v-text-field
              v-model="checkInterval"
              label="فترة التحقق (بالثواني)"
              type="number"
              min="5"
              max="300"
              outlined
              @change="updateCheckInterval"
            ></v-text-field>
            
            <v-alert
              v-if="!notificationSupported"
              type="warning"
              dense
              text
            >
              متصفحك لا يدعم التنبيهات
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>إحصائيات التنبيهات</v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="6">
                <div class="text-center">
                  <div class="text-h4 primary--text">{{ notifications.length }}</div>
                  <div class="text-caption">إجمالي التنبيهات</div>
                </div>
              </v-col>
              <v-col cols="6">
                <div class="text-center">
                  <div class="text-h4 error--text">{{ unreadCount }}</div>
                  <div class="text-caption">غير مقروء</div>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import notificationService from '@/services/notificationService'

export default {
  name: 'NotificationsPage',
  
  data() {
    return {
      checkInterval: 5, // Default 5 seconds
      browserNotificationsEnabled: false,
      audioNotificationsEnabled: true,
      notificationSupported: false
    }
  },

  computed: {
    notifications() {
      return this.$store.getters.notifications || []
    },
    
    unreadCount() {
      return this.$store.getters.unreadNotificationsCount || 0
    },

    userRole() {
      return this.$store.state.AdminInfo?.role
    }
  },

  mounted() {
    // Check if user is accountant
    if (this.userRole !== 'accounter') {
      this.$router.push('/')
      return
    }

    // Initialize notification service
    notificationService.init()

    // Check notification support
    this.notificationSupported = 'Notification' in window
    this.browserNotificationsEnabled = Notification.permission === 'granted'
    
    // Load audio notification setting
    const savedAudioSetting = localStorage.getItem('notification-audio-enabled')
    if (savedAudioSetting !== null) {
      this.audioNotificationsEnabled = savedAudioSetting === 'true'
    } else {
      this.audioNotificationsEnabled = notificationService.isAudioEnabled()
    }

    // Listen for notification updates
    window.addEventListener('notifications-updated', this.handleNotificationUpdate)

    // Load saved check interval
    const savedInterval = localStorage.getItem('notification-check-interval')
    if (savedInterval) {
      this.checkInterval = parseInt(savedInterval)
    }
  },

  beforeDestroy() {
    window.removeEventListener('notifications-updated', this.handleNotificationUpdate)
  },

  methods: {
    handleNotificationUpdate() {
      // This will trigger reactivity in computed properties
      this.$forceUpdate()
    },

    handleNotificationClick(notification) {
      // Mark as read
      notificationService.markAsRead(notification.id)
      
      // Navigate to patient page
      if (notification.patientId) {
        this.$router.push(`/patient/${notification.patientId}`)
      }
    },

    markAllAsRead() {
      notificationService.markAllAsRead()
      this.$swal.fire({
        position: "top-end",
        icon: "success",
        title: "تم تحديد جميع التنبيهات كمقروءة",
        showConfirmButton: false,
        timer: 1500
      })
    },

    deleteNotification(notificationId) {
      notificationService.deleteNotification(notificationId)
    },

    clearAllNotifications() {
      this.$swal.fire({
        title: 'حذف جميع التنبيهات؟',
        text: 'سيتم حذف جميع التنبيهات نهائياً',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'حذف',
        cancelButtonText: 'إلغاء'
      }).then((result) => {
        if (result.isConfirmed) {
          notificationService.clearAll()
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم حذف جميع التنبيهات",
            showConfirmButton: false,
            timer: 1500
          })
        }
      })
    },

    async toggleBrowserNotifications() {
      if (this.browserNotificationsEnabled) {
        const granted = await notificationService.requestNotificationPermission()
        this.browserNotificationsEnabled = granted
        
        if (granted) {
          this.$swal.fire({
            position: "top-end",
            icon: "success",
            title: "تم تفعيل تنبيهات المتصفح",
            showConfirmButton: false,
            timer: 2000
          })
        }
      }
    },

    toggleAudioNotifications() {
      notificationService.setAudioEnabled(this.audioNotificationsEnabled)
      
      if (this.audioNotificationsEnabled) {
        // Test the notification sound
        notificationService.playNotificationSound()
        
        this.$swal.fire({
          position: "top-end",
          icon: "success",
          title: "تم تفعيل الصوت للتنبيهات",
          showConfirmButton: false,
          timer: 2000
        })
      } else {
        this.$swal.fire({
          position: "top-end",
          icon: "info",
          title: "تم إيقاف الصوت للتنبيهات",
          showConfirmButton: false,
          timer: 2000
        })
      }
    },

    testNotificationSound() {
      notificationService.playNotificationSound()
      
      this.$swal.fire({
        position: "top-end",
        icon: "success",
        title: "تم تشغيل صوت التنبيه",
        showConfirmButton: false,
        timer: 1500
      })
    },

    updateCheckInterval() {
      if (this.checkInterval >= 5 && this.checkInterval <= 300) {
        localStorage.setItem('notification-check-interval', this.checkInterval.toString())
        
        // Update notification service interval
        notificationService.stopPeriodicCheck()
        notificationService.startPeriodicCheck()
        
        this.$swal.fire({
          position: "top-end",
          icon: "success",
          title: `تم تحديث فترة التحقق إلى ${this.checkInterval} ثانية`,
          showConfirmButton: false,
          timer: 2000
        })
      }
    },

    getNotificationIcon(notification) {
      switch (notification.type) {
        case 'billing_required':
          return 'mdi-currency-usd'
        default:
          return 'mdi-bell'
      }
    },

    getNotificationColor(notification) {
      switch (notification.priority) {
        case 'high':
          return 'red'
        case 'medium':
          return 'orange'
        default:
          return 'blue'
      }
    },

    formatDateTime(timestamp) {
      const date = new Date(timestamp)
      return date.toLocaleString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.notification-unread {
  background: #f0f8ff !important;
  border-left: 4px solid #2196F3;
}

.notification-title {
  font-weight: 600 !important;
  font-size: 16px;
}

.notification-message {
  color: #666 !important;
  margin-top: 4px !important;
  line-height: 1.4 !important;
}

.notification-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px !important;
  font-size: 12px !important;
}

.notification-time {
  color: #999;
}

.notification-cases {
  background: #e3f2fd;
  color: #1976d2;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

/* Arabic RTL support */
[dir="rtl"] .notification-unread {
  border-left: none;
  border-right: 4px solid #2196F3;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .notification-meta {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .notification-cases {
    margin-top: 4px;
  }
}
</style>
