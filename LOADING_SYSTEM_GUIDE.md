# Enhanced Loading System Documentation

## Overview

This enhanced loading system provides a consistent and smooth UI/UX experience across all pages in the clinic management system. The system automatically manages loading states during API calls and provides better error handling.

## Key Features

- **Automatic Loading Management**: Shows/hides loading spinner automatically during API calls
- **Consistent UI/UX**: Unified loading experience across all pages
- **Better Error Handling**: Standardized error messages and handling
- **Performance Optimized**: Prevents loading flicker with minimum loading times
- **Multi-request Support**: Handles multiple concurrent API calls properly

## System Components

### 1. LoadingManager (`src/utils/loadingManager.js`)
Central singleton that manages all loading states with request tracking.

### 2. Enhanced GlobalLoadingSpinner (`src/components/GlobalLoadingSpinner.vue`)
Improved spinner component with animations and Arabic loading messages.

### 3. Page Loading Mixin (`src/mixins/pageLoadingMixin.js`)
Vue mixin that provides consistent loading behavior for all pages.

## How to Use in New/Existing Pages

### Step 1: Import and Use the Mixin

```javascript
import pageLoadingMixin from '@/mixins/pageLoadingMixin';

export default {
  mixins: [pageLoadingMixin],
  // ... your component code
}
```

### Step 2: Override loadInitialData Method

```javascript
methods: {
  // Override this method to load your page data
  async loadInitialData() {
    await Promise.all([
      this.loadMainData(),
      this.loadAdditionalData(),
      this.loadMoreData()
    ]);
  },

  async loadMainData() {
    try {
      const response = await this.axios.get('/api/your-endpoint', {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${this.$store.state.AdminInfo.token}`
        }
      });
      this.yourData = response.data;
    } catch (error) {
      console.error('Error loading main data:', error);
    }
  }
}
```

### Step 3: Use executeWithLoading for Manual Loading

For API calls triggered by user actions:

```javascript
methods: {
  async handleButtonClick() {
    await this.executeWithLoading(
      () => this.saveData(),
      'جاري حفظ البيانات',
      'save-data'
    );
  },

  async saveData() {
    const response = await this.axios.post('/api/save', this.formData);
    return response.data;
  }
}
```

## Examples

### Example 1: Dashboard Page
```javascript
// Dashboard.vue
export default {
  mixins: [pageLoadingMixin],
  methods: {
    async loadInitialData() {
      await Promise.all([
        this.loadCaseStats(),
        this.loadDashboardCounts(),
        this.loadBookingData(),
        this.loadAccountsCounts()
      ]);
      
      this.$nextTick(() => {
        this.createCharts();
      });
    },

    async loadCaseStats() {
      try {
        const response = await this.axios.get('cases/getCaseCategoriesCounts', {
          headers: { /* auth headers */ }
        });
        this.dataSource = response.data.data;
      } catch (error) {
        console.error('Error loading case stats:', error);
      }
    }
  }
}
```

### Example 2: Patient Page
```javascript
// patient.vue
export default {
  mixins: [pageLoadingMixin],
  methods: {
    async loadInitialData() {
      await this.loadPatientData();
    },

    async loadPatientData() {
      const patientId = this.$route.params.id;
      const response = await this.$http.get(`/api/patients/${patientId}`);
      this.patient = response.data;
    }
  }
}
```

### Example 3: Manual Loading for User Actions
```javascript
methods: {
  async refreshData() {
    await this.executeWithLoading(
      () => this.loadInitialData(),
      'جاري تحديث البيانات',
      'refresh'
    );
  },

  async deleteItem(itemId) {
    try {
      await this.executeWithLoading(
        () => this.axios.delete(`/api/items/${itemId}`),
        'جاري حذف العنصر',
        'delete-item'
      );
      // Refresh the list after deletion
      await this.loadInitialData();
    } catch (error) {
      // Error handling is automatic via mixin
    }
  }
}
```

## Migration Guide for Existing Pages

### Old Pattern:
```javascript
// OLD - Manual loading management
created() {
  this.loadingData = true;
  this.getData();
},
methods: {
  getData() {
    this.axios.get('/api/data')
      .then(response => {
        this.data = response.data;
        this.loadingData = false;
      })
      .catch(error => {
        console.error(error);
        this.loadingData = false;
      });
  }
}
```

### New Pattern:
```javascript
// NEW - Automatic loading management
mixins: [pageLoadingMixin],
methods: {
  async loadInitialData() {
    await this.loadData();
  },
  
  async loadData() {
    try {
      const response = await this.axios.get('/api/data');
      this.data = response.data;
    } catch (error) {
      console.error('Error loading data:', error);
    }
  }
}
```

## Benefits

1. **Consistent Loading States**: All pages show loading in the same way
2. **Better Error Handling**: Standardized error messages in Arabic
3. **No Loading Flicker**: Minimum loading time prevents quick flash
4. **Concurrent Request Management**: Properly handles multiple API calls
5. **Cleaner Code**: Less boilerplate for loading management
6. **Better UX**: Smooth animations and informative messages

## Best Practices

1. **Use loadInitialData**: Override this method for page initialization
2. **Group Related API Calls**: Use Promise.all for concurrent requests
3. **Handle Errors Gracefully**: The mixin provides automatic error handling
4. **Use executeWithLoading**: For user-triggered actions with custom messages
5. **Keep Loading Logic Simple**: Let the mixin handle the complexity

## Troubleshooting

### Loading Not Showing
- Ensure you're using the mixin: `mixins: [pageLoadingMixin]`
- Check that loadInitialData is properly overridden
- Verify API calls are using async/await pattern

### Loading Not Hiding
- Check for unhandled promise rejections
- Ensure all API calls have proper try/catch blocks
- Verify that executeWithLoading is used correctly

### Error Messages Not Showing
- Ensure this.$swal is available (Vue-SweetAlert2 plugin)
- Check that error responses follow expected format
- Verify network connectivity for API calls
