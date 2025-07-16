// Dropzone plugin for vue2-dropzone compatibility
import Dropzone from 'dropzone'
import 'dropzone/dist/dropzone.css'

// Ensure Dropzone is globally available
if (typeof window !== 'undefined') {
  window.Dropzone = Dropzone
}

// Configure Dropzone defaults
Dropzone.autoDiscover = false

// Vue plugin installation
const DropzonePlugin = {
  install(Vue) {
    // Make Dropzone available in Vue instances
    Vue.prototype.$Dropzone = Dropzone
    
    // Ensure global availability
    if (typeof window !== 'undefined') {
      window.Dropzone = Dropzone
    }
  }
}

export default DropzonePlugin
