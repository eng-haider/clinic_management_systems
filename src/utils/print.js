// Print utility for PWA applications
// Supports multiple paper sizes and PWA-friendly printing

export const PAPER_SIZES = {
  A4: {
    name: 'A4',
    width: '210mm',
    height: '297mm',
    orientation: 'portrait'
  },
  A5: {
    name: 'A5',
    width: '148mm',
    height: '210mm',
    orientation: 'portrait'
  },
  A5_LANDSCAPE: {
    name: 'A5 Landscape',
    width: '210mm',
    height: '148mm',
    orientation: 'landscape'
  }
};

export class PWAPrint {
  constructor() {
    this.originalContent = null;
    this.printStyleElement = null;
  }

  // PWA-friendly print method that doesn't open new windows
  async printElement(element, options = {}) {
    const {
      paperSize = PAPER_SIZES.A5,
      title = 'Print Document',
      customStyles = '',
      hideAfterPrint = true
    } = options;

    try {
      // Store original content and title
      this.originalContent = document.body.innerHTML;
      const originalTitle = document.title;

      // Get the element to print
      const printContent = element.cloneNode(true);
      
      // Set document title
      document.title = title;

      // Create print styles
      this.createPrintStyles(paperSize, customStyles);

      // Replace body content with print content
      document.body.innerHTML = '';
      document.body.appendChild(printContent);

      // Add print-mode class to body
      document.body.classList.add('print-mode');

      // Wait for any images to load
      await this.waitForImages();

      // Trigger print
      window.print();

      // Restore original content after print dialog
      setTimeout(() => {
        this.restoreOriginalContent();
        document.title = originalTitle;
        
        // Reload the page to restore Vue components state
        if (hideAfterPrint) {
          window.location.reload();
        }
      }, 500);

    } catch (error) {
      console.error('Print error:', error);
      this.restoreOriginalContent();
      throw error;
    }
  }

  // Create iframe for print preview (alternative method)
  printWithIframe(element, options = {}) {
    const {
      paperSize = PAPER_SIZES.A5,
      title = 'Print Document',
      customStyles = ''
    } = options;

    return new Promise((resolve, reject) => {
      try {
        // Create hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.top = '-10000px';
        iframe.style.left = '-10000px';
        iframe.style.width = '1px';
        iframe.style.height = '1px';
        document.body.appendChild(iframe);

        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        
        // Build HTML for iframe
        const printHTML = this.buildPrintHTML(element, paperSize, title, customStyles);
        
        iframeDoc.open();
        iframeDoc.write(printHTML);
        iframeDoc.close();

        // Wait for content to load
        iframe.onload = () => {
          try {
            // Focus iframe and print
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            
            // Clean up
            setTimeout(() => {
              document.body.removeChild(iframe);
              resolve();
            }, 1000);
          } catch (error) {
            document.body.removeChild(iframe);
            reject(error);
          }
        };

      } catch (error) {
        reject(error);
      }
    });
  }

  buildPrintHTML(element, paperSize, title, customStyles) {
    const elementHTML = element.outerHTML || element.innerHTML;
    
    return `
      <!DOCTYPE html>
      <html dir="rtl">
      <head>
        <meta charset="utf-8">
        <title>${title}</title>
        <style>
          ${this.getBasePrintStyles(paperSize)}
          ${customStyles}
        </style>
      </head>
      <body>
        ${elementHTML}
      </body>
      </html>
    `;
  }

  createPrintStyles(paperSize, customStyles) {
    // Remove existing print styles
    if (this.printStyleElement) {
      this.printStyleElement.remove();
    }

    // Create new style element
    this.printStyleElement = document.createElement('style');
    this.printStyleElement.innerHTML = `
      ${this.getBasePrintStyles(paperSize)}
      ${customStyles}
    `;
    
    document.head.appendChild(this.printStyleElement);
  }

  getBasePrintStyles(paperSize) {
    return `
      /* Base print styles */
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      
      body {
        font-family: 'Cairo', Arial, sans-serif;
        direction: rtl;
        background: white;
        padding: 10mm;
        font-size: 12px;
        line-height: 1.4;
        color: #000;
      }

      body.print-mode {
        margin: 0;
        padding: 10mm;
      }
      
      /* Hide elements that shouldn't be printed */
      .no-print,
      .v-btn,
      .v-toolbar,
      .v-navigation-drawer,
      .v-app-bar,
      .v-footer,
      button {
        display: none !important;
      }
      
      /* Ensure print elements are visible */
      .print-only {
        display: block !important;
      }
      
      /* Page break utilities */
      .page-break-before {
        page-break-before: always;
      }
      
      .page-break-after {
        page-break-after: always;
      }
      
      .page-break-avoid {
        page-break-inside: avoid;
      }
      
      /* Table styles */
      table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        font-size: 11px;
      }
      
      th, td {
        padding: 6px 4px;
        text-align: right;
        border: 1px solid #333;
        word-wrap: break-word;
      }
      
      th {
        background-color: #f5f5f5;
        font-weight: bold;
      }
      
      /* Image optimization */
      img {
        max-width: 100%;
        height: auto;
        page-break-inside: avoid;
      }
      
      @page {
        size: ${paperSize.width} ${paperSize.height} ${paperSize.orientation};
        margin: 10mm;
      }
      
      @media print {
        body {
          font-size: 11px;
          padding: 0;
        }
        
        table {
          font-size: 10px;
        }
        
        th, td {
          padding: 3px 2px;
          font-size: 9px;
        }
        
        .print-small {
          font-size: 9px;
        }
        
        .print-large {
          font-size: 14px;
          font-weight: bold;
        }
      }
    `;
  }

  async waitForImages() {
    const images = document.querySelectorAll('img');
    const promises = Array.from(images).map(img => {
      if (img.complete) return Promise.resolve();
      
      return new Promise((resolve) => {
        img.onload = resolve;
        img.onerror = resolve;
        // Timeout after 3 seconds
        setTimeout(resolve, 3000);
      });
    });
    
    await Promise.all(promises);
  }

  restoreOriginalContent() {
    if (this.originalContent) {
      document.body.innerHTML = this.originalContent;
      document.body.classList.remove('print-mode');
      this.originalContent = null;
    }
    
    if (this.printStyleElement) {
      this.printStyleElement.remove();
      this.printStyleElement = null;
    }
  }

  // Static method for quick printing
  static async print(element, options = {}) {
    const printer = new PWAPrint();
    return printer.printElement(element, options);
  }

  // Static method for iframe printing (more PWA-friendly)
  static async printWithIframe(element, options = {}) {
    const printer = new PWAPrint();
    return printer.printWithIframe(element, options);
  }
}

// Vue plugin
export default {
  install(Vue) {
    Vue.prototype.$print = PWAPrint;
    Vue.prototype.$paperSizes = PAPER_SIZES;
  }
};
