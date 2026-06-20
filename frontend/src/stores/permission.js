import { reactive } from 'vue'
import { GUARD_TYPES } from '@/constants/rbac'

const state = reactive({
  user: null,
  permissions: [],
  roles: [],
  currentGuard: GUARD_TYPES.PLATFORM,
  isSuperAdmin: false,
  isLoaded: false,
})

export function usePermissionStore() {
  function setUser(user) {
    state.user = user
    state.isSuperAdmin = user?.is_super_admin || false
  }

  function setPermissions(permissions) {
    state.permissions = permissions || []
  }

  function setRoles(roles) {
    state.roles = roles || []
  }

  function setCurrentGuard(guard) {
    state.currentGuard = guard
  }

  function setLoaded(loaded) {
    state.isLoaded = loaded
  }

  function hasPermission(permissionName) {
    if (state.isSuperAdmin) {
      return true
    }
    return state.permissions.includes(permissionName)
  }

  function hasAnyPermission(permissionNames) {
    if (state.isSuperAdmin) {
      return true
    }
    return permissionNames.some((perm) => state.permissions.includes(perm))
  }

  function hasAllPermissions(permissionNames) {
    if (state.isSuperAdmin) {
      return true
    }
    return permissionNames.every((perm) => state.permissions.includes(perm))
  }

  function reset() {
    state.user = null
    state.permissions = []
    state.roles = []
    state.isSuperAdmin = false
    state.isLoaded = false
  }

  return {
    state,
    setUser,
    setPermissions,
    setRoles,
    setCurrentGuard,
    setLoaded,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    reset,
  }
}

export const permissionStore = usePermissionStore()
