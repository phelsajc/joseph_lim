import request from '@/utils/request'

export function getPlansTemplates() {
  return request({
    url: '/plans-templates',
    method: 'get'
  })
}

export function createPlansTemplate(data) {
  return request({
    url: '/plans-templates',
    method: 'post',
    data
  })
}

export function updatePlansTemplate(id, data) {
  return request({
    url: `/plans-templates/${id}`,
    method: 'put',
    data
  })
}

export function deletePlansTemplate(id) {
  return request({
    url: `/plans-templates/${id}`,
    method: 'delete'
  })
}
