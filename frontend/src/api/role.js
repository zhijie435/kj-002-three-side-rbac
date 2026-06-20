import request from '@/utils/request'

export function getRoleList(params) {
  return request({
    url: '/roles',
    method: 'get',
    params,
  })
}

export function getRole(id) {
  return request({
    url: `/roles/${id}`,
    method: 'get',
  })
}

export function createRole(data) {
  return request({
    url: '/roles',
    method: 'post',
    data,
  })
}

export function updateRole(id, data) {
  return request({
    url: `/roles/${id}`,
    method: 'put',
    data,
  })
}

export function deleteRole(id) {
  return request({
    url: `/roles/${id}`,
    method: 'delete',
  })
}

export function toggleRoleStatus(id) {
  return request({
    url: `/roles/${id}/toggle-status`,
    method: 'patch',
  })
}

export function getAllRoles() {
  return request({
    url: '/roles/all',
    method: 'get',
  })
}

export function getPermissionTree() {
  return request({
    url: '/permissions/all',
    method: 'get',
  })
}

export function getPermissionList(params) {
  return request({
    url: '/permissions',
    method: 'get',
    params,
  })
}

export function createPermission(data) {
  return request({
    url: '/permissions',
    method: 'post',
    data,
  })
}
