<template>
    <div>
        <v-toolbar dark color="primary lighten-1">
            <v-toolbar-title>
                <h3 style="color:#fff;font-family: 'Cairo'"> تقرير الحساب </h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="printReport()" icon title="طباعة التقرير">
                <v-icon>mdi-printer</v-icon>
            </v-btn>
            <v-btn @click="close()" icon>
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>

        <v-card class="bill-card" style="padding: 20px;">
            <!-- Patient Header Information -->
            <div class="patient-header">
                <div class="patient-details">
                    <div class="patient-info">
                        <div class="info-label">اسم المراجع:</div>
                        <div class="info-value">{{ patient.billable ? patient.billable.name : patient.name }}</div>
                    </div>
                    <div class="patient-info">
                        <div class="info-label">رقم الهاتف:</div>
                        <div class="info-value" style="direction: ltr;">{{ patient.billable ? patient.billable.phone : patient.phone }}</div>
                    </div>
                    <div class="patient-info">
                        <div class="info-label">تاريخ التقرير:</div>
                        <div class="info-value">{{ getCurrentDate() }}</div>
                    </div>
                </div>
                <div class="clinic-logo">
                    <img style="width:100px;height:100px" :src="getLogoUrl()" alt="Clinic Logo" class="clinic-logo-image" />
                    <div class="clinic-name">{{ $store.state.AdminInfo.clinics_info.name }} </div>
                </div>
            </div>

            <v-divider class="my-3"></v-divider>

            <!-- Bills Table -->
            <v-data-table 
                class="bill-table"
                :headers="headers"
                :items="billItems"
                hide-default-footer
                disable-pagination>
                
             
                
                <template v-slot:no-data>
                    <div class="text-center pa-4">لا توجد فواتير</div>
                </template>
            </v-data-table>

            <v-divider class="my-3"></v-divider>

            <!-- Bill Summary -->
            <div class="bill-summary">
                <div class="summary-row">
                    <div class="summary-label">إجمالي الفاتورة:</div>
                    <div class="summary-value">
                        {{ formatCurrency(totalBill) }} <span class="money">د.ع</span>
                    </div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">المبلغ المدفوع:</div>
                    <div class="summary-value">{{ totalPaid | currency }} <span class="money">د.ع</span></div>
                </div>
                <div class="summary-row remaining">
                    <div class="summary-label">المبلغ المتبقي:</div>
                    <div class="summary-value">{{ totalRemaining | currency }} <span class="money">د.ع</span></div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-label">توقيع الطبيب</div>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">توقيع المريض</div>
                    <div class="signature-line"></div>
                </div>
            </div>
        </v-card>
    </div>
</template>

