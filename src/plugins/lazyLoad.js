/**
 * Global Lazy Loading Utilities
 * Provides image lazy loading, component lazy loading, and performance optimizations
 */

class LazyLoadManager {
  constructor() {
    this.imageObserver = null
    this.componentObserver = null
    this.loadedImages = new Set()
    this.loadingImages = new Set()
    this.retryAttempts = new Map()
    this.maxRetries = 3
    
    this.init()
  }

  init() {
    // Initialize Intersection Observer for images
    if ('IntersectionObserver' in window) {
      this.imageObserver = new IntersectionObserver(
        this.handleImageIntersection.bind(this),
        {
          root: null,
          rootMargin: '50px',
          threshold: 0.1
        }
      )

      // Observer for lazy components
      this.componentObserver = new IntersectionObserver(
        this.handleComponentIntersection.bind(this),
        {
          root: null,
          rootMargin: '100px',
          threshold: 0.1
        }
      )
    }
  }

  // Handle image intersection
  handleImageIntersection(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target
        this.loadImage(img)
        this.imageObserver.unobserve(img)
      }
    })
  }

  // Handle component intersection
  handleComponentIntersection(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const element = entry.target
        const loadCallback = element._lazyLoadCallback
        if (loadCallback && typeof loadCallback === 'function') {
          loadCallback()
          this.componentObserver.unobserve(element)
        }
      }
    })
  }

  // Load image with retry logic
  async loadImage(img) {
    const src = img.dataset.src
    if (!src || this.loadingImages.has(src) || this.loadedImages.has(src)) {
      return
    }

    this.loadingImages.add(src)
    
    try {
      // Add loading class
      img.classList.add('lazy-loading')
      
      // Create new image for preloading
      const newImg = new Image()
      
      await new Promise((resolve, reject) => {
        newImg.onload = () => {
          // Update the actual image
          img.src = src
          img.classList.remove('lazy-loading')
          img.classList.add('lazy-loaded')
          
          // Mark as loaded
          this.loadedImages.add(src)
          this.loadingImages.delete(src)
          
          resolve()
        }
        
        newImg.onerror = () => {
          img.classList.remove('lazy-loading')
          img.classList.add('lazy-error')
          
          const retries = this.retryAttempts.get(src) || 0
          if (retries < this.maxRetries) {
            this.retryAttempts.set(src, retries + 1)
            setTimeout(() => {
              img.classList.remove('lazy-error')
              this.loadImage(img)
            }, 1000 * (retries + 1)) // Exponential backoff
          } else {
            // Use fallback image or show error state
            img.src = '/placeholder-error.png' // Add a fallback image
            img.alt = 'Image failed to load'
          }
          
          this.loadingImages.delete(src)
          reject(new Error('Image failed to load'))
        }
        
        newImg.src = src
      })
    } catch (error) {
      console.warn('Lazy loading failed for image:', src, error)
    }
  }

  // Observe image for lazy loading
  observeImage(img) {
    if (!this.imageObserver) {
      // Fallback for browsers without IntersectionObserver
      this.loadImage(img)
      return
    }

    // Set initial placeholder
    if (!img.src || img.src === img.dataset.src) {
      img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect width="100%25" height="100%25" fill="%23f0f0f0"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy="0.3em" fill="%23999"%3ELoading...%3C/text%3E%3C/svg%3E'
      img.classList.add('lazy-placeholder')
    }

    this.imageObserver.observe(img)
  }

  // Observe element for lazy component loading
  observeComponent(element, loadCallback) {
    if (!this.componentObserver) {
      // Fallback
      loadCallback()
      return
    }

    element._lazyLoadCallback = loadCallback
    this.componentObserver.observe(element)
  }

  // Preload critical images
  preloadImages(urls) {
    urls.forEach(url => {
      if (!this.loadedImages.has(url)) {
        const img = new Image()
        img.onload = () => this.loadedImages.add(url)
        img.src = url
      }
    })
  }

  // Cleanup
  destroy() {
    if (this.imageObserver) {
      this.imageObserver.disconnect()
    }
    if (this.componentObserver) {
      this.componentObserver.disconnect()
    }
  }
}

// Create global instance
const lazyLoadManager = new LazyLoadManager()

// Vue directive for lazy image loading
const LazyImageDirective = {
  bind(el, binding) {
    // Set data-src attribute
    el.dataset.src = binding.value
    
    // Add lazy loading classes
    el.classList.add('lazy-image')
    
    // Start observing
    lazyLoadManager.observeImage(el)
  },
  
  update(el, binding) {
    if (binding.value !== binding.oldValue) {
      el.dataset.src = binding.value
      el.classList.remove('lazy-loaded', 'lazy-error')
      lazyLoadManager.observeImage(el)
    }
  },
  
  unbind(el) {
    if (lazyLoadManager.imageObserver) {
      lazyLoadManager.imageObserver.unobserve(el)
    }
  }
}

// Vue directive for lazy component loading
const LazyComponentDirective = {
  bind(el, binding) {
    if (typeof binding.value === 'function') {
      lazyLoadManager.observeComponent(el, binding.value)
    }
  },
  
  unbind(el) {
    if (lazyLoadManager.componentObserver) {
      lazyLoadManager.componentObserver.unobserve(el)
    }
  }
}

// Plugin for Vue
const LazyLoadPlugin = {
  install(Vue) {
    // Register directives
    Vue.directive('lazy-img', LazyImageDirective)
    Vue.directive('lazy-component', LazyComponentDirective)
    
    // Add to Vue prototype
    Vue.prototype.$lazyLoad = lazyLoadManager
    
    // Global mixin for automatic image lazy loading setup
    Vue.mixin({
      mounted() {
        // Auto-setup lazy loading for images with data-src
        this.$nextTick(() => {
          const lazyImages = this.$el.querySelectorAll('img[data-src]:not(.lazy-image)')
          lazyImages.forEach(img => {
            img.classList.add('lazy-image')
            lazyLoadManager.observeImage(img)
          })
        })
      },
      
      beforeDestroy() {
        // Cleanup observers when component is destroyed
        if (this.$el && this.$el.querySelectorAll) {
          const lazyImages = this.$el.querySelectorAll('img.lazy-image')
          lazyImages.forEach(img => {
            if (lazyLoadManager.imageObserver) {
              lazyLoadManager.imageObserver.unobserve(img)
            }
          })
        }
      }
    })
  }
}

export default LazyLoadPlugin
export { lazyLoadManager }
