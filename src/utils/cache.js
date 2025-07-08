/**
 * Cache utility for storing and retrieving API data with timestamps
 */
class CacheManager {
  constructor() {
    this.cachePrefix = 'clinic_app_cache_';
    this.defaultTTL = 5 * 60 * 1000; // 5 minutes default TTL
  }

  /**
   * Set data in cache with timestamp
   * @param {string} key - Cache key
   * @param {any} data - Data to cache
   * @param {number} ttl - Time to live in milliseconds
   */
  set(key, data, ttl = this.defaultTTL) {
    try {
      const cacheData = {
        timestamp: Date.now(),
        data: data,
        ttl: ttl
      };
      localStorage.setItem(this.cachePrefix + key, JSON.stringify(cacheData));
      console.log(`✅ Cached data for key: ${key}`);
    } catch (error) {
      console.error('❌ Cache set error:', error);
    }
  }

  /**
   * Get data from cache if not expired
   * @param {string} key - Cache key
   * @returns {any|null} - Cached data or null if expired/not found
   */
  get(key) {
    try {
      const cachedItem = localStorage.getItem(this.cachePrefix + key);
      if (!cachedItem) {
        console.log(`⚠️ Cache miss for key: ${key}`);
        return null;
      }

      const cacheData = JSON.parse(cachedItem);
      const now = Date.now();
      const isExpired = (now - cacheData.timestamp) > cacheData.ttl;

      if (isExpired) {
        console.log(`⏰ Cache expired for key: ${key}`);
        this.remove(key);
        return null;
      }

      console.log(`✅ Cache hit for key: ${key}`);
      return cacheData.data;
    } catch (error) {
      console.error('❌ Cache get error:', error);
      return null;
    }
  }

  /**
   * Remove data from cache
   * @param {string} key - Cache key
   */
  remove(key) {
    try {
      localStorage.removeItem(this.cachePrefix + key);
      console.log(`🗑️ Removed cache for key: ${key}`);
    } catch (error) {
      console.error('❌ Cache remove error:', error);
    }
  }

  /**
   * Clear all cache
   */
  clear() {
    try {
      const keys = Object.keys(localStorage);
      keys.forEach(key => {
        if (key.startsWith(this.cachePrefix)) {
          localStorage.removeItem(key);
        }
      });
      console.log('🧹 Cache cleared');
    } catch (error) {
      console.error('❌ Cache clear error:', error);
    }
  }

  /**
   * Check if data exists in cache and is valid
   * @param {string} key - Cache key
   * @returns {boolean}
   */
  has(key) {
    return this.get(key) !== null;
  }

  /**
   * Get cache size in bytes
   * @returns {number}
   */
  getSize() {
    let size = 0;
    try {
      const keys = Object.keys(localStorage);
      keys.forEach(key => {
        if (key.startsWith(this.cachePrefix)) {
          size += localStorage.getItem(key).length;
        }
      });
    } catch (error) {
      console.error('❌ Cache size calculation error:', error);
    }
    return size;
  }
}

// Create singleton instance
const cacheManager = new CacheManager();

export default cacheManager;