<script>
    import {
        EventBus
    } from "../event-bus.js";
    
    export default {
        inheritAttrs: false,

        props: {
            patient: Object
        },

        data() {
            return {
                headers: [
                    {
                        text: 'نوع الحاله',
                        value: 'case_type',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'رقم السن',
                        value: 'tooth_number',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'اسم الطبيب',
                        value: 'doctor_name',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'السعر',
                        value: 'price_formatted',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'التاريخ',
                        value: 'date',
                        align: 'right',
                        sortable: false
                    }
                ]
            }
        },

        computed: {
            billItems() {
                let items = [];
                
                // Debug logging
                console.log('Patient data:', this.patient);
                
                // Add case items
                if (this.patient.cases && this.patient.cases.length > 0) {
                    console.log('Cases found:', this.patient.cases);
                    items = this.patient.cases.map(item => {
                        console.log('Processing case item:', item);
                        console.log('Tooth data available:', {
                            tooth_num: item.tooth_num,
                            tooth_number: item.tooth_number,
                            toothNumber: item.toothNumber,
                            teeth: item.teeth
                        });
                        console.log('Date data available:', {
                            created_at: item.created_at,
                            date: item.date,
                            updated_at: item.updated_at,
                            PaymentDate: item.PaymentDate
                        });
                        
                        const toothData = item.tooth_num || item.tooth_number || item.toothNumber || item.teeth;
                        const dateData = item.created_at || item.date || item.updated_at || item.PaymentDate;
                        
                        return {
                            case_type: item.case_categories ? item.case_categories.name_ar : 'غير محدد',
                            tooth_number: this.formatToothNumbers(toothData),
                            doctor_name: this.getDoctorName(item),
                            price_formatted: `${this.$options.filters.currency(item.price || 0)} د.ع`,
                            date: this.formatDate(dateData),
                            price: item.price || 0
                        };
                    });
                }
                
                // Remove payment items from bills array - commented out
                /*
                if (this.patient.bills && this.patient.bills.length > 0) {
                    const paymentItems = this.patient.bills.map(bill => {
                        console.log('Processing bill item:', bill);
                        console.log('Bill date data:', {
                            PaymentDate: bill.PaymentDate,
                            created_at: bill.created_at,
                            date: bill.date,
                            updated_at: bill.updated_at
                        });
                        
                        const billDate = bill.PaymentDate || bill.created_at || bill.date || bill.updated_at;
                        
                        return {
                            case_type: bill.is_paid ? 'دفعة مالية' : 'فاتورة غير مدفوعة',
                            tooth_number: '-',
                            doctor_name: this.getDoctorName(bill) || '-',
                            price_formatted: `${this.$options.filters.currency(bill.price || 0)} د.ع`,
                            date: this.formatDate(billDate),
                            price: bill.price || 0,
                            is_payment: true,
                            is_paid: bill.is_paid
                        };
                    });
                    items = items.concat(paymentItems);
                }
                */
                
                // If no cases but has direct price (fallback)
                if (items.length === 0 && this.patient.price) {
                    const patientDate = this.patient.PaymentDate || this.patient.created_at || this.patient.date || this.patient.updated_at;
                    console.log('Fallback patient date data:', {
                        PaymentDate: this.patient.PaymentDate,
                        created_at: this.patient.created_at,
                        date: this.patient.date,
                        updated_at: this.patient.updated_at
                    });
                    
                    items = [{
                        case_type: 'فاتورة مباشرة',
                        tooth_number: '-',
                        doctor_name: this.getDoctorName(this.patient) || '-',
                        price_formatted: `${this.$options.filters.currency(this.patient.price || 0)} د.ع`,
                        date: this.formatDate(patientDate),
                        price: this.patient.price || 0
                    }];
                }
                
                console.log('Final bill items:', items);
                return items;
            },

            totalBill() {
                // Sum only the case prices from patient.cases
                if (this.patient && this.patient.cases && this.patient.cases.length > 0) {
                    return this.patient.cases.reduce((total, caseItem) => {
                        return total + (parseFloat(caseItem.price) || 0);
                    }, 0);
                }
                return 0;
            },

            totalPaid() {
                // Check if patient has bills array (new structure)
                if (this.patient.bills && this.patient.bills.length > 0) {
                    return this.patient.bills.reduce((total, bill) => {
                        if (bill.is_paid) {
                            return total + (parseFloat(bill.price) || 0);
                        }
                        return total;
                    }, 0);
                }
                // Fallback to old structure
                else if (this.patient.cases && this.patient.cases.length > 0) {
                    return this.patient.cases.reduce((total, item) => {
                        if (item.bills && item.bills.length > 0) {
                            return total + this.xx(item.bills);
                        }
                        return total;
                    }, 0);
                } else {
                    return this.patient.is_paid ? this.totalBill : 0;
                }
            },

            totalRemaining() {
                return this.totalBill - this.totalPaid;
            }
        },

        mounted() {
            // Component mounted - ready to display bill report
        },

        methods: {
            parseToArray(toothNum) {
                // Check if it's a string that looks like an array, parse it
                if (typeof toothNum === 'string') {
                    try {
                        return JSON.parse(toothNum);
                    } catch (e) {
                        return [toothNum]; // Return the value as-is if it's not a valid array string
                    }
                }
                // If it's already an array, return it
                return Array.isArray(toothNum) ? toothNum : [toothNum];
            },

            formatToothNumbers(toothNum) {
                if (!toothNum) return '-';
                
                // Debug logging to see what data we're getting
                console.log('formatToothNumbers input:', toothNum, 'type:', typeof toothNum);
                
                try {
                    // Handle different tooth number formats
                    let toothArray;
                    
                    if (typeof toothNum === 'string') {
                        // Try to parse as JSON array first
                        try {
                            toothArray = JSON.parse(toothNum);
                        } catch (e) {
                            // If not JSON, check if it's comma-separated or single value
                            if (toothNum.includes(',')) {
                                toothArray = toothNum.split(',').map(t => t.trim());
                            } else if (toothNum.includes('-')) {
                                toothArray = toothNum.split('-').map(t => t.trim());
                            } else {
                                toothArray = [toothNum];
                            }
                        }
                    } else if (Array.isArray(toothNum)) {
                        toothArray = toothNum;
                    } else {
                        toothArray = [toothNum];
                    }
                    
                    // Filter out empty values and format
                    if (Array.isArray(toothArray) && toothArray.length > 0) {
                        const filteredArray = toothArray.filter(tooth => tooth && tooth.toString().trim() !== '');
                        if (filteredArray.length > 0) {
                            const result = filteredArray.join(' - ');
                            console.log('formatToothNumbers result:', result);
                            return result;
                        }
                    }
                    
                    const fallback = toothNum ? toothNum.toString() : '-';
                    console.log('formatToothNumbers fallback:', fallback);
                    return fallback;
                } catch (error) {
                    console.error('Error formatting tooth numbers:', error, 'Input:', toothNum);
                    return toothNum ? toothNum.toString() : '-';
                }
            },

            getDoctorName(item) {
                if (!item) return null;
                
                console.log('getDoctorName input:', item);
                
                // First check if item has doctors array (from cases)
                if (item.doctors && Array.isArray(item.doctors) && item.doctors.length > 0) {
                    console.log('Found doctors array:', item.doctors);
                    return item.doctors[0].name; // Get first doctor's name
                }
                
                // Check if item has a single doctors object (from patient level)
                if (item.doctors && !Array.isArray(item.doctors) && item.doctors.name) {
                    console.log('Found doctors object:', item.doctors);
                    return item.doctors.name;
                }
                
                // Try different possible doctor field names
                if (item.doctor && item.doctor.name) {
                    console.log('Found doctor.name:', item.doctor.name);
                    return item.doctor.name;
                }
                if (item.doctor_name) {
                    console.log('Found doctor_name:', item.doctor_name);
                    return item.doctor_name;
                }
                if (item.doctorName) {
                    console.log('Found doctorName:', item.doctorName);
                    return item.doctorName;
                }
                if (item.user && item.user.name) {
                    console.log('Found user.name:', item.user.name);
                    return item.user.name;
                }
                if (item.created_by && item.created_by.name) {
                    console.log('Found created_by.name:', item.created_by.name);
                    return item.created_by.name;
                }
                if (item.doctor_id) {
                    console.log('Found doctor_id:', item.doctor_id);
                    if (this.$store.state.doctors) {
                        const doctor = this.$store.state.doctors.find(d => d.id === item.doctor_id);
                        if (doctor) {
                            console.log('Found doctor from store:', doctor.name);
                            return doctor.name;
                        }
                    }
                    // Try to get from AdminInfo doctors list
                    if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.doctors) {
                        const doctor = this.$store.state.AdminInfo.doctors.find(d => d.id === item.doctor_id);
                        if (doctor) {
                            console.log('Found doctor from AdminInfo:', doctor.name);
                            return doctor.name;
                        }
                    }
                }
                
                // Check if this is patient data and has doctors object at root level
                if (this.patient && this.patient.doctors && this.patient.doctors.name) {
                    console.log('Found patient.doctors.name:', this.patient.doctors.name);
                    return this.patient.doctors.name;
                }
                
                // Fallback to store current user if available
                if (this.$store.state.AdminInfo && this.$store.state.AdminInfo.name) {
                    console.log('Using fallback AdminInfo.name:', this.$store.state.AdminInfo.name);
                    return this.$store.state.AdminInfo.name;
                }
                
                console.log('No doctor name found');
                return null;
            },

            formatCurrency(amount) {
                if (!amount) return '0';
                return parseFloat(amount);
            },

            formatDate(dateValue) {
                console.log('formatDate input:', dateValue, 'type:', typeof dateValue);
                
                if (!dateValue) {
                    console.log('No date value provided, returning current date');
                    return this.getCurrentDate();
                }
                
                try {
                    let date;
                    
                    // Handle different date formats
                    if (typeof dateValue === 'string') {
                        // If it's already in YYYY-MM-DD format, return as is
                        if (/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
                            console.log('Date already in YYYY-MM-DD format:', dateValue);
                            return dateValue;
                        }
                        
                        // Try to parse as ISO string or other formats
                        date = new Date(dateValue);
                    } else if (dateValue instanceof Date) {
                        date = dateValue;
                    } else {
                        // Try to convert to string first then parse
                        date = new Date(dateValue.toString());
                    }
                    
                    // Check if date is valid
                    if (isNaN(date.getTime())) {
                        console.log('Invalid date, using current date');
                        return this.getCurrentDate();
                    }
                    
                    // Format as YYYY-MM-DD
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;
                    
                    console.log('Formatted date:', formattedDate);
                    return formattedDate;
                    
                } catch (error) {
                    console.error('Error formatting date:', error, 'Input:', dateValue);
                    return this.getCurrentDate();
                }
            },

            cropdate(x) {
                // Keep the old method for compatibility but use formatDate internally
                return this.formatDate(x);
            },
            
            getCurrentDate() {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            },

            getLogoUrl() {
                // Get the current window location origin to build the correct URL
                const origin = window.location.origin;
                return `${origin}/111.png`;
            },

            printReport() {
                // Create a new window for printing only the bill content
                const printWindow = window.open('', '_blank', 'width=800,height=600');
                
                // Get the bill card HTML content
                const billContent = document.querySelector('.bill-card').outerHTML;
                
                // Create the print HTML
                const printHTML = `
                    <!DOCTYPE html>
                    <html dir="rtl">
                    <head>
                        <meta charset="utf-8">
                        <title>تقرير الحساب</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: 'Cairo', Arial, sans-serif;
                                direction: rtl;
                                background: white;
                                padding: 15px;
                                font-size: 12px;
                                line-height: 1.4;
                            }
                            
                            .bill-card {
                                width: 100%;
                                max-width: none;
                                margin: 0;
                                padding: 0;
                                background: white;
                                border: none;
                                box-shadow: none;
                            }
                            
                            .patient-header {
                                display: flex;
                                justify-content: space-between;
                                flex-wrap: nowrap;
                                margin-bottom: 20px;
                                padding-bottom: 15px;
                                border-bottom: 2px solid #333;
                                align-items: flex-start;
                            }
                            
                            .patient-details {
                                flex: 1;
                                max-width: 65%;
                            }
                            
                            .patient-info {
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                margin-bottom: 8px;
                                font-size: 12px;
                            }
                            
                            .info-label {
                                font-weight: bold;
                                white-space: nowrap;
                                min-width: 80px;
                            }
                            
                            .info-value {
                                font-weight: 500;
                            }
                            
                            .clinic-logo {
                                text-align: center;
                                flex-shrink: 0;
                                max-width: 30%;
                            }
                            
                            .clinic-logo-image {
                                width: 80px;
                                height: 80px;
                                object-fit: contain;
                                margin-bottom: 10px;
                            }
                            
                            .clinic-name {
                                font-weight: bold;
                                font-size: 14px;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 20px 0;
                                font-size: 11px;
                            }
                            
                            th, td {
                                padding: 8px 6px;
                                text-align: right;
                                border: 1px solid #ddd;
                                white-space: nowrap;
                            }
                            
                            th {
                                background-color: #f5f5f5;
                                font-weight: bold;
                            }
                            
                            .bill-summary {
                                margin: 20px 0;
                                padding: 15px;
                                background-color: #f9f9f9;
                                border: 1px solid #ddd;
                                border-radius: 5px;
                            }
                            
                            .summary-row {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 8px;
                                padding: 5px 0;
                            }
                            
                            .summary-row.remaining {
                                border-top: 2px solid #333;
                                margin-top: 10px;
                                padding-top: 10px;
                                font-weight: bold;
                                font-size: 1.1em;
                            }
                            
                            .summary-label {
                                font-weight: bold;
                            }
                            
                            .signature-section {
                                display: flex;
                                justify-content: space-between;
                                margin-top: 30px;
                                padding-top: 20px;
                                border-top: 1px solid #ddd;
                                gap: 30px;
                            }
                            
                            .signature-box {
                                text-align: center;
                                flex: 1;
                                max-width: 200px;
                            }
                            
                            .signature-label {
                                font-weight: bold;
                                margin-bottom: 25px;
                            }
                            
                            .signature-line {
                                border-bottom: 2px solid #333;
                                height: 40px;
                                width: 100%;
                            }
                            
                            /* Hide v-divider elements */
                            .v-divider {
                                display: none;
                            }
                            
                            @media print {
                                body {
                                    padding: 10px;
                                    font-size: 10px;
                                }
                                
                                .patient-info {
                                    font-size: 10px;
                                }
                                
                                table {
                                    font-size: 9px;
                                }
                                
                                th, td {
                                    padding: 4px 3px;
                                }
                                
                                .clinic-logo-image {
                                    width: 60px;
                                    height: 60px;
                                }
                                
                                .signature-section {
                                    margin-top: 20px;
                                    padding-top: 15px;
                                }
                                
                                .signature-line {
                                    height: 30px;
                                }
                                
                                @page {
                                    size: A4 portrait;
                                    margin: 1cm;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        ${billContent}
                    </body>
                    </html>
                `;
                
                // Write the HTML to the new window
                printWindow.document.write(printHTML);
                printWindow.document.close();
                
                // Wait for content to load, then print and close
                printWindow.onload = function() {
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                };
            },

            close() {
                EventBus.$emit("billsReportclose", false);
            },

            xx(ite) {
                if (!ite || !Array.isArray(ite)) return 0;
                
                let x = 0;
                for (let i = 0; i < ite.length; i++) {
                    x += parseFloat(ite[i].price) || 0;
                }
                return x;
            }
        }
    }
