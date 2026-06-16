import request from '@/utils/request';

export function listFormTemplates(params) {
  return request({
    url: '/form-templates',
    method: 'get',
    params,
  });
}

export function getFormTemplateCategories() {
  return request({
    url: '/form-templates/meta/categories',
    method: 'get',
  });
}

export function getFormTemplate(id) {
  return request({
    url: `/form-templates/${id}`,
    method: 'get',
  });
}

export function createFormTemplate(data) {
  return request({
    url: '/form-templates',
    method: 'post',
    data,
  });
}

export function updateFormTemplate(id, data) {
  return request({
    url: `/form-templates/${id}`,
    method: 'put',
    data,
  });
}

export function deleteFormTemplate(id) {
  return request({
    url: `/form-templates/${id}`,
    method: 'delete',
  });
}

export function duplicateFormTemplate(id) {
  return request({
    url: `/form-templates/${id}/duplicate`,
    method: 'post',
  });
}
