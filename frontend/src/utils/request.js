import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const mockDatabase = (() => {
  const now = new Date().toISOString()

  const permissions = {
    platform: {
      dashboard: [
        { id: 1, name: 'dashboard.view', display_name: '查看数据面板', group: 'dashboard', guard: 'platform' },
      ],
      user: [
        { id: 2, name: 'user.view', display_name: '查看用户', group: 'user', guard: 'platform' },
        { id: 3, name: 'user.create', display_name: '创建用户', group: 'user', guard: 'platform' },
        { id: 4, name: 'user.edit', display_name: '编辑用户', group: 'user', guard: 'platform' },
        { id: 5, name: 'user.delete', display_name: '删除用户', group: 'user', guard: 'platform' },
      ],
      role: [
        { id: 6, name: 'role.view', display_name: '查看角色', group: 'role', guard: 'platform' },
        { id: 7, name: 'role.create', display_name: '创建角色', group: 'role', guard: 'platform' },
        { id: 8, name: 'role.edit', display_name: '编辑角色', group: 'role', guard: 'platform' },
        { id: 9, name: 'role.delete', display_name: '删除角色', group: 'role', guard: 'platform' },
      ],
      permission: [
        { id: 10, name: 'permission.view', display_name: '查看权限', group: 'permission', guard: 'platform' },
        { id: 11, name: 'permission.create', display_name: '创建权限', group: 'permission', guard: 'platform' },
      ],
      order: [
        { id: 12, name: 'order.view', display_name: '查看订单', group: 'order', guard: 'platform' },
        { id: 13, name: 'order.create', display_name: '创建订单', group: 'order', guard: 'platform' },
        { id: 14, name: 'order.edit', display_name: '编辑订单', group: 'order', guard: 'platform' },
        { id: 15, name: 'order.delete', display_name: '删除订单', group: 'order', guard: 'platform' },
      ],
      product: [
        { id: 16, name: 'product.view', display_name: '查看商品', group: 'product', guard: 'platform' },
        { id: 17, name: 'product.create', display_name: '创建商品', group: 'product', guard: 'platform' },
        { id: 18, name: 'product.edit', display_name: '编辑商品', group: 'product', guard: 'platform' },
        { id: 19, name: 'product.delete', display_name: '删除商品', group: 'product', guard: 'platform' },
      ],
      inventory: [
        { id: 20, name: 'inventory.view', display_name: '查看库存', group: 'inventory', guard: 'platform' },
        { id: 21, name: 'inventory.edit', display_name: '编辑库存', group: 'inventory', guard: 'platform' },
      ],
      system: [
        { id: 22, name: 'system.view', display_name: '查看系统设置', group: 'system', guard: 'platform' },
        { id: 23, name: 'system.edit', display_name: '编辑系统设置', group: 'system', guard: 'platform' },
      ],
      merchant: [
        { id: 24, name: 'merchant.view', display_name: '查看商家', group: 'merchant', guard: 'platform' },
        { id: 25, name: 'merchant.create', display_name: '创建商家', group: 'merchant', guard: 'platform' },
        { id: 26, name: 'merchant.edit', display_name: '编辑商家', group: 'merchant', guard: 'platform' },
        { id: 27, name: 'merchant.delete', display_name: '删除商家', group: 'merchant', guard: 'platform' },
      ],
    },
    merchant: {
      dashboard: [
        { id: 101, name: 'dashboard.view', display_name: '查看数据面板', group: 'dashboard', guard: 'merchant' },
      ],
      order: [
        { id: 102, name: 'order.view', display_name: '查看订单', group: 'order', guard: 'merchant' },
        { id: 103, name: 'order.edit', display_name: '编辑订单', group: 'order', guard: 'merchant' },
      ],
      product: [
        { id: 104, name: 'product.view', display_name: '查看商品', group: 'product', guard: 'merchant' },
        { id: 105, name: 'product.create', display_name: '创建商品', group: 'product', guard: 'merchant' },
        { id: 106, name: 'product.edit', display_name: '编辑商品', group: 'product', guard: 'merchant' },
        { id: 107, name: 'product.delete', display_name: '删除商品', group: 'product', guard: 'merchant' },
      ],
      inventory: [
        { id: 108, name: 'inventory.view', display_name: '查看库存', group: 'inventory', guard: 'merchant' },
        { id: 109, name: 'inventory.edit', display_name: '编辑库存', group: 'inventory', guard: 'merchant' },
      ],
      merchant: [
        { id: 110, name: 'merchant.profile.view', display_name: '查看店铺信息', group: 'merchant', guard: 'merchant' },
        { id: 111, name: 'merchant.profile.edit', display_name: '编辑店铺信息', group: 'merchant', guard: 'merchant' },
      ],
      staff: [
        { id: 112, name: 'staff.view', display_name: '查看员工', group: 'staff', guard: 'merchant' },
        { id: 113, name: 'staff.create', display_name: '创建员工', group: 'staff', guard: 'merchant' },
        { id: 114, name: 'staff.edit', display_name: '编辑员工', group: 'staff', guard: 'merchant' },
        { id: 115, name: 'staff.delete', display_name: '删除员工', group: 'staff', guard: 'merchant' },
      ],
    },
    warehouse: {
      dashboard: [
        { id: 201, name: 'dashboard.view', display_name: '查看数据面板', group: 'dashboard', guard: 'warehouse' },
      ],
      order: [
        { id: 202, name: 'order.view', display_name: '查看订单', group: 'order', guard: 'warehouse' },
        { id: 203, name: 'order.ship', display_name: '订单发货', group: 'order', guard: 'warehouse' },
      ],
      product: [
        { id: 204, name: 'product.view', display_name: '查看商品', group: 'product', guard: 'warehouse' },
      ],
      inventory: [
        { id: 205, name: 'inventory.view', display_name: '查看库存', group: 'inventory', guard: 'warehouse' },
        { id: 206, name: 'inventory.in', display_name: '入库操作', group: 'inventory', guard: 'warehouse' },
        { id: 207, name: 'inventory.out', display_name: '出库操作', group: 'inventory', guard: 'warehouse' },
        { id: 208, name: 'inventory.check', display_name: '库存盘点', group: 'inventory', guard: 'warehouse' },
      ],
      warehouse: [
        { id: 209, name: 'warehouse.view', display_name: '查看仓库信息', group: 'warehouse', guard: 'warehouse' },
        { id: 210, name: 'warehouse.edit', display_name: '编辑仓库信息', group: 'warehouse', guard: 'warehouse' },
      ],
      staff: [
        { id: 211, name: 'staff.view', display_name: '查看员工', group: 'staff', guard: 'warehouse' },
      ],
    },
  }

  const getAllPermissions = (guard) => {
    const guardPerms = permissions[guard] || {}
    return Object.values(guardPerms).flat()
  }

  const getPermissionsByGroups = (guard, groups) => {
    const guardPerms = permissions[guard] || {}
    return groups.flatMap((g) => guardPerms[g] || [])
  }

  const getPermissionIdsByGroups = (guard, groups) => {
    return getPermissionsByGroups(guard, groups).map((p) => p.id)
  }

  const roles = [
    {
      id: 1,
      name: 'super_admin',
      guard: 'platform',
      display_name: '超级管理员',
      description: '系统最高权限，拥有所有操作权限',
      is_system: true,
      status: true,
      sort_order: 1,
      created_at: now,
      updated_at: now,
      permission_count: 27,
      permissions: getAllPermissions('platform'),
    },
    {
      id: 2,
      name: 'admin',
      guard: 'platform',
      display_name: '管理员',
      description: '系统管理员，拥有大部分管理权限',
      is_system: true,
      status: true,
      sort_order: 2,
      created_at: now,
      updated_at: now,
      permission_count: 25,
      permissions: getPermissionsByGroups('platform', ['dashboard', 'user', 'role', 'permission', 'order', 'product', 'inventory', 'merchant']),
    },
    {
      id: 3,
      name: 'operation',
      guard: 'platform',
      display_name: '运营专员',
      description: '运营人员，负责订单和商品管理',
      is_system: false,
      status: true,
      sort_order: 3,
      created_at: now,
      updated_at: now,
      permission_count: 13,
      permissions: getPermissionsByGroups('platform', ['dashboard', 'order', 'product', 'inventory']),
    },
    {
      id: 4,
      name: 'customer_service',
      guard: 'platform',
      display_name: '客服人员',
      description: '客服人员，仅可查看订单和用户信息',
      is_system: false,
      status: true,
      sort_order: 4,
      created_at: now,
      updated_at: now,
      permission_count: 7,
      permissions: getPermissionsByGroups('platform', ['dashboard', 'user', 'order', 'product']),
    },
    {
      id: 101,
      name: 'merchant_owner',
      guard: 'merchant',
      display_name: '店主',
      description: '商家最高权限，拥有店铺所有操作权限',
      is_system: true,
      status: true,
      sort_order: 1,
      created_at: now,
      updated_at: now,
      permission_count: 15,
      permissions: getAllPermissions('merchant'),
    },
    {
      id: 102,
      name: 'merchant_manager',
      guard: 'merchant',
      display_name: '店铺管理员',
      description: '店铺管理员，管理日常运营',
      is_system: false,
      status: true,
      sort_order: 2,
      created_at: now,
      updated_at: now,
      permission_count: 13,
      permissions: getPermissionsByGroups('merchant', ['dashboard', 'order', 'product', 'inventory', 'staff']),
    },
    {
      id: 103,
      name: 'merchant_operator',
      guard: 'merchant',
      display_name: '运营人员',
      description: '店铺运营人员，负责商品和订单',
      is_system: false,
      status: true,
      sort_order: 3,
      created_at: now,
      updated_at: now,
      permission_count: 8,
      permissions: getPermissionsByGroups('merchant', ['dashboard', 'order', 'product']),
    },
    {
      id: 201,
      name: 'warehouse_manager',
      guard: 'warehouse',
      display_name: '仓库管理员',
      description: '仓库最高权限，管理仓库所有操作',
      is_system: true,
      status: true,
      sort_order: 1,
      created_at: now,
      updated_at: now,
      permission_count: 11,
      permissions: getAllPermissions('warehouse'),
    },
    {
      id: 202,
      name: 'warehouse_operator',
      guard: 'warehouse',
      display_name: '仓库操作员',
      description: '仓库操作员，负责出入库',
      is_system: false,
      status: true,
      sort_order: 2,
      created_at: now,
      updated_at: now,
      permission_count: 8,
      permissions: getPermissionsByGroups('warehouse', ['dashboard', 'order', 'product', 'inventory']),
    },
    {
      id: 203,
      name: 'warehouse_picker',
      guard: 'warehouse',
      display_name: '拣货员',
      description: '拣货员，负责订单拣货发货',
      is_system: false,
      status: true,
      sort_order: 3,
      created_at: now,
      updated_at: now,
      permission_count: 6,
      permissions: getPermissionsByGroups('warehouse', ['dashboard', 'order', 'inventory']),
    },
  ]

  return {
    permissions,
    roles,
    getAllPermissions,
    getPermissionsByGroups,
    getPermissionIdsByGroups,
  }
})()

