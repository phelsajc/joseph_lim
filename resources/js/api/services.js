import request from '@/utils/request';

export default {
  add(announcements) {
    return request.post('patients', announcements);
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
    return request.patch('patients', announcements);
  },
  delete(id) {
    return request.remove('patients', id);
  },
  findservices(params) {
    return request.get('find-services/' + params);
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
  remove_service(id) {
    return request.delete('remove-service/' + id);
  },
  add_service(data) {
    return request.post('add-service', data);
  },
  getAppointmentService(id) {
    return request.get('get-appointment-service/' + id);
  },
  getAllServices() {
    return request.get('get-all-services');
  },
};
