<template>
    <div>
        <v-toolbar dark color="primary lighten-1">
            <v-toolbar-title>
                <h3 style="color:#fff;font-family: 'Cairo'"> تقرير الحساب </h3>
            </v-toolbar-title>
            <v-spacer />
            <v-btn @click="printReport()" icon title="طباعة التقرير" style="touch-action: manipulation;">
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
                    <div class="clinic-phone" v-if="$store.state.AdminInfo.clinics_info.whatsapp_phone" style="font-size: 12px; color: #666; direction: ltr;">{{ $store.state.AdminInfo.clinics_info.whatsapp_phone }}</div>
                </div>
            </div>

            <v-divider class="my-3"></v-divider>

        

            <!-- Bills Table - Force Desktop Layout -->
            <div class="table-container">
                <v-data-table 
                    class="bill-table"
                    :headers="headers"
                    :items="billItems"
                    hide-default-footer
                    disable-pagination
                    :mobile-breakpoint="0"
                    fixed-header>
                    
                 
                    
                    <template v-slot:no-data>
                        <div class="text-center pa-4">لا توجد فواتير</div>
                    </template>
                </v-data-table>
            </div>

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
                        // if (bill.is_paid) {
                            return total + (parseFloat(bill.price) || 0);
                        // }
                        // return total;
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
                
                // Only check for doctors array from cases - removed all other sources
                if (item.doctors && Array.isArray(item.doctors) && item.doctors.length > 0) {
                    console.log('Found doctors array:', item.doctors);
                    return item.doctors[0].name; // Get first doctor's name
                }
                
                // Check if item has a single doctors object (from cases)
                if (item.doctors && !Array.isArray(item.doctors) && item.doctors.name) {
                    console.log('Found doctors object:', item.doctors);
                    return item.doctors.name;
                }
                
                console.log('No doctor name found from cases');
                return '-';
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
                // Try to get clinic logo from store first
                const clinicLogo = this.$store.getters.getClinicLogo;
                if (clinicLogo) {
                    return clinicLogo;
                }
                
                // Fallback to default logo
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
                                paperSize: 'A4', // Changed from A5 to A4
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
                // Show mobile-specific confirmation dialog with A5 instructions
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
                            /* A4 page setup - 210mm x 297mm */
                            body {
                                font-family: 'Cairo', Arial, sans-serif;
                                direction: rtl;
                                margin: 0;
                                padding: 10px;
                                background: #f5f5f5;
                                min-height: 100vh;
                                /* A4 dimensions simulation - properly centered */
                                max-width: 210mm;
                                width: 210mm;
                                margin: 0 auto;
                                background: white;
                                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                                /* Ensure content doesn't get cut from left */
                                overflow-x: visible;
                                position: relative;
                            }
                            
                            /* Print controls styling */
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
                            
                            /* Bill card styling for A4 */
                            .bill-card {
                                width: 100%;
                                margin: 0;
                                padding: 10mm;
                                background: white;
                                border: none;
                                box-shadow: none;
                                font-family: 'Cairo', Arial, sans-serif;
                                direction: rtl;
                                font-size: 14px;
                                line-height: 1.5;
                                /* Ensure content fits properly in A4 */
                                box-sizing: border-box;
                                overflow: visible;
                            }
                            
                            /* Patient header */
                            .patient-header {
                                display: flex;
                                justify-content: space-between;
                                flex-wrap: nowrap;
                                margin-bottom: 15px;
                                padding-bottom: 10px;
                                border-bottom: 2px solid #333;
                                align-items: flex-start;
                                width: 100%;
                                /* Ensure proper spacing for A4 */
                                gap: 15px;
                            }
                            
                            .patient-details {
                                flex: 1;
                                max-width: 65%;
                                /* Ensure text doesn't get cut */
                                overflow: visible;
                            }
                            
                            .patient-info {
                                display: flex;
                                align-items: center;
                                gap: 8px;
                                margin-bottom: 8px;
                                font-size: 12px;
                                /* Ensure content fits */
                                flex-wrap: wrap;
                            }
                            
                            .info-label {
                                font-weight: bold;
                                white-space: nowrap;
                                min-width: 80px;
                                font-size: 12px;
                            }
                            
                            .info-value {
                                font-weight: 500;
                                font-size: 12px;
                                /* Allow text to wrap if needed */
                                word-break: break-word;
                            }
                            
                            .clinic-logo {
                                text-align: center;
                                flex-shrink: 0;
                                max-width: 30%;
                                /* Better positioning for A4 */
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: flex-start;
                            }
                            
                            .clinic-logo-image {
                                width: 80px;
                                height: 80px;
                                object-fit: contain;
                                margin-bottom: 8px;
                                /* Ensure image doesn't get cut */
                                max-width: 100%;
                            }
                            
                            .clinic-name {
                                font-weight: bold;
                                font-size: 12px;
                                text-align: center;
                                line-height: 1.4;
                                /* Ensure text wraps properly */
                                word-break: break-word;
                                max-width: 100%;
                            }
                            
                            /* Table styling */
                            .table-container {
                                margin: 15px 0;
                                width: 100%;
                                overflow: visible;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 11px;
                                table-layout: fixed;
                                /* Ensure table fits properly */
                                margin: 0;
                            }
                            
                            th, td {
                                padding: 6px 4px;
                                text-align: right;
                                border: 1px solid #333;
                                font-size: 10px;
                                line-height: 1.3;
                                /* Ensure text fits in cells */
                                word-break: break-word;
                                overflow: hidden;
                            }
                            
                            th {
                                background-color: #f5f5f5;
                                font-weight: bold;
                                font-size: 10px;
                            }
                            
                            /* Bill summary */
                            .bill-summary {
                                margin: 15px 0;
                                padding: 10px;
                                background-color: #f9f9f9;
                                border: 1px solid #333;
                                border-radius: 4px;
                                width: 100%;
                                box-sizing: border-box;
                            }
                            
                            .summary-row {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 8px;
                                padding: 4px 0;
                                font-size: 11px;
                                /* Ensure content fits */
                                align-items: center;
                            }
                            
                            .summary-row.remaining {
                                border-top: 2px solid #333;
                                margin-top: 10px;
                                padding-top: 10px;
                                font-weight: bold;
                                font-size: 12px;
                            }
                            
                            .summary-label {
                                font-weight: bold;
                                flex-shrink: 0;
                            }
                            
                            .summary-value {
                                text-align: left;
                                flex-shrink: 0;
                            }
                            
                            /* Signature section */
                            .signature-section {
                                display: flex;
                                justify-content: space-between;
                                margin-top: 20px;
                                padding-top: 15px;
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
                                margin-bottom: 20px;
                                font-size: 11px;
                                line-height: 1.3;
                            }
                            
                            .signature-line {
                                border-bottom: 2px solid #333;
                                height: 30px;
                                width: 100%;
                            }
                            
                            /* Hide dividers and buttons */
                            .v-divider {
                                display: none;
                            }
                            
                            /* Print media query for A4 */
                            @media print {
                                .print-controls {
                                    display: none !important;
                                }
                                
                                @page {
                                    size: A4;
                                    margin: 15mm;
                                    /* Standard A4 margins */
                                    margin-left: 15mm;
                                    margin-right: 15mm;
                                }
                                
                                body {
                                    max-width: none;
                                    width: auto;
                                    box-shadow: none;
                                    background: white;
                                    margin: 0;
                                    padding: 0;
                                    /* Prevent content from being cut */
                                    overflow: visible;
                                }
                                
                                .bill-card {
                                    padding: 10mm;
                                    font-size: 11px;
                                    width: 100%;
                                    max-width: none;
                                    /* Ensure all content fits */
                                    overflow: visible;
                                }
                                
                                .patient-info {
                                    font-size: 10px;
                                    margin-bottom: 6px;
                                }
                                
                                .clinic-logo-image {
                                    width: 60px;
                                    height: 60px;
                                }
                                
                                .clinic-name {
                                    font-size: 10px;
                                }
                                
                                th, td {
                                    font-size: 9px;
                                    padding: 4px 3px;
                                }
                                
                                .summary-row {
                                    font-size: 10px;
                                }
                                
                                .signature-label {
                                    font-size: 9px;
                                }
                                
                                .signature-line {
                                    height: 20px;
                                }
                            }
                            
                            /* Responsive adjustments */
                            @media screen and (max-width: 480px) {
                                body {
                                    max-width: 100vw;
                                    width: 100vw;
                                    padding: 3px;
                                    /* Prevent horizontal scroll */
                                    overflow-x: hidden;
                                }
                                
                                .bill-card {
                                    padding: 3px;
                                    /* Ensure content fits on mobile */
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
                    /* Hide screen-only elements during print */
                    @media print {
                        .screen-only {
                            display: none !important;
                        }
                        
                        /* Hide all buttons when printing */
                        button {
                            display: none !important;
                        }
                        
                        /* A4 page setup for compact printing */
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        
                        /* Fallback for mobile browsers that don't support A4 */
                        @supports not (size: A4) {
                            @page {
                                margin: 15mm;
                            }
                            
                            body {
                                /* Scale content to fit A4 size on standard paper */
                                transform: scale(1.0);
                                transform-origin: top center;
                                width: 210mm;
                                max-width: 210mm;
                            }
                        }
                        
                        /* Mobile-specific print adjustments */
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
                            font-size: 12px !important;
                            line-height: 1.4 !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            /* Force A4-like dimensions */
                            max-width: 210mm !important;
                            max-height: 297mm !important;
                        }
                        
                        /* Standard layout for A4 */
                        .bill-card {
                            margin: 0 !important;
                            padding: 15mm !important;
                            font-size: 11px !important;
                        }
                        
                        /* Patient header standard */
                        .patient-header {
                            margin-bottom: 15px !important;
                            font-size: 10px !important;
                        }
                        
                        .patient-info {
                            margin-bottom: 6px !important;
                            font-size: 10px !important;
                        }
                        
                        .clinic-logo-image {
                            width: 60px !important;
                            height: 60px !important;
                        }
                        
                        .clinic-name {
                            font-size: 10px !important;
                        }
                        
                        /* Force table to display properly and standard A4 size */
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
                            font-size: 9px !important;
                        }
                        
                        .v-data-table th,
                        .v-data-table td {
                            border: 1px solid #333 !important;
                            padding: 4px 3px !important;
                            font-size: 9px !important;
                            text-align: right !important;
                            white-space: nowrap !important;
                            line-height: 1.2 !important;
                        }
                        
                        .v-data-table thead th {
                            background-color: #f5f5f5 !important;
                            font-weight: bold !important;
                            font-size: 9px !important;
                        }
                        
                        /* Standard bill summary */
                        .bill-summary {
                            margin: 10px 0 !important;
                            font-size: 10px !important;
                        }
                        
                        .summary-row {
                            margin-bottom: 6px !important;
                            font-size: 10px !important;
                        }
                        
                        /* Standard signature section */
                        .signature-section {
                            margin-top: 15px !important;
                            padding-top: 10px !important;
                        }
                        
                        .signature-label {
                            font-size: 9px !important;
                            margin-bottom: 15px !important;
                        }
                        
                        .signature-line {
                            height: 20px !important;
                        }
                        
                        /* Hide dividers to save space */
                        .v-divider {
                            display: none !important;
                        }
                    }
                    
                    /* Screen styles for better view before print */
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
                                <li>في إعدادات الطباعة، اختر حجم الورق A5</li>
                                <li>إذا لم يكن A5 متاحاً، اختر "مخصص" واكتب: العرض 148mm، الطول 210mm</li>
                                <li>اختر الاتجاه "أفقي" أو "Landscape" لعرض أفضل للجدول</li>
                                <li>اضغط "طباعة"</li>
                            </ol>
                        </div>
                    `,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#2196F3'
                });
            },

            // Check if device is mobile (keep for reference but use desktop print for all)
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
                        font-size: 11px;
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
                        width: 60px;
                        height: 60px;
                        object-fit: contain;
                        margin-bottom: 8px;
                    }
                    
                    .clinic-name {
                        font-weight: bold;
                        font-size: 12px;
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 15px 0;
                        font-size: 10px;
                    }
                    
                    th, td {
                        padding: 6px 4px;
                        text-align: right;
                        border: 1px solid #333;
                        white-space: nowrap;
                        font-size: 9px;
                    }
                    
                    th {
                        background-color: #f5f5f5;
                        font-weight: bold;
                    }
                    
                    .bill-summary {
                        margin: 15px 0;
                        padding: 10px;
                        background-color: #f9f9f9;
                        border: 1px solid #333;
                        border-radius: 3px;
                    }
                    
                    .summary-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 6px;
                        padding: 3px 0;
                        font-size: 10px;
                    }
                    
                    .summary-row.remaining {
                        border-top: 2px solid #333;
                        margin-top: 8px;
                        padding-top: 8px;
                        font-weight: bold;
                        font-size: 11px;
                    }
                    
                    .summary-label {
                        font-weight: bold;
                    }
                    
                    .signature-section {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 20px;
                        padding-top: 15px;
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
                        margin-bottom: 20px;
                        font-size: 10px;
                    }
                    
                    .signature-line {
                        border-bottom: 2px solid #333;
                        height: 30px;
                        width: 100%;
                    }
                    
                    /* Hide v-divider elements and buttons */
                    .v-divider,
                    .v-btn,
                    button {
                        display: none !important;
                    }
                    
                    /* A4 print page setup for standard one-page layout */
                    @media print {
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        
                        /* Fallback for browsers that don't support A4 size */
                        @supports not (size: A4) {
                            @page {
                                margin: 15mm;
                            }
                            
                            body {
                                max-width: 210mm !important;
                                margin: 0 auto !important;
                                transform: scale(1.0) !important;
                                transform-origin: top center !important;
                            }
                        }
                        
                        /* Mobile browser specific adjustments */
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
                            font-size: 9px !important;
                            line-height: 1.1 !important;
                        }
                        
                        .bill-card {
                            padding: 5mm !important;
                            font-size: 8px !important;
                        }
                        
                        .patient-header {
                            margin-bottom: 6px !important;
                            padding-bottom: 4px !important;
                        }
                        
                        .patient-info {
                            font-size: 7px !important;
                            margin-bottom: 2px !important;
                        }
                        
                        .clinic-logo-image {
                            width: 25px !important;
                            height: 25px !important;
                        }
                        
                        .clinic-name {
                            font-size: 7px !important;
                        }
                        
                        table {
                            margin: 4px 0 !important;
                            font-size: 6px !important;
                        }
                        
                        th, td {
                            padding: 1px 2px !important;
                            font-size: 6px !important;
                        }
                        
                        .bill-summary {
                            margin: 4px 0 !important;
                            padding: 4px !important;
                            font-size: 7px !important;
                        }
                        
                        .summary-row {
                            margin-bottom: 2px !important;
                            font-size: 7px !important;
                        }
                        
                        .signature-section {
                            margin-top: 6px !important;
                            padding-top: 4px !important;
                        }
                        
                        .signature-label {
                            font-size: 6px !important;
                            margin-bottom: 8px !important;
                        }
                        
                        .signature-line {
                            height: 10px !important;
                        }
                    }
                `;
            },

            // Show instructions for manual printing on mobile
            showMobilePrintInstructions() {
                this.$swal.fire({
                    icon: 'info',
                    title: 'تعليمات الطباعة',
                    html: `
                        <div style="text-align: right; direction: rtl;">
                            <p><strong>للطباعة بحجم A5:</strong></p>
                            <ol style="text-align: right;">
                                <li>في نافذة الطباعة، اختر "خيارات أكثر" أو "More settings"</li>
                                <li>في "حجم الورق" أو "Paper size"، اختر A5</li>
                                <li>إذا لم يكن A5 متاحاً، اختر "مخصص" واكتب:</li>
                                <ul style="margin: 10px 0;">
                                    <li>العرض: 148mm أو 5.8 inches</li>
                                    <li>الطول: 210mm أو 8.3 inches</li>
                                </ul>
                                <li>تأكد من أن الهوامش مضبوطة على "ضيق" أو "Narrow"</li>
                                <li>اضغط على "طباعة"</li>
                            </ol>
                            <p><strong>على الهاتف المحمول:</strong></p>
                            <ol style="text-align: right;">
                                <li>اضغط على زر القائمة (⋮) في المتصفح</li>
                                <li>اختر "مشاركة" أو "Share"</li>
                                <li>اختر "طباعة" أو "Print"</li>
                                <li>أو احفظ الصفحة كـ PDF ثم اطبعها</li>
                            </ol>
                        </div>
                    `,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#2196F3',
                    width: '600px'
                });
            },

            // Alternative: Download as PDF (fallback)
            downloadAsPDF(element) {
                // TODO: Implement PDF generation using element content
                console.log('PDF generation requested for element:', element);
                
                this.$swal.fire({
                    icon: 'info', 
                    title: 'تنزيل التقرير',
                    text: 'سيتم تحويل التقرير إلى PDF للتنزيل...',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                // You can implement PDF generation here using libraries like jsPDF or html2canvas
                // For now, we'll show the print instructions
                setTimeout(() => {
                    this.showMobilePrintInstructions();
                }, 2000);
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

/* Table container for proper scrolling */
.table-container {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    margin: 10px 0;
}

/* Mobile scroll hint */
.mobile-scroll-hint {
    text-align: center;
    direction: rtl;
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
    /* Force desktop table layout on all devices */
    width: 100% !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}

/* Force desktop table behavior globally */
.bill-table >>> .v-data-table__wrapper {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}

.bill-table >>> table {
    width: 100% !important;
    min-width: 600px !important; /* Ensure minimum width for desktop layout */
    border-collapse: collapse !important;
    table-layout: auto !important;
}

.bill-table >>> th {
    background-color: #f5f5f5 !important;
    font-weight: bold !important;
    text-align: right !important;
    /* Desktop-style headers */
    padding: 12px 16px !important;
    border: 1px solid #e0e0e0 !important;
    white-space: nowrap !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 2 !important;
}

.bill-table >>> td {
    text-align: right !important;
    padding: 12px 16px !important;
    /* Desktop-style cells */
    border: 1px solid #e0e0e0 !important;
    white-space: nowrap !important;
    min-width: 100px !important;
}

/* Disable Vuetify mobile breakpoints for this table */
.bill-table >>> .v-data-table {
    /* Force desktop layout regardless of screen size */
    display: table !important;
    width: 100% !important;
}

.bill-table >>> .v-data-table__wrapper {
    /* Ensure table wrapper doesn't collapse on mobile */
    overflow-x: auto !important;
    display: block !important;
}

.bill-table >>> .v-data-table > .v-data-table__wrapper > table {
    /* Force table display (not block) */
    display: table !important;
    width: 100% !important;
    min-width: 600px !important;
}

.bill-table >>> .v-data-table > .v-data-table__wrapper > table > thead,
.bill-table >>> .v-data-table > .v-data-table__wrapper > table > tbody {
    /* Force table-row-group display */
    display: table-row-group !important;
}

.bill-table >>> .v-data-table > .v-data-table__wrapper > table > thead > tr,
.bill-table >>> .v-data-table > .v-data-table__wrapper > table > tbody > tr {
    /* Force table-row display */
    display: table-row !important;
}

.bill-table >>> .v-data-table > .v-data-table__wrapper > table > thead > tr > th,
.bill-table >>> .v-data-table > .v-data-table__wrapper > table > tbody > tr > td {
    /* Force table-cell display */
    display: table-cell !important;
}

/* Override any mobile responsive CSS from Vuetify */
@media screen and (max-width: 960px) {
    .bill-table >>> .v-data-table {
        display: table !important;
    }
    
    .bill-table >>> .v-data-table__wrapper {
        overflow-x: auto !important;
    }
    
    .bill-table >>> table {
        display: table !important;
        min-width: 600px !important;
    }
    
    .bill-table >>> thead,
    .bill-table >>> tbody {
        display: table-row-group !important;
    }
    
    .bill-table >>> tr {
        display: table-row !important;
    }
    
    .bill-table >>> th,
    .bill-table >>> td {
        display: table-cell !important;
    }
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

/* Mobile responsive - force desktop table layout on mobile */
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
    
    /* Force desktop table layout on mobile - NO mobile responsive table */
    .bill-table {
        overflow-x: auto !important;
        width: 100% !important;
        margin: 10px 0 !important;
        /* Force table to display as desktop */
        min-width: 600px !important;
    }
    
    .bill-table >>> .v-data-table__wrapper {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        /* Ensure table shows full width */
        min-width: 600px !important;
    }
    
    .bill-table >>> table {
        /* Force table to maintain desktop layout */
        width: 100% !important;
        min-width: 600px !important;
        table-layout: auto !important;
        border-collapse: collapse !important;
    }
    
    .bill-table >>> th,
    .bill-table >>> td {
        /* Desktop-style cells on mobile */
        padding: 12px 8px !important;
        font-size: 13px !important;
        white-space: nowrap !important;
        min-width: 100px !important;
        border: 1px solid #e0e0e0 !important;
        text-align: right !important;
        /* Prevent text wrapping to maintain desktop look */
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    
    .bill-table >>> thead th {
        background-color: #f5f5f5 !important;
        font-weight: bold !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 1 !important;
    }
    
    .v-card {
        overflow-x: auto !important;
        /* Add padding to allow horizontal scroll */
        padding: 10px !important;
    }
    
    /* Add scroll hint for mobile users */
    .bill-table::before {
        content: "← اسحب يميناً ويساراً لرؤية جميع البيانات →" !important;
        display: block !important;
        text-align: center !important;
        font-size: 12px !important;
        color: #666 !important;
        padding: 8px !important;
        background-color: #e3f2fd !important;
        border-radius: 4px !important;
        margin-bottom: 10px !important;
        font-family: 'Cairo', sans-serif !important;
    }
}

/* Mobile print button enhancement */
@media screen and (max-width: 768px) {
    .v-toolbar .v-btn {
        /* Ensure print button is large enough for touch */
        min-width: 48px !important;
        min-height: 48px !important;
        padding: 12px !important;
    }
    
    .v-toolbar .v-btn .v-icon {
        font-size: 24px !important;
    }
    
    /* Add touch feedback */
    .v-toolbar .v-btn:active {
        background-color: rgba(255, 255, 255, 0.2) !important;
        transform: scale(0.98) !important;
        transition: all 0.1s ease !important;
    }
    
    /* Prevent text selection on buttons */
    .v-toolbar .v-btn {
        -webkit-user-select: none !important;
        user-select: none !important;
        -webkit-touch-callout: none !important;
    }
}

/* A5 page size support for new window printing only */
@media print {
    @page {
        size: A5;
        margin: 10mm;
    }
}

/* Mobile print modal styles */
.mobile-print-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 10000 !important;
    background: rgba(0, 0, 0, 0.8) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: auto !important;
}

.mobile-print-content {
    background: white !important;
    margin: 10px !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    max-height: calc(100vh - 20px) !important;
}

.mobile-print-header {
    background: #2196F3 !important;
    color: white !important;
    padding: 15px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-shrink: 0 !important;
}

.mobile-print-body {
    padding: 20px !important;
    overflow: auto !important;
    flex: 1 !important;
    font-family: Cairo, sans-serif !important;
    -webkit-overflow-scrolling: touch !important;
}

.mobile-print-footer {
    background: #f5f5f5 !important;
    padding: 15px !important;
    border-top: 1px solid #ddd !important;
    flex-shrink: 0 !important;
}

/* Responsive adjustments for mobile modal */
@media screen and (max-width: 480px) {
    .mobile-print-content {
        margin: 5px !important;
        border-radius: 4px !important;
    }
    
    .mobile-print-header {
        padding: 12px !important;
    }
    
    .mobile-print-body {
        padding: 15px !important;
    }
    
    .mobile-print-footer {
        padding: 12px !important;
    }
}
</style>