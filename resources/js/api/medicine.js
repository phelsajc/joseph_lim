import request from '@/utils/request';

export default {
  add(data) {
    return request.post('store-meds', data);
  },
  get(id) {
    return request.get('get-meds/' + id);
  },
  list(params) {
    return request.get('medicines', params);
  },
  update(data) {
    return request.patch('update-meds', data);
  },
  delete(id) {
    return request.delete('delete-meds/' + id);
  },
  remove_rx(id) {
    return request.delete('remove-meds/' + id);
  },
  add_rx(data) {
    return request.post('add-meds', data);
  },
  findmedicine(params) {
    return request.get('find-medicine/' + params);
  },
  getAppointmentMeds(id) {
    return request.get('get-appointment-meds/' + id);
  },
  add_rx_blank(data) {
    return request.post('add-meds-blank', data);
  },

  getAppointmentMedsBlank(id) {
    return request.get('get-appointment-meds-blank/' + id);
  },
};
