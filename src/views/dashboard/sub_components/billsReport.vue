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
                    <v-icon size="80" color="primary">mdi-hospital-building</v-icon>
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
                    <div class="summary-value">{{ totalBill | currency }} <span class="money">د.ع</span></div>
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
                // Handle both old structure (patient.cases) and new structure (direct bills)
                if (this.patient.cases && this.patient.cases.length > 0) {
                    return this.patient.cases.map(item => ({
                        case_type: item.case_categories ? item.case_categories.name_ar : 'غير محدد',
                        tooth_number: item.tooth_num || item.upper_right || item.upper_left || item.lower_right || item.lower_left || '-',
                        price_formatted: `${this.$options.filters.currency(item.price || 0)} د.ع`,
                        date: this.cropdate(item.created_at),
                        price: item.price || 0
                    }));
                } else {
                    // Handle single bill item (new structure)
                    return [{
                        case_type: 'فاتورة مباشرة',
                        tooth_number: '-',
                        price_formatted: `${this.$options.filters.currency(this.patient.price || 0)} د.ع`,
                        date: this.cropdate(this.patient.PaymentDate || this.patient.created_at),
                        price: this.patient.price || 0
                    }];
                }
            },

            totalBill() {
                if (this.patient.cases && this.patient.cases.length > 0) {
                    return this.patient.cases.reduce((total, item) => total + (parseFloat(item.price) || 0), 0);
                } else {
                    return parseFloat(this.patient.price) || 0;
                }
            },

            totalPaid() {
                if (this.patient.cases && this.patient.cases.length > 0) {
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

        methods: {
            cropdate(x) {
                if (!x) return '';
                return x.slice(0, 10);
            },
            
            getCurrentDate() {
                const today = new Date();
                return today.toISOString().slice(0, 10);
            },

            printReport() {
                window.print();
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
    .v-toolbar {
        display: none !important;
    }
    
    .bill-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .patient-header {
        border-bottom: 2px solid #333;
        padding-bottom: 20px;
        margin-bottom: 30px;
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