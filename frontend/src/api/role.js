import request from '@/utils/request'
import { withErrorHandler } from '@/utils/errorHandler'

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

export function getAllRoles(params) {
  return request({
    url: '/roles/all',
    method: 'get',
    params,
  })
}

export function getPermissionTree(params) {
  return request({
    url: '/permissions/all',
    method: 'get',
    params,
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

export const roleApi = {
  getRoleList: withErrorHandler(getRoleList, { defaultValue: { list: [], pagination: {}, stats: {} } }),
  getRole: withErrorHandler(getRole),
  createRole: withErrorHandler(createRole),
  updateRole: withErrorHandler(updateRole),
  deleteRole: withErrorHandler(deleteRole),
  toggleRoleStatus: withErrorHandler(toggleRoleStatus),
  getAllRoles: withErrorHandler(getAllRoles, { defaultValue: [] }),
  getPermissionTree: withErrorHandler(getPermissionTree, { defaultValue: [] }),
  getPermissionList: withErrorHandler(getPermissionList, { defaultValue: [] }),
  createPermission: withErrorHandler(createPermission),
}
