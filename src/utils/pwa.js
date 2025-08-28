/**
 * PWA Utility functions
 */

class PWAManager {
  constructor() {
    this.deferredPrompt = null;
    this.isInstalled = false;
    this.isStandalone = false;
    this.init();
  }

  init() {
    // Check if app is running in standalone mode
    this.isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                        window.navigator.standalone ||
                        document.referrer.includes('android-app://');

    // Check if app is already installed
    this.isInstalled = this.isStandalone;

    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt.bind(this));
    
    // Listen for appinstalled event
    window.addEventListener('appinstalled', this.handleAppInstalled.bind(this));

    console.log('PWA Manager initialized', {
      isStandalone: this.isStandalone,
      isInstalled: this.isInstalled
    });
  }

  handleBeforeInstallPrompt(event) {
    console.log('Before install prompt event received');
    event.preventDefault();
    this.deferredPrompt = event;
    
    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('pwa-install-available'));
  }

  handleAppInstalled() {
    console.log('App installed successfully');
    this.isInstalled = true;
    this.deferredPrompt = null;
    
    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('pwa-installed'));
  }

  async promptInstall() {
    if (!this.deferredPrompt) {
      throw new Error('No install prompt available');
    }

    // Show the install prompt
    this.deferredPrompt.prompt();
    
    // Wait for the user to respond to the prompt
    const result = await this.deferredPrompt.userChoice;
    
    console.log('User choice:', result.outcome);
    
    // Clear the deferred prompt
    this.deferredPrompt = null;
    
    return result.outcome;
  }

  canInstall() {
    return this.deferredPrompt !== null && !this.isInstalled;
  }

  getInstallStatus() {
    return {
      canInstall: this.canInstall(),
      isInstalled: this.isInstalled,
      isStandalone: this.isStandalone
    };
  }

  // Check if notifications are supported
  isNotificationSupported() {
    return 'Notification' in window;
  }

  // Request notification permission
  async requestNotificationPermission() {
    if (!this.isNotificationSupported()) {
      return 'unsupported';
    }

    if (Notification.permission === 'granted') {
      return 'granted';
    }

    if (Notification.permission === 'denied') {
      return 'denied';
    }

    const permission = await Notification.requestPermission();
    return permission;
  }

  // Show notification
  showNotification(title, options = {}) {
    if (Notification.permission === 'granted') {
      const notification = new Notification(title, {
        icon: '/logo.png',
        badge: '/logo.png',
        ...options
      });

      return notification;
    }
  }

  // Get device info
  getDeviceInfo() {
    const userAgent = navigator.userAgent;
    const platform = navigator.platform;
    const standalone = this.isStandalone;

    return {
      userAgent,
      platform,
      standalone,
      isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent),
      isIOS: /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream,
      isAndroid: /Android/.test(userAgent),
      isChrome: /Chrome/.test(userAgent) && /Google Inc/.test(navigator.vendor),
      isSafari: /Safari/.test(userAgent) && /Apple Computer/.test(navigator.vendor),
      isFirefox: /Firefox/.test(userAgent)
    };
  }

  // Add to home screen guidance
  getAddToHomeScreenGuidance() {
    const device = this.getDeviceInfo();
    
    if (device.isIOS) {
      return {
        supported: true,
        steps: [
          'Tap the Share button',
          'Scroll down and tap "Add to Home Screen"',
          'Enter a name for the app',
          'Tap "Add"'
        ]
      };
    } else if (device.isAndroid) {
      return {
        supported: true,
        steps: [
          'Tap the menu button (three dots)',
          'Tap "Add to Home screen"',
          'Confirm by tapping "Add"'
        ]
      };
    } else {
      return {
        supported: false,
        steps: []
      };
    }
  }
}

// Create singleton instance
const pwaManager = new PWAManager();

export default pwaManager;
