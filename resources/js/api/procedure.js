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
  update(announcements) {
    return request.patch('patients', announcements);
  },
  delete(id) {
    return request.remove('patients', id);
  },
  findprocedure(params) {
    return request.get('find-procedure/' + params);
  },
  getAppointment(id) {
    return request.get('get-appointment/' + id);
  },
  remove_diagnostic(id) {
    return request.delete('remove-diagnostic/' + id);
  },
  add_diagnostic(data) {
    return request.post('add-diagnostic', data);
  },
  getAppointmentDiagnostics(id) {
    return request.get('get-appointment-diagnostics/' + id);
  },
};
