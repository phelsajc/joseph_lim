import request from '@/utils/request';

export default {
  add(announcements) {
    return request.post('service', announcements);
  },
  update(announcements) {
    return request.patch('service', announcements);
  },
  delete(id) {
    return request.delete('service/'+ id);
  },
  findservices(params) {
    return request.get('find-services/' + params);
  },
  getAllServices(params) {
    return request.get('services',params);
  },
  getAppointmentService(id) {
    return request.get('get-appointment-service/' + id);
  },
};
