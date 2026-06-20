import request from '@/utils/request'

export function getShearerlineList(params) {
  return request({
    url: '/shearers',
    method: 'get',
    params,
  })
}

export function getShearerline(id) {
  return request({
    url: `/shearers/${id}`,
    method: 'get',
  })
}

export function createShearerline(data) {
  return request({
    url: '/shearers',
    method: 'post',
    data,
  })
}

export function updateShearerline(id, data) {
  return request({
    url: `/shearers/${id}`,
    method: 'put',
    data,
  })
}

export function deleteShearerline(id) {
  return request({
    url: `/shearers/${id}`,
    method: 'delete',
  })
}

export function startShearerline(id) {
  return request({
    url: `/shearers/${id}/start`,
    method: 'patch',
  })
}

export function stopShearerline(id) {
  return request({
    url: `/shearers/${id}/stop`,
    method: 'patch',
  })
}

export function setMaintenanceShearerline(id) {
  return request({
    url: `/shearers/${id}/maintenance`,
    method: 'patch',
  })
}

export function setErrorShearerline(id) {
  return request({
    url: `/shearers/${id}/error`,
    method: 'patch',
  })
}

export function getAllShearers() {
  return request({
    url: '/shearers/all',
    method: 'get',
  })
}

export function getTaskList(params) {
  return request({
    url: '/shearerline-tasks',
    method: 'get',
    params,
  })
}

export function getTask(id) {
  return request({
    url: `/shearerline-tasks/${id}`,
    method: 'get',
  })
}

export function createTask(data) {
  return request({
    url: '/shearerline-tasks',
    method: 'post',
    data,
  })
}

export function updateTask(id, data) {
  return request({
    url: `/shearerline-tasks/${id}`,
    method: 'put',
    data,
  })
}

export function deleteTask(id) {
  return request({
    url: `/shearerline-tasks/${id}`,
    method: 'delete',
  })
}

export function assignTask(id, data) {
  return request({
    url: `/shearerline-tasks/${id}/assign`,
    method: 'patch',
    data,
  })
}

export function startTask(id) {
  return request({
    url: `/shearerline-tasks/${id}/start`,
    method: 'patch',
  })
}

export function completeTask(id) {
  return request({
    url: `/shearerline-tasks/${id}/complete`,
    method: 'patch',
  })
}

export function cancelTask(id) {
  return request({
    url: `/shearerline-tasks/${id}/cancel`,
    method: 'patch',
  })
}
