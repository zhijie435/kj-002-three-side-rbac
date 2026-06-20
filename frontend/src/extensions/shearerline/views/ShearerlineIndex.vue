<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">剪切线管理</h2>
        <p class="page-subtitle">管理生产剪切线配置及运行状态</p>
      </div>
      <el-button type="primary" :icon="Plus" @click="handleCreate">
        新建剪切线
      </el-button>
    </div>

    <el-row :gutter="16" style="margin-bottom: 20px">
      <el-col :span="4">
        <div class="stat-card">
          <div class="stat-icon primary">
            <el-icon :size="28"><Tools /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.total }}</div>
            <div class="stat-label">剪切线总数</div>
          </div>
        </div>
      </el-col>
      <el-col :span="4">
        <div class="stat-card">
          <div class="stat-icon success">
            <el-icon :size="28"><CircleCheck /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.idle }}</div>
            <div class="stat-label">空闲</div>
          </div>
        </div>
      </el-col>
      <el-col :span="4">
        <div class="stat-card">
          <div class="stat-icon running">
            <el-icon :size="28"><VideoPlay /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.running }}</div>
            <div class="stat-label">运行中</div>
          </div>
        </div>
      </el-col>
      <el-col :span="4">
        <div class="stat-card">
          <div class="stat-icon warning">
            <el-icon :size="28"><Setting /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.maintenance }}</div>
            <div class="stat-label">维护中</div>
          </div>
        </div>
      </el-col>
      <el-col :span="4">
        <div class="stat-card">
          <div class="stat-icon danger">
            <el-icon :size="28"><Warning /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.error }}</div>
            <div class="stat-label">故障</div>
          </div>
        </div>
      </el-col>
      <el-col :span="4">
        <div class="stat-card">
          <div class="stat-icon info">
            <el-icon :size="28"><List /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ totalTasks }}</div>
            <div class="stat-label">待处理任务</div>
          </div>
        </div>
      </el-col>
    </el-row>

    <div class="filter-bar">
      <el-form :inline="true" :model="filterForm" @submit.prevent>
        <el-form-item label="关键词">
          <el-input
            v-model="filterForm.keyword"
            placeholder="请输入编码/名称"
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
            <el-option label="空闲" value="idle" />
            <el-option label="运行中" value="running" />
            <el-option label="维护中" value="maintenance" />
            <el-option label="故障" value="error" />
            <el-option label="已停用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item label="类型">
          <el-select
            v-model="filterForm.type"
            placeholder="全部类型"
            clearable
            style="width: 140px"
          >
            <el-option label="平板剪切" value="plate" />
            <el-option label="卷材剪切" value="coil" />
            <el-option label="精密剪切" value="precision" />
            <el-option label="粗剪切" value="rough" />
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
        <el-button :icon="Refresh" @click="fetchList">刷新</el-button>
        <el-button type="success" :icon="View" @click="goToTasks">
          任务管理
        </el-button>
      </div>
    </div>

    <el-table
      :data="shearerList"
      v-loading="loading"
      border
      stripe
      style="width: 100%"
      :default-sort="{ prop: 'sort_order', order: 'ascending' }"
    >
      <el-table-column type="index" label="#" width="60" align="center" />
      <el-table-column
        prop="code"
        label="编码"
        width="120"
        sortable
      >
        <template #default="{ row }">
          <el-tag type="info" size="small">{{ row.code }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column
        prop="name"
        label="名称"
        min-width="140"
        sortable
      >
        <template #default="{ row }">
          <div style="font-weight: 500">{{ row.name }}</div>
        </template>
      </el-table-column>
      <el-table-column prop="type" label="类型" width="120" />
      <el-table-column prop="location" label="位置" width="120" />
      <el-table-column label="负载" width="180">
        <template #default="{ row }">
          <div style="display: flex; align-items: center; gap: 8px">
            <el-progress
              :percentage="row.load_ratio || 0"
              :status="getLoadStatus(row)"
              :stroke-width="10"
              style="flex: 1"
            />
            <span style="font-size: 12px; color: #909399">
              {{ row.current_load }}/{{ row.max_capacity }}
            </span>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="任务数" width="120" align="center">
        <template #default="{ row }">
          <div>
            <el-tag type="warning" size="small" style="margin-right: 4px">
              {{ row.pending_task_count || 0 }}
            </el-tag>
            <el-tag type="success" size="small">
              {{ row.completed_task_count || 0 }}
            </el-tag>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="100" align="center">
        <template #default="{ row }">
          <span
            class="status-tag"
            :class="getStatusClass(row.status)"
          >
            {{ row.status_label }}
          </span>
        </template>
      </el-table-column>
      <el-table-column prop="sort_order" label="排序" width="80" align="center" sortable />
      <el-table-column label="创建时间" width="170" align="center">
        <template #default="{ row }">
          {{ formatDate(row.created_at) }}
        </template>
      </el-table-column>
      <el-table-column label="操作" width="280" fixed="right" align="center">
        <template #default="{ row }">
          <el-dropdown @command="(cmd) => handleStatusChange(cmd, row)" trigger="click">
            <el-button type="primary" size="small" :icon="Operation">
              状态
              <el-icon class="el-icon--right"><ArrowDown /></el-icon>
            </el-button>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="start" :disabled="row.status === 'running'">
                  <el-icon><VideoPlay /></el-icon> 启动
                </el-dropdown-item>
                <el-dropdown-item command="stop" :disabled="row.status === 'idle' || row.status === 'disabled'">
                  <el-icon><VideoPause /></el-icon> 停止
                </el-dropdown-item>
                <el-dropdown-item command="maintenance">
                  <el-icon><Setting /></el-icon> 维护
                </el-dropdown-item>
                <el-dropdown-item command="error" divided>
                  <el-icon><Warning /></el-icon> 报障
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
          <el-button
            type="warning"
            size="small"
            :icon="Edit"
            @click="handleEdit(row)"
          >
            编辑
          </el-button>
          <el-button
            type="danger"
            size="small"
            :icon="Delete"
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
      width="640px"
      :close-on-click-modal="false"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="剪切线编码" prop="code">
              <el-input
                v-model="formData.code"
                placeholder="如：SL-001"
                maxlength="50"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="剪切线名称" prop="name">
              <el-input
                v-model="formData.name"
                placeholder="如：1号平板剪切线"
                maxlength="100"
                show-word-limit
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="类型" prop="type">
              <el-select v-model="formData.type" style="width: 100%">
                <el-option label="平板剪切" value="plate" />
                <el-option label="卷材剪切" value="coil" />
                <el-option label="精密剪切" value="precision" />
                <el-option label="粗剪切" value="rough" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="位置">
              <el-input
                v-model="formData.location"
                placeholder="如：A车间-1区"
                maxlength="100"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="最大产能">
              <el-input-number
                v-model="formData.max_capacity"
                :min="0"
                :max="99999"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="当前负载">
              <el-input-number
                v-model="formData.current_load"
                :min="0"
                :max="99999"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="状态">
              <el-select v-model="formData.status" style="width: 100%">
                <el-option label="空闲" value="idle" />
                <el-option label="运行中" value="running" />
                <el-option label="维护中" value="maintenance" />
                <el-option label="故障" value="error" />
                <el-option label="已停用" value="disabled" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="排序">
              <el-input-number
                v-model="formData.sort_order"
                :min="0"
                :max="999"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="请输入描述信息"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button
            type="primary"
            :loading="submitLoading"
            @click="handleSubmit"
          >
            确认提交
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  Search,
  Refresh,
  Edit,
  Delete,
  View,
  Tools,
  CircleCheck,
  VideoPlay,
  VideoPause,
  Setting,
  Warning,
  List,
  Operation,
  ArrowDown,
} from '@element-plus/icons-vue'
import {
  getShearerlineList,
  createShearerline,
  updateShearerline,
  deleteShearerline,
  startShearerline,
  stopShearerline,
  setMaintenanceShearerline,
  setErrorShearerline,
} from '@/extensions/shearerline/api/shearerline'

