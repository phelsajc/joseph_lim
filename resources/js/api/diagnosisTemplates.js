import request from '@/utils/request'

export function getDiagnosisTemplates() {
  return request({
    url: '/diagnosis-templates',
    method: 'get'
  })
}

export function createDiagnosisTemplate(data) {
  return request({
    url: '/diagnosis-templates',
    method: 'post',
    data
  })
}

export function updateDiagnosisTemplate(id, data) {
  return request({
    url: `/diagnosis-templates/${id}`,
    method: 'put',
    data
  })
}

export function deleteDiagnosisTemplate(id) {
  return request({
    url: `/diagnosis-templates/${id}`,
    method: 'delete'
  })
}
