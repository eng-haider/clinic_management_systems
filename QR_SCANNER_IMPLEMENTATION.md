# QR Scanner Implementation Guide

## Overview

This document describes the QR code scanner implementation for the casesheet page, allowing users to scan patient QR codes and automatically navigate to their profile.

## Frontend Implementation

### 1. QR Scanner Component (`src/components/QRScanner.vue`)

A reusable Vue component that:

- Opens device camera for QR code scanning
- Provides visual feedback during scanning
- Handles camera permissions and errors
- Emits scanned data to parent component

**Features:**

- Arabic UI
- Mobile responsive
- Animated scanning frame
- Error handling for camera access issues
- Auto-closes after successful scan

### 2. Integration in Casesheet Page (`src/views/dashboard/casesheet.vue`)

**Added Elements:**

- QR Scanner button in toolbar with icon `mdi-qrcode-scan`
- QR Scanner dialog component
- Methods to handle scanned QR codes

**New Methods:**

- `openQRScanner()` - Opens the QR scanner dialog
- `handleQRScanned(result)` - Processes scanned QR code data
- `findAndRedirectToPatient(identifier)` - Finds patient and redirects to their page
- `showPatientNotFoundError()` - Shows error if patient not found

### 3. Dependencies

- **vue-qrcode-reader** (v2.3.17) - QR code scanning library for Vue 2

## Backend Requirements

### API Endpoint Used

The implementation uses the existing Laravel backend API endpoint:

**Endpoint:** `GET /api/getPatientByQrCode/{qrCode}`

**Controller:** `patientsControllerAPI::getPatientByQrCode`

**Parameters:**

- `qrCode` - The QR code value extracted from the scanned QR code (e.g., `425152`)

**Expected Response:**
The API returns patient data with all related information (same structure as `getPatientById`):

```json
{
  "id": 123,
  "name": "Patient Name",
  "phone": "1234567890",
  "qr_code": "425152",
  "age": 30,
  "sex": 1,
  "address": "Patient Address",
  "email": "patient@example.com",
  "systemic_conditions": "None",
  "birth_date": "1995-01-01",
  "notes": "Patient notes",
  "rx_id": null,
  "credit_balance": 0,
  "tooth_parts": [...],
  "cases": [
    {
      "id": 1,
      "tooth_num": "[18]",
      "case_categories": {
        "name_ar": "علاج عصب"
      },
      "sessions": [...],
      // ... other case fields
    }
  ],
  // ... other patient fields
}
```

**Error Response (Patient Not Found):**
The API should return a 404 status code when the patient is not found.

### Authentication

The API endpoint requires Bearer token authentication:

```
Authorization: Bearer {token}
Accept: application/json
```

## Usage Flow

1. User clicks "مسح QR" button in casesheet page
2. QR Scanner dialog opens with camera view
3. User points camera at patient's QR code
4. QR code is automatically detected and scanned
5. Scanner sends QR data to `handleQRScanned` method
6. System extracts `code` parameter from scanned URL (e.g., from `https://...com/patient-lookup?code=425152&phone=...`)
7. API call made to `/api/getPatientByQrCode/{qrCode}` with Bearer token authentication
8. If patient found:
   - Success message displayed with patient name
   - After 1.5 seconds, redirects to patient page (`/patient/:id`)
   - Patient page loads with full patient data (same as when opening patient directly)
9. If patient not found:
   - Error message displayed asking to try again

## QR Code Format

The QR code should be a URL in this format:

**Format:** `https://mediumturquoise-ram-158778.hostingersite.com/patient-lookup?code={QR_CODE}&phone={PHONE}`

**Example:**

```
https://mediumturquoise-ram-158778.hostingersite.com/patient-lookup?code=425152&phone=07717259088
```

The frontend automatically:

1. Parses the URL
2. Extracts the `code` query parameter (e.g., `425152`)
3. Uses this code to query the API via `/getPatientByQrCode/425152`

**Fallback:** If the QR code is just the code value (e.g., `425152`), it will be used directly.

## Security Considerations

1. **Authentication**: Ensure the API endpoint requires authentication
2. **Authorization**: Only return patients belonging to the authenticated user/clinic
3. **Rate Limiting**: Add rate limiting to prevent abuse
4. **Input Validation**: Validate and sanitize the identifier parameter

## Browser Requirements

For the QR scanner to work:

- **HTTPS required** (camera access requires secure context)
- Modern browser with camera API support
- Camera permission granted by user

## Testing

1. **Desktop Testing**: Use a second device to display QR code
2. **Mobile Testing**: More reliable as mobile devices have better cameras
3. **QR Code Generation**: Use the existing QR code generation feature in the patient card

## Troubleshooting

**Camera not working:**

- Ensure HTTPS is being used
- Check browser camera permissions
- Try a different browser
- Restart the browser

**Patient not found:**

- Verify QR code matches patient record
- Check backend API endpoint is working
- Verify database has correct QR code values

**Scanner not opening:**

- Check browser console for errors
- Verify vue-qrcode-reader is installed correctly
- Ensure component is properly imported

## Future Enhancements

1. Add ability to scan QR codes from uploaded images
2. Add QR code generation for patients without one
3. Add bulk QR code printing functionality
4. Add QR code history/audit log
5. Support for different QR code formats (vCard, etc.)

## Arabic Labels

- **مسح QR** - Scan QR
- **مسح رمز الاستجابة السريع** - Scan QR Code (full text)
- **جاري تحضير الكاميرا** - Preparing camera
- **وجه الكاميرا نحو رمز QR** - Point camera at QR code
- **تم العثور على المراجع** - Patient found
- **لم يتم العثور على المراجع** - Patient not found

## Notes

- The implementation is fully compatible with Vue 2 and Vuetify 2
- The scanner automatically stops after successful scan to save battery
- The component is reusable and can be used in other pages
- Mobile-first design with responsive breakpoints