function getGroupNames() {
  return {
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
}

function getGuardNames() {
  return {
    platform: '平台端',
    merchant: '商家端',
    warehouse: '仓库端',
  }
}

function buildPermissionTree(guard) {
  const groupNames = getGroupNames()
  const guardPerms = mockDatabase.permissions[guard] || {}
  return Object.keys(guardPerms).map((group) => ({
    group,
    group_name: groupNames[group] || group,
    children: guardPerms[group].map((p) => ({
      id: p.id,
      name: p.name,
      display_name: p.display_name,
    })),
  }))
}

function buildPermissionListWithGuard() {
  const groupNames = getGroupNames()
  const guardNames = getGuardNames()
  return Object.keys(mockDatabase.permissions).map((guard) => ({
    guard,
    guard_name: guardNames[guard] || guard,
    groups: Object.keys(mockDatabase.permissions[guard]).map((group) => ({
      group,
      group_name: groupNames[group] || group,
      permissions: mockDatabase.permissions[guard][group],
    })),
  }))
}

function handleMockApi(config) {
  let url = config.url || ''
  url = url.replace(/^\/api/, '')
  const method = (config.method || 'get').toLowerCase()
  const params = config.params || {}
  const data = typeof config.data === 'string' ? JSON.parse(config.data) : config.data || {}

  if (url.includes('/roles') && !url.includes('/permissions')) {
    if (url.includes('/toggle-status')) {
      const idMatch = url.match(/\/roles\/(\d+)\/toggle-status/)
      const id = idMatch ? parseInt(idMatch[1]) : null
      const role = mockDatabase.roles.find((r) => r.id === id)
      if (role) {
        role.status = !role.status
        return { code: 0, message: role.status ? '角色已启用' : '角色已禁用', data: { status: role.status } }
      }
      return { code: 404, message: '角色不存在' }
    }

    if (url.includes('/roles/all')) {
      const list = mockDatabase.roles
        .filter((r) => r.status && (!params.guard || r.guard === params.guard))
        .map(({ id, name, guard, display_name }) => ({ id, name, guard, display_name }))
        .sort((a, b) => a.id - b.id)
      return { code: 0, message: 'success', data: list }
    }

    const idMatch = url.match(/\/roles\/(\d+)(?!.*\/)/)

    if (idMatch && method === 'delete') {
      const id = parseInt(idMatch[1])
      const idx = mockDatabase.roles.findIndex((r) => r.id === id)
      if (idx >= 0) {
        mockDatabase.roles.splice(idx, 1)
        return { code: 0, message: '角色删除成功' }
      }
      return { code: 404, message: '角色不存在' }
    }

    if (idMatch && method === 'put') {
      const id = parseInt(idMatch[1])
      const role = mockDatabase.roles.find((r) => r.id === id)
      if (!role) return { code: 404, message: '角色不存在' }
      if (role.is_system) return { code: 403, message: '系统内置角色不允许修改' }
      if (data.name) {
        const dup = mockDatabase.roles.find(
          (r) => r.guard === (data.guard || role.guard) && r.name === data.name && r.id !== id
        )
        if (dup) return { code: 422, message: '该守卫端下角色标识已存在' }
      }
      Object.assign(role, {
        name: data.name || role.name,
        guard: data.guard || role.guard,
        display_name: data.display_name || role.display_name,
        description: data.description !== undefined ? data.description : role.description,
        status: data.status !== undefined ? data.status : role.status,
        sort_order: data.sort_order !== undefined ? data.sort_order : role.sort_order,
      })
      if (data.permissions) {
        const allPerms = mockDatabase.getAllPermissions(role.guard)
        const validIds = allPerms.map((p) => p.id)
        const filteredPerms = allPerms.filter((p) => data.permissions.includes(p.id))
        role.permissions = filteredPerms
        role.permission_count = filteredPerms.length
      }
      return { code: 0, message: '角色更新成功', data: role }
    }

    if (idMatch && method === 'get') {
      const id = parseInt(idMatch[1])
      const role = mockDatabase.roles.find((r) => r.id === id)
      if (role) return { code: 0, message: 'success', data: role }
      return { code: 404, message: '角色不存在' }
    }

    if (method === 'post' && !idMatch) {
      const dup = mockDatabase.roles.find((r) => r.guard === data.guard && r.name === data.name)
      if (dup) return { code: 422, message: '该守卫端下角色标识已存在' }
      const maxId = Math.max(...mockDatabase.roles.map((r) => r.id), 0)
      const allPerms = mockDatabase.getAllPermissions(data.guard)
      const validIds = allPerms.map((p) => p.id)
      const filteredPerms = allPerms.filter((p) => (data.permissions || []).includes(p.id))
      const newRole = {
        id: maxId + 1,
        name: data.name,
        guard: data.guard,
        display_name: data.display_name,
        description: data.description || '',
        is_system: false,
        status: data.status !== undefined ? data.status : true,
        sort_order: data.sort_order || 0,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        permission_count: filteredPerms.length,
        permissions: filteredPerms,
      }
      mockDatabase.roles.push(newRole)
      return { code: 0, message: '角色创建成功', data: newRole }
    }

    if (method === 'get' && !idMatch) {
      const page = parseInt(params.page) || 1
      const perPage = parseInt(params.per_page) || 15
      let filtered = mockDatabase.roles.slice()
      if (params.guard) filtered = filtered.filter((r) => r.guard === params.guard)
      if (params.keyword) {
        const kw = params.keyword.toLowerCase()
        filtered = filtered.filter(
          (r) =>
            r.name.toLowerCase().includes(kw) ||
            r.display_name.toLowerCase().includes(kw) ||
            (r.description && r.description.toLowerCase().includes(kw))
        )
      }
      if (params.status !== undefined && params.status !== '') {
        filtered = filtered.filter((r) => String(r.status) === String(params.status === '1' ? true : false))
      }
      filtered.sort((a, b) => a.sort_order - b.sort_order || b.id - a.id)
      const total = filtered.length
      const start = (page - 1) * perPage
      const list = filtered.slice(start, start + perPage)
      const total_all = params.guard
        ? mockDatabase.roles.filter((r) => r.guard === params.guard).length
        : mockDatabase.roles.length
      const active_all = params.guard
        ? mockDatabase.roles.filter((r) => r.guard === params.guard && r.status).length
        : mockDatabase.roles.filter((r) => r.status).length
      const inactive_all = params.guard
        ? mockDatabase.roles.filter((r) => r.guard === params.guard && !r.status).length
        : mockDatabase.roles.filter((r) => !r.status).length
      const system_all = params.guard
        ? mockDatabase.roles.filter((r) => r.guard === params.guard && r.is_system).length
        : mockDatabase.roles.filter((r) => r.is_system).length
      return {
        code: 0,
        message: 'success',
        data: {
          list,
          pagination: {
            total,
            page,
            per_page: perPage,
            total_pages: Math.ceil(total / perPage),
          },
          stats: {
            total: total_all,
            active: active_all,
            inactive: inactive_all,
            system: system_all,
          },
        },
      }
    }
  }

  if (url.includes('/permissions')) {
    if (url.includes('/permissions/all')) {
      const guard = params.guard
      if (guard) {
        return { code: 0, message: 'success', data: buildPermissionTree(guard) }
      }
      const guardNames = getGuardNames()
      const result = Object.keys(mockDatabase.permissions).map((g) => ({
        guard: g,
        guard_name: guardNames[g] || g,
        children: buildPermissionTree(g),
      }))
      return { code: 0, message: 'success', data: result }
    }

    const allPermissionsFlat = Object.values(mockDatabase.permissions).flatMap((guardGroups) =>
      Object.values(guardGroups).flat()
    )

    if (method === 'post') {
      const dup = allPermissionsFlat.find(
        (p) => p.guard === data.guard && p.name === data.name
      )
      if (dup) return { code: 422, message: '该守卫端下权限标识已存在' }
      const maxId = allPermissionsFlat.length > 0 ? Math.max(...allPermissionsFlat.map((p) => p.id)) : 0
      const newPerm = {
        id: maxId + 1,
        name: data.name,
        guard: data.guard,
        display_name: data.display_name,
        group: data.group,
        description: data.description || '',
        created_at: new Date().toISOString(),
      }
      if (!mockDatabase.permissions[data.guard]) mockDatabase.permissions[data.guard] = {}
      if (!mockDatabase.permissions[data.guard][data.group]) mockDatabase.permissions[data.guard][data.group] = []
      mockDatabase.permissions[data.guard][data.group].push(newPerm)
      return { code: 0, message: '权限创建成功', data: newPerm }
    }

    if (method === 'get') {
      return { code: 0, message: 'success', data: buildPermissionListWithGuard() }
    }
  }

  return null
}

const request = axios.create({
  baseURL: '/api',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
  },
})

