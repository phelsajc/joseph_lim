/** Predefined template categories (also merged with DB values via API). */
export const FORM_TEMPLATE_CATEGORIES = [
  'Medical Certificate',
  'Referral',
  'Admitting Letter',
  'PT Notes',
  'Consultation Form',
  'Clearance',
  'Others',
];

/** Placeholders available in the builder sidebar and resolved in appointments. */
export const FORM_TEMPLATE_PLACEHOLDERS = [
  { token: 'patient_name', label: 'Patient name', example: 'Juan Dela Cruz' },
  { token: 'age', label: 'Age', example: '45' },
  { token: 'gender', label: 'Gender', example: 'Male' },
  { token: 'address', label: 'Address', example: '123 Main St' },
  { token: 'patient_address', label: 'Address (alias)', example: '123 Main St' },
  { token: 'patient_contact', label: 'Contact', example: '0917…' },
  { token: 'date', label: 'Today\'s date', example: 'May 21, 2026' },
  { token: 'appointment_date', label: 'Appointment date', example: 'May 21, 2026 2:00 PM' },
  { token: 'consultation_date', label: 'Consultation date', example: 'May 21, 2026 2:00 PM' },
  { token: 'doctor_name', label: 'Doctor name', example: 'Dr. Smith' },
  { token: 'diagnosis', label: 'Diagnosis', example: 'Hypertension' },
];

/** Default clinic logo for letterhead snippets (public path). */
export const FORM_TEMPLATE_CLINIC_LOGO = '/img/lim_fb.png';

/**
 * Clinic form page — A5 portrait (matches prescription PDF / RequestprescriptionA5).
 */
export const FORM_PAGE_SIZE = {
  label: 'A5 portrait',
  description: 'Same as prescription forms',
  widthMm: 148,
  heightMm: 210,
  cssPageSize: 'A5 portrait',
  printMargins: '7mm 8mm',
  contentPadding: '7mm 8mm',
  previewFontSize: '11px',
  previewLineHeight: '1.45',
};
