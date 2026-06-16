import request from '@/utils/request'

export function getMedcertRemarksTemplates() {
  return request({
    url: '/medcert-remarks-templates',
    method: 'get'
  })
}

export function createMedcertRemarksTemplate(data) {
  return request({
    url: '/medcert-remarks-templates',
    method: 'post',
    data
  })
}

export function updateMedcertRemarksTemplate(id, data) {
  return request({
    url: `/medcert-remarks-templates/${id}`,
    method: 'put',
    data
  })
}

export function deleteMedcertRemarksTemplate(id) {
  return request({
    url: `/medcert-remarks-templates/${id}`,
    method: 'delete'
  })
}
