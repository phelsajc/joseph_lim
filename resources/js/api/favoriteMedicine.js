import request from '@/utils/request';

export function listFavoriteMedicines() {
  return request.get('favorite-medicines');
}

export function createFavoriteMedicine(data) {
  return request.post('favorite-medicines', data);
}

export function updateFavoriteMedicine(id, data) {
  return request.put(`favorite-medicines/${id}`, data);
}

export function deleteFavoriteMedicine(id) {
  return request.delete(`favorite-medicines/${id}`);
}
