import request from '@/utils/request';

export function login(data) {
  return request({
    url: '/auth/login',
    method: 'post',
    data: data,
  });
}

export function getInfo(token) {
  return request({
    url: '/user',
    method: 'get',
  });
}

export function logout() {
  return request({
    url: '/auth/logout',
    method: 'post',
  });
}

/**
 * Change password for the authenticated user.
 * Expects: current_password, password, password_confirmation
 */
export function updatePassword(data) {
  return request({
    url: '/auth/password',
    method: 'patch',
    data,
    skipGlobalErrorMessage: true,
  });
}

export function csrf() {
  return request({
    url: '/sanctum/csrf-cookie',
    method: 'get',
  });
}
