import axios from 'axios'
import store from '../store'

class NotificationService {
  constructor() {
    this.notifications = []
    this.checkInterval = null
    this.isRunning = false
    this.lastBrowserNotificationTime = new Map() // Track last browser notification per patient
    this.audioEnabled = true // Default enable sound
    this.notificationSound = null
    this.dismissedNotifications = this.loadDismissedNotifications() // Track dismissed notifications
    this.initSound()
  }

  // Load dismissed notification IDs from localStorage
  loadDismissedNotifications() {
    try {
      const dismissed = localStorage.getItem('dismissed-notifications')
      return dismissed ? new Set(JSON.parse(dismissed)) : new Set()
    } catch (error) {
      console.warn('Error loading dismissed notifications:', error)
      return new Set()
    }
  }

  // Save dismissed notification IDs to localStorage
  saveDismissedNotifications() {
    try {
      localStorage.setItem('dismissed-notifications', JSON.stringify([...this.dismissedNotifications]))
    } catch (error) {
      console.warn('Error saving dismissed notifications:', error)
    }
  }

  // Initialize notification sound
  initSound() {
    try {
      // Create audio element for notification sound
      this.notificationSound = new Audio()
      // Use a simple beep sound data URL or you can use an actual sound file
      this.notificationSound.src = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+Lyt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+Lzt2QdCSGN0fPNeSsFJHfH8N2QQAoUXrTp66hVFApGn+L'
      this.notificationSound.volume = 0.3 // Set volume to 30%
      this.notificationSound.preload = 'auto'
    } catch (error) {
      console.warn('Could not initialize notification sound:', error)
      this.notificationSound = null
    }
  }

  // Play notification sound
  playNotificationSound() {
    if (this.audioEnabled && this.notificationSound) {
      try {
        // Reset and play the sound
        this.notificationSound.currentTime = 0
        this.notificationSound.play().catch(error => {
          console.warn('Could not play notification sound:', error)
        })
      } catch (error) {
        console.warn('Error playing notification sound:', error)
      }
    }
  }

  // Enable/disable sound notifications
  setAudioEnabled(enabled) {
    this.audioEnabled = enabled
    localStorage.setItem('notification-audio-enabled', enabled.toString())
  }

  // Get audio enabled state
  isAudioEnabled() {
    return this.audioEnabled
  }

  // Initialize notification service for accountant role
  init() {
    const userRole = store.state.AdminInfo?.role
    
    // Load audio preference from localStorage
    const savedAudioSetting = localStorage.getItem('notification-audio-enabled')
    if (savedAudioSetting !== null) {
      this.audioEnabled = savedAudioSetting === 'true'
    }
    
    // Only run for accountants (role: accounter)
    if (userRole === 'accounter') {
      this.startPeriodicCheck()
    }
  }

  // Start periodic checking for cases without bills
  startPeriodicCheck() {
    if (this.isRunning) return
    
    this.isRunning = true
    
    // Check immediately
    this.checkCasesWithoutBills()
    
    // Get interval from localStorage or use default (5 seconds)
    const savedInterval = localStorage.getItem('notification-check-interval')
    const intervalSeconds = savedInterval ? parseInt(savedInterval) : 5
    
    // Then check at the specified interval
    this.checkInterval = setInterval(() => {
      this.checkCasesWithoutBills()
    }, intervalSeconds * 1000) // Convert to milliseconds
  }

  // Stop periodic checking
  stopPeriodicCheck() {
    if (this.checkInterval) {
      clearInterval(this.checkInterval)
      this.checkInterval = null
    }
    this.isRunning = false
  }

  // Fetch cases without bills using the provided API
  async checkCasesWithoutBills() {
    try {
      const token = store.state.AdminInfo?.token
      if (!token) {
        console.error('No authentication token available')
        return
      }

      const response = await axios.get('https://titaniumapi.tctate.com/api/cases/UserCasesv2', {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      })

      if (response.data && response.data.data) {
        const cases = response.data.data
        const casesWithoutBills = cases.filter(caseItem => 
          !caseItem.bills || caseItem.bills.length === 0
        )

        // Clean up notifications for patients who no longer have cases without bills
        this.cleanupResolvedNotifications(casesWithoutBills)

        // If there are cases without bills, create notifications
        if (casesWithoutBills.length > 0) {
          this.createNotifications(casesWithoutBills)
        }
      }
    } catch (error) {
      console.error('Error fetching cases:', error)
    }
  }

  // Clean up notifications for cases that now have bills
  cleanupResolvedNotifications(currentCasesWithoutBills) {
    const currentPatientIds = new Set()
    
    // Get all patient IDs that currently have cases without bills
    currentCasesWithoutBills.forEach(caseItem => {
      const patientId = caseItem.patient?.id || caseItem.patient_id
      currentPatientIds.add(patientId)
    })

    // Remove notifications for patients who no longer have cases without bills
    const initialLength = this.notifications.length
    this.notifications = this.notifications.filter(notification => {
      if (notification.type === 'billing_required') {
        return currentPatientIds.has(notification.patientId)
      }
      return true
    })

    // Update store if notifications were removed
    if (this.notifications.length !== initialLength) {
      this.updateNotificationStore()
    }
  }