const router = useRouter()
const loading = ref(false)
const submitLoading = ref(false)
const dialogVisible = ref(false)
const dialogType = ref('create')
const shearerList = ref([])
const formRef = ref(null)

const filterForm = reactive({
  keyword: '',
  status: '',
  type: '',
})

const pagination = reactive({
  page: 1,
  per_page: 15,
  total: 0,
  total_pages: 0,
})

const stats = reactive({
  total: 0,
  idle: 0,
  running: 0,
  maintenance: 0,
  error: 0,
})

const formData = reactive({
  id: null,
  code: '',
  name: '',
  type: 'plate',
  location: '',
  status: 'idle',
  max_capacity: 100,
  current_load: 0,
  operator_id: null,
  description: '',
  sort_order: 0,
})

const formRules = {
  code: [
    { required: true, message: '请输入剪切线编码', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' },
  ],
  name: [
    { required: true, message: '请输入剪切线名称', trigger: 'blur' },
    { min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' },
  ],
  type: [
    { required: true, message: '请选择剪切线类型', trigger: 'change' },
  ],
}

const totalTasks = computed(() => {
  return shearerList.value.reduce((sum, item) => sum + (item.pending_task_count || 0), 0)
})

const dialogTitle = computed(() => {
  const titles = {
    create: '新建剪切线',
    edit: '编辑剪切线',
  }
  return titles[dialogType.value] || '剪切线信息'
})

function getStatusClass(status) {
  const classes = {
    idle: 'idle',
    running: 'active',
    maintenance: 'warning',
    error: 'inactive',
    disabled: 'system',
  }
  return classes[status] || ''
}

function getLoadStatus(row) {
  const ratio = row.load_ratio || 0
  if (ratio >= 90) return 'exception'
  if (ratio >= 70) return 'warning'
  return 'success'
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

async function fetchList() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      per_page: pagination.per_page,
      ...filterForm,
    }
    const data = await getShearerlineList(params)
    shearerList.value = data.list
    pagination.total = data.pagination.total
    pagination.total_pages = data.pagination.total_pages

    if (data.stats) {
      stats.total = data.stats.total
      stats.idle = data.stats.idle
      stats.running = data.stats.running
      stats.maintenance = data.stats.maintenance
      stats.error = data.stats.error
    }
  } catch (error) {
    console.error('获取剪切线列表失败:', error)
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  pagination.page = 1
  fetchList()
}

function handleReset() {
  filterForm.keyword = ''
  filterForm.status = ''
  filterForm.type = ''
  pagination.page = 1
  fetchList()
}

function handleSizeChange(val) {
  pagination.per_page = val
  pagination.page = 1
  fetchList()
}

function handlePageChange(val) {
  pagination.page = val
  fetchList()
}

function handleCreate() {
  dialogType.value = 'create'
  resetForm()
  dialogVisible.value = true
}

function handleEdit(row) {
  dialogType.value = 'edit'
  formData.id = row.id
  formData.code = row.code
  formData.name = row.name
  formData.type = row.type
  formData.location = row.location || ''
  formData.status = row.status
  formData.max_capacity = row.max_capacity
  formData.current_load = row.current_load
  formData.description = row.description || ''
  formData.sort_order = row.sort_order
  dialogVisible.value = true
}

function handleDelete(row) {
  ElMessageBox.confirm(
    `确定要删除剪切线「${row.name}」吗？删除后关联任务将解除绑定。`,
    '删除确认',
    {
      confirmButtonText: '确定删除',
      cancelButtonText: '取消',
      type: 'warning',
    }
  )
    .then(async () => {
      try {
        await deleteShearerline(row.id)
        ElMessage.success('删除成功')
        fetchList()
      } catch (error) {
        console.error('删除失败:', error)
      }
    })
    .catch(() => {})
}

async function handleStatusChange(action, row) {
  try {
    let message = ''
    switch (action) {
      case 'start':
        await startShearerline(row.id)
        message = '剪切线已启动'
        break
      case 'stop':
        await stopShearerline(row.id)
        message = '剪切线已停止'
        break
      case 'maintenance':
        await setMaintenanceShearerline(row.id)
        message = '剪切线设置为维护中'
        break
      case 'error':
        await setErrorShearerline(row.id)
        message = '剪切线已报障'
        break
    }
    ElMessage.success(message)
    fetchList()
  } catch (error) {
    console.error('状态变更失败:', error)
  }
}

function goToTasks() {
  router.push('/shearerline/tasks')
}

function resetForm() {
  formData.id = null
  formData.code = ''
  formData.name = ''
  formData.type = 'plate'
  formData.location = ''
  formData.status = 'idle'
  formData.max_capacity = 100
  formData.current_load = 0
  formData.description = ''
  formData.sort_order = 0

  if (formRef.value) {
    formRef.value.resetFields()
  }
}

function handleDialogClose() {
  resetForm()
  dialogType.value = 'create'
}

async function handleSubmit() {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitLoading.value = true
      try {
        const data = {
          code: formData.code,
          name: formData.name,
          type: formData.type,
          location: formData.location,
          status: formData.status,
          max_capacity: formData.max_capacity,
          current_load: formData.current_load,
          description: formData.description,
          sort_order: formData.sort_order,
        }

        if (dialogType.value === 'create') {
          await createShearerline(data)
          ElMessage.success('创建成功')
        } else if (dialogType.value === 'edit') {
          await updateShearerline(formData.id, data)
          ElMessage.success('更新成功')
        }

        dialogVisible.value = false
        fetchList()
      } catch (error) {
        console.error('提交失败:', error)
      } finally {
        submitLoading.value = false
      }
    }
  })
}

onMounted(() => {
  fetchList()
})
</script>

<style scoped>
.stat-icon.running {
  background: linear-gradient(135deg, #67c23a, #85ce61);
}

.stat-icon.info {
  background: linear-gradient(135deg, #909399, #a6a9ad);
}
</style>
