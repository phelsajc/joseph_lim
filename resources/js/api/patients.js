import request from '@/utils/request';

export default {
  add(params) {
    return request.post('add-patients', params);
  },
  get(id) {
    return request.fetch('patients/' + id);
  },
  list(params) {
    return request.get('patients', params);
  },
  apt_list(params) {
    return request.get('apt_list', params);
  },
  update(announcements) {
    return request.post('update-patients', announcements);
  },
  generate_image(params) {
    return request.get('generateImage', params);
  },
  emailPrescription(params) {
    return request.get('email-prescription/'+ params);
  },
  generate_att(params) {
    return request.get('generateAtt', params);
  },
  delete(id) {
    return request.get('delete-patient/' + id);
  },
  findpatients(params) {
    return request.get('find-patient/' + params);
  },
  addAppointment(params) {
    return request.post('save-appointment', params);
  },
  updateDiagnose(params) {
    return request.post('update-appointment', params);
  },
  getAppointment(id) {
    return request.get('get-appointment/' + id);
  },
  printpdf(){
    return request.get('printpdf');
  },
  getpatient(params) {
    return request.get('get-patient/' + params);
  },
  getpatientpastconsult(params) {
    return request.get('get-patient-past-consult/' + params);
  },
  doneConsult(id){
    return request.get('done-consult/' + id);
  },
  dashboard(){
    return request.get('dashboard');
  },
  getAttachments(id){
    return request.get('get-patient-attachments/' + id);
  },
  addAttachments(form){
    return request.post('add-patient-attachments', form);
  },
  deleteAttachments(id) {
    return request.delete('delete-patient-attachments/' + id);
  },
  cancel_appointment(data) {
    return request.patch('cancel-appointment', data);
  },
  update_bp(data) {
    return request.patch('update-bp', data);
  },
  reorder(data) {
    return request.post('reorder', data);
  },
  activerow(id) {
    return request.patch('set-active/' + id);
  },
  AddProblem(params) {
    return request.post('add-problem', params);
  },
  getPatientAdditionalCheckList(id){
    return request.get('getPatient-additional-checkList/' + id);
  },
  deleteProblem(id) {
    return request.delete('delete-patientProblem/' + id);
  },
  AddAdolecense(params) {
    return request.post('add-adolecense', params);
  },
  getPatientAdolecense(id){
    return request.get('getPatient-adolecense/' + id);
  },
  deleteAdolecense(id) {
    return request.delete('delete-adolecense/' + id);
  },
  AddVax(params) {
    return request.post('add-vaccination', params);
  },
  getPatientVaxs(id){
    return request.get('getPatient-vaccinations/' + id);
  },
  deleteVax(id) {
    return request.delete('delete-vaccination/' + id);
  },
  AddGrowthDev(params) {
    return request.post('add-growthdev', params);
  },
  getPatientGrowthDev(id){
    return request.get('getPatient-growthdevs/' + id);
  },
  deleteGrowthDev(id) {
    return request.delete('delete-growthdev/' + id);
  },
  ImportMedicine(id,appid){
    return request.get('import-medicine/' + id+"/"+appid);
  },
  GetService(id){
    return request.get('get-service/' + id);
  },
  UpdateService(data) {
    return request.post('update-service', data);
  },
  add_service(data) {
    return request.post('add-service', data);
  },
  remove_service(id) {
    return request.delete('remove-service/'+ id);
  },
};
