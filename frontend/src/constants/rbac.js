export const GUARD_TYPES = {
  PLATFORM: 'platform',
  MERCHANT: 'merchant',
  WAREHOUSE: 'warehouse',
}

export const GUARD_NAMES = {
  [GUARD_TYPES.PLATFORM]: '平台端',
  [GUARD_TYPES.MERCHANT]: '商家端',
  [GUARD_TYPES.WAREHOUSE]: '仓库端',
}

export const GUARD_TAG_TYPES = {
  [GUARD_TYPES.PLATFORM]: 'primary',
  [GUARD_TYPES.MERCHANT]: 'success',
  [GUARD_TYPES.WAREHOUSE]: 'warning',
}

export const GROUP_NAMES = {
  user: '用户管理',
  role: '角色管理',
  permission: '权限管理',
  order: '订单管理',
  product: '商品管理',
  inventory: '库存管理',
  system: '系统设置',
  dashboard: '数据面板',
  merchant: '商家管理',
  staff: '员工管理',
  warehouse: '仓库管理',
}

export const ROLE_STATUS = {
  ACTIVE: true,
  INACTIVE: false,
}

export const ROLE_STATUS_LABELS = {
  [ROLE_STATUS.ACTIVE]: '已启用',
  [ROLE_STATUS.INACTIVE]: '已禁用',
}

export const ERROR_CODES = {
  SUCCESS: 0,
  BAD_REQUEST: 400,
  UNAUTHORIZED: 401,
  FORBIDDEN: 403,
  NOT_FOUND: 404,
  VALIDATION_FAILED: 422,
  SERVER_ERROR: 500,
}