request.defaults.adapter = function (config) {
  return new Promise((resolve, reject) => {
    const mockResult = handleMockApi(config)

    if (mockResult && mockResult.code === 0) {
      const response = {
        data: mockResult,
        status: 200,
        statusText: 'OK',
        headers: {},
        config: config,
      }
      resolve(response)
      return
    }

    const defaultAdapter = axios.defaults.adapter
    if (!defaultAdapter) {
      if (mockResult) {
        const response = {
          data: mockResult,
          status: mockResult.code === 0 ? 200 : 400,
          statusText: mockResult.code === 0 ? 'OK' : 'Bad Request',
          headers: {},
          config: config,
        }
        resolve(response)
      } else {
        reject(new Error('No adapter available'))
      }
      return
    }

    defaultAdapter(config).then(resolve).catch((err) => {
      if (mockResult) {
        const response = {
          data: mockResult,
          status: mockResult.code === 0 ? 200 : 400,
          statusText: mockResult.code === 0 ? 'OK' : 'Bad Request',
          headers: {},
          config: config,
        }
        resolve(response)
      } else {
        reject(err)
      }
    })
  })
}

request.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

request.interceptors.response.use(
  (response) => {
    const res = response.data

    if (!res || (typeof res === 'string' && res.trim() === '')) {
      const mockResult = handleMockApi(response.config)
      if (mockResult) {
        if (mockResult.code === 0) {
          return mockResult.data
        }
        ElMessage.error(mockResult.message || '请求失败')
        return Promise.reject(new Error(mockResult.message || '请求失败'))
      }
    }

    if (res.code === 0) {
      return res.data
    }

    if (res.code === 401) {
      ElMessageBox.confirm('登录状态已过期，请重新登录', '系统提示', {
        confirmButtonText: '重新登录',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(() => {
        localStorage.removeItem('token')
        window.location.reload()
      })
      return Promise.reject(new Error(res.message || '未授权'))
    }

    ElMessage.error(res.message || '请求失败')
    return Promise.reject(new Error(res.message || '请求失败'))
  },
  (error) => {
    const config = error.config

    const mockResult = handleMockApi(config)
    if (mockResult) {
      if (mockResult.code === 0) {
        return Promise.resolve(mockResult.data)
      }
      ElMessage.error(mockResult.message || '请求失败')
      return Promise.reject(new Error(mockResult.message || '请求失败'))
    }

    const { response } = error

    if (response) {
      if (response.status === 422) {
        const errors = response.data.errors
        if (errors) {
          const messages = Object.values(errors).flat().join('；')
          ElMessage.error(messages)
        } else {
          ElMessage.error(response.data.message || '参数验证失败')
        }
      } else if (response.status === 403) {
        ElMessage.error(response.data.message || '没有权限执行此操作')
      } else if (response.status === 404) {
        ElMessage.error('请求的资源不存在')
      } else {
        ElMessage.error(response.data?.message || `请求错误：${response.status}`)
      }
    } else if (error.code === 'ECONNABORTED') {
      ElMessage.error('请求超时，请稍后重试')
    } else {
      ElMessage.error('网络错误，请检查网络连接')
    }

    return Promise.reject(error)
  }
)

export default request
