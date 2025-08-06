import request from '@/utils/request';

export default {
  update(data) {
    return request.patch('update-profile', data);
  },
  get() {
    return request.get('get-profile');
  },
  removeSignature(id) {
    return request.get('remove-signature/'+id);
  },
};
