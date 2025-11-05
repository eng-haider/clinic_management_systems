// Cache Management Utility
export default class CacheManager {
  static async clearCache() {
    try {
      if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        // Send message to service worker to clear cache
        const response = await this.sendMessageToSW({ type: 'CLEAR_CACHE' });
        if (response.success) {
          console.log('Cache cleared successfully');
          return true;
        } else {
          throw new Error(response.error || 'Failed to clear cache');
        }
      } else {
        // Fallback: clear caches directly
        const cacheNames = await caches.keys();
        await Promise.all(
          cacheNames.map(cacheName => caches.delete(cacheName))
        );
        console.log('Cache cleared directly');
        return true;
      }
    } catch (error) {
      console.error('Error clearing cache:', error);
      // Force reload as fallback
      window.location.href = window.location.href + '?clear-cache=true';
      return false;
    }
  }

  static async checkVersion() {
    try {
      if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        const response = await this.sendMessageToSW({ type: 'CHECK_VERSION' });
        return response.updated;
      }
      return false;
    } catch (error) {
      console.error('Error checking version:', error);
      return false;
    }
  }

  static sendMessageToSW(message) {
    return new Promise((resolve, reject) => {
      if (!navigator.serviceWorker.controller) {
        reject(new Error('No service worker controller'));
        return;
      }

      const messageChannel = new MessageChannel();
      messageChannel.port1.onmessage = event => {
        resolve(event.data);
      };

      navigator.serviceWorker.controller.postMessage(message, [messageChannel.port2]);
    });
  }

  static async forceRefresh() {
    try {
      await this.clearCache();
      window.location.reload(true);
    } catch (error) {
      console.error('Error during force refresh:', error);
      window.location.reload(true);
    }
  }

  static addCacheBusterToUrl(url) {
    const timestamp = Date.now();
    const separator = url.includes('?') ? '&' : '?';
    return `${url}${separator}v=${timestamp}`;
  }

  static setupAutoRefreshOnUpdate() {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('Service worker controller changed, refreshing...');
        window.location.reload();
      });
    }
  }
}
