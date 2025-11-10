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
                        <div class="info-value">{{ getPatientName() }}</div>
                    </div>
                    <div class="patient-info">
                        <div class="info-label">رقم الهاتف:</div>
                        <div class="info-value" style="direction: ltr;">{{ getPatientPhone() }}</div>
                    </div>
                    <div class="patient-info">
                        <div class="info-label">تاريخ التقرير:</div>
                        <div class="info-value">{{ getCurrentDate() }}</div>
                    </div>
                </div>
                <div class="clinic-logo">
                    <img :src="getLogoUrl()" alt="Clinic Logo" class="clinic-logo-image" />
                    <div class="clinic-name">{{ getClinicName() }} </div>
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
            <!-- <div class="bill-summary">
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
            </div> -->

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
                        text: 'رقم الدفعة',
                        value: 'payment_number',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'المبلغ المدفوع',
                        value: 'amount_formatted',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'تاريخ الدفع',
                        value: 'payment_date',
                        align: 'right',
                        sortable: false
                    },
                    {
                        text: 'ملاحظات',
                        value: 'notes',
                        align: 'right',
                        sortable: false
                    }
                ]
            }
        },

        computed: {
            billItems() {
                // Safety check for patient data
                if (!this.patient) {
                    console.warn('No patient data available');
                    return [];
                }
                
                let items = [];
                
                // Debug logging
                console.log('Patient data:', this.patient);
                
                // Show only bills/payments data
                if (this.patient.bills && this.patient.bills.length > 0) {
                    console.log('Bills found:', this.patient.bills);
                    items = this.patient.bills.map((bill, index) => {
                        console.log('Processing bill:', bill);
                        
                        const billDate = bill.PaymentDate || bill.created_at || bill.date || bill.updated_at;
                        const billAmount = parseFloat(bill.price) || 0;
                        
                        return {
                            payment_number: index + 1,
                            amount_formatted: `${this.$options.filters.currency(billAmount)} د.ع`,
                            payment_date: this.formatDate(billDate),
                            notes: bill.notes || bill.description || '-',
                            amount: billAmount
                        };
                    });
                }
                
                console.log('Final bill items:', items);
                return items;
            },

            totalBill() {
                // Safety check for patient data
                if (!this.patient) return 0;
                
                // Sum only the case prices from patient.cases
                if (this.patient.cases && this.patient.cases.length > 0) {
                    return this.patient.cases.reduce((total, caseItem) => {
                        return total + (parseFloat(caseItem.price) || 0);
                    }, 0);
                }
                return 0;
            },

            totalPaid() {
                // Safety check for patient data
                if (!this.patient) return 0;
                
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

            getPatientName() {
                if (!this.patient) return '-';
                if (this.patient.billable && this.patient.billable.name) {
                    return this.patient.billable.name;
                }
                return this.patient.name || '-';
            },

            getPatientPhone() {
                if (!this.patient) return '-';
                if (this.patient.billable && this.patient.billable.phone) {
                    return this.patient.billable.phone;
                }
                return this.patient.phone || '-';
            },

            getClinicName() {
                try {
                    return this.$store.state.AdminInfo?.clinics_info?.name || 'العيادة';
                } catch (error) {
                    return 'العيادة';
                }
            },

            getLogoUrl() {
                // Get the current window location origin to build the correct URL
                const origin = window.location.origin;
                return `${origin}/111.png`;
            },

            async printReport() {
                console.log('Print report clicked - Mobile detection:', this.isMobileDevice());
                
                try {
                    // Get the bill card element
                    const billElement = document.querySelector('.bill-card');
                    if (!billElement) {
                        this.$swal.fire({
                            icon: 'error',
                            title: 'خطأ في الطباعة',
                            text: 'لا يمكن العثور على التقرير المطلوب طباعته'
                        });
                        return;
                    }

                    // Check if device is mobile
                    const isMobile = this.isMobileDevice();
                    
                    if (isMobile) {
                        // Use mobile-specific method that actually works
                        this.printOnMobile(billElement);
                    } else {
                        // Use desktop print method
                        if (this.$print && this.$print.printWithIframe) {
                            await this.$print.printWithIframe(billElement, {
                                paperSize: 'A4',
                                title: 'تقرير الحساب',
                                customStyles: this.getPrintStyles() + this.getMobilePrintStyles()
                            });
                        } else {
                            // Fallback if print plugin not available
                            this.printFallback(billElement);
                        }
                    }

                } catch (error) {
                    console.error('Print error:', error);
                    // Fallback for any device if main method fails
                    const billElement = document.querySelector('.bill-card');
                    if (billElement) {
                        this.printFallback(billElement);
                    } else {
                        this.showPrintInstructions();
                    }
                }
            },

            // Mobile print method that definitely works
            printOnMobile(element) {
                console.log('printOnMobile called for element:', element);
                
                // For mobile, use new window method to show preview page
                this.printWithNewWindow(element);
            },

            // Create new window with preview page for mobile
            printWithNewWindow(element) {
                try {
                    // Create a new window for the preview
                    const printWindow = window.open('', '_blank', 'width=400,height=600,scrollbars=yes,resizable=yes');
                    
                    if (printWindow) {
                        const printHTML = this.buildMobilePrintHTML(element);
                        printWindow.document.write(printHTML);
                        printWindow.document.close();
                        
                        // Wait for content to load
                        setTimeout(() => {
                            printWindow.focus();
                        }, 500);
                    } else {
                        // If popup blocked, show instructions
                        this.$swal.fire({
                            icon: 'warning',
                            title: 'تم حظر النافذة المنبثقة',
                            html: `
                                <div style="text-align: right; direction: rtl;">
                                    <p>يرجى السماح للنوافذ المنبثقة في هذا الموقع، ثم المحاولة مرة أخرى.</p>
                                    <p>أو استخدم الطباعة المباشرة من الصفحة الحالية.</p>
                                </div>
                            `,
                            confirmButtonText: 'حسناً',
                            confirmButtonColor: '#2196F3'
                        });
                    }
                } catch (error) {
                    console.error('New window print error:', error);
                    // Fallback to current window method
                    this.printInCurrentWindow(element);
                }
            },

            // Print in current window (reliable mobile method)
            printInCurrentWindow(element) {
                // Show mobile-specific confirmation dialog with A4 instructions
                this.$swal.fire({
                    title: 'طباعة التقرير',
                    html: `
                        <div style="text-align: right; direction: rtl; font-family: Cairo, sans-serif;">
                            <p style="margin-bottom: 15px;"><strong>سيتم فتح نافذة الطباعة.</strong></p>
                            <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 10px 0;">
                                <h4 style="margin: 0 0 10px 0; color: #1976D2;">📋 لطباعة بحجم A4:</h4>
                                <ol style="text-align: right; margin: 0; padding-right: 20px;">
                                    <li>في نافذة الطباعة، ابحث عن "إعدادات الطباعة" أو "More settings"</li>
                                    <li>في "حجم الورق"، اختر <strong>A4</strong> (الحجم الافتراضي)</li>
                                    <li>إذا لم يكن A4 متاحاً، اختر "مخصص" واكتب:</li>
                                    <ul style="margin: 5px 0; padding-right: 20px;">
                                        <li><strong>العرض:</strong> 210mm أو 8.3 inches</li>
                                        <li><strong>الطول:</strong> 297mm أو 11.7 inches</li>
                                    </ul>
                                    <li>اختر الهوامش "عادي" أو "Normal"</li>
                                </ol>
                                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                                    💡 A4 هو الحجم الافتراضي للطباعة - التقرير مُحسَّن للطباعة
                                </p>
                            </div>
                            <p>بعد الانتهاء، سيتم العودة تلقائياً للصفحة السابقة.</p>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'متابعة الطباعة',
                    cancelButtonText: 'إلغاء',
                    confirmButtonColor: '#2196F3',
                    cancelButtonColor: '#f44336',
                    width: '90%'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.executeCurrentWindowPrint(element);
                    }
                });
            },

            // Execute the actual print in current window
            executeCurrentWindowPrint(element) {
                // Save current content
                const originalContent = document.body.innerHTML;
                const originalTitle = document.title;
                
                // Build print content with navigation
                const printContent = this.buildSimplePrintContent(element);
                
                // Replace content temporarily
                document.title = 'تقرير الحساب';
                document.body.innerHTML = printContent;
                
                // Add print styles
                const printStyles = document.createElement('style');
                printStyles.innerHTML = this.getPrintStyles() + this.getMobilePrintStyles();
                document.head.appendChild(printStyles);
                
                // Show loading message
                const loadingMsg = document.createElement('div');
                loadingMsg.innerHTML = `
                    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; color: white; font-family: Cairo, sans-serif; direction: rtl;">
                        <div style="text-align: center; background: white; color: black; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            <h3>جاري تحضير الطباعة...</h3>
                            <p>سيتم فتح نافذة الطباعة خلال ثانية واحدة</p>
                            <button onclick="this.parentElement.parentElement.remove(); window.history.back();" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 10px;">
                                إلغاء والعودة
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(loadingMsg);
                
                // Trigger print after delay
                setTimeout(() => {
                    loadingMsg.remove();
                    
                    // Listen for print dialog events
                    const handleAfterPrint = () => {
                        // Restore content after print
                        document.title = originalTitle;
                        document.body.innerHTML = originalContent;
                        if (printStyles && printStyles.parentNode) {
                            printStyles.parentNode.removeChild(printStyles);
                        }
                        
                        // Remove event listener
                        window.removeEventListener('afterprint', handleAfterPrint);
                        
                        // Reload page to restore Vue functionality
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    };
                    
                    // Add event listener for after print
                    window.addEventListener('afterprint', handleAfterPrint);
                    
                    // Add a backup timeout in case afterprint doesn't fire
                    setTimeout(() => {
                        if (document.title === 'تقرير الحساب') {
                            handleAfterPrint();
                        }
                    }, 10000); // 10 seconds backup
                    
                    // Trigger print
                    window.print();
                    
                }, 1000);
            },

            // Build simple print content for current window method
            buildSimplePrintContent(element) {
                return `
                    <div style="font-family: Cairo, Arial, sans-serif; direction: rtl; padding: 15mm;">
                        ${element.outerHTML}
                        <div style="text-align: center; margin-top: 10px;" class="screen-only">
                            <button onclick="window.history.back(); window.location.reload();" style="
                                background: #2196F3; 
                                color: white; 
                                border: none; 
                                padding: 8px 16px; 
                                border-radius: 4px; 
                                cursor: pointer; 
                                font-family: Cairo, Arial, sans-serif;
                                font-size: 12px;
                            ">
                                العودة
                            </button>
                        </div>
                    </div>
                `;
            },

            // Build complete HTML for new window method
            buildMobilePrintHTML(element) {
                return `
                    <!DOCTYPE html>
                    <html dir="rtl" lang="ar">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>تقرير الحساب</title>
                        <style>
                            * {
                                box-sizing: border-box;
                            }
                            
                            /* A4 page setup - 210mm x 297mm */
                            body {
                                font-family: 'Cairo', Arial, sans-serif;
                                direction: rtl;
                                margin: 0 auto;
                                padding: 12px;
                                background: #f5f5f5;
                                min-height: 100vh;
                                max-width: 210mm;
                                width: 100%;
                                background: white;
                                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                                overflow-x: visible;
                                position: relative;
                                box-sizing: border-box;
                            }
                            
                            .print-controls {
                                text-align: center;
                                margin: 10px 0;
                                padding: 10px;
                                background: #f8f9fa;
                                border-radius: 8px;
                                border: 1px solid #dee2e6;
                            }
                            
                            .print-btn, .close-btn {
                                padding: 10px 20px;
                                margin: 0 5px;
                                font-size: 14px;
                                cursor: pointer;
                                border: none;
                                border-radius: 6px;
                                color: white;
                                font-family: 'Cairo', Arial, sans-serif;
                                transition: all 0.3s ease;
                            }
                            
                            .print-btn {
                                background: #28a745;
                            }
                            
                            .print-btn:hover {
                                background: #218838;
                            }
                            
                            .close-btn {
                                background: #dc3545;
                            }
                            
                            .close-btn:hover {
                                background: #c82333;
                            }
                            
                            .bill-card {
                                width: 100%;
                                margin: 0;
                                padding: 15px;
                                background: white;
                                border: none;
                                box-shadow: none;
                                font-family: 'Cairo', Arial, sans-serif;
                                direction: rtl;
                                font-size: 14px;
                                line-height: 1.5;
                                box-sizing: border-box;
                                overflow: visible;
                            }
                            
                            .patient-header {
                                display: flex;
                                justify-content: space-between;
                                flex-wrap: nowrap;
                                margin-bottom: 15px;
                                padding-bottom: 10px;
                                border-bottom: 2px solid #333;
                                align-items: flex-start;
                                width: 100%;
                                gap: 15px;
                            }
                            
                            .patient-details {
                                flex: 1;
                                max-width: 65%;
                                overflow: visible;
                            }
                            
                            .patient-info {
                                display: flex;
                                align-items: center;
                                gap: 8px;
                                margin-bottom: 8px;
                                font-size: 14px;
                                flex-wrap: wrap;
                            }
                            
                            .info-label {
                                font-weight: bold;
                                white-space: nowrap;
                                min-width: 80px;
                                font-size: 14px;
                            }
                            
                            .info-value {
                                font-weight: 500;
                                font-size: 14px;
                                word-break: break-word;
                            }
                            
                            .clinic-logo {
                                text-align: center;
                                flex-shrink: 0;
                                max-width: 30%;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: flex-start;
                            }
                            
                            .clinic-logo-image {
                                width: 100px;
                                height: 100px;
                                object-fit: contain;
                                margin-bottom: 8px;
                                max-width: 100%;
                            }
                            
                            .clinic-name {
                                font-weight: bold;
                                font-size: 14px;
                                text-align: center;
                                line-height: 1.4;
                                word-break: break-word;
                                max-width: 100%;
                            }
                            
                            .table-container {
                                margin: 15px 0;
                                width: 100%;
                                overflow: visible;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 14px;
                                table-layout: fixed;
                                margin: 10px 0;
                                box-sizing: border-box;
                            }
                            
                            th, td {
                                padding: 10px 6px;
                                text-align: right;
                                border: 1px solid #333;
                                font-size: 14px;
                                line-height: 1.3;
                                word-break: break-word;
                                overflow: visible;
                                box-sizing: border-box;
                            }
                            
                            th {
                                background-color: #f5f5f5;
                                font-weight: bold;
                                font-size: 14px;
                            }
                            
                            .bill-summary {
                                margin: 15px 0;
                                padding: 20px;
                                background-color: #f9f9f9;
                                border: 1px solid #333;
                                border-radius: 4px;
                                width: 100%;
                                box-sizing: border-box;
                            }
                            
                            .summary-row {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 10px;
                                padding: 8px 0;
                                font-size: 16px;
                                align-items: center;
                            }
                            
                            .summary-row.remaining {
                                border-top: 2px solid #333;
                                margin-top: 15px;
                                padding-top: 15px;
                                font-weight: bold;
                                font-size: 18px;
                            }
                            
                            .summary-label {
                                font-weight: bold;
                                flex-shrink: 0;
                            }
                            
                            .summary-value {
                                text-align: left;
                                flex-shrink: 0;
                            }
                            
                            .signature-section {
                                display: flex;
                                justify-content: space-between;
                                margin-top: 40px;
                                padding-top: 20px;
                                border-top: 2px solid #333;
                                gap: 30px;
                                width: 100%;
                            }
                            
                            .signature-box {
                                text-align: center;
                                flex: 1;
                                max-width: 45%;
                            }
                            
                            .signature-label {
                                font-weight: bold;
                                margin-bottom: 30px;
                                font-size: 14px;
                                line-height: 1.3;
                            }
                            
                            .signature-line {
                                border-bottom: 2px solid #333;
                                height: 50px;
                                width: 100%;
                            }
                            
                            .v-divider {
                                display: none;
                            }
                            
                            @media print {
                                .print-controls {
                                    display: none !important;
                                }
                                
                                @page {
                                    size: A4;
                                    margin: 10mm 12mm;
                                }
                                
                                body {
                                    max-width: none;
                                    width: auto;
                                    box-shadow: none;
                                    background: white;
                                    margin: 0;
                                    padding: 0;
                                    overflow: visible;
                                }
                                
                                .bill-card {
                                    padding: 12px;
                                    font-size: 14px;
                                    width: 100%;
                                    max-width: none;
                                    overflow: visible;
                                    margin: 0;
                                    box-sizing: border-box;
                                }
                                
                                .patient-info {
                                    font-size: 14px;
                                    margin-bottom: 6px;
                                }
                                
                                .clinic-logo-image {
                                    width: 100px;
                                    height: 100px;
                                }
                                
                                .clinic-name {
                                    font-size: 14px;
                                }
                                
                                table {
                                    font-size: 14px;
                                    box-sizing: border-box;
                                }
                                
                                th, td {
                                    font-size: 14px;
                                    padding: 10px 6px;
                                    box-sizing: border-box;
                                    overflow: visible;
                                }
                                
                                .summary-row {
                                    font-size: 16px;
                                }
                                
                                .summary-row.remaining {
                                    font-size: 18px;
                                }
                                
                                .signature-label {
                                    font-size: 14px;
                                }
                                
                                .signature-line {
                                    height: 50px;
                                }
                            }
                            
                            @media screen and (max-width: 480px) {
                                body {
                                    max-width: 100vw;
                                    width: 100vw;
                                    padding: 3px;
                                    overflow-x: hidden;
                                }
                                
                                .bill-card {
                                    padding: 3px;
                                    overflow-x: hidden;
                                }
                                
                                .patient-header {
                                    gap: 5px;
                                }
                                
                                .patient-details {
                                    max-width: 65%;
                                }
                                
                                .clinic-logo {
                                    max-width: 30%;
                                }
                                
                                .clinic-logo-image {
                                    width: 30px;
                                    height: 30px;
                                }
                                
                                table {
                                    font-size: 7px;
                                }
                                
                                th, td {
                                    font-size: 7px;
                                    padding: 1px;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-controls">
                            <button onclick="window.print()" class="print-btn">🖨️ طباعة</button>
                            <button onclick="window.close()" class="close-btn">✕ إغلاق</button>
                        </div>
                        ${element.outerHTML}
                    </body>
                    </html>
                `;
            },

            // Fallback print method for mobile devices
            printFallback(element) {
                try {
                    // Create a new window for printing
                    const printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
                    
                    if (printWindow) {
                        const printHTML = this.buildDesktopPrintHTML(element);
                        printWindow.document.write(printHTML);
                        printWindow.document.close();
                        
                        // Wait for content to load then print
                        setTimeout(() => {
                            printWindow.focus();
                            printWindow.print();
                        }, 1000);
                    } else {
                        // If popup blocked, show instructions
                        this.showPrintInstructions();
                    }
                } catch (error) {
                    console.error('Fallback print error:', error);
                    this.showPrintInstructions();
                }
            },

            // Build HTML for desktop-style printing
            buildDesktopPrintHTML(element) {
                return `
                    <!DOCTYPE html>
                    <html dir="rtl" lang="ar">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>تقرير الحساب</title>
                        <style>
                            ${this.getPrintStyles()}
                            ${this.getMobilePrintStyles()}
                        </style>
                    </head>
                    <body>
                        ${element.outerHTML}
                    </body>
                    </html>
                `;
            },

            // Get mobile-specific print styles to ensure table displays properly
            getMobilePrintStyles() {
                return `
                    @media print {
                        .screen-only {
                            display: none !important;
                        }
                        
                        button {
                            display: none !important;
                        }
                        
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        
                        @supports not (size: A4) {
                            @page {
                                margin: 15mm;
                            }
                            
                            body {
                                transform: scale(1.0);
                                transform-origin: top center;
                                width: 210mm;
                                max-width: 210mm;
                            }
                        }
                        
                        @media print and (max-width: 480px) {
                            body {
                                font-size: 8px !important;
                                padding: 5mm !important;
                            }
                            
                            .bill-card {
                                padding: 3mm !important;
                                max-width: 140mm !important;
                            }
                        }
                        
                        body {
                            font-family: 'Cairo', sans-serif !important;
                            direction: rtl !important;
                            font-size: 14px !important;
                            line-height: 1.4 !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            max-width: 210mm !important;
                            max-height: 297mm !important;
                        }
                        
                        .bill-card {
                            margin: 0 !important;
                            padding: 20px !important;
                            font-size: 14px !important;
                        }
                        
                        .patient-header {
                            margin-bottom: 15px !important;
                            font-size: 14px !important;
                        }
                        
                        .patient-info {
                            margin-bottom: 6px !important;
                            font-size: 14px !important;
                        }
                        
                        .clinic-logo-image {
                            width: 100px !important;
                            height: 100px !important;
                        }
                        
                        .clinic-name {
                            font-size: 14px !important;
                        }
                        
                        .v-data-table,
                        .bill-table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            margin: 10px 0 !important;
                        }
                        
                        .v-data-table__wrapper {
                            overflow: visible !important;
                        }
                        
                        .v-data-table table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            font-size: 14px !important;
                        }
                        
                        .v-data-table th,
                        .v-data-table td {
                            border: 1px solid #333 !important;
                            padding: 12px 16px !important;
                            font-size: 14px !important;
                            text-align: right !important;
                            white-space: nowrap !important;
                            line-height: 1.2 !important;
                        }
                        
                        .v-data-table thead th {
                            background-color: #f5f5f5 !important;
                            font-weight: bold !important;
                            font-size: 14px !important;
                        }
                        
                        .bill-summary {
                            margin: 15px 0 !important;
                            padding: 20px !important;
                            font-size: 16px !important;
                        }
                        
                        .summary-row {
                            margin-bottom: 10px !important;
                            font-size: 16px !important;
                        }
                        
                        .summary-row.remaining {
                            font-size: 18px !important;
                        }
                        
                        .signature-section {
                            margin-top: 40px !important;
                            padding-top: 20px !important;
                        }
                        
                        .signature-label {
                            font-size: 14px !important;
                            margin-bottom: 30px !important;
                        }
                        
                        .signature-line {
                            height: 50px !important;
                        }
                        
                        .v-divider {
                            display: none !important;
                        }
                    }
                    
                    @media screen {
                        .screen-only {
                            display: block !important;
                        }
                    }
                `;
            },

            // Show print instructions if all methods fail
            showPrintInstructions() {
                this.$swal.fire({
                    icon: 'info',
                    title: 'تعليمات الطباعة',
                    html: `
                        <div style="text-align: right; direction: rtl;">
                            <p><strong>للطباعة على الهاتف:</strong></p>
                            <ol style="text-align: right;">
                                <li>اضغط على زر القائمة (⋮) في المتصفح</li>
                                <li>اختر "طباعة" أو "Print"</li>
                                <li>في إعدادات الطباعة، اختر حجم الورق A4</li>
                                <li>إذا لم يكن A4 متاحاً، اختر "مخصص" واكتب: العرض 210mm، الطول 297mm</li>
                                <li>اختر الهوامش "عادي" أو "Normal"</li>
                                <li>اضغط "طباعة"</li>
                            </ol>
                        </div>
                    `,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#2196F3'
                });
            },

            // Check if device is mobile
            isMobileDevice() {
                const mobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile Safari|Chrome Mobile|Samsung Internet/i.test(navigator.userAgent);
                const touchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
                const screenSize = window.innerWidth <= 768 || window.screen.width <= 768;
                const isMobile = mobileUA || (touchDevice && screenSize);
                
                console.log('Mobile detection:', {
                    userAgent: mobileUA,
                    touchDevice: touchDevice,
                    screenSize: screenSize,
                    finalResult: isMobile
                });
                
                return isMobile;
            },

            // Get print styles for desktop printing (used for all devices)
            getPrintStyles() {
                return `
                    * {
                        box-sizing: border-box;
                    }
                    
                    body {
                        margin: 0;
                        padding: 0;
                        overflow-x: visible;
                    }
                    
                    .bill-card {
                        width: 100%;
                        max-width: none;
                        margin: 0;
                        padding: 15px;
                        background: white;
                        border: none;
                        box-shadow: none;
                        font-family: 'Cairo', Arial, sans-serif;
                        direction: rtl;
                        box-sizing: border-box;
                    }
                    
                    .patient-header {
                        display: flex;
                        justify-content: space-between;
                        flex-wrap: nowrap;
                        margin-bottom: 15px;
                        padding-bottom: 10px;
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
                        gap: 8px;
                        margin-bottom: 6px;
                        font-size: 14px;
                    }
                    
                    .info-label {
                        font-weight: bold;
                        white-space: nowrap;
                        min-width: 70px;
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
                        width: 100px;
                        height: 100px;
                        object-fit: contain;
                        margin-bottom: 8px;
                    }
                    
                    .clinic-name {
                        font-weight: bold;
                        font-size: 14px;
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 15px 0;
                        font-size: 14px;
                        box-sizing: border-box;
                    }
                    
                    th, td {
                        padding: 10px 8px;
                        text-align: right;
                        border: 1px solid #333;
                        white-space: nowrap;
                        font-size: 14px;
                        box-sizing: border-box;
                    }
                    
                    th {
                        background-color: #f5f5f5;
                        font-weight: bold;
                    }
                    
                    .bill-summary {
                        margin: 15px 0;
                        padding: 20px;
                        background-color: #f9f9f9;
                        border: 1px solid #333;
                        border-radius: 3px;
                    }
                    
                    .summary-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10px;
                        padding: 8px 0;
                        font-size: 16px;
                    }
                    
                    .summary-row.remaining {
                        border-top: 2px solid #333;
                        margin-top: 15px;
                        padding-top: 15px;
                        font-weight: bold;
                        font-size: 18px;
                    }
                    
                    .summary-label {
                        font-weight: bold;
                    }
                    
                    .signature-section {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 40px;
                        padding-top: 20px;
                        border-top: 1px solid #333;
                        gap: 20px;
                    }
                    
                    .signature-box {
                        text-align: center;
                        flex: 1;
                        max-width: 150px;
                    }
                    
                    .signature-label {
                        font-weight: bold;
                        margin-bottom: 30px;
                        font-size: 14px;
                    }
                    
                    .signature-line {
                        border-bottom: 2px solid #333;
                        height: 50px;
                        width: 100%;
                    }
                    
                    .v-divider,
                    .v-btn,
                    button {
                        display: none !important;
                    }
                    
                    @media print {
                        @page {
                            size: A4;
                            margin: 10mm 12mm;
                        }
                        
                        @supports not (size: A4) {
                            @page {
                                margin: 10mm 12mm;
                            }
                            
                            body {
                                max-width: 210mm !important;
                                margin: 0 auto !important;
                                transform: scale(1.0) !important;
                                transform-origin: top center !important;
                                padding: 0 !important;
                            }
                        }
                        
                        @media (max-width: 480px) {
                            body {
                                transform: scale(0.9) !important;
                                max-width: 200mm !important;
                            }
                            
                            .bill-card {
                                padding: 8mm !important;
                                font-size: 9px !important;
                            }
                            
                            table {
                                font-size: 7px !important;
                            }
                            
                            th, td {
                                padding: 2px !important;
                                font-size: 7px !important;
                            }
                        }
                        
                        body {
                            font-size: 14px !important;
                            line-height: 1.4 !important;
                            margin: 0 !important;
                            padding: 0 !important;
                        }
                        
                        .bill-card {
                            padding: 12px !important;
                            font-size: 14px !important;
                            width: 100% !important;
                            max-width: none !important;
                            margin: 0 !important;
                            box-sizing: border-box !important;
                        }
                        
                        .patient-header {
                            margin-bottom: 15px !important;
                            padding-bottom: 10px !important;
                        }
                        
                        .patient-info {
                            font-size: 14px !important;
                            margin-bottom: 6px !important;
                        }
                        
                        .clinic-logo-image {
                            width: 100px !important;
                            height: 100px !important;
                        }
                        
                        .clinic-name {
                            font-size: 14px !important;
                        }
                        
                        table {
                            margin: 10px 0 !important;
                            font-size: 14px !important;
                            width: 100% !important;
                            box-sizing: border-box !important;
                        }
                        
                        th, td {
                            padding: 10px 6px !important;
                            font-size: 14px !important;
                            box-sizing: border-box !important;
                        }
                        
                        .bill-summary {
                            margin: 15px 0 !important;
                            padding: 20px !important;
                            font-size: 16px !important;
                        }
                        
                        .summary-row {
                            margin-bottom: 10px !important;
                            font-size: 16px !important;
                        }
                        
                        .summary-row.remaining {
                            font-size: 18px !important;
                        }
                        
                        .signature-section {
                            margin-top: 40px !important;
                            padding-top: 20px !important;
                        }
                        
                        .signature-label {
                            font-size: 14px !important;
                            margin-bottom: 30px !important;
                        }
                        
                        .signature-line {
                            height: 50px !important;
                        }
                    }
                `;
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

/* Print styles - maintain same appearance as on screen */
@media print {
    /* Hide toolbar and buttons */
    .v-toolbar,
    .v-btn,
    button {
        display: none !important;
    }
    
    /* A4 page setup */
    @page {
        size: A4;
        margin: 15mm;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .bill-card {
        box-shadow: none !important;
        border: none !important;
        padding: 20px !important;
    }
    
    /* Keep same patient header styling */
    .patient-header {
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .patient-info {
        font-size: 14px !important;
        margin-bottom: 8px !important;
    }
    
    .clinic-logo-image {
        width: 100px !important;
        height: 100px !important;
    }
    
    .clinic-name {
        font-size: 14px !important;
    }
    
    /* Keep same table styling */
    .bill-table >>> th,
    .bill-table >>> td {
        padding: 12px 16px !important;
        font-size: 14px !important;
        border: 1px solid #e0e0e0 !important;
    }
    
    .bill-table >>> th {
        background-color: #f5f5f5 !important;
    }
    
    /* Keep same summary styling */
    .bill-summary {
        margin: 20px 0 !important;
        padding: 20px !important;
        background-color: #f9f9f9 !important;
        border-radius: 8px !important;
    }
    
    .summary-row {
        font-size: 16px !important;
        margin-bottom: 10px !important;
        padding: 8px 0 !important;
    }
    
    .summary-row.remaining {
        font-size: 18px !important;
        margin-top: 15px !important;
        padding-top: 15px !important;
    }
    
    /* Keep same signature styling */
    .signature-section {
        margin-top: 40px !important;
        padding-top: 20px !important;
    }
    
    .signature-label {
        font-size: 14px !important;
        margin-bottom: 30px !important;
    }
    
    .signature-line {
        height: 50px !important;
    }
    
    /* Hide dividers */
    .v-divider {
        display: none !important;
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
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
}
</style>