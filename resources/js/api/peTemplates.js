import request from '@/utils/request'

export function getPeTemplates() {
  return request({
    url: '/pe-templates',
    method: 'get'
  })
}

export function getPeTemplatesByType(type) {
  return request({
    url: `/pe-templates/type/${type}`,
    method: 'get'
  })
}

export function createPeTemplate(data) {
  return request({
    url: '/pe-templates',
    method: 'post',
    data
  })
}

export function updatePeTemplate(id, data) {
  return request({
    url: `/pe-templates/${id}`,
    method: 'put',
    data
  })
}

export function deletePeTemplate(id) {
  return request({
    url: `/pe-templates/${id}`,
    method: 'delete'
  })
}

export function togglePeTemplateStatus(id) {
  return request({
    url: `/pe-templates/${id}/toggle-status`,
    method: 'patch'
  })
}

