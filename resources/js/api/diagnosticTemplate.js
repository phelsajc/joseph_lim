import request from '@/utils/request';

export function listDiagnosticTemplates(params) {
  return request({
    url: '/diagnostic-templates',
    method: 'get',
    params,
  });
}

export function getDiagnosticTemplate(id) {
  return request({
    url: `/diagnostic-templates/${id}`,
    method: 'get',
  });
}

export function createDiagnosticTemplate(data) {
  return request({
    url: '/diagnostic-templates',
    method: 'post',
    data,
  });
}

export function updateDiagnosticTemplate(id, data) {
  return request({
    url: `/diagnostic-templates/${id}`,
    method: 'put',
    data,
  });
}

export function deleteDiagnosticTemplate(id) {
  return request({
    url: `/diagnostic-templates/${id}`,
    method: 'delete',
  });
}

export function diagnosisTemplateNameSuggestions(params) {
  return request({
    url: '/diagnostic-templates/diagnosis-suggestions',
    method: 'get',
    params,
  });
}

