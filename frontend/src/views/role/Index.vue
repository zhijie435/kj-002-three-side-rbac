<template>
  <div class="page-container role-overview-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">角色全览</h2>
        <p class="page-subtitle">管理系统角色及权限配置</p>
      </div>
      <el-button type="primary" :icon="Plus" @click="handleCreate">
        新建角色
      </el-button>
    </div>

    <el-tabs v-model="activeGuard" class="guard-tabs" @tab-change="handleGuardChange">
      <el-tab-pane name="platform">
        <template #label>
          <span style="display: flex; align-items: center; gap: 6px">
            <el-icon><Monitor /></el-icon>
            平台端
          </span>
        </template>
      </el-tab-pane>
      <el-tab-pane name="merchant">
        <template #label>
          <span style="display: flex; align-items: center; gap: 6px">
            <el-icon><Shop /></el-icon>
            商家端
          </span>
        </template>
      </el-tab-pane>
      <el-tab-pane name="warehouse">
        <template #label>
          <span style="display: flex; align-items: center; gap: 6px">
            <el-icon><Box /></el-icon>
            仓库端
          </span>
        </template>
      </el-tab-pane>
    </el-tabs>

    <el-row :gutter="16" class="stats-row" style="margin-bottom: 20px">
      <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
        <div class="stat-card">
          <div class="stat-icon primary">
            <el-icon :size="28"><UserFilled /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.total }}</div>
            <div class="stat-label">角色总数</div>
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
        <div class="stat-card">
          <div class="stat-icon success">
            <el-icon :size="28"><CircleCheck /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.active }}</div>
            <div class="stat-label">已启用</div>
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
        <div class="stat-card">
          <div class="stat-icon warning">
            <el-icon :size="28"><Warning /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.inactive }}</div>
            <div class="stat-label">已禁用</div>
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
        <div class="stat-card">
          <div class="stat-icon danger">
            <el-icon :size="28"><Lock /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.system }}</div>
            <div class="stat-label">系统角色</div>
          </div>
        </div>
      </el-col>
    </el-row>

    <div class="filter-bar">
      <el-form :inline="true" :model="filterForm" @submit.prevent>
        <el-form-item label="关键词">
          <el-input
            v-model="filterForm.keyword"
            placeholder="请输入角色名称/标识"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select
            v-model="filterForm.status"
            placeholder="全部状态"
            clearable
            style="width: 140px"
          >
            <el-option label="已启用" value="1" />
            <el-option label="已禁用" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="handleSearch">
            搜索
          </el-button>
          <el-button :icon="Refresh" @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </div>

    <div class="table-toolbar">
      <div>
        <span style="color: #909399; font-size: 13px">
          共 <b style="color: #303133">{{ pagination.total }}</b> 条记录
        </span>
      </div>
      <div>
        <el-button :icon="Refresh" @click="fetchRoleList">刷新</el-button>
      </div>
    </div>

    <el-table
      :data="roleList"
      v-loading="loading"
      border
      stripe
      style="width: 100%"
      :max-height="tableMaxHeight"
      :default-sort="{ prop: 'sort_order', order: 'ascending' }"
    >
      <el-table-column type="index" label="#" width="60" align="center" />
      <el-table-column
        prop="display_name"
        label="角色名称"
        min-width="140"
        sortable
      >
        <template #default="{ row }">
          <div style="display: flex; align-items: center; gap: 8px">
            <el-avatar :size="32" :icon="getRoleIcon(row.name)" />
            <div>
              <div style="font-weight: 500">{{ row.display_name }}</div>
              <div style="font-size: 12px; color: #909399">
                {{ row.name }}
              </div>
            </div>
          </div>
        </template>
      </el-table-column>
      <el-table-column
        prop="description"
        label="角色描述"
        min-width="200"
        show-overflow-tooltip
      />
      <el-table-column
        prop="permission_count"
        label="权限数量"
        width="100"
        align="center"
      >
        <template #default="{ row }">
          <el-tag type="primary" size="small">
            {{ row.permission_count || 0 }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="sort_order" label="排序" width="80" align="center" sortable />
      <el-table-column label="状态" width="140" align="center">
        <template #default="{ row }">
          <div style="display: flex; align-items: center; justify-content: center; gap: 8px">
            <el-switch
              v-model="row.status"
              :disabled="row.is_system"
              :loading="row._statusLoading"
              @change="(val) => handleToggleStatus(row, val)"
              active-text="启用"
              inactive-text="禁用"
              inline-prompt
            />
          </div>
        </template>
      </el-table-column>
      <el-table-column label="类型" width="100" align="center">
        <template #default="{ row }">
          <span v-if="row.is_system" class="status-tag system">系统内置</span>
          <span v-else class="status-tag">自定义</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" width="170" align="center">
        <template #default="{ row }">
          {{ formatDate(row.created_at) }}
        </template>
      </el-table-column>
      <el-table-column label="操作" width="260" fixed="right" align="center">
        <template #default="{ row }">
          <el-button
            type="primary"
            size="small"
            :icon="View"
            @click="handleView(row)"
          >
            查看
          </el-button>
          <el-button
            type="warning"
            size="small"
            :icon="Edit"
            :disabled="row.is_system"
            @click="handleEdit(row)"
          >
            编辑
          </el-button>
          <el-button
            type="danger"
            size="small"
            :icon="Delete"
            :disabled="row.is_system"
            @click="handleDelete(row)"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <div style="margin-top: 20px; display: flex; justify-content: flex-end">
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.per_page"
        :page-sizes="[10, 15, 20, 50, 100]"
        :total="pagination.total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
      />
    </div>

    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="720px"
      :close-on-click-modal="false"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="90px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="守卫端" prop="guard">
              <el-select
                v-model="formData.guard"
                placeholder="请选择守卫端"
                :disabled="isView || dialogType === 'edit'"
                style="width: 100%"
              >
                <el-option label="平台端" value="platform" />
                <el-option label="商家端" value="merchant" />
                <el-option label="仓库端" value="warehouse" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="角色标识" prop="name">
              <el-input
                v-model="formData.name"
                placeholder="如：admin"
                :disabled="isView"
                maxlength="50"
                show-word-limit
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="角色名称" prop="display_name">
              <el-input
                v-model="formData.display_name"
                placeholder="如：管理员"
                :disabled="isView"
                maxlength="100"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="排序">
              <el-input-number
                v-model="formData.sort_order"
                :min="0"
                :max="999"
                :disabled="isView"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="状态">
              <el-switch
                v-model="formData.status"
                :disabled="isView || formData.is_system"
                active-text="启用"
                inactive-text="禁用"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="角色描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="2"
            placeholder="请输入角色描述"
            :disabled="isView"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="权限配置">
          <div class="permission-config-wrapper">
            <div v-if="isView" style="color: #909399; font-size: 13px; margin-bottom: 8px">
              该角色拥有以下权限：
            </div>
            <div class="permission-scroll-container">
              <div
                v-for="group in permissionTree"
                :key="group.group"
                class="permission-group"
              >
                <div class="permission-group-title">
                  <el-checkbox
                    v-if="!isView"
                    v-model="group.checkedAll"
                    :indeterminate="group.isIndeterminate"
                    @change="handleCheckAllGroup($event, group)"
                  >
                    {{ group.group_name }}
                  </el-checkbox>
                  <span v-else style="font-weight: 600">{{ group.group_name }}</span>
                </div>
                <div class="permission-list">
                  <el-checkbox
                    v-for="item in group.children"
                    :key="item.id"
                    v-model="formData.permissions"
                    :label="item.id"
                    :disabled="isView"
                    class="permission-checkbox"
                    @change="handlePermissionChange(group)"
                  >
                    {{ item.display_name }}
                  </el-checkbox>
                </div>
              </div>
            </div>
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button
            v-if="!isView"
            type="primary"
            :loading="submitLoading"
            @click="handleSubmit"
          >
            确认提交
          </el-button>
        </div>
      </template>
    </el-dialog>

    <el-dialog
      v-model="detailVisible"
      title="角色详情"
      width="600px"
    >
      <el-descriptions :column="1" border v-if="currentRole">
        <el-descriptions-item label="守卫端">
          <el-tag :type="getGuardTagType(currentRole.guard)">
            {{ getGuardName(currentRole.guard) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="角色标识">
          <el-tag type="primary">{{ currentRole.name }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="角色名称">
          {{ currentRole.display_name }}
        </el-descriptions-item>
        <el-descriptions-item label="角色描述">
          {{ currentRole.description || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="角色类型">
          <span
            class="status-tag"
            :class="currentRole.is_system ? 'system' : ''"
          >
            {{ currentRole.is_system ? '系统内置' : '自定义角色' }}
          </span>
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="currentRole.status ? 'success' : 'info'">
            {{ currentRole.status ? '已启用' : '已禁用' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="排序">
          {{ currentRole.sort_order }}
        </el-descriptions-item>
        <el-descriptions-item label="权限列表">
          <div style="max-height: 200px; overflow-y: auto">
            <div
              v-for="group in groupedPermissions"
              :key="group.group"
              style="margin-bottom: 8px"
            >
              <div style="font-weight: 500; margin-bottom: 4px; color: #303133">
                {{ group.group_name }}
              </div>
              <div style="display: flex; flex-wrap: wrap; gap: 4px">
                <el-tag
                  v-for="perm in group.permissions"
                  :key="perm.id"
                  size="small"
                  type="success"
                  effect="light"
                >
                  {{ perm.display_name }}
                </el-tag>
              </div>
            </div>
          </div>
        </el-descriptions-item>
        <el-descriptions-item label="创建时间">
          {{ formatDate(currentRole.created_at) }}
        </el-descriptions-item>
        <el-descriptions-item label="更新时间">
          {{ formatDate(currentRole.updated_at) }}
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  Search,
  Refresh,
  View,
  Edit,
  Delete,
  UserFilled,
  CircleCheck,
  Warning,
  Lock,
  User,
  Goods,
  Document,
  Box,
  DataAnalysis,
  Setting,
  Monitor,
  Shop,
} from '@element-plus/icons-vue'
import {
  getRoleList,
  createRole,
  updateRole,
  deleteRole,
  toggleRoleStatus,
  getPermissionTree,
} from '@/api/role'

const loading = ref(false)
const submitLoading = ref(false)
const dialogVisible = ref(false)
const detailVisible = ref(false)
const dialogType = ref('create')
const currentRole = ref(null)
const roleList = ref([])
const permissionTree = ref([])
const formRef = ref(null)
const tableMaxHeight = ref(400)
const activeGuard = ref('platform')
const mainContentEl = ref(null)

const calculateTableHeight = () => {
  nextTick(() => {
    const headerHeight = 60
    const mainPadding = 40
    const pageHeaderHeight = 100
    const statCardsHeight = 140
    const filterBarHeight = 80
    const toolbarHeight = 50
    const paginationHeight = 60
    const tabsHeight = 60
    const otherSpacing = 40

    const availableHeight =
      window.innerHeight -
      headerHeight -
      mainPadding -
      pageHeaderHeight -
      statCardsHeight -
      filterBarHeight -
      toolbarHeight -
      paginationHeight -
      tabsHeight -
      otherSpacing

    tableMaxHeight.value = Math.max(availableHeight, 300)
  })
}

const handleResize = () => {
  calculateTableHeight()
}

const filterForm = reactive({
  keyword: '',
  status: '',
})

const pagination = reactive({
  page: 1,
  per_page: 15,
  total: 0,
  total_pages: 0,
})

const stats = reactive({
  total: 0,
  active: 0,
  inactive: 0,
  system: 0,
})

const formData = reactive({
  id: null,
  name: '',
  guard: 'platform',
  display_name: '',
  description: '',
  status: true,
  sort_order: 0,
  is_system: false,
  permissions: [],
})

const formRules = {
  name: [
    { required: true, message: '请输入角色标识', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' },
  ],
  display_name: [
    { required: true, message: '请输入角色名称', trigger: 'blur' },
    { min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' },
  ],
}

const isView = computed(() => dialogType.value === 'view')
const isEdit = computed(() => dialogType.value === 'edit')

const dialogTitle = computed(() => {
  const titles = {
    create: '新建角色',
    edit: '编辑角色',
    view: '查看角色',
  }
  return titles[dialogType.value] || '角色信息'
})

const groupedPermissions = computed(() => {
  if (!currentRole.value?.permissions) return []
  const groups = {}
  currentRole.value.permissions.forEach((perm) => {
    if (!groups[perm.group]) {
      groups[perm.group] = {
        group: perm.group,
        group_name: getGroupName(perm.group),
        permissions: [],
      }
    }
    groups[perm.group].permissions.push(perm)
  })
  return Object.values(groups)
})

function getRoleIcon(name) {
  const icons = {
    super_admin: UserFilled,
    admin: User,
    operation: DataAnalysis,
    customer_service: User,
    warehouse: Box,
  }
  return icons[name] || User
}

function getGroupName(group) {
  const names = {
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
  return names[group] || group
}

function getGuardName(guard) {
  const names = {
    platform: '平台端',
    merchant: '商家端',
    warehouse: '仓库端',
  }
  return names[guard] || guard
}

function getGuardTagType(guard) {
  const types = {
    platform: 'primary',
    merchant: 'success',
    warehouse: 'warning',
  }
  return types[guard] || 'info'
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function fetchRoleList() {
  loading.value = true
  try {
    const params = {
      guard: activeGuard.value,
      page: pagination.page,
      per_page: pagination.per_page,
      ...filterForm,
    }
    const data = await getRoleList(params)
    roleList.value = data.list.map((row) => ({
      ...row,
      status: Boolean(row.status),
      is_system: Boolean(row.is_system),
      _statusLoading: false,
    }))
    pagination.total = data.pagination.total
    pagination.total_pages = data.pagination.total_pages

    if (data.stats) {
      stats.total = data.stats.total
      stats.active = data.stats.active
      stats.inactive = data.stats.inactive
      stats.system = data.stats.system
    } else {
      stats.total = data.pagination.total
      stats.active = 0
      stats.inactive = 0
      stats.system = 0
    }
  } catch (error) {
    console.error('获取角色列表失败:', error)
  } finally {
    loading.value = false
  }
}

async function fetchPermissionTree(guard) {
  try {
    const data = await getPermissionTree({ guard: guard || activeGuard.value })
    permissionTree.value = data.map((group) => ({
      ...group,
      checkedAll: false,
      isIndeterminate: false,
    }))
  } catch (error) {
    console.error('获取权限列表失败:', error)
  }
}

function handleGuardChange() {
  filterForm.keyword = ''
  filterForm.status = ''
  pagination.page = 1
  fetchRoleList()
}

function handleSearch() {
  pagination.page = 1
  fetchRoleList()
}

function handleReset() {
  filterForm.keyword = ''
  filterForm.status = ''
  pagination.page = 1
  fetchRoleList()
}

function handleSizeChange(val) {
  pagination.per_page = val
  pagination.page = 1
  fetchRoleList()
}

function handlePageChange(val) {
  pagination.page = val
  fetchRoleList()
}

async function handleCreate() {
  await fetchPermissionTree()
  dialogType.value = 'create'
  resetForm()
  formData.guard = activeGuard.value
  dialogVisible.value = true
}

async function handleEdit(row) {
  await fetchPermissionTree(row.guard || activeGuard.value)
  dialogType.value = 'edit'
  currentRole.value = row
  formData.id = row.id
  formData.name = row.name
  formData.guard = row.guard || activeGuard.value
  formData.display_name = row.display_name
  formData.description = row.description || ''
  formData.status = Boolean(row.status)
  formData.sort_order = row.sort_order
  formData.is_system = Boolean(row.is_system)
  formData.permissions = row.permissions ? row.permissions.map((p) => p.id) : []

  updatePermissionCheckStates()
  dialogVisible.value = true
}

async function handleView(row) {
  await fetchPermissionTree(row.guard || activeGuard.value)
  dialogType.value = 'view'
  currentRole.value = {
    ...row,
    status: Boolean(row.status),
    is_system: Boolean(row.is_system),
  }
  formData.id = row.id
  formData.name = row.name
  formData.guard = row.guard || activeGuard.value
  formData.display_name = row.display_name
  formData.description = row.description || ''
  formData.status = Boolean(row.status)
  formData.sort_order = row.sort_order
  formData.is_system = Boolean(row.is_system)
  formData.permissions = row.permissions ? row.permissions.map((p) => p.id) : []

  updatePermissionCheckStates()
  detailVisible.value = true
}

function handleDelete(row) {
  ElMessageBox.confirm(
    `确定要删除角色「${row.display_name}」吗？删除后不可恢复。`,
    '删除确认',
    {
      confirmButtonText: '确定删除',
      cancelButtonText: '取消',
      type: 'warning',
    }
  )
    .then(async () => {
      try {
        await deleteRole(row.id)
        ElMessage.success('删除成功')
        fetchRoleList()
      } catch (error) {
        console.error('删除失败:', error)
      }
    })
    .catch(() => {})
}

async function handleToggleStatus(row, newStatus) {
  if (row.is_system) {
    ElMessage.warning('系统内置角色不允许修改状态')
    row.status = !newStatus
    return
  }

  const oldStatus = !newStatus
  row._statusLoading = true
  try {
    await toggleRoleStatus(row.id)
    ElMessage.success(newStatus ? '角色已启用' : '角色已禁用')
    fetchRoleList()
  } catch (error) {
    console.error('状态切换失败:', error)
    row.status = oldStatus
    ElMessage.error('状态切换失败')
  } finally {
    row._statusLoading = false
  }
}

function resetForm() {
  formData.id = null
  formData.name = ''
  formData.guard = activeGuard.value
  formData.display_name = ''
  formData.description = ''
  formData.status = true
  formData.sort_order = 0
  formData.is_system = false
  formData.permissions = []

  permissionTree.value.forEach((group) => {
    group.checkedAll = false
    group.isIndeterminate = false
  })

  if (formRef.value) {
    formRef.value.resetFields()
  }
}

function handleDialogClose() {
  resetForm()
  dialogType.value = 'create'
}

function updatePermissionCheckStates() {
  permissionTree.value.forEach((group) => {
    const groupIds = group.children.map((c) => c.id)
    const checkedCount = groupIds.filter((id) =>
      formData.permissions.includes(id)
    ).length

    group.checkedAll = checkedCount === groupIds.length && groupIds.length > 0
    group.isIndeterminate =
      checkedCount > 0 && checkedCount < groupIds.length
  })
}

function handleCheckAllGroup(val, group) {
  const groupIds = group.children.map((c) => c.id)
  if (val) {
    formData.permissions = [...new Set([...formData.permissions, ...groupIds])]
    group.checkedAll = true
    group.isIndeterminate = false
  } else {
    formData.permissions = formData.permissions.filter(
      (id) => !groupIds.includes(id)
    )
    group.checkedAll = false
    group.isIndeterminate = false
  }
}

function handlePermissionChange(group) {
  const groupIds = group.children.map((c) => c.id)
  const checkedCount = groupIds.filter((id) =>
    formData.permissions.includes(id)
  ).length

  group.checkedAll = checkedCount === groupIds.length && groupIds.length > 0
  group.isIndeterminate =
    checkedCount > 0 && checkedCount < groupIds.length
}

async function handleSubmit() {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitLoading.value = true
      try {
        const data = {
          name: formData.name,
          guard: formData.guard,
          display_name: formData.display_name,
          description: formData.description,
          status: formData.status,
          sort_order: formData.sort_order,
          permissions: formData.permissions,
        }

        if (dialogType.value === 'create') {
          await createRole(data)
          ElMessage.success('创建成功')
        } else if (dialogType.value === 'edit') {
          await updateRole(formData.id, data)
          ElMessage.success('更新成功')
        }

        dialogVisible.value = false
        fetchRoleList()
      } catch (error) {
        console.error('提交失败:', error)
      } finally {
        submitLoading.value = false
      }
    }
  })
}

onMounted(() => {
  calculateTableHeight()
  fetchRoleList()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.role-overview-page {
  min-width: 900px;
}

.guard-tabs {
  margin-bottom: 20px;
}

:deep(.guard-tabs .el-tabs__nav-wrap::after) {
  background-color: #e4e7ed;
}

:deep(.guard-tabs .el-tabs__item) {
  padding: 0 20px;
  font-size: 14px;
  height: 44px;
  line-height: 44px;
}

:deep(.guard-tabs .el-tabs__active-bar) {
  height: 3px;
}

.stats-row {
  padding: 16px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 8px;
}

.stats-row .stat-card {
  background: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.permission-config-wrapper {
  width: 100%;
}

.permission-scroll-container {
  max-height: 280px;
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 8px;
}

.permission-scroll-container::-webkit-scrollbar {
  width: 6px;
}

.permission-scroll-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.permission-scroll-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.permission-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

.permission-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0;
}

:deep(.el-dialog) {
  margin-top: 5vh !important;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

:deep(.el-dialog .el-dialog__header) {
  flex-shrink: 0;
  margin-right: 0;
  padding-bottom: 15px;
}

:deep(.el-dialog .el-dialog__body) {
  flex: 1 1 auto;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 20px;
  padding-right: 12px;
  box-sizing: border-box;
  max-height: none;
}

:deep(.el-dialog .el-dialog__footer) {
  flex-shrink: 0;
  border-top: 1px solid #e4e7ed;
  padding-top: 15px;
  padding-bottom: 15px;
  padding-right: 20px;
  text-align: right;
}

:deep(.el-dialog__body)::-webkit-scrollbar {
  width: 6px;
}

:deep(.el-dialog__body)::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

:deep(.el-dialog__body)::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

:deep(.el-dialog__body)::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>