</script>

<style scoped>
.bill-card {
    font-family: 'Cairo', sans-serif;
}

.patient-header {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.patient-info {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 200px;
}

.info-label {
    font-weight: bold;
    color: #333;
    white-space: nowrap;
}

.info-value {
    font-weight: 500;
    color: #555;
}

.clinic-logo {
    text-align: center;
}

.clinic-logo-image {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 10px;
}

.clinic-name {
    font-weight: bold;
    font-size: 14px;
    color: #333;
}

.bill-table {
    margin: 20px 0;
}

.bill-table >>> th {
    background-color: #f5f5f5 !important;
    font-weight: bold !important;
    text-align: right !important;
}

.bill-table >>> td {
    text-align: right !important;
    padding: 12px 16px !important;
}

.payment-item {
    color: #4CAF50;
    font-weight: bold;
}

.unpaid-item {
    color: #f44336;
    font-weight: bold;
}

.payment-amount {
    color: #4CAF50;
    font-weight: bold;
}

.unpaid-amount {
    color: #f44336;
    font-weight: bold;
}

.bill-summary {
    margin: 20px 0;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px 0;
}

.summary-row.remaining {
    border-top: 2px solid #ddd;
    margin-top: 15px;
    padding-top: 15px;
    font-weight: bold;
    font-size: 1.1em;
}

.summary-label {
    font-weight: bold;
    color: #333;
}

.summary-value {
    font-weight: 500;
    color: #555;
}

.money {
    font-size: 0.9em;
    color: #666;
}

.signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 20px;
}

