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
  update_rx(id, data) {
    return request.patch('update-meds/' + id, data);
  },
  findmedicine(params) {
    return request.get('find-medicine/' + params);
  },
  getAppointmentMeds(id) {
    return request.get('get-appointment-meds/' + id);
  },
  reorderAppointmentMeds(data) {
    return request.post('reorder-appointment-meds', data);
  },
  createPrescriptionGroup(data) {
    return request.post('prescription-groups', data);
  },
  updatePrescriptionGroup(id, data) {
    return request.patch(`prescription-groups/${id}`, data);
  },
  deletePrescriptionGroup(id) {
    return request.delete(`prescription-groups/${id}`);
  },
  add_rx_blank(data) {
    return request.post('add-meds-blank', data);
  },

  getAppointmentMedsBlank(id) {
    return request.get('get-appointment-meds-blank/' + id);
  },
};
