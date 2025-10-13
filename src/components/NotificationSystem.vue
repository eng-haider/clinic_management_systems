<template>
  <div class="notification-system">
    <!-- Notification Bell Icon -->
    <v-menu
      v-model="showNotifications"
      :close-on-content-click="false"
      :nudge-width="400"
      offset-y
      left
    >
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          icon
          v-bind="attrs"
          v-on="on"
          class="notification-btn"
          @click="markAllAsRead"
        >
          <v-badge
            :value="unreadCount > 0"
            :content="unreadCount"
            color="red"
            overlap
          >
            <v-icon>mdi-bell</v-icon>
          </v-badge>
        </v-btn>
      </template>

      <v-card class="notification-card" max-width="400">
        <v-card-title class="notification-header">
          <span>التنبيهات</span>
          <v-chip small color="green" text-color="white" class="ml-2" v-if="notifications.length > 0">
            تحديث مباشر
          </v-chip>
          <v-spacer></v-spacer>
          <v-btn
            icon
            small
            @click="clearAllNotifications"
            v-if="notifications.length > 0"
          >
            <v-icon small>mdi-delete-sweep</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <div class="notification-list" v-if="notifications.length > 0">
          <div
            v-for="notification in notifications.slice(0, 10)"
            :key="notification.id"
            class="notification-item"
            :class="{ 'unread': !notification.isRead }"
            @click="handleNotificationClick(notification)"
          >
            <div class="notification-content">
              <div class="notification-title">
                <v-icon
                  small
                  :color="notification.priority === 'high' ? 'red' : 'orange'"
                  class="mr-2"
                >
                  mdi-currency-usd
                </v-icon>
                {{ notification.title }}
              </div>
              
              <div class="notification-message">
                {{ notification.message }}
              </div>
              
              <div class="notification-meta">
                <span class="notification-time">
                  {{ formatTimeAgo(notification.timestamp) }}
                </span>
                <span class="notification-cases" v-if="notification.caseCount">
                  {{ notification.caseCount }} حالة
                </span>
              </div>
            </div>

            <v-btn
              icon
              small
              @click.stop="deleteNotification(notification.id)"
              class="notification-delete"
            >
              <v-icon small>mdi-close</v-icon>
            </v-btn>
          </div>
        </div>

        <div v-else class="no-notifications">
          <v-icon large color="grey lighten-2">mdi-bell-off</v-icon>
          <p>لا توجد تنبيهات</p>
        </div>

        <v-divider v-if="notifications.length > 10"></v-divider>
        
        <v-card-actions v-if="notifications.length > 10">
          <v-btn
            text
            small
            color="primary"
            @click="showAllNotifications"
          >
            عرض جميع التنبيهات ({{ notifications.length }})
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-menu>

    <!-- Full Notifications Dialog -->
    <v-dialog
      v-model="showAllDialog"
      max-width="600px"
      scrollable
    >
      <v-card>
        <v-card-title>
          جميع التنبيهات
          <v-spacer></v-spacer>
          <v-btn icon @click="showAllDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text class="notification-dialog-content">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="notification-item full-width"
            :class="{ 'unread': !notification.isRead }"
            @click="handleNotificationClick(notification)"
          >
            <div class="notification-content">
              <div class="notification-title">
                <v-icon
                  small
                  :color="notification.priority === 'high' ? 'red' : 'orange'"
                  class="mr-2"
                >
                  mdi-currency-usd
                </v-icon>
                {{ notification.title }}
              </div>
              
              <div class="notification-message">
                {{ notification.message }}
              </div>
              
              <div class="notification-meta">
                <span class="notification-time">
                  {{ formatTimeAgo(notification.timestamp) }}
                </span>
                <span class="notification-cases" v-if="notification.caseCount">
                  {{ notification.caseCount }} حالة
                </span>
              </div>
            </div>

            <v-btn
              icon
              small
              @click.stop="deleteNotification(notification.id)"
              class="notification-delete"
            >
              <v-icon small>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import notificationService from '@/services/notificationService'