  // Create notifications for cases without bills
  createNotifications(casesWithoutBills) {
    // Group cases by patient for better UX
    const patientCases = {}
    
    casesWithoutBills.forEach(caseItem => {
      const patientId = caseItem.patient?.id || caseItem.patient_id
      const patientName = caseItem.patient?.name || 'مريض غير محدد'
      
      if (!patientCases[patientId]) {
        patientCases[patientId] = {
          patientId,
          patientName,
          cases: []
        }
      }
      
      patientCases[patientId].cases.push(caseItem)
    })

    // Create notifications for each patient (only if not already exists)
    Object.values(patientCases).forEach(patient => {
      const existingNotification = this.notifications.find(
        notification => notification.patientId === patient.patientId && 
        notification.caseCount === patient.cases.length // Also check case count to detect changes
      )

      if (!existingNotification) {
        // Remove old notification for this patient if case count changed
        this.notifications = this.notifications.filter(
          notification => notification.patientId !== patient.patientId
        )

        const notification = {
          id: `patient-${patient.patientId}-${Date.now()}`,
          type: 'billing_required',
          patientId: patient.patientId,
          patientName: patient.patientName,
          caseCount: patient.cases.length,
          cases: patient.cases,
          title: 'إضافة فاتورة مطلوبة',
          message: `المريض ${patient.patientName} لديه ${patient.cases.length} حالة بدون فاتورة`,
          timestamp: new Date(),
          isRead: false,
          priority: 'high'
        }

        this.notifications.unshift(notification) // Add to beginning
        this.showBrowserNotification(notification)
        
        // Play notification sound for new notifications
        this.playNotificationSound()
      }
    })

    // Limit notifications to 50 to prevent memory issues
    if (this.notifications.length > 50) {
      this.notifications = this.notifications.slice(0, 50)
    }

    // Update store with new notifications
    this.updateNotificationStore()
  }

  // Show browser notification if permission granted (with throttling)
  async showBrowserNotification(notification) {
    if ('Notification' in window && Notification.permission === 'granted') {
      // Throttle browser notifications to once every 30 seconds per patient
      const now = Date.now()
      const lastNotificationTime = this.lastBrowserNotificationTime.get(notification.patientId)
      
      if (lastNotificationTime && (now - lastNotificationTime) < 30000) {
        return // Skip showing browser notification if shown recently
      }
      
      this.lastBrowserNotificationTime.set(notification.patientId, now)

      const browserNotification = new Notification(notification.title, {
        body: notification.message,
        icon: '/logo.png',
        badge: '/logo.png',
        tag: `billing-${notification.patientId}`, // Prevent duplicate notifications
        requireInteraction: true,
        actions: [
          {
            action: 'view',
            title: 'عرض المريض'
          },
          {
            action: 'dismiss',
            title: 'تجاهل'
          }
        ]
      })

      browserNotification.onclick = () => {
        this.handleNotificationClick(notification)
        browserNotification.close()
      }

      // Auto-close after 10 seconds
      setTimeout(() => {
        browserNotification.close()
      }, 10000)
    }
  }

  // Handle notification click - navigate to patient page
  handleNotificationClick(notification) {
    // Mark as read
    this.markAsRead(notification.id)
    
    // Navigate to patient page
    const router = window.app?.$router
    if (router && notification.patientId) {
      router.push(`/patient/${notification.patientId}`)
    }
  }

  // Request notification permission
  async requestNotificationPermission() {
    if ('Notification' in window) {
      const permission = await Notification.requestPermission()
      return permission === 'granted'
    }
    return false
  }

  // Get all notifications (excluding dismissed ones)
  getNotifications() {
    return this.notifications.filter(n => !this.dismissedNotifications.has(n.id))
  }

  // Get unread notifications count (excluding dismissed ones)
  getUnreadCount() {
    return this.notifications.filter(n => !n.isRead && !this.dismissedNotifications.has(n.id)).length
  }

  // Mark notification as read
  markAsRead(notificationId) {
    const notification = this.notifications.find(n => n.id === notificationId)
    if (notification) {
      notification.isRead = true
      this.updateNotificationStore()
    }
  }

  // Dismiss notification (hide permanently)
  dismissNotification(notificationId) {
    this.dismissedNotifications.add(notificationId)
    this.saveDismissedNotifications()
    this.updateNotificationStore()
  }

  // Mark all notifications as read
  markAllAsRead() {
    this.notifications.forEach(notification => {
      notification.isRead = true
    })
    this.updateNotificationStore()
  }

  // Delete notification
  deleteNotification(notificationId) {
    this.notifications = this.notifications.filter(n => n.id !== notificationId)
    this.updateNotificationStore()
  }

  // Clear all notifications
  clearAll() {
    this.notifications = []
    this.dismissedNotifications.clear()
    this.saveDismissedNotifications()
    this.updateNotificationStore()
  }

  // Update notification store/state
  updateNotificationStore() {
    // Update store if available
    if (store && store.commit) {
      store.commit('SET_NOTIFICATIONS', {
        notifications: this.notifications,
        unreadCount: this.getUnreadCount()
      })
    }

    // Trigger custom event for components to listen
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('notifications-updated', {
        detail: {
          notifications: this.notifications,
          unreadCount: this.getUnreadCount()
        }
      }))
    }
  }

  // Cleanup when service is destroyed
  destroy() {
    this.stopPeriodicCheck()
    this.notifications = []
  }
}

// Create singleton instance
const notificationService = new NotificationService()

export default notificationService
