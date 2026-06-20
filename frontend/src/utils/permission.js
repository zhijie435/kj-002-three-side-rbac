import { GUARD_TYPES, GUARD_NAMES, GUARD_TAG_TYPES, GROUP_NAMES, ROLE_STATUS_LABELS } from '@/constants/rbac'

export function getGuardName(guard) {
  return GUARD_NAMES[guard] || guard
}

export function getGuardTagType(guard) {
  return GUARD_TAG_TYPES[guard] || 'info'
}

export function getGroupName(group) {
  return GROUP_NAMES[group] || group
}

export function getRoleStatusLabel(status) {
  return ROLE_STATUS_LABELS[status] || '未知'
}

export function isValidGuard(guard) {
  return Object.values(GUARD_TYPES).includes(guard)
}

export function hasPermission(permissionName, userPermissions) {
  if (!permissionName || !userPermissions) {
    return false
  }

  if (userPermissions.isSuperAdmin) {
    return true
  }

  const permissions = userPermissions.permissions || []
  return permissions.includes(permissionName)
}

export function hasAnyPermission(permissionNames, userPermissions) {
  if (!permissionNames || !userPermissions || permissionNames.length === 0) {
    return false
  }

  if (userPermissions.isSuperAdmin) {
    return true
  }

  const permissions = userPermissions.permissions || []
  return permissionNames.some((perm) => permissions.includes(perm))
}

export function hasAllPermissions(permissionNames, userPermissions) {
  if (!permissionNames || !userPermissions || permissionNames.length === 0) {
    return false
  }

  if (userPermissions.isSuperAdmin) {
    return true
  }

  const permissions = userPermissions.permissions || []
  return permissionNames.every((perm) => permissions.includes(perm))
}

export function formatPermissionsByGroup(permissions) {
  if (!permissions || permissions.length === 0) {
    return []
  }

  const groups = {}
  permissions.forEach((perm) => {
    const groupKey = perm.group || 'default'
    if (!groups[groupKey]) {
      groups[groupKey] = {
        group: groupKey,
        groupName: getGroupName(groupKey),
        permissions: [],
      }
    }
    groups[groupKey].permissions.push(perm)
  })

  return Object.values(groups)
}

export function buildPermissionTree(permissions) {
  if (!permissions || permissions.length === 0) {
    return []
  }

  const groups = {}
  permissions.forEach((perm) => {
    const groupKey = perm.group || 'default'
    if (!groups[groupKey]) {
      groups[groupKey] = {
        group: groupKey,
        group_name: getGroupName(groupKey),
        children: [],
        checkedAll: false,
        isIndeterminate: false,
      }
    }
    groups[groupKey].children.push({
      id: perm.id,
      name: perm.name,
      display_name: perm.display_name,
    })
  })

  return Object.values(groups)
}