export default {
  name: 'NotificationSystem',
  
  data() {
    return {
      showNotifications: false,
      showAllDialog: false
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
    // Initialize notification service for accountant role
    if (this.userRole === 'accounter') {
      notificationService.init()
      
      // Request notification permission
      this.requestNotificationPermission()
    }

    // Listen for notification updates
    window.addEventListener('notifications-updated', this.handleNotificationUpdate)
  },

  beforeDestroy() {
    // Cleanup
    window.removeEventListener('notifications-updated', this.handleNotificationUpdate)
    notificationService.destroy()
  },

  methods: {
    async requestNotificationPermission() {
      if ('Notification' in window && Notification.permission === 'default') {
        try {
          const granted = await notificationService.requestNotificationPermission()
          if (granted) {
            this.$swal.fire({
              position: "top-end",
              icon: "success",
              title: "تم تفعيل التنبيهات",
              text: "سيتم إشعارك عند وجود حالات تحتاج فواتير",
              showConfirmButton: false,
              timer: 3000
            })
          }
        } catch (error) {
          console.error('Error requesting notification permission:', error)
        }
      }
    },

    handleNotificationUpdate() {
      // This will trigger reactivity in the computed properties
      // The store is already updated by the notification service
    },

    handleNotificationClick(notification) {
      // Mark as read
      notificationService.markAsRead(notification.id)
      
      // Navigate to patient page
      if (notification.patientId) {
        this.$router.push(`/patient/${notification.patientId}`)
        this.showNotifications = false
        this.showAllDialog = false
      }
    },

    markAllAsRead() {
      if (this.unreadCount > 0) {
        notificationService.markAllAsRead()
      }
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
          this.showNotifications = false
        }
      })
    },

    showAllNotifications() {
      this.showNotifications = false
      this.showAllDialog = true
    },

    formatTimeAgo(timestamp) {
      const now = new Date()
      const diff = now - new Date(timestamp)
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      const days = Math.floor(diff / 86400000)

      if (minutes < 1) return 'الآن'
      if (minutes < 60) return `منذ ${minutes} دقيقة`
      if (hours < 24) return `منذ ${hours} ساعة`
      if (days < 7) return `منذ ${days} يوم`
      
      return new Date(timestamp).toLocaleDateString('ar-SA')
    }
  }
}
</script>

<style scoped>
.notification-system {
  position: relative;
}

.notification-btn {
  margin-left: 8px;
}

.notification-card {
  box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important;
}

.notification-header {
  background: #f5f5f5;
  font-weight: 600;
  padding: 12px 16px;
}

.notification-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background: #f9f9f9;
}

.notification-item.unread {
  background: #f0f8ff;
  border-left: 4px solid #2196F3;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
}

.notification-message {
  color: #666;
  font-size: 13px;
  margin-bottom: 8px;
  line-height: 1.4;
}

.notification-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: #999;
}

.notification-time {
  font-size: 11px;
}

.notification-cases {
  background: #e3f2fd;
  color: #1976d2;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 500;
}

.notification-delete {
  margin-left: 8px;
  opacity: 0.6;
}

.notification-delete:hover {
  opacity: 1;
}

.no-notifications {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.no-notifications p {
  margin-top: 8px;
  margin-bottom: 0;
}

.notification-dialog-content {
  padding: 0 !important;
}

.notification-item.full-width {
  margin: 0;
}

/* Arabic RTL support */
[dir="rtl"] .notification-item {
  border-left: none;
  border-right: 4px solid #2196F3;
}

[dir="rtl"] .notification-delete {
  margin-left: 0;
  margin-right: 8px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .notification-card {
    max-width: 100vw !important;
    margin: 0 8px;
  }
  
  .notification-item {
    padding: 8px 12px;
  }
  
  .notification-title {
    font-size: 13px;
  }
  
  .notification-message {
    font-size: 12px;
  }
}
</style>
