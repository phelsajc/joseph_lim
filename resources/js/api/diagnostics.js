import request from '@/utils/request';

export default {
  add(data) {
    return request.post('store-diagnostics', data);
  },
  get(id) {
    return request.get('get-diagnostics/' + id);
  },
  list(params) {
    return request.get('diagnostics', params);
  },
  update(data) {
    return request.patch('update-diagnostics', data);
  },
  delete(id) {
    return request.delete('delete-diagnostics/' + id);
  },
  getAllDiagnostics() {
    return request.get('get-all-diagnostics');
  },
};