.signature-box {
    text-align: center;
    width: 200px;
}

.signature-label {
    font-weight: bold;
    margin-bottom: 30px;
    color: #333;
}

.signature-line {
    border-bottom: 2px solid #333;
    height: 50px;
    margin-top: 10px;
}

/* Print styles */
@media print {
    /* Remove all the complex print styles since we're using a separate window */
    .v-toolbar {
        display: none !important;
    }
}

/* Mobile-specific print overrides */
@media print and (max-width: 768px) {
    /* Even more aggressive mobile print styles */
    body {
        zoom: 0.75 !important;
        -webkit-transform: scale(0.75) !important;
        transform: scale(0.75) !important;
        transform-origin: top left !important;
    }
    
    .bill-card {
        font-size: 8px !important;
        padding: 8px !important;
        zoom: 1 !important;
        transform: none !important;
    }
    
    .patient-header {
        margin-bottom: 8px !important;
        padding-bottom: 6px !important;
    }
    
    .patient-info {
        font-size: 8px !important;
        gap: 3px !important;
    }
    
    .info-label {
        min-width: 50px !important;
        font-size: 8px !important;
    }
    
    .clinic-logo-image {
        width: 35px !important;
        height: 35px !important;
    }
    
    .clinic-name {
        font-size: 7px !important;
    }
    
    .bill-table {
        margin: 6px 0 !important;
        font-size: 7px !important;
    }
    
    .bill-table >>> th,
    .bill-table >>> td {
        padding: 1px !important;
        font-size: 7px !important;
        line-height: 1 !important;
    }
    
    /* Even more compact columns for mobile */
    .bill-table >>> th:first-child,
    .bill-table >>> td:first-child {
        width: 32% !important;
    }
    
    .bill-table >>> th:nth-child(2),
    .bill-table >>> td:nth-child(2) {
        width: 10% !important;
    }
    
    .bill-table >>> th:nth-child(3),
    .bill-table >>> td:nth-child(3) {
        width: 16% !important;
    }
    
    .bill-table >>> th:nth-child(4),
    .bill-table >>> td:nth-child(4) {
        width: 21% !important;
    }
    
    .bill-table >>> th:nth-child(5),
    .bill-table >>> td:nth-child(5) {
        width: 21% !important;
    }
    
    .bill-summary {
        padding: 4px !important;
        font-size: 8px !important;
        margin: 6px 0 !important;
    }
    
    .summary-row {
        font-size: 8px !important;
        margin-bottom: 2px !important;
    }
    
    .summary-row.remaining {
        font-size: 9px !important;
        margin-top: 4px !important;
        padding-top: 4px !important;
    }
    
    .signature-section {
        margin-top: 10px !important;
        padding-top: 6px !important;
        gap: 15px !important;
    }
    
    .signature-box {
        width: 100px !important;
    }
    
    .signature-label {
        font-size: 7px !important;
        margin-bottom: 10px !important;
    }
    
    .signature-line {
        height: 15px !important;
    }
}

/* Mobile responsive - only for screen */
@media screen and (max-width: 768px) {
    .patient-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .patient-info {
        min-width: auto;
        width: 100%;
    }
    
    .signature-section {
        flex-direction: column;
        gap: 30px;
    }
    
    .signature-box {
        width: 100%;
    }
    
    /* Mobile table styles - only for screen view */
    .bill-table {
        overflow-x: auto !important;
        width: 100% !important;
        margin: 10px 0 !important;
    }
    
    .bill-table >>> .v-data-table__wrapper {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }
    
    .bill-table >>> th,
    .bill-table >>> td {
        padding: 8px 6px !important;
        font-size: 12px !important;
        white-space: nowrap !important;
        min-width: 80px !important;
    }
    
    .v-card {
        overflow-x: auto !important;
    }
}
</style>