# Notification System Implementation for Accountant Role

## Overview

Implemented a comprehensive notification system for the accountant role (role = 'accounter') that monitors cases without bills and alerts accountants when action is needed.

## Features Implemented

### 1. Notification Service (`src/services/notificationService.js`)

- **Real-time Detection**: Checks the API `https://titaniumapi.tctate.com/api/cases/UserCasesv2` every 5 seconds for cases without bills
- **Smart Notifications**: Shows browser notifications when new cases requiring bills are found
- **Patient-based Grouping**: Groups cases by patient for better organization
- **Intelligent Cleanup**: Automatically removes notifications when cases get bills
- **Navigation Integration**: Clicking notifications navigates directly to the patient page
- **Throttling**: Prevents spam notifications with 30-second intervals per patient

### 2. Notification Component (`src/components/NotificationSystem.vue`)

- **Bell Icon with Badge**: Shows unread notification count
- **Dropdown Menu**: Quick view of recent notifications
- **Mark as Read**: Individual and bulk mark as read functionality
- **Delete Notifications**: Remove individual or all notifications
- **Mobile Responsive**: Optimized for all device sizes

### 3. Notifications Page (`src/views/dashboard/notifications.vue`)

- **Full Notification List**: Complete view of all notifications
- **Settings Panel**: Configure check intervals and browser notifications
- **Statistics**: Shows total and unread notification counts
- **Notification Management**: Mark as read, delete, and clear all functions

### 4. Store Integration (`src/store.js`)

- **State Management**: Added notification state to Vuex store
- **Mutations**: Added mutations for notification CRUD operations
- **Getters**: Added computed getters for notification data

### 5. UI Integration

- **AppBar Integration**: Added notification bell to the top navigation bar
- **Sidebar Menu**: Added notifications menu item (visible only to accountants)
- **Role-based Access**: All notification features are restricted to accountant role

## API Integration

### Endpoint Used

- **URL**: `https://titaniumapi.tctate.com/api/cases/UserCasesv2`
- **Method**: GET
- **Headers**: Authorization with Bearer token
- **Response**: Returns array of cases with bills information

### Logic

```javascript
// Filter cases without bills
const casesWithoutBills = cases.filter(
  (caseItem) => !caseItem.bills || caseItem.bills.length === 0
);
```

## User Flow

1. **Real-time Check**: System checks for cases without bills every 5 seconds (configurable from 5-300 seconds)
2. **Notification Creation**: When cases without bills are found, notifications are created
3. **Browser Alert**: If permission granted, browser notification is shown
4. **In-app Notification**: Bell icon shows badge with unread count
5. **User Action**: Accountant clicks notification to view/manage
6. **Navigation**: System navigates to patient page for bill management

## Role-based Access Control

- **Accountant Role Only**: All notification features are restricted to users with `role === 'accounter'`
- **Permission Check**: Notifications menu only appears for accountants
- **Auto-initialization**: Service only starts for accountant users

## Browser Compatibility

- **Notification API**: Uses browser Notification API for desktop alerts
- **Fallback**: In-app notifications work even without browser permission
- **Progressive Enhancement**: Gracefully degrades on unsupported browsers

## Configuration

### Check Interval

- **Default**: 3 seconds
- **Configurable**: Users can change from 3-300 seconds
- **Persistent**: Settings saved to localStorage

### Browser Notifications

- **Permission Request**: Prompts user for notification permission
- **Auto-close**: Notifications auto-close after 10 seconds
- **Prevent Duplicates**: Uses tags to prevent duplicate notifications

## Technical Implementation

### Service Pattern

- **Singleton**: Single instance manages all notification logic
- **Event-driven**: Uses custom events to update UI components
- **Memory Management**: Limits notifications to 50 items maximum

### State Management

- **Reactive**: Uses Vuex for reactive state management
- **Computed Properties**: Components use getters for reactive updates
- **Event Bus**: Custom events ensure components stay in sync

### Error Handling

- **Network Errors**: Graceful handling of API failures
- **Permission Errors**: Handles notification permission denials
- **Authentication**: Validates tokens before API calls

## Files Modified/Created

### New Files

1. `src/services/notificationService.js` - Core notification logic
2. `src/components/NotificationSystem.vue` - Notification UI component
3. `src/views/dashboard/notifications.vue` - Full notifications page

### Modified Files

1. `src/store.js` - Added notification state and mutations
2. `src/components/core/AppBar.vue` - Added notification bell
3. `src/components/core/Drawer.vue` - Added notifications menu item
4. `src/router.js` - Added notifications route
5. `src/main.js` - Made app globally available
6. `src/locales/ar.json` - Added Arabic translations
7. `src/locales/en.json` - Added English translations

## Usage Instructions

1. **For Accountants**:

   - System automatically starts monitoring when logged in
   - Bell icon appears in top navigation
   - Notifications appear when cases need bills
   - Click notifications to go to patient page

2. **Configuration**:

   - Visit /notifications page to configure settings
   - Adjust check interval as needed
   - Enable/disable browser notifications

3. **Navigation**:
   - Click bell icon for quick notification view
   - Click individual notifications to go to patient
   - Use notifications page for full management

This implementation provides a complete, user-friendly notification system that helps accountants stay on top of cases requiring bill creation, improving workflow efficiency and ensuring no cases are missed.
