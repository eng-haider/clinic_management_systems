/**
 * Services Export
 * ملف التصدير المركزي لجميع الخدمات
 * 
 * @author Clinic Management System
 * @version 1.0.0
 * 
 * @example
 * // استيراد خدمة واحدة
 * import { PatientService } from '@/services/api'
 * 
 * // استيراد عدة خدمات
 * import { PatientService, CaseService, BillService } from '@/services/api'
 * 
 * // استيراد الكل
 * import api from '@/services/api'
 * api.patients.getAll()
 */

// Axios Client
import apiClient from './index'

// Services
import AuthService from './auth.service'
import PatientService from './patient.service'
import CaseService from './case.service'
import BillService from './bill.service'
import AppointmentService from './appointment.service'
import DashboardService from './dashboard.service'
import DoctorService from './doctor.service'
import UploadService from './upload.service'

// Named exports للاستيراد الفردي
export {
  apiClient,
  AuthService,
  PatientService,
  CaseService,
  BillService,
  AppointmentService,
  DashboardService,
  DoctorService,
  UploadService
}

// Default export للاستيراد الموحد
export default {
  client: apiClient,
  auth: AuthService,
  patients: PatientService,
  cases: CaseService,
  bills: BillService,
  appointments: AppointmentService,
  dashboard: DashboardService,
  doctors: DoctorService,
  upload: UploadService
}
