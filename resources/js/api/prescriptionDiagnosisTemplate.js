import request from '@/utils/request';

export function listPrescriptionDiagnosisTemplates(params) {
  return request({
    url: '/prescription-diagnosis-templates',
    method: 'get',
    params,
  });
}

export function getPrescriptionDiagnosisTemplate(id) {
  return request({
    url: `/prescription-diagnosis-templates/${id}`,
    method: 'get',
  });
}

export function createPrescriptionDiagnosisTemplate(data) {
  return request({
    url: '/prescription-diagnosis-templates',
    method: 'post',
    data,
  });
}

export function updatePrescriptionDiagnosisTemplate(id, data) {
  return request({
    url: `/prescription-diagnosis-templates/${id}`,
    method: 'put',
    data,
  });
}

export function deletePrescriptionDiagnosisTemplate(id) {
  return request({
    url: `/prescription-diagnosis-templates/${id}`,
    method: 'delete',
  });
}

export function diagnosisNameSuggestions(params) {
  return request({
    url: '/prescription-diagnosis-templates/diagnosis-suggestions',
    method: 'get',
    params,
  });
}
