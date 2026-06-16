import moment from 'moment-timezone';

/**
 * Replace {{token}} placeholders in HTML with appointment context.
 * Unknown tokens are left unchanged so authors can add custom placeholders later.
 */
export function replaceFormTemplatePlaceholders(html, context) {
  if (!html || typeof html !== 'string') {
    return '';
  }
  const c = context || {};
  const map = {
    patient_name: c.patient_name != null ? String(c.patient_name) : '',
    doctor_name: c.doctor_name != null ? String(c.doctor_name) : '',
    date: c.date != null ? String(c.date) : '',
    age: c.age != null ? String(c.age) : '',
    gender: c.gender != null ? String(c.gender) : '',
    address: c.address != null ? String(c.address) : '',
    patient_address: c.patient_address != null ? String(c.patient_address) : '',
    patient_contact: c.patient_contact != null ? String(c.patient_contact) : '',
    appointment_date: c.appointment_date != null ? String(c.appointment_date) : '',
    consultation_date: c.consultation_date != null ? String(c.consultation_date) : '',
    diagnosis: c.diagnosis != null ? String(c.diagnosis) : '',
  };

  return html.replace(/\{\{\s*([a-z0-9_]+)\s*\}\}/gi, (match, key) => {
    const k = String(key).toLowerCase();
    if (Object.prototype.hasOwnProperty.call(map, k)) {
      return map[k];
    }
    return match;
  });
}

function formatGenderFromSex(sex) {
  if (sex === '2' || sex === 2) {
    return 'Female';
  }
  if (sex === '1' || sex === 1) {
    return 'Male';
  }
  return '';
}

/**
 * Build replacement context from appointment form.vue state (profile, form, store).
 */
export function buildFormTemplateContext(vm) {
  const profile = (vm && vm.profile) || {};
  const form = (vm && vm.form) || {};
  const store = vm && vm.$store;
  const doctorName = store && store.getters && store.getters.name ? store.getters.name : '';

  const apptRaw = form.appointment_dt || vm.appointment_dt || '';
  const appt = apptRaw ? moment(apptRaw).format('MMM D, YYYY h:mm A') : '';
  const address = profile.address || '';
  const diagnosis = (form.diagnosis || form.medcert_diagnosis || form.referral_diagnosis || '').trim();

  return {
    patient_name: profile.patientname || '',
    doctor_name: doctorName,
    date: moment().format('MMM D, YYYY'),
    age: profile.age != null && profile.age !== '' ? String(profile.age) : '',
    gender: formatGenderFromSex(profile.sex),
    address,
    patient_address: address,
    patient_contact: profile.contactno || '',
    appointment_date: appt,
    consultation_date: appt,
    diagnosis,
  };
}
