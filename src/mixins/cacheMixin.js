/**
 * Cache Mixin - Provides localStorage caching with TTL for API responses.
 * 
 * Usage:
 *   import cacheMixin from '@/mixins/cacheMixin';
 *   export default { mixins: [cacheMixin], ... }
 * 
 * Then use in methods:
 *   this.setCache('my_key', data, 5 * 60 * 1000);
 *   const cached = this.getCache('my_key');
 *   this.clearCache('my_key');
 *   this.clearCacheByPrefix('patients_');
 */
export default {
    data() {
        return {
            // Default TTL values (in ms) for common data types
            cacheTTL: {
                short: 2 * 60 * 1000,      // 2 minutes - frequently changing data
                medium: 5 * 60 * 1000,      // 5 minutes - patient lists, cases
                long: 15 * 60 * 1000,       // 15 minutes - recipes, items
                veryLong: 30 * 60 * 1000,   // 30 minutes - doctors, categories
                hour: 60 * 60 * 1000,       // 1 hour - rarely changing data
            }
        };
    },
    methods: {
        /**
         * Store data in localStorage with TTL
         * @param {string} key - Cache key
         * @param {*} data - Data to cache
         * @param {number} ttl - Time to live in milliseconds (default: 5 min)
         */
        setCache(key, data, ttl = 5 * 60 * 1000) {
            const cacheItem = {
                data: data,
                timestamp: Date.now(),
                ttl: ttl
            };
            try {
                localStorage.setItem(key, JSON.stringify(cacheItem));
            } catch (error) {
                // localStorage might be full - clear old caches and retry
                if (error.name === 'QuotaExceededError' || error.code === 22) {
                    this._cleanExpiredCaches();
                    try {
                        localStorage.setItem(key, JSON.stringify(cacheItem));
                    } catch (e) {
                        console.warn('Cache storage full, could not save:', key);
                    }
                } else {
                    console.warn('Failed to set cache:', error);
                }
            }
        },

        /**
         * Get cached data if not expired
         * @param {string} key - Cache key
         * @returns {*} Cached data or null if expired/missing
         */
        getCache(key) {
            try {
                const cached = localStorage.getItem(key);
                if (!cached) return null;

                const cacheItem = JSON.parse(cached);
                const now = Date.now();

                // Check if cache is expired
                if (now - cacheItem.timestamp > cacheItem.ttl) {
                    localStorage.removeItem(key);
                    return null;
                }

                return cacheItem.data;
            } catch (error) {
                console.warn('Failed to get cache:', error);
                return null;
            }
        },

        /**
         * Get expired cache data as fallback (useful when API fails)
         * @param {string} key - Cache key
         * @returns {*} Cached data regardless of expiry, or null
         */
        getExpiredCache(key) {
            try {
                const cached = localStorage.getItem(key);
                if (!cached) return null;
                return JSON.parse(cached).data;
            } catch (error) {
                return null;
            }
        },

        /**
         * Remove a specific cache entry
         * @param {string} key - Cache key to remove
         */
        clearCache(key) {
            try {
                localStorage.removeItem(key);
            } catch (error) {
                console.warn('Failed to clear cache:', error);
            }
        },

        /**
         * Clear all cache entries matching a prefix
         * @param {string} prefix - Key prefix to match
         */
        clearCacheByPrefix(prefix) {
            try {
                const keys = Object.keys(localStorage);
                keys.forEach(key => {
                    if (key.startsWith(prefix)) {
                        localStorage.removeItem(key);
                    }
                });
            } catch (error) {
                console.warn('Failed to clear cache by prefix:', error);
            }
        },

        /**
         * Clear all app caches (keys starting with 'cache_')
         */
        clearAllAppCache() {
            this.clearCacheByPrefix('cache_');
        },

        /**
         * Remove expired cache entries to free space
         */
        _cleanExpiredCaches() {
            try {
                const now = Date.now();
                const keys = Object.keys(localStorage);
                keys.forEach(key => {
                    if (key.startsWith('cache_')) {
                        try {
                            const item = JSON.parse(localStorage.getItem(key));
                            if (item && item.timestamp && (now - item.timestamp > item.ttl)) {
                                localStorage.removeItem(key);
                            }
                        } catch (e) {
                            // Not a valid cache item, skip
                        }
                    }
                });
            } catch (error) {
                console.warn('Failed to clean expired caches:', error);
            }
        }
    }
};
